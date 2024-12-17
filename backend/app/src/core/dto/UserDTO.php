<?php

namespace backend\app\src\core\dto;
use backend\app\src\core\domain\User\User;
use backend\app\src\core\dto\DTO;

class UserDTO extends DTO
{
    protected string $login;
    protected string $email;
    protected string $role;

    public function __construct(User $u)
    {
        $this->ID = $u->getID();
        $this->email = $u->getEmail();
        $this->login = $u->getLogin();
    }
}