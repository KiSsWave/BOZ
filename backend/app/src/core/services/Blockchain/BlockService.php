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


    public function getBalance(string $account): float
    {
        $balance = 0;
        $blocks = $this->blockRepository->getAllBlocks();
        foreach ($blocks as $block) {
            if ($block->transaction->account === $account) {
                if ($block->transaction->type === 'credit') {
                    $balance += $block->transaction->price;
                } else {
                    $balance -= $block->transaction->price;
                }
            }
        }
        return $balance;
    }





}
