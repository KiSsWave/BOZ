<?php

namespace boz\core\domain\Blockchain;

use boz\core\domain\Entity;

class Ticket extends Entity
{

    public string $user_id;
    public ?string $admin_id;

    public string $message;

    public string $type;

    public string $status;

    public function __construct(string $user_id, ?string $admin_id, string $message, string $type, string $status)
    {
        $this->user_id = $user_id;
        $this->admin_id = $admin_id;
        $this->message = $message;
        $this->type = $type;
        $this->status = $status;
    }

    public function getType(): string{
        return $this->type;
    }

    public function getAdminId(): ?string{
        return $this->admin_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getMessage(): string{
        return $this->message;
    }

    public function getStatus(): string{
        return $this->status;
    }

}