<?php

namespace boz\core\services\tickets;


interface TicketServiceInterface
{
    public function addTicket(string $iduser, string $adminid, string $message, string $type, string $status): void;

    public function closeTicket(string $id): void;

    public function takeTicket(string $ticketId, string $adminId): void;

    public function getTicketsByAdminId(string $id): array;

    public function getTicketsByUserId(string $iduser): array;

    public function getTicketsPending(): array;
}