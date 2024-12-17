<?php

namespace backend\app\src\core\domain\User;

use backend\app\src\core\domain\Entity;

class User extends Entity
{
    protected string $login;
    protected string $email;
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



}