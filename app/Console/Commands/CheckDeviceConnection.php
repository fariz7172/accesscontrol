<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\deviceGateModel;
use App\Models\ApiModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckDeviceConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:check-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check connection status of all devices and update database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting device connection check...');
        
        $devices = deviceGateModel::select('id', 'sn', 'ip', 'type')->get();
        $apiUrl = ApiModel::where('id', 11)->value('name');
        
        foreach ($devices as $device) {
            $isConnected = false;
            $checkType = 'Unknown';

            if ($device->type == 1) {
                // Type 1 (Soyal) -> Ping Check
                $checkType = 'Ping';
                if ($device->ip) {
                    $output = [];
                    $exitCode = 1;
                    
                    // Windows logic
                     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        $command = "ping -n 1 -w 1000 " . $device->ip;
                        exec($command, $output, $exitCode);
                        
                        // Strict check for Windows: Look for "TTL=" in output
                        // ensure it's not "Destination host unreachable" which might exit 0 on some systems/versions
                        $isConnected = false;
                        foreach ($output as $line) {
                            if (stripos($line, 'TTL=') !== false) {
                                $isConnected = true;
                                break;
                            }
                        }
                    } else {
                        // Linux/Mac logic
                        $command = "ping -c 1 -W 1 " . $device->ip;
                        exec($command, $output, $exitCode);
                        $isConnected = ($exitCode === 0);
                    }
                }
            } else {
                // Other Types -> API Check
                $checkType = 'API';
                if ($apiUrl && $device->sn) {
                     try {
                        $response = Http::timeout(5)->post($apiUrl, [
                            'sn' => $device->sn,
                        ]);

                        if ($response->status() === 200) {
                            $isConnected = true;
                        }
                     } catch (\Exception $e) {
                         $isConnected = false;
                     }
                }
            }

            // Update database
            $deviceGate = deviceGateModel::find($device->id);
            if ($deviceGate) {
                if ($deviceGate->flagstatus != ($isConnected ? 1 : 0)) {
                    $deviceGate->flagstatus = $isConnected ? 1 : 0;
                    $deviceGate->save();
                    $this->info("Device {$device->sn} ({$checkType}): Status changed to " . ($isConnected ? 'Connected' : 'Disconnected'));
                } else {
                    //$this->line("Device {$device->sn} ({$checkType}): No change");
                }
            }
        }

        $this->info('Device connection check completed.');
    }
}
