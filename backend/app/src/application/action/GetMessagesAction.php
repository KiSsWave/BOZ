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

            $user = $request->getAttribute('user');
            $userLogin = $user->getEmail();


            $conversationId = $args['id'] ?? null;

            if (!$conversationId) {
                throw new \InvalidArgumentException('ID de conversation manquant');
            }


            $conversation = $this->conversationService->getConversationById($conversationId);

            if (!$conversation) {
                throw new \Exception('Conversation non trouvée');
            }


            if ($conversation->user1Login !== $userLogin && $conversation->user2Login !== $userLogin) {
                throw new \Exception('Vous n\'avez pas accès à cette conversation');
            }


            $messages = $this->conversationService->getMessagesByConversationId($conversationId);


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