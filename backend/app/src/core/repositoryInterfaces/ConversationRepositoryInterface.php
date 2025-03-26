<?php

namespace boz\core\repositoryInterfaces;

use boz\core\domain\Blockchain\Conversation;
use boz\core\domain\Blockchain\Message;
use boz\core\dto\ConversationDTO;
use boz\core\dto\MessageDTO;

interface ConversationRepositoryInterface {
    public function saveConversation(ConversationDTO $conversationDTO): void;
    public function getConversationById(string $conversationId): ?Conversation;
    public function getConversationsByUserLogin(string $userLogin): array;
    public function conversationExists(string $user1Login, string $user2Login): bool;
    public function updateLastMessageTimestamp(string $conversationId, int $timestamp): void;
    public function saveMessage(MessageDTO $messageDTO): void;
    public function getMessagesByConversationId(string $conversationId): array;

}
