<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\conversations\ConversationServiceInterface;
use boz\core\services\tickets\TicketServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StartConversationFromTicketAction extends AbstractAction
{
    private ConversationServiceInterface $conversationService;
    private TicketServiceInterface $ticketService;

    public function __construct(
        ConversationServiceInterface $conversationService,
        TicketServiceInterface $ticketService
    ) {
        $this->conversationService = $conversationService;
        $this->ticketService = $ticketService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {

            $admin = $request->getAttribute('user');
            $adminId = $admin->getID();
            $adminLogin = $admin->getEmail();


            $data = $request->getParsedBody();

            if (!isset($data['ticketId']) || empty($data['ticketId'])) {
                throw new \InvalidArgumentException('ID du ticket requis');
            }

            $ticketId = $data['ticketId'];


            $tickets = $this->ticketService->getTicketsByAdminId($adminId);

            $targetTicket = null;
            foreach ($tickets as $ticket) {
                if ($ticket->getId() === $ticketId) {
                    $targetTicket = $ticket;
                    break;
                }
            }

            if (!$targetTicket) {
                throw new \Exception('Ticket non trouvé ou vous n\'êtes pas assigné à ce ticket');
            }

            $userLogin = $targetTicket->getUserLogin();


            if ($this->conversationService->conversationExists($adminLogin, $userLogin)) {

                $conversations = $this->conversationService->getConversationsByUserLogin($adminLogin);

                $existingConversation = null;
                foreach ($conversations as $conversation) {
                    if (
                        ($conversation->user1Login === $adminLogin && $conversation->user2Login === $userLogin) ||
                        ($conversation->user1Login === $userLogin && $conversation->user2Login === $adminLogin)
                    ) {
                        $existingConversation = $conversation;
                        break;
                    }
                }

                $responseData = [
                    'status' => 'success',
                    'message' => 'Conversation existante récupérée',
                    'conversation' => [
                        'id' => $existingConversation->ID,
                        'user1Login' => $existingConversation->user1Login,
                        'user2Login' => $existingConversation->user2Login,
                        'lastMessageTimestamp' => $existingConversation->last_message_timestamp
                    ]
                ];
            } else {

                $conversation = $this->conversationService->createConversation($adminLogin, $userLogin);


                $this->conversationService->sendMessage(
                    $conversation->ID,
                    $adminLogin,
                    $userLogin,
                    "Bonjour, je suis en charge de votre ticket concernant \"" . $targetTicket->getMessage() . "\". Comment puis-je vous aider ?"
                );

                $responseData = [
                    'status' => 'success',
                    'message' => 'Conversation créée avec succès',
                    'conversation' => [
                        'id' => $conversation->ID,
                        'user1Login' => $conversation->user1Login,
                        'user2Login' => $conversation->user2Login,
                        'lastMessageTimestamp' => $conversation->last_message_timestamp
                    ]
                ];
            }

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}