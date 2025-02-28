<?php

namespace boz\core\services\Blockchain;

use boz\core\domain\Blockchain\Block;
use boz\core\domain\Blockchain\Transaction;

interface AuthnServiceInterface
{

    public function afficherSolde(string $id):float;
    public function afficherHistorique(string $id):array;
    public function creerFacture(string $login,float $tarif, string $label):void;
    public function payerFacture(string $factureId,string $buyerId):void;

    public function giveCash(string $adminLogin, string $userLogin, float $amount): void;

    public function getFactureById(string $factureId): array;

    public function getFacturesByUserLogin(string $userLogin): array;

}
