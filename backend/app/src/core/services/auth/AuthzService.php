<?php

namespace boz\core\services\auth;

use boz\core\repositoryInterfaces\UserRepositoryInterface;
use boz\core\services\auth\AuthzServiceInterface;

class AuthzService implements AuthzServiceInterface
{

    private UserRepositoryInterface $userRepository;

    public  function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    #[\Override] public function isAdmin(string $id): bool
    {
        $user = $this->userRepository->getUserByID($id);
        return isset($user['role']) && $user['role'] === '3';
    }

    #[\Override] public function isUser(string $id): bool
    {
        $user = $this->userRepository->getUserByID($id);
        return isset($user['role']) && $user['role'] === '1';
    }

    #[\Override] public function isVendeur(string $id): bool
    {
        $user = $this->userRepository->getUserByID($id);
        return isset($user['role']) && $user['role'] === '2';
    }
}