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

            $user = $request->getAttribute('user');
            $userLogin = $user->getEmail();


            $includeLastMessage = false;
            $params = $request->getQueryParams();
            if (isset($params['include_last_message']) && $params['include_last_message'] === 'true') {
                $includeLastMessage = true;
            }


            $conversations = $this->conversationService->getConversationsByUserLogin($userLogin);


            $responseData = [
                'status' => 'success',
                'conversations' => []
            ];

            foreach ($conversations as $conversation) {

                $otherUserLogin = ($conversation->user1Login === $userLogin)
                    ? $conversation->user2Login
                    : $conversation->user1Login;

                $lastMessage = '';


                if ($includeLastMessage && $conversation->last_message_timestamp) {

                    $messages = $this->conversationService->getMessagesByConversationId($conversation->ID, 1);
                    if (!empty($messages)) {
                        $lastMessage = $messages[0]->content;
                    }
                }

                $responseData['conversations'][] = [
                    'id' => $conversation->ID,
                    'user1Login' => $conversation->user1Login,
                    'user2Login' => $conversation->user2Login,
                    'lastMessage' => $lastMessage,
                    'last_message_timestamp' => $conversation->last_message_timestamp
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