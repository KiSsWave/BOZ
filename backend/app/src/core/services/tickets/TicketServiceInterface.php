<?php

namespace boz\core\services\tickets;


interface TicketServiceInterface
{
    public function addTicket(string $iduser, string $adminid, string $message, string $type, string $status): void;

    public function closeTicket(string $id): void;

    public function takeTicket(string $ticketId, string $adminId): void;

    public function getTicketByAdminId(string $id): array;

    public function getTicketByUserId(string $iduser): array;
}