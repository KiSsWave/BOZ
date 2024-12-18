<?php

namespace boz\core\domain\Blockchain;
use boz\core\domain\Entity;

class Transaction extends Entity
{
    public string $account;
    public float $price;

    public string $type;

    public function __construct(string $a, float $p, string $t)
    {
        $this->account = $a;
        $this->price = $p;
        $this->type = $t;
    }




}