<?php

namespace backend\app\src\core\domain\Blockchain;

use backend\app\src\core\domain\Entity;

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
}
