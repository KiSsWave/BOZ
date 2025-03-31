<?php

namespace boz\core\dto;

use boz\core\domain\Blockchain\Block;

class BlockDTO extends DTO
{
    public string $ID;
    public string $hash;
    public string $previousHash;
    public string $account;
    public float $amount;
    public string $emitter;
    public string $receiver;
    public int $timestamp;
    public string $transactionType; 
    public function __construct(Block $block)
    {
        $this->ID = $block->getID();
        $this->hash = $block->hash;
        $this->previousHash = $block->previousHash;
        $this->account = $block->getAccount();
        $this->amount = $block->getAmount();
        $this->emitter = $block->getEmitter();
        $this->receiver = $block->getReceiver();
        $this->timestamp = $block->timestamp;
        
        if ($block->isPayment()) {
            $this->transactionType = 'pay';
        } elseif ($block->isReceived()) {
            $this->transactionType = 'add';
        } elseif ($block->isCreation()) {
            $this->transactionType = 'add';
        } else {
            $this->transactionType = 'unknown';
        }
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
    
   
    public function getTransactionType(): string
    {
        return $this->transactionType;
    }
}