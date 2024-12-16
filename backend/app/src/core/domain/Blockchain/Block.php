<?php

namespace backend\app\src\core\domain\Blockchain;

class Block {
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
}
