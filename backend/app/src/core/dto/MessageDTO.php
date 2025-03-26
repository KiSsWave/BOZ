<?php

namespace boz\core\dto;

use boz\core\domain\Blockchain\Message;

class MessageDTO extends DTO {
    public string $ID;
    public string $senderLogin;
    public string $receiverLogin;
    public string $content;
    public int $timestamp;
    public string $conversationId;

    public function __construct(Message $message) {
        $this->ID = $message->getID();
        $this->senderLogin = $message->getSenderLogin();
        $this->receiverLogin = $message->getReceiverLogin();
        $this->content = $message->getContent();
        $this->timestamp = $message->getTimestamp();;
        $this->conversationId = $message->getConversationId();
    }
}