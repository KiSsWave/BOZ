<?php

namespace boz\core\domain\Blockchain;

use boz\core\domain\Entity;

class Ticket extends Entity
{
    public string $userLogin;
    public ?string $adminId;
    public string $message;
    public string $type;
    public string $status;

    public function __construct(string $userLogin, ?string $adminId, string $message, string $type, string $status)
    {
        $this->userLogin = $userLogin;
        $this->adminId = $adminId;
        $this->message = $message;
        $this->type = $type;
        $this->status = $status;
    }

    public function getType(): string{
        return $this->type;
    }

    public function getAdminId(): ?string{
        return $this->adminId;
    }

    public function getUserLogin(): string{
        return $this->userLogin;
    }

    public function getMessage(): string{
        return $this->message;
    }

    public function getStatus(): string{
        return $this->status;
    }
}