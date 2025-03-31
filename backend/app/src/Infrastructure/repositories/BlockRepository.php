<?php

namespace boz\Infrastructure\repositories;

use boz\core\domain\Blockchain\Transaction;
use boz\core\domain\Blockchain\Block;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use PDO;
use Ramsey\Uuid\Uuid;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class BlockRepository implements BlockRepositoryInterface
{

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getBalanceByUserId(string $userId): float
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE 
                    WHEN t.type = 'add' THEN t.amount 
                    WHEN t.type = 'pay' THEN -t.amount 
                    ELSE 0 
                END), 0) AS balance
            FROM users u
            LEFT JOIN transactions t ON u.login = t.account
            WHERE u.id = :user_id
        ");
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return floatval($result['balance']) ?? 0.0;
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors du calcul du solde : " . $e->getMessage());
        }
    }

    public function getHistoryByUserId(string $userId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.id AS transaction_id,
                    t.account,
                    t.amount,
                    t.type,
                    t.account AS user_login,
                    b.id AS block_id,
                    b.hash AS block_hash,
                    b.previous_hash,
                    b.timestamp AS block_timestamp
                FROM users u
                JOIN transactions t ON u.login = t.account
                LEFT JOIN blocks b ON b.transaction_id = t.id
                WHERE u.id = :user_id
                ORDER BY b.timestamp DESC
            ");

            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération de l'historique : " . $e->getMessage());
        }
    }

    public function getLoginByUserId(string $userId): string
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT login
                FROM users
                WHERE id = :user_id
            ");

            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new RepositoryEntityNotFoundException("Utilisateur non trouvé.");
            }

            return $result['login'];
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération du login : " . $e->getMessage());
        }
    }

    public function createFacture($login, float $tarif, string $label): void
    {
        try {
            $factureId = Uuid::uuid4()->toString();


            $qrCodeData = [
                'id' => $factureId,
                'tarif' => $tarif,
                'label' => $label,
                'seller' => $login
            ];

            $qrCodeJson = json_encode($qrCodeData);

            $qrCode = new QrCode($qrCodeJson);
            $writer = new PngWriter();
            $result = $writer->writeString($qrCode);

            $stmt = $this->pdo->prepare("
            INSERT INTO facture (id, seller_login, qr_code, label, amount, status)
            VALUES (:id, :seller_login, :qr_code, :label, :amount, :status)
        ");

            $stmt->bindValue('id', $factureId);
            $stmt->bindValue('seller_login', $login);
            $stmt->bindValue('qr_code', $result, PDO::PARAM_LOB);
            $stmt->bindValue('label', $label);
            $stmt->bindValue('amount', $tarif);
            $stmt->bindValue('status', 'non payée');

            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erreur lors de la création de la facture : " . $e->getMessage());
            throw new RepositoryEntityNotFoundException("Erreur lors de la création de la facture : " . $e->getMessage());
        } catch (RepositoryEntityNotFoundException $e) {
        }
    }

    public function payFacture(string $factureId, string $userId, string $userLogin): void
{
    try {
        // Récupérer les détails de la facture
        $stmt = $this->pdo->prepare("SELECT seller_login, amount, status FROM facture WHERE id = :id");
        $stmt->execute(['id' => $factureId]);
        $facture = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$facture) {
            throw new RepositoryEntityNotFoundException("La facture avec l'ID {$factureId} n'existe pas.");
        }

        if ($facture['status'] === 'payée') {
            throw new RepositoryEntityNotFoundException("La facture avec l'ID {$factureId} est déjà payée.");
        }

        $sellerLogin = $facture['seller_login'];
        $amount = (float) $facture['amount'];

        // Vérifier si l'acheteur a suffisamment de fonds
        if (!$this->isTransactionValid($userId, $amount, 'pay')) {
            throw new Exception("Solde insuffisant pour effectuer ce paiement.");
        }

        // Récupérer l'ID du vendeur à partir de son login
        $sellerIdStmt = $this->pdo->prepare("SELECT id FROM users WHERE login = :login");
        $sellerIdStmt->execute(['login' => $sellerLogin]);
        $sellerId = $sellerIdStmt->fetchColumn();

        if (!$sellerId) {
            throw new Exception("Vendeur avec login {$sellerLogin} non trouvé.");
        }

        $this->pdo->beginTransaction();
        
        try {
            // Utiliser addBlock avec les IDs et indiquer de ne pas gérer les transactions
            $this->addBlock($userId, $amount, 'pay', false);
            $this->addBlock($sellerId, $amount, 'add', false);
            
            // Mettre à jour le statut de la facture
            $this->pdo->prepare("
                UPDATE facture
                SET status = :status
                WHERE id = :id
            ")->execute([
                'status' => 'payée',
                'id' => $factureId
            ]);
            
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    } catch (Exception $e) {
        throw new RepositoryEntityNotFoundException("Erreur lors du paiement de la facture : " . $e->getMessage());
    }
}
    public function getLastBlock(): array
    {
        try {
            $stmt = $this->pdo->query("
            SELECT * FROM blocks
            ORDER BY timestamp DESC
            LIMIT 1
        ");
            $block = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$block) {
                throw new RepositoryEntityNotFoundException("Aucun bloc trouvé.");
            }

            return $block;
        } catch (Exception $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération du dernier bloc : " . $e->getMessage());
        }
    }

    public function addBlock(string $userId, float $amount, string $type, bool $manageTransaction = true): void
{
    try {
        // Vérifier la validité de la transaction
        if (!$this->isTransactionValid($userId, $amount, $type)) {
            throw new Exception("Transaction invalide pour l'utilisateur avec ID {$userId}.");
        }

        // Récupérer le login de l'utilisateur à partir de son ID
        $userLoginStmt = $this->pdo->prepare("SELECT login FROM users WHERE id = :id");
        $userLoginStmt->execute(['id' => $userId]);
        $userLogin = $userLoginStmt->fetchColumn();
        
        if (!$userLogin) {
            throw new Exception("Utilisateur avec ID {$userId} non trouvé.");
        }

        $transactionId = Uuid::uuid4()->toString();
        
        // Démarrer une transaction seulement si on gère la transaction dans cette méthode
        if ($manageTransaction && !$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }

        // Utiliser le login comme account dans la transaction
        $transactionStmt = $this->pdo->prepare("
            INSERT INTO transactions (id, account, amount, type)
            VALUES (:id, :account, :amount, :type)
        ");
        $transactionStmt->execute([
            'id' => $transactionId,
            'account' => $userLogin,  // Utiliser le login, pas l'ID
            'amount' => $amount,
            'type' => $type
        ]);

        $lastBlock = $this->getLastBlock();

        $newBlockStmt = $this->pdo->prepare("
            INSERT INTO blocks (id, hash, previous_hash, transaction_id, timestamp)
            VALUES (:id, :hash, :previous_hash, :transaction_id, to_timestamp(:timestamp))
        ");
        $blockId = Uuid::uuid4()->toString();
        $blockHash = hash('sha256', $blockId . $transactionId . time());

        $newBlockStmt->execute([
            'id' => $blockId,
            'hash' => $blockHash,
            'previous_hash' => $lastBlock['hash'],
            'transaction_id' => $transactionId,
            'timestamp' => time()
        ]);

        // Faire un commit seulement si on gère la transaction dans cette méthode
        if ($manageTransaction && $this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    } catch (Exception $e) {
        // Faire un rollback seulement si on gère la transaction dans cette méthode
        if ($manageTransaction && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        throw new Exception("Erreur lors de l'ajout d'un nouveau bloc : " . $e->getMessage());
    }
}

    public function isTransactionValid(string $userId, float $amount, string $type): bool
    {
        if ($type === 'pay') {
            $balance = $this->getBalanceByUserId($userId);
            if ($balance < $amount) {
                return false;
            }
        }
        return true;
    }

    public function getFactureById(string $factureId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *
                FROM facture
                WHERE id = :id
            ");

            $stmt->execute(['id' => $factureId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new RepositoryEntityNotFoundException("Facture non trouvée.");
            }

            return $result;
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération de la facture : " . $e->getMessage());
        }
    }

    public function getFacturesByUserLogin(string $userLogin): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT *
                FROM facture
                WHERE seller_login = :seller_login
            ");

            $stmt->execute(['seller_login' => $userLogin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération des factures : " . $e->getMessage());
        }
    }

    public function createGenesisBlock(string $adminLogin): void
    {
        try {
            $genesisTransactionId = Uuid::uuid4()->toString();
            $this->pdo->beginTransaction();

            $genesisTransactionStmt = $this->pdo->prepare("
            INSERT INTO transactions (id, account, amount, type)
            VALUES (:id, :account, :amount, :type)
        ");
            $genesisTransactionStmt->execute([
                'id' => $genesisTransactionId,
                'account' => $adminLogin,
                'amount' => 0.0,
                'type' => 'add'
            ]);

            $genesisBlockStmt = $this->pdo->prepare("
            INSERT INTO blocks (id, hash, previous_hash, transaction_id, timestamp)
            VALUES (:id, :hash, :previous_hash, :transaction_id, to_timestamp(:timestamp))
        ");
            $genesisBlockId = Uuid::uuid4()->toString();
            $genesisBlockHash = hash('sha256', $genesisBlockId . $genesisTransactionId . time());

            $genesisBlockStmt->execute([
                'id' => $genesisBlockId,
                'hash' => $genesisBlockHash,
                'previous_hash' => '0',
                'transaction_id' => $genesisTransactionId,
                'timestamp' => time()
            ]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erreur lors de la création du bloc de genèse : " . $e->getMessage());
        }
    }


}

