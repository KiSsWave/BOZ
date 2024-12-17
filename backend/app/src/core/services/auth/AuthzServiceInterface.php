<?php

namespace backend\app\src\core\services\auth;

interface AuthzServiceInterface
{
    public function isAdmin(string $id): bool;
    public function isUser(string $id): bool;

    public function isVendeur(string $id): bool;

}