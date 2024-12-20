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
                    SUM(CASE 
                        WHEN t.type = 'add' THEN t.price 
                        WHEN t.type = 'pay' THEN -t.price 
                        ELSE 0 
                    END) AS balance
                FROM users u
                JOIN transactions t ON u.login = t.account
                WHERE u.id = :user_id
            ");
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['balance'] ?? 0.0;
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
                t.price,
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

    public function createFacture(string $userId,float $tarif, string $label): void
    {
        try {
            $factureId = Uuid::uuid4()->toString();
            $directoryPath = __DIR__ . '/../../../public/qr/';
            $relativePath = "/qr/{$factureId}.png";
            $qrCodePath = $directoryPath . "{$factureId}.png";

            $qrCode = new QrCode($factureId);
            $writer = new PngWriter();
            $writer->writeFile($qrCode, $qrCodePath);

            if (!file_exists($qrCodePath)) {
                throw new Exception("Le fichier QR Code n'a pas été créé : {$qrCodePath}");
            }

            $stmt = $this->pdo->prepare("
            INSERT INTO facture (id, seller_login, qr_link, label, amount, status)
            VALUES (:id, :seller_login, :qr_link, :label, :amount, :status)
        ");
            $stmt->execute([
                'id' => $factureId,
                'seller_login' => $userId,
                'qr_link' => $relativePath,
                'label' => $label,
                'amount' => $tarif,
                'status' => 'non payée'
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors de la création de la facture : " . $e->getMessage());
            throw new RepositoryEntityNotFoundException("Erreur lors de la création de la facture : " . $e->getMessage());
        }
    }

    public function payFacture(string $factureId): void
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT status FROM facture WHERE id = :id
        ");
            $stmt->execute(['id' => $factureId]);
            $facture = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$facture) {
                throw new  RepositoryEntityNotFoundException("La facture avec l'ID {$factureId} n'existe pas.");
            }

            if ($facture['status'] === 'payée') {
                throw new  RepositoryEntityNotFoundException("La facture avec l'ID {$factureId} est déjà payée.");
            }


            $updateStmt = $this->pdo->prepare("
            UPDATE facture
            SET status = :status
            WHERE id = :id
        ");
            $updateStmt->execute([
                'status' => 'payée',
                'id' => $factureId
            ]);

        } catch (Exception $e) {
            error_log("Erreur lors du paiement de la facture : " . $e->getMessage());
            throw new RepositoryEntityNotFoundException("Erreur lors du paiement de la facture : " . $e->getMessage());
        }
    }

    public function createGenesisBlock(): void
    {
        try {
            $transactionId = Uuid::uuid4()->toString();
            $this->pdo->beginTransaction();


            $transactionStmt = $this->pdo->prepare("
            INSERT INTO transactions (id, account, price, type)
            VALUES (:id, :account, :price, :type)
        ");
            $transactionStmt->execute([
                'id' => $transactionId,
                'account' => 'admin',
                'price' => 10000.0,
                'type' => 'add'
            ]);

            // Insérer le bloc Genesis dans la table `blocks`
            $blockStmt = $this->pdo->prepare("
            INSERT INTO blocks (id, hash, previous_hash, transaction_id, timestamp)
            VALUES (:id, :hash, :previous_hash, :transaction_id, :timestamp)
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

    public function addBlock(string $userId, float $price, string $type): void
    {
        try {
            // Valider la transaction
            if (!$this->isTransactionValid($userId, $price, $type)) {
                throw new Exception("Transaction invalide pour l'utilisateur {$userId}.");
            }

            $transactionId = Uuid::uuid4()->toString();
            $this->pdo->beginTransaction();

            // Insérer la transaction
            $transactionStmt = $this->pdo->prepare("
            INSERT INTO transactions (id, account, price, type)
            VALUES (:id, :account, :price, :type)
        ");
            $transactionStmt->execute([
                'id' => $transactionId,
                'account' => $userId,
                'price' => $price,
                'type' => $type
            ]);

            // Récupérer le dernier bloc
            $lastBlock = $this->getLastBlock();

            // Générer un nouveau bloc
            $newBlockStmt = $this->pdo->prepare("
            INSERT INTO blocks (id, hash, previous_hash, transaction_id, timestamp)
            VALUES (:id, :hash, :previous_hash, :transaction_id, :timestamp)
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

    public function isTransactionValid(string $userId, float $price, string $type): bool
    {
        if ($type === 'pay') {
            $balance = $this->getBalanceByUserId($userId);
            if ($balance < $price) {
                return false;
            }
        }
        return true;
    }


}

