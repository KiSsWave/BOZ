<?php

namespace backend\app\src\core\domain\User;

use backend\app\src\core\domain\Entity;

class User extends Entity
{
    protected string $login;
    protected string $password;
    protected int $role;


    public function __construct(string $l, string $p, string $r){
        $this->login = $l;
        $this->password = $p;
        $this->role = $r;
    }

    public function __get(string $name): mixed
    {
        return parent::__get($name);
    }


}