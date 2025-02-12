<?php

namespace boz\Infrastructure\repositories;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use Exception;

use PDO;
use Ramsey\Uuid\Uuid;

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

    public function createFacture(string $login, float $tarif, string $label): void
    {
        try {
            $factureId = Uuid::uuid4()->toString();


            $qrCode = new QrCode($factureId);
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
        }
    }

    public function payFacture(string $factureId, string $buyerId): void
    {
        try {

            $stmt = $this->pdo->prepare("
            SELECT seller_login, amount, status 
            FROM facture 
            WHERE id = :id
        ");
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


            if (!$this->isTransactionValid($buyerId, $amount, 'pay')) {
                throw new Exception("Solde insuffisant pour l'utilisateur {$buyerId}.");
            }

            $this->pdo->beginTransaction();

            $buyerTransactionId = Uuid::uuid4()->toString();
            $debitStmt = $this->pdo->prepare("
                INSERT INTO transactions (id, account, amount, type)
                VALUES (:id, :account, :amount, :type)
            ");
            $debitStmt->execute([
                'id' => $buyerTransactionId,
                'account' => $buyerId,
                'amount' => $amount,
                'type' => 'pay'
            ]);
            $sellerTransactionId = Uuid::uuid4()->toString();
            $creditStmt = $this->pdo->prepare("
                INSERT INTO transactions (id, account, amount, type)
                VALUES (:id, :account, :amount, :type)
            ");
            $creditStmt->execute([
                'id' => $sellerTransactionId,
                'account' => $sellerLogin,
                'amount' => $amount,
                'type' => 'add'
            ]);


            $updateStmt = $this->pdo->prepare("
            UPDATE facture
            SET status = :status
            WHERE id = :id
        ");
            $updateStmt->execute([
                'status' => 'payée',
                'id' => $factureId
            ]);


            $this->addBlock($buyerId, $amount, 'pay');
            $this->addBlock($sellerLogin, $amount, 'add');

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur lors du paiement de la facture : " . $e->getMessage());
            throw new RepositoryEntityNotFoundException("Erreur lors du paiement de la facture : " . $e->getMessage());
        }
    }


    public function createGenesisBlock(?string $userId): void
    {
        try {
            $transactionId = Uuid::uuid4()->toString();
            $this->pdo->beginTransaction();

            $transactionStmt = $this->pdo->prepare("
            INSERT INTO transactions (id, account, amount, type)
            VALUES (:id, :account, :amount, :type)
        ");
            $transactionStmt->execute([
                'id' => $transactionId,
                'account' => $userId,
                'amount' => 0.0,
                'type' => 'add'
            ]);

            $blockStmt = $this->pdo->prepare("
            INSERT INTO blocks (id, hash, previous_hash, transaction_id, timestamp)
            VALUES (:id, :hash, :previous_hash, :transaction_id, to_timestamp(:timestamp))
        ");
            $blockStmt->execute([
                'id' => Uuid::uuid4()->toString(),
                'hash' => hash('sha256', 'genesis'),
                'previous_hash' => '0',
                'transaction_id' => $transactionId,
                'timestamp' => time()
            ]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erreur lors de la création du bloc Genesis : " . $e->getMessage());
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

    public function addBlock(string $userId, float $amount, string $type): void
    {
        try {
            if (!$this->isTransactionValid($userId, $amount, $type)) {
                throw new Exception("Transaction invalide pour l'utilisateur {$userId}.");
            }

            $transactionId = Uuid::uuid4()->toString();
            $this->pdo->beginTransaction();

            $transactionStmt = $this->pdo->prepare("
            INSERT INTO transactions (id, account, amount, type)
            VALUES (:id, :account, :amount, :type)
        ");
            $transactionStmt->execute([
                'id' => $transactionId,
                'account' => $userId,
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

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
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


}

