<?php

namespace boz\core\dto;
use AllowDynamicProperties;
use boz\core\domain\User\User;
use boz\core\dto\DTO;

class UserDTO extends DTO
{
    protected string $ID;
    protected string $login;
    protected string $email;
    protected string $role;
    protected string $token;

    public function __construct(User $u)
    {
        $this->ID = $u->getID();
        $this->email = $u->getEmail();
        $this->login = $u->getLogin();
        $this->role = $u->getRole();
    }

    public function getToken(){
        return $this->token;
    }

    public function getLogin(): string
    {
        return $this->login;
    }
    public function getID(): ?string
    {
        return $this->ID;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getRole(): string
    {
        return $this->role;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }
}