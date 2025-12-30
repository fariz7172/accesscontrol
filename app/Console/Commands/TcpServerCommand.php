<?php

namespace App\Console\Commands;

use App\Jobs\SaveTcpLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TcpServerCommand extends Command
{
    protected $signature = 'tcp:server';
    protected $description = 'Run TCP server to listen for device data';

    public function handle()
    {
        Log::setDefaultDriver('tcp_server');

        $ip   = '0.0.0.0';
        $port = 8031;

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $this->error("Failed to create socket: " . socket_strerror(socket_last_error()));
            return 1;
        }

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        if (!socket_bind($socket, $ip, $port)) {
            $this->error("Failed to bind to $ip:$port - " . socket_strerror(socket_last_error()));
            socket_close($socket);
            return 1;
        }

        if (!socket_listen($socket, 10)) {
            $this->error("Failed to listen: " . socket_strerror(socket_last_error()));
            socket_close($socket);
            return 1;
        }

        socket_set_nonblock($socket);

        $this->info("TCP Server berjalan di {$ip}:{$port}");
        $this->info("Menunggu koneksi dari device...");

        $clients = [$socket];

        while (true) {
            $read   = $clients;
            $write  = $except = null;

            if (@socket_select($read, $write, $except, null) === false) {
                $this->error("socket_select() failed: " . socket_strerror(socket_last_error()));
                sleep(1);
                continue;
            }

            // Koneksi baru
            if (in_array($socket, $read)) {
                $newClient = @socket_accept($socket);
                if ($newClient !== false) {
                    socket_set_nonblock($newClient);
                    $clients[] = $newClient;
                    socket_getpeername($newClient, $ip, $port);
                    $this->info("Device terhubung → {$ip}:{$port}");
                }
                $key = array_search($socket, $read);
                if ($key !== false) unset($read[$key]);
            }

            // Baca data dari client
            foreach ($read as $clientSocket) {
                $buffer = '';
                $bytes = @socket_recv($clientSocket, $buffer, 4096, 0);

                if ($bytes === false || $bytes === 0) {
                    $this->closeClient($clientSocket, $clients);
                    continue;
                }

                if ($bytes > 0) {
                    $this->handleClient($clientSocket, trim($buffer));
                }
            }

            usleep(10000); // 10ms
        }
    }

    private function closeClient($clientSocket, array &$clients)
    {
        $key = array_search($clientSocket, $clients);
        if ($key !== false) unset($clients[$key]);

        socket_getpeername($clientSocket, $ip, $port);
        $this->info("Device terputus ← {$ip}:{$port}");

        @socket_close($clientSocket);
    }

    private function handleClient($client, $data)
    {
        $start = microtime(true);

        if (empty(trim($data))) return;

        $this->info("Data diterima: {$data}");

        try {
            $lines = array_filter(explode("\n", trim($data)));

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $this->info("Memproses: {$line}");

                $datas = array_filter(explode(' ', $line));

                // === EXTRACT USERID — PERSIS SEPERTI KODE LAMA YANG SUDAH BENAR ===
                $userid = null;
                if (strpos($line, '(M03)Invalid card') === false) {
                    if (preg_match('/UserID:(\d+)/', $line, $matches)) {
                        $userid = (int)$matches[1];
                    }
                    // PERBAIKAN PENTING: Harus >= 7, bukan >= 8!
                    elseif (count($datas) >= 7) {
                        $potentialUserId = str_replace(["'", "No", "`"], '', $datas[7] ?? '');
                        $potentialUserId = ltrim($potentialUserId, '0');
                        if (is_numeric($potentialUserId) && $potentialUserId !== '') {
                            $userid = (int)$potentialUserId;
                        }
                    }
                }

                // === NODE ID ===
                $guest  = str_replace(['[', ']'], '', $datas[2] ?? '');
                $guests = explode('(0)', $guest);
                $nodes  = explode('.', $guests[0] ?? '');
                $nodeId = ltrim($nodes[0] ?? '', '0') ?: '0';

                // === CARD ===
                $cardNumber  = $this->extractCardNumber($line);      // Kartu1 (decimal)
                $cardNumber2 = $this->extractCardNumberData($line);  // Kartu2 (raw)

                socket_getpeername($client, $clientIp);
                $tappingTime = now();
                $deviceType  = 2;
                $card        = $cardNumber2 ?: $cardNumber;

                // LOG 
                $this->info("Waktu: {$tappingTime} | IP: {$clientIp} | Kartu1: {$cardNumber} | Kartu2: {$cardNumber2} | Node: {$nodeId} | UserID: " . ($userid ?? 'null') . " | DeviceType: {$deviceType} | Card: {$card}");

                // Dispatch ke Job untuk simpan kedalam database yang data nya diambil dari folder jobs/SaveTcpLog
                SaveTcpLog::dispatch(
                    $tappingTime,
                    $clientIp,
                    $cardNumber,
                    $cardNumber2,
                    $nodeId,
                    $userid,
                    $deviceType,
                    $card
                );
            }
        } catch (\Exception $e) {
            $this->error('Error processing data: ' . $e->getMessage());
            Log::error('TCP Handle Exception', ['exception' => $e, 'data' => $data]);
        }

        $duration = round(microtime(true) - $start, 4);
        $this->info("Selesai proses dalam {$duration}s");
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