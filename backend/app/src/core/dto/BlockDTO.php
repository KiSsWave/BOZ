<?php

namespace backend\app\src\core\dto;

use backend\app\src\core\domain\Blockchain\Block;

class BlockDTO extends DTO
{
    public string $ID;
    public string $hash;
    public string $previousHash;
    public TransactionDTO $transaction;

    public function __construct(Block $block)
    {
        $this->ID  = $block->getID();
        $this->hash = $block->hash;
        $this->previousHash = $block->previousHash;
        $this->transaction = new TransactionDTO($block->transaction);
    }
}
