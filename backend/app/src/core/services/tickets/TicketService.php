<?php

namespace boz\core\services\tickets;

use boz\core\domain\Blockchain\Ticket;
use boz\core\dto\TicketDTO;
use boz\core\repositoryInterfaces\TicketRepositoryInterface;
use boz\core\services\tickets\TicketServiceInterface;
use Ramsey\Uuid\Uuid;

class TicketService implements TicketServiceInterface
{
    private TicketRepositoryInterface $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository){
        $this->ticketRepository = $ticketRepository;
    }

    public function addTicket(string $iduser, ?string $adminid, string $message, string $type, string $status): void
    {
        $ticket = new Ticket($iduser,$adminid, $message, $type, $status);
        $ticket->setID(Uuid::uuid4()->toString());
        $this->ticketRepository->addTicket(new TicketDTO($ticket));

    }

    public function closeTicket(string $id): void
    {
        $this->ticketRepository->closeTicket($id);
    }

    public function takeTicket(string $ticketId, string $adminId): void
    {
        $this->ticketRepository->takeTicket($ticketId, $adminId);
    }

    public function getTicketByAdminId(string $id): array
    {
        $tickets = $this->ticketRepository->getTicketByAdminId($id);
        $ticketsDTO = [];
        foreach ($tickets as $ticket) {
            $ticketsDTO[] = new TicketDTO($ticket);
        }
        return $ticketsDTO;
    }

    public function getTicketByUserId(string $iduser): array
    {
        $tickets = $this->ticketRepository->getTicketByUserId($iduser);
        $ticketsDTO = [];
        foreach ($tickets as $ticket) {
            $ticketsDTO[] = new TicketDTO($ticket);
        }
        return $ticketsDTO;
    }
}