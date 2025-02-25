<?php

namespace boz\application\action;

use boz\application\action\AbstractAction;
use boz\core\services\conversations\ConversationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SendMessageAction extends AbstractAction
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
            $senderLogin = $user->getLogin();

            // Récupérer l'ID de conversation depuis les arguments de route
            $conversationId = $args['id'] ?? null;

            if (!$conversationId) {
                throw new \InvalidArgumentException('ID de conversation manquant');
            }

            // Récupérer le contenu du message depuis le corps de la requête
            $data = $request->getParsedBody();

            if (!isset($data['content']) || empty($data['content'])) {
                throw new \InvalidArgumentException('Le contenu du message est requis');
            }

            $content = $data['content'];

            // Vérifier que la conversation existe
            $conversation = $this->conversationService->getConversationById($conversationId);

            if (!$conversation) {
                throw new \Exception('Conversation non trouvée');
            }

            // Vérifier que l'utilisateur fait partie de la conversation
            if ($conversation->user1Login !== $senderLogin && $conversation->user2Login !== $senderLogin) {
                throw new \Exception('Vous n\'avez pas accès à cette conversation');
            }

            // Déterminer le destinataire
            $receiverLogin = ($conversation->user1Login === $senderLogin)
                ? $conversation->user2Login
                : $conversation->user1Login;

            // Envoyer le message
            $message = $this->conversationService->sendMessage(
                $conversationId,
                $senderLogin,
                $receiverLogin,
                $content
            );

            // Préparation de la réponse
            $responseData = [
                'status' => 'success',
                'message' => 'Message envoyé avec succès',
                'data' => [
                    'id' => $message->ID,
                    'senderLogin' => $message->senderLogin,
                    'receiverLogin' => $message->receiverLogin,
                    'content' => $message->content,
                    'timestamp' => $message->timestamp,
                    'read' => $message->read
                ]
            ];

            $response->getBody()->write(json_encode($responseData));
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