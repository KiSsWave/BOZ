<?php

namespace boz\core\repositoryInterfaces;

use boz\core\domain\Blockchain\Block;
use boz\core\domain\Blockchain\Transaction;

interface BlockRepositoryInterface
{
    public function getBalanceByUserId(string $userId): float;
    public function getHistoryByUserId(string $userId): array;
    public function createFacture(string $login, float $tarif, string $label): void;
    public function payFacture(string $factureId, string $buyerId): void;
    public function getFactureById(string $factureId): array;
    public function getFacturesByUserLogin(string $userLogin): array;
    public function addBlock(string $userLogin, float $amount, string $type): void;
    public function createGenesisBlock(string $adminLogin): void;
    public function getLastBlock(): array;



}