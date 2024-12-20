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





}

