<?php

namespace boz\core\domain\Blockchain;

use boz\core\domain\Entity;
use Cassandra\Timestamp;
use Cassandra\Uuid;

class Conversation extends Entity {

    public string $user1Login;
    public string $user2Login;
    public int $last_message_timestamp;

    public function __construct(string $user1Login, string $user2Login) {
        $this->user1Login = $user1Login;
        $this->user2Login = $user2Login;
        $this->last_message_timestamp = time();
    }

    public function getUser1Login(): string{
        return $this->user1Login;
    }

    public function getUser2Login(): string{
        return $this->user2Login;
    }

    public function getLastMessageTimestamp(): int{
        return $this->last_message_timestamp;
    }
}
