<?php

namespace boz\Infrastructure\repositories;

use boz\core\domain\Blockchain\Transaction;
use boz\core\domain\Blockchain\Block;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use PDO;
use PHPUnit\Framework\Exception;

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

    #[\Override] public function addTransaction(Transaction $t, Block $b): void
    {
        try {
            $stmt = $this->pdo->prepare( "INSERT INTO transactions (id, account, price, type)VALUES (:id, :account, :price, :type)");

            $stmt->bindValue(':id', $t->getId(), PDO::PARAM_STR);
            $stmt->bindValue(':account', $t->getAccount(), PDO::PARAM_STR);
            $stmt->bindValue(':price', $t->getPrice(), PDO::PARAM_STR);
            $stmt->bindValue(':type', $t->getType(), PDO::PARAM_STR);

            $stmt->execute();

        } catch (\PDOException $e) {
            throw new \RuntimeException('Erreur lors d ajout de la transaction : ' . $e->getMessage());
        }

        try{
            $insert = $this->pdo->prepare("IN");
        }catch (Exception $e){
            throw new Exception("Erreur lors d'un ajout");
        }
    }
}

