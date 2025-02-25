<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\conversations\ConversationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetMessagesAction extends AbstractAction
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
            $userLogin = $user->getLogin();

            // Récupérer l'ID de conversation depuis les arguments de route
            $conversationId = $args['id'] ?? null;

            if (!$conversationId) {
                throw new \InvalidArgumentException('ID de conversation manquant');
            }

            // Vérifier que la conversation existe
            $conversation = $this->conversationService->getConversationById($conversationId);

            if (!$conversation) {
                throw new \Exception('Conversation non trouvée');
            }

            // Vérifier que l'utilisateur fait partie de la conversation
            if ($conversation->user1Login !== $userLogin && $conversation->user2Login !== $userLogin) {
                throw new \Exception('Vous n\'avez pas accès à cette conversation');
            }

            // Marquer les messages comme lus pour cet utilisateur
            $this->conversationService->markMessagesAsRead($conversationId, $userLogin);

            // Récupérer les messages
            $messages = $this->conversationService->getMessagesByConversationId($conversationId);

            // Préparation de la réponse
            $responseData = [
                'status' => 'success',
                'messages' => [],
                'conversation' => [
                    'id' => $conversation->ID,
                    'user1Login' => $conversation->user1Login,
                    'user2Login' => $conversation->user2Login,
                    'lastMessageTimestamp' => $conversation->last_message_timestamp
                ]
            ];

            foreach ($messages as $message) {
                $responseData['messages'][] = [
                    'id' => $message->ID,
                    'senderLogin' => $message->senderLogin,
                    'receiverLogin' => $message->receiverLogin,
                    'content' => $message->content,
                    'timestamp' => $message->timestamp,
                    'read' => $message->read,
                    'isMine' => ($message->senderLogin === $userLogin)
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