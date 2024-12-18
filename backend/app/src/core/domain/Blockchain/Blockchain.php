<?php

namespace boz\core\domain\Blockchain;

use Exception;

class Blockchain
{
    public array $blocks;

    public function __construct() {
        $this->blocks = [$this->createGenesisBlock()];
    }
    public function createGenesisBlock(): Block {
        return new Block(new Transaction("admin", 10000.0, "add"), "0");
    }

    public function getLastBlock(): Block {
        return $this->blocks[count($this->blocks) - 1];
    }

    public function addBlock(Transaction $t) : void {
        if (!$this->isTransactionValid($t)) {
            throw new Exception("Transaction invalide");
        }
        $previousBlock = $this->getLastBlock();
        $newBlock = new Block($t, $previousBlock->hash);
        $this->blocks[] = $newBlock;
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
    public function getBalance(string $account): float {
        $balance = 0.0;
        foreach ($this->blocks as $block) {
            $transaction = $block->transaction;
            if ($transaction->account === $account) {
                if ($transaction->type === "add") {
                    $balance += $transaction->price;
                } elseif ($transaction->type === "pay") {
                    $balance -= $transaction->price;
                }
            }
        }

        return $balance;
    }
}
