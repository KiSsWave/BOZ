<?php

namespace boz\core\dto;

use boz\core\domain\Blockchain\Block;
use boz\core\domain\Blockchain\Conversation;

class ConversationDTO extends DTO {

    public string $ID;
    public string $user1Login;
    public string $user2Login;
    public int $last_message_timestamp;

    public function __construct(Conversation $conversation)
    {
        $this->ID = $conversation->getID();
        $this->user1Login = $conversation->getUser1Login();
        $this->user2Login = $conversation->getUser2Login();
        $this->last_message_timestamp = $conversation->getLastMessageTimestamp();
    }
}

