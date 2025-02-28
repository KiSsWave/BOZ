<?php

namespace boz\core\domain\User;

use boz\core\domain\Entity;

class User extends Entity
{
    protected string $email;
    protected string $login;
    protected string $password;
    protected int $role;


    public function __construct(string $e,string $l, string $r){
        $this->email = $e;
        $this->login = $l;
        $this->role = $r;
    }

    public function getEmail() :string {
        return $this->email;
    }

    public function getLogin() : string{
        return $this->login;
    }

    public function getPassword() : string{
        return $this->password;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function setPassword(string $p)
    {
        $this->password = $p;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }



}