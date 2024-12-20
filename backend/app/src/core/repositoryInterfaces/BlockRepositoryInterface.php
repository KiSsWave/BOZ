<?php

namespace boz\core\repositoryInterfaces;

interface BlockRepositoryInterface
{
    public function getBalanceByUserId(string $userId): float;
    public function getHistoryByUserId(string $userId): array;
    public function createFacture(string $userId, float $tarif, string $label): void;
    public function payFacture(string $factureId): void;


}