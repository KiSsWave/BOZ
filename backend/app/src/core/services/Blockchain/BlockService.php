<?php

namespace boz\core\services\Blockchain;

use boz\core\domain\Blockchain\Block;
use boz\core\dto\BlockDTO;
use boz\core\repositoryInterfaces\BlockRepositoryInterface;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use Exception;

class BlockService implements BlockServiceInterface
{
    private BlockRepositoryInterface $blockRepository;

    public function __construct(BlockRepositoryInterface $b)
    {
        $this->blockRepository = $b;
    }

    public function afficherSolde(string $id): float
    {
        return $this->blockRepository->getBalanceByUserId($id);
    }

    public function afficherHistorique(string $id): array
    {
        return $this->blockRepository->getHistoryByUserId($id);
    }

    public function creerFacture(string $login, float $tarif, string $label, ?string $buyerLogin = null): void
    {
        $this->blockRepository->createFacture($login, $tarif, $label, $buyerLogin);
    }

    public function payerFacture(string $factureId, string $userId, string $userLogin): void
    {
        $this->blockRepository->payFacture($factureId, $userId, $userLogin);
    }

    public function giveCash(string $adminLogin, string $userLogin, float $amount): void
    {
        try {
            $hasBlocks = true;
            try {
                $this->blockRepository->getLastBlock();
            } catch (RepositoryEntityNotFoundException $e) {
                $hasBlocks = false;
            }

            if (!$hasBlocks) {
                $this->blockRepository->createGenesisBlock($adminLogin);
            }
            
            $this->blockRepository->addBlock($userLogin, $amount, $adminLogin, $userLogin);
            
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getFactureById(string $factureId): array
    {
        return $this->blockRepository->getFactureById($factureId);
    }

    public function getFacturesByUserLogin(string $userLogin): array
    {
        return $this->blockRepository->getFacturesByUserLogin($userLogin);
    }
}