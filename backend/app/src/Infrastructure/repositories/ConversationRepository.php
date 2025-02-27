<?php

namespace boz\Infrastructure\repositories;

use boz\core\domain\Blockchain\Conversation;
use boz\core\domain\Blockchain\Message;
use boz\core\dto\ConversationDTO;
use boz\core\dto\MessageDTO;
use boz\core\repositoryInterfaces\ConversationRepositoryInterface;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use Exception;
use PDO;
use Ramsey\Uuid\Uuid;

class ConversationRepository implements ConversationRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function saveConversation(ConversationDTO $conversationDTO): void
    {
        try {
            $query = $this->pdo->prepare("
                INSERT INTO conversations (id, user1_login, user2_login, last_message_timestamp)
                VALUES (:id, :user1_login, :user2_login, to_timestamp(:last_message_timestamp))
            ");

            $query->execute([
                ':id' => $conversationDTO->ID,
                ':user1_login' => $conversationDTO->user1Login,
                ':user2_login' => $conversationDTO->user2Login,
                ':last_message_timestamp' => $conversationDTO->last_message_timestamp
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la création de la conversation : ' . $e->getMessage());
        }
    }

    public function getConversationById(string $conversationId): ?Conversation
    {
        try {
            $query = $this->pdo->prepare("
                SELECT * FROM conversations
                WHERE id = :id
            ");
            $query->execute([':id' => $conversationId]);
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return null;
            }

            $conversation = new Conversation($result['user1_login'], $result['user2_login']);
            $conversation->setId($result['id']);

            $timestampObj = new \DateTime($result['last_message_timestamp']);
            $conversation->last_message_timestamp = $timestampObj->getTimestamp();

            return $conversation;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération de la conversation : ' . $e->getMessage());
        }
    }

    public function getConversationsByUserLogin(string $userLogin): array
    {
        try {
            $query = $this->pdo->prepare("
                SELECT * FROM conversations
                WHERE user1_login = :userLogin OR user2_login = :userLogin
                ORDER BY last_message_timestamp DESC
            ");
            $query->execute([':userLogin' => $userLogin]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);

            $conversations = [];
            foreach ($results as $result) {
                $conversation = new Conversation($result['user1_login'], $result['user2_login']);
                $conversation->setId($result['id']);

                $timestampObj = new \DateTime($result['last_message_timestamp']);
                $conversation->last_message_timestamp = $timestampObj->getTimestamp();

                $conversations[] = $conversation;
            }

            return $conversations;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des conversations : ' . $e->getMessage());
        }
    }

    public function conversationExists(string $user1Login, string $user2Login): bool
    {
        try {
            $query = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM conversations
                WHERE (user1_login = :user1Login AND user2_login = :user2Login)
                OR (user1_login = :user2Login AND user2_login = :user1Login)
            ");
            $query->execute([
                ':user1Login' => $user1Login,
                ':user2Login' => $user2Login
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result['count'] > 0;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la vérification de l\'existence de la conversation : ' . $e->getMessage());
        }
    }

    public function updateLastMessageTimestamp(string $conversationId, int $timestamp): void
    {
        try {
            $query = $this->pdo->prepare("
                UPDATE conversations
                SET last_message_timestamp = to_timestamp(:timestamp)
                WHERE id = :id
            ");
            $query->execute([
                ':timestamp' => $timestamp,
                ':id' => $conversationId
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la mise à jour du timestamp : ' . $e->getMessage());
        }
    }

    public function saveMessage(MessageDTO $messageDTO): void
    {
        try {
            $query = $this->pdo->prepare("INSERT INTO messages (id, sender_login, receiver_login, content, timestamp)
            VALUES (:id, :sender_login, :receiver_login, :content, to_timestamp(:timestamp))
            ");

            $query->execute([
                ':id' => $messageDTO->ID,
                ':sender_login' => $messageDTO->senderLogin,
                ':receiver_login' => $messageDTO->receiverLogin,
                ':content' => $messageDTO->content,
                ':timestamp' => $messageDTO->timestamp,
            ]);


            $this->updateLastMessageTimestamp($messageDTO->conversationId, $messageDTO->timestamp);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'enregistrement du message : ' . $e->getMessage());
        }
    }

    public function getMessagesByConversationId(string $conversationId): array
    {
        try {

            $conversation = $this->getConversationById($conversationId);
            if (!$conversation) {
                throw new RepositoryEntityNotFoundException("Conversation non trouvée");
            }

            $user1Login = $conversation->getUser1Login();
            $user2Login = $conversation->getUser2Login();


            $query = $this->pdo->prepare("
                SELECT * FROM messages
                WHERE (sender_login = :user1Login AND receiver_login = :user2Login)
                OR (sender_login = :user2Login AND receiver_login = :user1Login)
                ORDER BY timestamp ASC
            ");
            $query->execute([
                ':user1Login' => $user1Login,
                ':user2Login' => $user2Login
            ]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);

            $messages = [];
            foreach ($results as $result) {
                $message = new Message(
                    $result['sender_login'],
                    $result['receiver_login'],
                    $result['content'],
                    $conversationId,
                    strtotime($result['timestamp']),
                );
                $message->setId($result['id']);
                $messages[] = $message;
            }

            return $messages;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des messages : ' . $e->getMessage());
        }
    }


}