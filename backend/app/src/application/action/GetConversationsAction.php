<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\conversations\ConversationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetConversationsAction extends AbstractAction
{
    private ConversationServiceInterface $conversationService;

    public function __construct(ConversationServiceInterface $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            // Récupérer l'utilisateur connecté depuis l'attribut user
            $user = $request->getAttribute('user');
            $userLogin = $user->getEmail();

            // Récupérer les conversations de l'utilisateur
            $conversations = $this->conversationService->getConversationsByUserLogin($userLogin);

            // Préparation de la réponse
            $responseData = [
                'status' => 'success',
                'conversations' => []
            ];

            foreach ($conversations as $conversation) {
                // Déterminer l'autre utilisateur dans la conversation
                $otherUserLogin = ($conversation->user1Login === $userLogin)
                    ? $conversation->user2Login
                    : $conversation->user1Login;

                $responseData['conversations'][] = [
                    'id' => $conversation->ID,
                    'otherUser' => $otherUserLogin,
                    'lastMessageTimestamp' => $conversation->last_message_timestamp
                ];
            }

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}