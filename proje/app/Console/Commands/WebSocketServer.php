<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\SignalingServer;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:serve';
    protected $description = 'WebSocket sunucusunu başlat';

    public function handle()
    {
        $this->info('WebSocket sunucusu başlatılıyor...');
        
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new SignalingServer()
                )
            ),
            6001,
            '0.0.0.0'
        );

        $server->run();
    }
} 