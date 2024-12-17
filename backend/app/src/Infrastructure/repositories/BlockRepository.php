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

