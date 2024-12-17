<?php

namespace backend\app\src\core\services\auth;

use backend\app\src\core\domain\User\User;
use backend\app\src\core\dto\CredentialDTO;
use backend\app\src\core\dto\UserDTO;
use backend\app\src\core\repositoryInterfaces\UserRepositoryInterface;
use backend\app\src\core\services\auth\AuthnServiceInterface;
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
}