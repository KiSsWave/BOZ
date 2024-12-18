<?php

namespace boz\test;

use boz\infrastructure\repositories\BlockRepository;
use PDO;
use PHPUnit\Framework\TestCase;

class BlockRepositoryTest extends TestCase
{
    private PDO $pdo;
    private BlockRepository $blockRepository;

    protected function setUp(): void
    {

        $container = require '/var/www/html/BOZ/backend/app/config/dependencies.php';
        $this->pdo = $container->get(PDO::class);


        $this->pdo->beginTransaction();

        // Injecter la connexion PDO dans le BlockRepository
        $this->blockRepository = new BlockRepository($this->pdo);

        // Préparer les données de test
        $this->setUpTestData();
    }

    private function setUpTestData(): void
    {
        // Insérer des données spécifiques pour les tests
        $this->pdo->exec("
            INSERT INTO users (id, login, email, password, role)
            VALUES ('123e4567-e89b-12d3-a456-426614174000', 'user1', 'user1@example.com', 'hashed_password', 1);
        ");
        $this->pdo->exec("
            INSERT INTO transactions (id, account, price, type)
            VALUES ('trans-001', 'user1', 500.00, 'add'),
                   ('trans-002', 'user1', 150.00, 'pay');
        ");
        $this->pdo->exec("
            INSERT INTO blocks (id, hash, previous_hash, transaction_id)
            VALUES ('block-001', 'abc123hash', 'prev001', 'trans-001'),
                   ('block-002', 'def456hash', 'abc123hash', 'trans-002');
        ");
    }

    protected function tearDown(): void
    {
        // Annuler la transaction pour restaurer l'état initial de la base
        $this->pdo->rollBack();
    }

    public function testGetBalanceByUserId(): void
    {
        // Tester le calcul du solde
        $balance = $this->blockRepository->getBalanceByUserId('123e4567-e89b-12d3-a456-426614174000');
        $this->assertEquals(350.00, $balance); // 500 (add) - 150 (pay)
    }

    public function testGetHistoryByUserId(): void
    {
        // Tester l'historique des transactions
        $history = $this->blockRepository->getHistoryByUserId('123e4567-e89b-12d3-a456-426614174000');

        $this->assertCount(2, $history); // Deux transactions
        $this->assertEquals('trans-001', $history[0]['transaction_id']);
        $this->assertEquals('add', $history[0]['type']);
    }
}
