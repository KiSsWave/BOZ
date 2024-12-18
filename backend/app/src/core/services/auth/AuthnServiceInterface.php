<?php

namespace boz\core\services\auth;

use boz\core\dto\CredentialDTO;
use boz\core\dto\UserDTO;

interface AuthnServiceInterface
{
    public function createUser(CredentialDTO $c,string $login,int $role);

    public function byCredentials(CredentialDTO $c): UserDTO;


}