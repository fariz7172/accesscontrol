<?php

namespace App\Console\Commands;

use App\Jobs\SaveTcpLog;
use App\Models\deviceGateModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'Run TCP server to listen for device data (Windows & Linux compatible)';

    // Gunakan spl_object_hash() agar aman di Windows
    private $connectedDevices = []; // hash => info

    public function handle()
    {
        Log::setDefaultDriver('tcp_server');

        $ip   = '0.0.0.0';
        $port = 8031;

        $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$serverSocket) {
            $this->error('Failed to create socket: ' . socket_strerror(socket_last_error()));
            return 1;
        }

        socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
        if (!socket_bind($serverSocket, $ip, $port)) {
            $this->error('Failed to bind: ' . socket_strerror(socket_last_error($serverSocket)));
            socket_close($serverSocket);
            return 1;
        }

        if (!socket_listen($serverSocket, 10)) {
            $this->error('Failed to listen: ' . socket_strerror(socket_last_error($serverSocket)));
            socket_close($serverSocket);
            return 1;
        }

        socket_set_nonblock($serverSocket);

        $this->info("TCP Server berjalan di {$ip}:{$port}");
        $this->line("Menunggu koneksi dari device...");

        $clients = [$serverSocket];

        while (true) {
            $read = $clients;
            $write = $except = null;

            if (@socket_select($read, $write, $except, 1) === false) {
                if (socket_last_error() === SOCKET_EINTR) break;
                continue;
            }

            // === Koneksi baru ===
            if (in_array($serverSocket, $read)) {
                if ($newSocket = socket_accept($serverSocket)) {
                    socket_set_nonblock($newSocket);
                    $clients[] = $newSocket;

                    socket_getpeername($newSocket, $clientIp, $clientPort);
                    $this->info("New connection → {$clientIp}:{$clientPort}");

                    // Gunakan spl_object_hash() → aman di Windows & Linux
                    $hash = spl_object_hash($newSocket);

                    $this->connectedDevices[$hash] = [
                        'ip'       => $clientIp,
                        'port'     => $clientPort,
                        'socket'   => $newSocket,
                        'nodeId'   => null,
                        'deviceSn' => null,
                        'lastSeen' => time(),
                    ];
                }
                $key = array_search($serverSocket, $read);
                unset($read[$key]);
            }

            // === Proses data / disconnect ===
            foreach ($read as $clientSocket) {
                $hash = spl_object_hash($clientSocket);

                $buffer = '';
                $bytes  = @socket_recv($clientSocket, $buffer, 8192, 0);

                // Koneksi putus
                if ($bytes === false || $bytes === 0) {
                    $this->handleClientDisconnect($clientSocket, $clients, $hash);
                    continue;
                }

                $data = trim($buffer);
                if ($data !== '') {
                    $this->handleClientData($clientSocket, $data, $hash);
                }

                // Update last seen
                if (isset($this->connectedDevices[$hash])) {
                    $this->connectedDevices[$hash]['lastSeen'] = time();
                }
            }
        }

        // Cleanup
        foreach ($clients as $c) {
            if ($c !== $serverSocket) @socket_close($c);
        }
        socket_close($serverSocket);
        $this->info('TCP Server dihentikan.');
    }

    private function handleClientDisconnect($clientSocket, &$clients, $hash)
    {
        socket_getpeername($clientSocket, $ip);

        if (isset($this->connectedDevices[$hash])) {
            $info = $this->connectedDevices[$hash];
            $node = $info['nodeId'] ? "NodeID: {$info['nodeId']}" : '';
            $this->warn("Device disconnected → {$ip} {$node}");
            unset($this->connectedDevices[$hash]);
        } else {
            $this->warn("Unknown client disconnected → {$ip}");
        }

        $key = array_search($clientSocket, $clients);
        if ($key !== false) unset($clients[$key]);
        socket_close($clientSocket);
    }

    private function handleClientData($clientSocket, $rawData, $hash)
    {
        $deviceInfo = $this->connectedDevices[$hash] ?? null;
        $ip = $deviceInfo['ip'] ?? 'unknown';

        $lines = array_filter(explode("\n", trim($rawData)));

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $this->info("← {$ip} | {$line}");

            // Ekstrak NodeID
            if (preg_match('/\[(\d{3})\./', $line, $m)) {
                $nodeId = ltrim($m[1], '0') ?: '0';

                if ($deviceInfo && $deviceInfo['nodeId'] !== $nodeId) {
                    $device = deviceGateModel::where('nodeid', $nodeId)->first();
                    $sn = $device?->sn ?? $nodeId;

                    $this->connectedDevices[$hash]['nodeId']   = $nodeId;
                    $this->connectedDevices[$hash]['deviceSn'] = $sn;

                    $this->info("Device identified → IP: {$ip} | NodeID: {$nodeId} | SN: {$sn}");
                }
            }

            $this->processLine($line, $ip);
        }
    }

    private function processLine($line, $clientIp)
    {
        preg_match('/\[(\d{3})\./', $line, $nodeMatch);
        $nodeId = $nodeMatch ? ltrim($nodeMatch[1], '0') : '0';

        $cardNumber  = $this->extractCardNumber($line);
        $cardNumber2 = $this->extractCardNumberData($line);

        $userid = null;
        if (strpos($line, '(M03)Invalid card') === false) {
            if (preg_match('/UserID:(\d+)/', $line, $m)) {
                $userid = (int)$m[1];
            } elseif (preg_match('/`(\d+)`/', $line, $m)) {
                $userid = (int)$m[1];
            }
        }

        SaveTcpLog::dispatch(
            now(),
            $clientIp,
            $cardNumber,
            $cardNumber2,
            $nodeId,
            $userid ?? 0,
            2,
            $cardNumber2 ?: $cardNumber
        );
    }

    private function extractCardNumber($data)
    {
        if (preg_match('/(\d{5}):(\d{5})/', $data, $m)) {
            return (string)((int)$m[1] << 16 | (int)$m[2]);
        }
        return '';
    }

    private function extractCardNumberData($data)
    {
        if (preg_match('/(\d{5}):(\d{5})/', $data, $m)) {
            return $m[1] . ':' . $m[2];
        }
        return '';
    }
}