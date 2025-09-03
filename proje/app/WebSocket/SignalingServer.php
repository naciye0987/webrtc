<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SignalingServer implements MessageComponentInterface
{
    protected $clients;
    protected $rooms = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Yeni bağlantı! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);
        
        if ($data->type === 'join') {
            $this->rooms[$data->room][$from->resourceId] = $from;
            
            // Odadaki diğer kullanıcıya bildir
            foreach ($this->rooms[$data->room] as $client) {
                if ($client !== $from) {
                    $client->send(json_encode([
                        'type' => 'user-joined',
                        'userId' => $from->resourceId
                    ]));
                }
            }
        } else {
            // WebRTC sinyallerini ilet
            foreach ($this->rooms[$data->room] as $client) {
                if ($client !== $from) {
                    $client->send($msg);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        // Odalardan çıkar
        foreach ($this->rooms as $roomId => $room) {
            if (isset($room[$conn->resourceId])) {
                unset($this->rooms[$roomId][$conn->resourceId]);
                
                // Diğer kullanıcıya bildir
                foreach ($room as $client) {
                    $client->send(json_encode([
                        'type' => 'user-left',
                        'userId' => $conn->resourceId
                    ]));
                }
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Hata: {$e->getMessage()}\n";
        $conn->close();
    }
} 