<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\tickets\TicketServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddTicketAction extends AbstractAction
{

    private TicketServiceInterface $ticketService;

    public function __construct(TicketServiceInterface $ticketService){
        $this->ticketService = $ticketService;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        try{
            $body = $rq->getParsedBody();
            $adminid = $body['adminid'] ?? null;
            $message = $body['message'];
            $type = $body['type'];

            $user = $rq->getAttribute('user');
            $userLogin = $user->getEmail();


            $this->ticketService->addTicket($userLogin, $adminid, $message, $type, "en attente");

            return $rs->withStatus(201)->withHeader('Content-Type', 'application/json');
        }catch(\Exception $e){
            $rs->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $rs->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}