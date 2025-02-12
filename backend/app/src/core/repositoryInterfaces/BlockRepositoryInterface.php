<?php

namespace boz\core\repositoryInterfaces;

use boz\core\domain\Blockchain\Block;
use boz\core\domain\Blockchain\Transaction;

interface BlockRepositoryInterface
{
    public function getBalanceByUserId(string $userId): float;
    public function getHistoryByUserId(string $userId): array;
    public function createFacture(string $userId, float $tarif, string $label): void;
    public function payFacture(string $factureId): void;

    public function addTransaction(Transaction $t,Block $b): void;


}