<?php

namespace boz\core\dto;

use boz\core\domain\Blockchain\Transaction;

class TransactionDTO extends DTO
{
    protected string $ID;
    protected string $account;
    protected float $price;
    protected string $type;

    public function __construct(Transaction $transaction)
    {
        $this->ID = $transaction->getID();
        $this->account = $transaction->account;
        $this->price = $transaction->price;
        $this->type = $transaction->type;
    }
}
