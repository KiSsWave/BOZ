<?php

namespace boz\core\services\conversations;

use boz\core\dto\ConversationDTO;
use boz\core\dto\MessageDTO;

interface ConversationServiceInterface
{

    public function getConversationsByUserLogin(string $userLogin): array;
    public function createConversation(string $user1Login, string $user2Login): ConversationDTO;
    public function getConversationById(string $conversationId): ?ConversationDTO;
    public function conversationExists(string $user1Login, string $user2Login): bool;
    public function getMessagesByConversationId(string $conversationId): array;
    public function sendMessage(string $conversationId, string $senderLogin, string $receiverLogin, string $content): MessageDTO;
    public function markMessagesAsRead(string $conversationId, string $userLogin): void;
}