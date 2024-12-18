<?php

namespace backend\app\src\core\services\Blockchain;

use backend\app\src\core\domain\Blockchain\Transaction;
use backend\app\src\core\domain\Blockchain\Block;
use backend\app\src\core\dto\BlockDTO;
use backend\app\src\core\dto\TransactionDTO;
use backend\app\src\core\repositoryInterfaces\BlockRepositoryInterface;
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







}
