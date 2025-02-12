<?php

namespace boz\core\services\Blockchain;

use boz\core\domain\Blockchain\Block;
use boz\core\domain\Blockchain\Transaction;

interface BlockServiceInterface
{

    public function afficherSolde(string $id):float;
    public function afficherHistorique(string $id):array;
    public function creerFacture(string $userId,float $tarif, string $label):void;
    public function payerFacture(string $factureId):void;

}
