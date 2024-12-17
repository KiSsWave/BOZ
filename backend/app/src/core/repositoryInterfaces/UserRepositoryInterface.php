<?php


namespace backend\app\src\core\repositoryInterfaces;
use backend\app\src\core\domain\User\User;

interface UserRepositoryInterface
{
    public function getUsers(): array;
    public function save(User $user): string;
    public function getUserByEmail(string $email): User;

    public function getUserByID(string $id): User;

}