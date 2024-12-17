<?php

namespace backend\app\src\core\services\Blockchain;

use backend\app\src\core\domain\Blockchain\Transaction;

class BlockchainService implements BlockchainInterface {

    private BlockchainInterface $blockchainRepository;

    public function __construct(BlockchainInterface $blockchainRepository) {
        $this->blockchainRepository = $blockchainRepository;
    }

    public function addTransaction(Transaction $transaction): void
    {
        $this->blockchainRepository->addTransaction($transaction);
    }

    public function getTransactions(): array
    {
        return $this->blockchainRepository->getTransactions();
    }

    public function getBalance(string $account): float
    {
        return $this->blockchainRepository->getBalance($account);
    }










}