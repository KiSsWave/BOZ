<?php

namespace backend\app\src\core\domain\Blockchain;

namespace backend\app\src\infrastructure\repositories;
use backend\app\src\core\repositoryInterfaces\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    private array $users;

    public function __construct(PDO $p)
    {
        $this->pdo = $p;
        $users = $this->pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $u){
            //TODO
        }
    }

}