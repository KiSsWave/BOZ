<?php

namespace boz\core\repositoryInterfaces;



use boz\core\dto\TicketDTO;

interface TicketRepositoryInterface
{

    public function addTicket(TicketDTO $ticketDTO): void;

    public function closeTicket(string $id): void;

    public function takeTicket(string $ticketId, string $adminId);

    public function getTicketsByAdminId(string $id): array;

    public function getTicketsByUserId(string $id): array;

    public function getTicketsPending(): array;

}