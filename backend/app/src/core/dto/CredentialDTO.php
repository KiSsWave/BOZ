<?php

namespace boz\core\dto;

use boz\core\dto\DTO;

class CredentialDTO extends DTO
{
    protected string $email;
    protected string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

}