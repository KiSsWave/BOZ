<?php

namespace backend\app\src\Infrastructure\repositories;

use backend\app\src\core\repositoryInterfaces\BlockRepositoryInterface;
use PDO;

class BlockRepository implements BlockRepositoryInterface
{


    private PDO $pdo;
    private array $block;



}

