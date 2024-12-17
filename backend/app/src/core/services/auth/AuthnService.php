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

    #[\Override] public function createUser(CredentialDTO $c, string $login, int $role)
    {
        $user = new User($c->getEmail(),$login, $role);
        $user->setID(Uuid::uuid4()->toString());
        $user->setPassword(password_hash($c->getPassword(), PASSWORD_DEFAULT));
        return $this->userRepository->save($user);
    }

    #[\Override] public function byCredentials(CredentialDTO $c): UserDTO
    {
        // TODO: Implement byCredentials() method.
    }
}