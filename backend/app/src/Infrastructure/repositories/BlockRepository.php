<?php

namespace backend\app\src\Infrastructure\repositories;

use backend\app\src\core\repositoryInterfaces\BlockRepositoryInterface;
use backend\app\src\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use PDO;

class BlockRepository implements BlockRepositoryInterface
{


    private PDO $pdo;


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAccountBalance(string $account): float
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(CASE 
                        WHEN t.type = 'add' THEN t.price 
                        WHEN t.type = 'pay' THEN -t.price 
                        ELSE 0 
                    END) AS balance
                FROM transactions t
                WHERE t.account = :account
            ");
            $stmt->execute(['account' => $account]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['balance'] ?? 0.0;
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors du calcul du solde : " . $e->getMessage());
        }
    }


    public function getAccountHistory(string $account): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.id AS transaction_id, 
                    t.account, 
                    t.price, 
                    t.type, 
                    b.id AS block_id, 
                    b.timestamp
                FROM transactions t
                LEFT JOIN blocks b ON b.transaction_id = t.id
                WHERE t.account = :account
                ORDER BY b.timestamp DESC
            ");
            $stmt->execute(['account' => $account]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryEntityNotFoundException("Erreur lors de la récupération de l'historique : " . $e->getMessage());
        }
    }





}

