<?php

namespace boz\core\domain\Blockchain;

use boz\core\domain\Entity;

class Block extends Entity {
    public string $previousHash;
    public int $timestamp;
    public string $hash;
    public string $account;
    public float $amount;
    public string $emitter;
    public string $receiver;

    public function __construct(string $account, float $amount, string $emitter, string $receiver, string $previousHash = '') {
        $this->account = $account;
        $this->amount = $amount;
        $this->emitter = $emitter;
        $this->receiver = $receiver;
        $this->previousHash = $previousHash;
        $this->timestamp = time();
        $this->hash = $this->calculateHash();
    }

    public function calculateHash(): string
    {
        return hash('sha256', $this->previousHash . $this->timestamp . $this->account . $this->amount . $this->emitter . $this->receiver);
    }

    public function getAccount(): string
    {
        return $this->account;
    }

    
    public function getAmount(): float
    {
        return $this->amount;
    }
    

    public function getEmitter(): string
    {
        return $this->emitter;
    }
    

    public function getReceiver(): string
    {
        return $this->receiver;
    }
    
    public function isPayment(): bool
    {

        return $this->emitter === $this->account && $this->emitter !== $this->receiver;
    }
    
    public function isReceived(): bool
    {
        return $this->receiver === $this->account && $this->emitter !== $this->receiver;
    }
    
    
    public function isCreation(): bool
    {
       
        return $this->emitter === $this->receiver;
    }
}