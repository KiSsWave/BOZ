<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\tickets\TicketService;
use boz\core\services\tickets\TicketServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetTicketsByAdminIdAction extends AbstractAction
{
    private TicketServiceInterface $ticketService;

    public function __construct(TicketServiceInterface $ticketService){
        $this->ticketService = $ticketService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        try {
            $admin = $rq->getAttribute('user');
            $adminId = $admin->getID();
            $tickets = $this->ticketService->getTicketsByAdminId($adminId);
            $resultat["Tickets"]  = [];
            foreach ($tickets as $ticket) {
                $resultat["Tickets"][] = [
                    'Id' => $ticket->ID,
                    'User Login' => $ticket->userLogin,
                    'Id Admin' => $ticket->adminId,
                    'message' => $ticket->message,
                    'type' => $ticket->type,
                    'status' => $ticket->status,
                ];
            }
            $rs->getBody()->write(json_encode($resultat));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
        }catch(\Exception $e){
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}
