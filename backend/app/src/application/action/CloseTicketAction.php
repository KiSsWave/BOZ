<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\tickets\TicketServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CloseTicketAction extends AbstractAction
{

    private TicketServiceInterface $ticketService;

    public function __construct(TicketServiceInterface $ticketService){
        $this->ticketService = $ticketService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        try {
            $ticketId = $args['ticketId'];
            $this->ticketService->closeTicket($ticketId);

            return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
        }catch (\Exception $e){
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}