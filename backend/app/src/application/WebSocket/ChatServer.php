<?php
namespace boz\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        if ($data->type === 'auth') {
            $this->userConnections[$data->userLogin] = $from;
        } elseif ($data->type === 'message') {
            if (isset($this->userConnections[$data->receiverLogin])) {
                $receiverConn = $this->userConnections[$data->receiverLogin];
                $receiverConn->send(json_encode([
                    'type' => 'message',
                    'from' => $data->senderLogin,
                    'content' => $data->content,
                    'timestamp' => time()
                ]));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}
