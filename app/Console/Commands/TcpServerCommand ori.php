<?php

namespace App\Console\Commands;

use App\Jobs\SaveTcpLog;
use App\Models\deviceGateModel;
use App\Models\userLogModel;
use App\Models\userProfileModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'Run TCP server to listen for device data';

    public function handle()
    {
        // Set channel logging ke 'tcp_server'
        Log::setDefaultDriver('tcp_server');

        $ip = '0.0.0.0';
        $port = 8031;

        // Buat socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $this->error("Failed to create socket: " . socket_strerror(socket_last_error()));
            return;
        }

        // Set opsi socket
        if (socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1) === false) {
            $this->error("Failed to set socket option: " . socket_strerror(socket_last_error()));
            socket_close($socket);
            return;
        }

        // Bind socket
        if (socket_bind($socket, $ip, $port) === false) {
            $this->error("Failed to bind socket: " . socket_strerror(socket_last_error()));
            socket_close($socket);
            return;
        }

        // Listen pada socket
        if (socket_listen($socket, 5) === false) {
            $this->error("Failed to listen on socket: " . socket_strerror(socket_last_error()));
            socket_close($socket);
            return;
        }

        // Set socket ke mode non-blocking
        socket_set_nonblock($socket);

        $this->info("TCP server running on $ip:$port");

        $clients = [$socket];

        while (true) {
            $read = $clients;
            $write = $except = null;

            // Periksa socket yang siap dibaca
            $selectResult = @socket_select($read, $write, $except, null);
            if ($selectResult === false) {
                $this->error("Socket select failed: " . socket_strerror(socket_last_error()));
                continue; // Lanjutkan loop untuk menjaga server tetap berjalan
            }

            if ($selectResult < 1) {
                continue;
            }

            // Tangani koneksi baru
            if (in_array($socket, $read)) {
                $client = socket_accept($socket);
                if ($client !== false) {
                    $clients[] = $client;
                    $this->info("New client connected");
                }
                unset($read[array_search($socket, $read)]);
            }

            // Tangani data dari client
            foreach ($read as $client) {
                $buffer = '';
                $bytesRead = @socket_recv($client, $buffer, 4096, 0);

                if ($bytesRead === false) {
                    $this->info("Socket read error for client " . spl_object_id($client) . ": " . socket_strerror(socket_last_error($client)));
                    continue; // Jangan putuskan koneksi
                }

                if ($bytesRead === 0) {
                    // Hapus log berikut agar tidak muncul di terminal
                    // $this->info("No data received from client " . spl_object_id($client) . ", keeping connection open");
                    continue; // Jangan putuskan koneksi
                }

                $this->handleClient($client, trim($buffer));
            }
        }

        socket_close($socket);
    }

    private function handleClient($client, $data)
    {
        $start = microtime(true);

        $this->info('Data received: ' . $data);

        try {
            // Pisahkan data berdasarkan newline
            $dataLines = array_filter(explode("\n", trim($data)), fn($value) => $value !== '');

            foreach ($dataLines as $line) {
                $this->info('Processing line: ' . $line);

                $datas = array_filter(explode(' ', trim($line)), fn($value) => $value !== '');

                $userid = null;
                if (strpos($line, '(M03)Invalid card') !== false) {
                    $this->info('Invalid card detected, no UserID available');
                } else {
                    if (preg_match('/UserID:(\d+)/', $line, $matches)) {
                        $userid = (int)$matches[1];
                    } elseif (count($datas) >= 7) {
                        $potentialUserId = str_replace(["'", "No", "`"], '', $datas[7]);
                        $potentialUserId = ltrim($potentialUserId, '0');
                        if (is_numeric($potentialUserId)) {
                            $userid = (int)$potentialUserId;
                        }
                    }
                }

                $guest = str_replace(['[', ']'], '', $datas[2] ?? '');
                $guests = explode('(0)', $guest);
                $nodes = explode('.', $guests[0] ?? '');
                $nodeId = ltrim($nodes[0] ?? '', '0');

                $cardNumber = $this->extractCardNumber($line);
                $cardNumber2 = $this->extractCardNumberData($line);

                socket_getpeername($client, $clientIp);
                $tappingTime = now();
                $deviceType = 2;
                $card = $cardNumber2;

                $this->info("Waktu: $tappingTime | IP: $clientIp | Kartu1: $cardNumber | Kartu2: $cardNumber2 | Node: $nodeId | UserID: " . ($userid ?? 0) . " | DeviceType: $deviceType | Card: $card");

                // Dispatch job untuk setiap baris data
                SaveTcpLog::dispatch($tappingTime, $clientIp, $cardNumber, $cardNumber2, $nodeId, $userid, $deviceType, $card);
            }
        } catch (\Exception $e) {
            $this->error('Error processing client data: ' . $e->getMessage());
        }

        $this->info('Processing time: ' . (microtime(true) - $start) . ' seconds');
    }

    private function extractCardNumber($data)
    {
        if (preg_match('/(\d{5}):(\d{5})/', $data, $matches)) {
            $part1 = (int)$matches[1];
            $part2 = (int)$matches[2];
            $result = ($part1 << 16) + $part2;
            return (string)$result;
        }
        return '';
    }

    private function extractCardNumberData($data)
    {
        if (preg_match('/(\d{5}):(\d{5})/', $data, $matches)) {
            return $matches[1] . ':' . $matches[2];
        }
        return '';
    }
}