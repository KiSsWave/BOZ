<?php


namespace boz\core\repositoryInterfaces;
use boz\core\domain\User\User;

interface UserRepositoryInterface
{
    public function getUsers(): array;
    public function save(User $user): string;
    public function getUserByEmail(string $email): User;

    public function getUserByID(string $id): User;

}