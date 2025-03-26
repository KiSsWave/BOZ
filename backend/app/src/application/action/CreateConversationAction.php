<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\conversations\ConversationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CreateConversationAction extends AbstractAction
{
    private ConversationServiceInterface $conversationService;

    public function __construct(ConversationServiceInterface $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {

            $userLogin = $request->getAttribute('userLogin');
            $data = $request->getParsedBody();


            if (!isset($data['otherUserLogin']) || empty($data['otherUserLogin'])) {
                throw new \InvalidArgumentException('L\'identifiant de l\'autre utilisateur est requis');
            }

            $otherUserLogin = $data['otherUserLogin'];


            if ($this->conversationService->conversationExists($userLogin, $otherUserLogin)) {

                $conversations = $this->conversationService->getConversationsByUserLogin($userLogin);
                $existingConversation = null;

                foreach ($conversations as $conversation) {
                    if (
                        ($conversation->user1Login === $userLogin && $conversation->user2Login === $otherUserLogin) ||
                        ($conversation->user1Login === $otherUserLogin && $conversation->user2Login === $userLogin)
                    ) {
                        $existingConversation = $conversation;
                        break;
                    }
                }

                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'Conversation existante récupérée',
                    'conversation' => [
                        'id' => $existingConversation->ID,
                        'user1' => $existingConversation->user1Login,
                        'user2' => $existingConversation->user2Login,
                        'lastMessageTimestamp' => $existingConversation->last_message_timestamp
                    ]
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }

            $conversation = $this->conversationService->createConversation($userLogin, $otherUserLogin);


            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Conversation créée avec succès',
                'conversation' => [
                    'id' => $conversation->ID,
                    'user1' => $conversation->user1Login,
                    'user2' => $conversation->user2Login,
                    'lastMessageTimestamp' => $conversation->last_message_timestamp
                ]
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

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