<?php

namespace boz\core\services\Blockchain;

use boz\core\domain\Blockchain\Transaction;
use boz\core\domain\Blockchain\Block;
use boz\core\dto\BlockDTO;
use boz\core\dto\TransactionDTO;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use Exception;

class BlockService implements BlockServiceInterface
{
    private BlockRepositoryInterface $blockRepository;


    public function __construct(BlockRepositoryInterface $b)
    {
        $this->blockRepository = $b;
    }

    public function afficherSolde(string $id):float{
        return $this->blockRepository->getBalanceByUserId($id);
    }

    public function afficherHistorique(string $id):array{
        return $this->blockRepository->getHistoryByUserId($id);
    }

    public function creerFacture(string $userId,float $tarif, string $label):void{
        $this->blockRepository->createFacture($userId,$tarif, $label);
    }

    public function payerFacture(string $factureId):void{
        $this->blockRepository->payFacture($factureId);
    }







}
