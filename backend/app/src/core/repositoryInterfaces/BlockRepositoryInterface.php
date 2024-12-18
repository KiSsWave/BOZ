<?php

namespace backend\app\src\core\repositoryInterfaces;

interface BlockRepositoryInterface
{
    public function getAccountBalance(string $account): float;
    public function getAccountHistory(string $account): array;


}