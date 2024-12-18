<?php

namespace backend\app\src\core\repositoryInterfaces;

interface BlockRepositoryInterface
{
    public function getBalanceByUserId(string $userId): float;
    public function getHistoryByUserId(string $userId): array;


}