<?php

namespace boz\core\domain\Blockchain;

use boz\core\domain\Entity;

class Block extends Entity {
    public string $previousHash;
    public int $timestamp;
    public Transaction $transaction;
    public string $hash;

    public function __construct($transaction, $previousHash = '') {
        $this->previousHash = $previousHash;
        $this->timestamp = time();
        $this->transaction = $transaction;
        $this->hash = $this->calculateHash();
    }

    public function calculateHash(): string
    {
        return hash('sha256', $this->previousHash . $this->timestamp . json_encode($this->transaction));
    }

    public function isTransactionValid(Transaction $transaction): bool {
        if ($transaction->type === "pay") {
            $balance = $this->getBalance($transaction->account);
            if ($balance < $transaction->price) {
                throw new Exception("Solde insuffisant pour la transaction de type 'pay'. Solde actuel de $transaction->account : $balance");
            }
        }
        return true;
    }
}
