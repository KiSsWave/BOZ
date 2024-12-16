<?php

namespace backend\src\Blockchain;
class Transaction
{
    public string $account;
    public float $price;

    public string $type; //pay or add

    public function __construct(string $a, float $p, string $t)
    {
        $this->account = $a;
        $this->price = $p;
        $this->type = $t;
    }




}