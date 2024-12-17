<?php

namespace backend\app\src\application\providers;

use backend\app\src\application\providers\AuthnProviderInterface;
use backend\app\src\core\dto\CredentialDTO;
use backend\app\src\core\dto\UserDTO;
use PhpParser\Token;

class JWTAuthnProvider implements AuthnProviderInterface
{

    private $jwtManager;
    private $authService;

    public function __construct(JWTManager $jwtManager, AuthnServiceInterface $authService)
    {
        $this->jwtManager = $jwtManager;
        $this->authService = $authService;
    }



    #[\Override] public function register(CredentialDTO $c, string $nom, string $prenom, string $tel, string $birthdate, string $eligible, int $role)
    {
        // TODO: Implement register() method.
    }

    #[\Override] public function signin(CredentialDTO $c): UserDTO
    {
        // TODO: Implement signin() method.
    }

    #[\Override] public function refresh(Token $token): UserDTO
    {
        // TODO: Implement refresh() method.
    }

    #[\Override] public function getSignedInUser(string $token): UserDTO
    {
        // TODO: Implement getSignedInUser() method.
    }
}