<?php

namespace boz\core\services\auth;

use boz\core\domain\User\User;
use boz\core\dto\CredentialDTO;
use boz\core\dto\UserDTO;
use boz\core\repositoryInterfaces\UserRepositoryInterface;
use boz\core\services\auth\AuthnServiceInterface;
use Ramsey\Uuid\Uuid;

class AuthnService implements AuthnServiceInterface
{

    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[\Override] public function createUser(CredentialDTO $c, string $login, int $role): string
    {
        $user = new User($c->getEmail(),$login, $role);
        $user->setID(Uuid::uuid4()->toString());
        $user->setPassword(password_hash($c->getPassword(), PASSWORD_DEFAULT));
        return $this->userRepository->save($user);
    }

    #[\Override] public function byCredentials(CredentialDTO $c): UserDTO
    {
        $user = $this->userRepository->getUserByEmail($c->getEmail());

        if ($user && password_verify($c->getPassword(), $user->getPassword())) {
            return new UserDTO($user);
        } else {
            throw new AuthServiceBadDataException('Erreur 400 : Email ou mot de passe incorrect', 400);
        }
    }

    public function searchUsers(string $query, string $currentUserLogin): array
    {
        return $this->userRepository->searchByLogin($query, $currentUserLogin);
    }

    public function updateProfile(string $userId, string $login, string $email, ?string $newPassword = null): void
    {

        $user = $this->userRepository->getUserByID($userId);


        $user->setLogin($login);
        $user->setEmail($email);


        if ($newPassword) {
            $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
        }


        $this->userRepository->update($user);
    }

    public function verifyPassword(CredentialDTO $credentials): bool
    {
        try {
            $user = $this->userRepository->getUserByEmail($credentials->getEmail());

            if (!password_verify($credentials->getPassword(), $user->getPassword())) {
                throw new AuthServiceBadDataException('Mot de passe incorrect', 401);
            }

            return true;
        } catch (RepositoryEntityNotFoundException $e) {
            throw new AuthServiceBadDataException('Utilisateur non trouvé', 404);
        }
    }

    public function changeRole(string $userId, int $role): void
    {
        $this->userRepository->changeRole($userId, $role);
    }
}