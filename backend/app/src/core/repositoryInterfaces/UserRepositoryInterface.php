<?php


namespace boz\core\repositoryInterfaces;
use boz\core\domain\User\User;

interface UserRepositoryInterface
{
    public function getUsers(): array;
    public function save(User $user): string;
    public function getUserByEmail(string $email): User;

    public function getUserByID(string $id): User;

    public function searchByLogin(string $query, string $currentUserLogin): array;

    public function update(User $user): void;

    public function changeRole(string $userId, int $role): void;


}