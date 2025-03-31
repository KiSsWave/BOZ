<?php

namespace boz\core\services\Blockchain;

interface BlockServiceInterface
{
    public function afficherSolde(string $id): float;
    
    public function afficherHistorique(string $id): array;
    
    public function creerFacture(string $login, float $tarif, string $label, ?string $buyerLogin = null): void;
    
    public function payerFacture(string $factureId, string $userId, string $userLogin): void;
    
    public function giveCash(string $adminLogin, string $userLogin, float $amount): void;
    
    public function getFactureById(string $factureId): array;
    
    public function getFacturesByUserLogin(string $userLogin): array;
}