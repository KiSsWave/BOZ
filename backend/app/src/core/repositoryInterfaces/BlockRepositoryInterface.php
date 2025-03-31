<?php

namespace boz\core\repositoryInterfaces;

interface BlockRepositoryInterface
{
    public function getBalanceByUserId(string $userId): array;
    
    public function getHistoryByUserId(string $userId): array;
    
    public function getLoginByUserId(string $userId): string;
    
    public function createFacture($login, float $tarif, string $label, ?string $buyerLogin = null): void;
    
    public function payFacture(string $factureId, string $userId, string $userLogin, string $role): void;
    
    public function getLastBlock(): array;
    
    public function addBlock(string $accountLogin, float $amount, string $emitter, string $receiver, string $role): void;
    
    public function isTransactionValid(string $userId, float $amount, string $role): bool;
    
    public function getFactureById(string $factureId): array;
    
    public function getFacturesByUserLogin(string $userLogin): array;

    public function getFacturesByBuyerLogin(string $buyerLogin): array;
    
    public function createGenesisBlock(string $adminLogin): void;
}