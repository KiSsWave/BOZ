<?php

namespace boz\core\domain\Blockchain;

use boz\core\domain\Entity;

class Message extends Entity {
    private string $senderLogin;
    private string $receiverLogin;
    private string $content;
    private int $timestamp;
    private string $conversationId;

    public function __construct(
        string $senderLogin,
        string $receiverLogin,
        string $content,
        string $conversationId,
        int $timestamp = null,
    ) {
        $this->senderLogin = $senderLogin;
        $this->receiverLogin = $receiverLogin;
        $this->content = $content;
        $this->conversationId = $conversationId;
        $this->timestamp = $timestamp ?? time();
    }

    public function getSenderLogin(): string {
        return $this->senderLogin;
    }

    public function getReceiverLogin(): string {
        return $this->receiverLogin;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getConversationId(): string {
        return $this->conversationId;
    }

    public function getTimestamp(): int {
        return $this->timestamp;
    }
}
