<?php

namespace boz\core\services\conversations;

use boz\core\domain\Blockchain\Conversation;
use boz\core\domain\Blockchain\Message;
use boz\core\dto\ConversationDTO;
use boz\core\dto\MessageDTO;
use boz\core\repositoryInterfaces\ConversationRepositoryInterface;
use boz\core\repositoryInterfaces\RepositoryEntityNotFoundException;
use Exception;
use Ramsey\Uuid\Uuid;

class ConversationService implements ConversationServiceInterface
{
    private ConversationRepositoryInterface $conversationRepository;

    public function __construct(ConversationRepositoryInterface $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    public function getConversationsByUserLogin(string $userLogin): array
    {
        try {
            $conversations = $this->conversationRepository->getConversationsByUserLogin($userLogin);
            $conversationDTOs = [];

            foreach ($conversations as $conversation) {
                $conversationDTOs[] = new ConversationDTO($conversation);
            }

            return $conversationDTOs;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des conversations : ' . $e->getMessage());
        }
    }

    public function createConversation(string $user1Login, string $user2Login): ConversationDTO
    {
        try {
            if ($this->conversationExists($user1Login, $user2Login)) {
                throw new Exception('Une conversation existe déjà entre ces deux utilisateurs');
            }


            $conversation = new Conversation($user1Login, $user2Login);
            $conversation->setId(Uuid::uuid4()->toString());

            $conversationDTO = new ConversationDTO($conversation);
            $this->conversationRepository->saveConversation($conversationDTO);

            return $conversationDTO;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la création de la conversation : ' . $e->getMessage());
        }
    }

    public function getConversationById(string $conversationId): ?ConversationDTO
    {
        try {
            $conversation = $this->conversationRepository->getConversationById($conversationId);

            if (!$conversation) {
                return null;
            }

            return new ConversationDTO($conversation);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération de la conversation : ' . $e->getMessage());
        }
    }

    public function conversationExists(string $user1Login, string $user2Login): bool
    {
        try {
            return $this->conversationRepository->conversationExists($user1Login, $user2Login);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la vérification de l\'existence de la conversation : ' . $e->getMessage());
        }
    }

    public function getMessagesByConversationId(string $conversationId): array
    {
        try {
            $messages = $this->conversationRepository->getMessagesByConversationId($conversationId);
            $messageDTOs = [];

            foreach ($messages as $message) {
                $messageDTOs[] = new MessageDTO($message);
            }

            return $messageDTOs;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des messages : ' . $e->getMessage());
        }
    }

    public function sendMessage(string $conversationId, string $senderLogin, string $receiverLogin, string $content): MessageDTO
    {
        try {

            $conversation = $this->conversationRepository->getConversationById($conversationId);
            if (!$conversation) {
                throw new RepositoryEntityNotFoundException('Conversation non trouvée');
            }
            $user1 = $conversation->getUser1Login();
            $user2 = $conversation->getUser2Login();
            if (
                ($senderLogin !== $user1 && $senderLogin !== $user2) ||
                ($receiverLogin !== $user1 && $receiverLogin !== $user2)
            ) {
                throw new Exception('L\'émetteur ou le destinataire n\'appartiennent pas à cette conversation');
            }

            $message = new Message($senderLogin, $receiverLogin, $content, $conversationId);
            $message->setId(Uuid::uuid4()->toString());

            $messageDTO = new MessageDTO($message);
            $this->conversationRepository->saveMessage($messageDTO);


            $this->conversationRepository->updateLastMessageTimestamp($conversationId, $message->getTimestamp());

            return $messageDTO;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'envoi du message : ' . $e->getMessage());
        }
    }

    public function markMessagesAsRead(string $conversationId, string $userLogin): void
    {
        try {
            $this->conversationRepository->markMessagesAsRead($conversationId, $userLogin);
        } catch (Exception $e) {
            throw new Exception('Erreur lors du marquage des messages comme lus : ' . $e->getMessage());
        }
    }
}