<?php

namespace backend\app\src\core\services\Blockchain;

use backend\app\src\core\domain\Blockchain\Transaction;

interface BlockchainInterface
{
    public function addTransaction(Transaction $transaction): void;

    public function getTransactions(): array;

    public function getBalance(string $account): float;
}
