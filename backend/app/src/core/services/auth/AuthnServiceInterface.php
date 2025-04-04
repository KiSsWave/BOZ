<?php

namespace boz\core\services\auth;

use boz\core\dto\CredentialDTO;
use boz\core\dto\UserDTO;

interface AuthnServiceInterface
{
    public function createUser(CredentialDTO $c,string $login,int $role);

    public function byCredentials(CredentialDTO $c): UserDTO;

    public function searchUsers(string $query, string $currentUserLogin): array;

    public function updateProfile(string $userId, string $login, string $email, ?string $newPassword = null): void;

    public function verifyPassword(CredentialDTO $credentials): bool;

    public function changeRole(string $userId, int $role): void;


}