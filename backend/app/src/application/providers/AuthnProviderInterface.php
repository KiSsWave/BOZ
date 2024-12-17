<?php

namespace backend\app\src\application\providers;


use DateTime;
use backend\app\src\core\dto\UserDTO;
use backend\app\src\core\dto\CredentialDTO;
use PhpParser\Token;

interface AuthnProviderInterface
{
    public function register(CredentialDTO $c,string $login,int $role);
    public function signin(CredentialDTO $c): UserDTO;
    public function refresh(Token $token): UserDTO;
    public function getSignedInUser(string $token): UserDTO;
}