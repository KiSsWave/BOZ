<?php

namespace boz\core\repositoryInterfaces;

interface BlockRepositoryInterface
{
    public function getBalanceByUserId(string $userId): float;
    
    public function getHistoryByUserId(string $userId): array;
    
    public function getLoginByUserId(string $userId): string;
    
    public function createFacture($login, float $tarif, string $label, ?string $buyerLogin = null): void;
    
    public function payFacture(string $factureId, string $userId, string $userLogin): void;
    
    public function getLastBlock(): array;
    
    public function addBlock(string $accountLogin, float $amount, string $emitter, string $receiver): void;
    
    public function isTransactionValid(string $userId, float $amount): bool;
    
    public function getFactureById(string $factureId): array;
    
    public function getFacturesByUserLogin(string $userLogin): array;
    
    public function createGenesisBlock(string $adminLogin): void;
}