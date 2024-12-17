<?php

namespace backend\app\src\core\services\Blockchain;

use backend\app\src\core\domain\Blockchain\Block;
use backend\app\src\core\domain\Blockchain\Transaction;

interface BlockServiceInterface
{
    public function createGenesisBlock(): Block;
    public function getLastBlock(): Block;

    public function getBalance(string $account): float;

    public function addBlock(Transaction $t): void;

    public function isTransactionValid(Transaction $transaction): bool;
}
