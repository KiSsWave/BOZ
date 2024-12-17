<?php

namespace backend\app\src\core\services\auth;

use backend\app\src\core\dto\CredentialDTO;
use backend\app\src\core\dto\UserDTO;

interface AuthnServiceInterface
{
    public function createUser(CredentialDTO $c,string $login,int $role);

    public function byCredentials(CredentialDTO $c): UserDTO;


}