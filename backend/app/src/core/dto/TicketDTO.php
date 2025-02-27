<?php

namespace boz\core\dto;

use boz\core\domain\Blockchain\Ticket;
use boz\core\dto\DTO;

class TicketDTO extends DTO
{
    protected string $ID;
    protected string $userLogin;
    protected ?string $adminId;
    protected string $message;
    protected string $type;
    protected string $status;

    public function __construct(Ticket $ticket){
        $this->ID = $ticket->getId();
        $this->userLogin = $ticket->getUserLogin();
        $this->adminId = $ticket->getAdminId();
        $this->message = $ticket->getMessage();
        $this->type = $ticket->getType();
        $this->status = $ticket->getStatus();
    }

    public function getId(): string{
        return $this->ID;
    }

    public function getUserLogin(): string{
        return $this->userLogin;
    }

    public function getAdminId(): ?string{
        return $this->adminId;
    }

    public function getMessage(): string{
        return $this->message;
    }

    public function getType(): string{
        return $this->type;
    }

    public function getStatus(): string{
        return $this->status;
    }
}