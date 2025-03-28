<?php

namespace boz\Infrastructure\repositories;

use boz\core\domain\User\User;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use boz\core\repositoryInterfaces\UserRepositoryInterface;
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
            $this->users[$u['id']] = new User($u['login'], $u['email'], $u['role']);
            $this->users[$u['id']]->setID($u['id']);
            $this->users[$u['id']]->setPassword($u['password']);
        }
    }

    #[\Override] public function getUsers(): array
    {
        return $this->users;
    }

    #[\Override] public function save(User $user): string
    {
        $this->users[$user->getID()] = $user;
        $insert = $this->pdo->prepare('INSERT INTO USERS(id, login, email, password, role) VALUES (:id,:login, :email, :password, :role)');
        $insert->execute([
            'id' => $user->getID(),
            'login' =>$user->getLogin(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'role' => $user->getRole()
        ]);
        return $user->getID();
    }

    #[\Override] public function getUserByEmail(string $email): User
    {
        foreach ($this->users as $user) {
            if ($user->getLogin() === $email) {
                return $user;
            }
        }
        throw new RepositoryEntityNotFoundException('User not found ');    }

    #[\Override] public function getUserByID(string $id): User
    {
        foreach ($this->users as $user) {
            if ($user->getID() === $id) {
                return $user;
            }
        }
        throw new RepositoryEntityNotFoundException('User not found');
    }

    public function searchByLogin(string $query, string $currentUserLogin): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT login, email
                FROM users
                WHERE login ILIKE :query
                AND login != :currentUserLogin
                LIMIT 10
            ");

            $stmt->execute([
                'query' => "%$query%",
                'currentUserLogin' => $currentUserLogin
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Erreur lors de la recherche d\'utilisateurs: ' . $e->getMessage());
        }
    }

    #[\Override] public function update(User $user): void
    {

        $this->users[$user->getID()] = $user;


        $update = $this->pdo->prepare('
        UPDATE users 
        SET login = :login, 
            email = :email, 
            password = :password 
        WHERE id = :id
    ');

        $update->execute([
            'id' => $user->getID(),
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ]);
    }

    #[\Override] public function changeRole(string $userId, int $role): void
    {
        $update = $this->pdo->prepare('
        UPDATE users
        SET role = :role
        WHERE id = :id
    ');

            $update->execute([
                'id' => $userId,
                'role' => $role
            ]);
    }



}