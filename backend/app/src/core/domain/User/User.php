<?php

namespace backend\app\src\core\domain\User;

use backend\app\src\core\domain\Entity;

class User extends Entity
{
    protected string $login;
    protected string $email;
    protected string $password;
    protected int $role;


    public function __construct(string $l,string $e, string $r){
        $this->login = $l;
        $this->email = $e;
        $this->role = $r;
    }

    public function getEmail() :string {
        return $this->email;
    }

    public function getLogin() : string{
        return $this->login;
    }



}