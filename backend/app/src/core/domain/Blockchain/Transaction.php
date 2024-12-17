<?php

namespace backend\app\src\core\domain\Blockchain;
use backend\app\src\core\domain\Entity;

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