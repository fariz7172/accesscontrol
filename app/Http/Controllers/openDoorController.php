<?php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use App\Models\deviceGateModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class openDoorController extends Controller
{

    public function index()
    {
        $devices = deviceGateModel::with(['userProfiles'])->paginate(10);
        $sztimmyGetOpenDoorApiUrl = ApiModel::where('id', 9)->value('name'); // Ambil URL dari ApiModel
        return view('door.index', compact('devices', 'sztimmyGetOpenDoorApiUrl'));
    }

    public function openDevice($id)
    {
        // Ambil device berdasarkan ID
        $device = deviceGateModel::find($id);

        // Cek apakah device ada
        if (!$device) {
            return redirect()->back()->with('error', 'Device not found.');
        }

        // Ambil URL API dari tabel ApiModel
        $sztimmyGetOpenDoorApiUrl = ApiModel::where('id', 9)->value('name');

        try {
            // Kirim data SN ke API
            $response = Http::post($sztimmyGetOpenDoorApiUrl, [
                'sn' => $device->sn,
            ]);

            if ($response->successful()) {
                $deviceName = $device->name;
                return redirect()->back()->with('success', 'Pintu <b>' . $deviceName . '</b> Berhasil Dibuka');
            } else {
                return redirect()->back()->with('error', 'Gagal mengirim data ke API');
            }
        } catch (\Exception $e) {
            // Cek jika error terkait dengan cURL error 7
            if (strpos($e->getMessage(), 'cURL error 7') !== false) {
                return redirect()->back()->with('error', 'DATA TIDAK TERHUBUNG DENGAN SERVER, CHECK KONEKSI');
            } else {
                return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        }
    }

    public function openGate(Request $request)
    {
        $nodeId = intval($request->node_id);
        $ipAddress = $request->ip_address;

        // Buat array byte perintah
        $bytes = [];
        $bytes[0] = 0x7E; // Header
        $bytes[1] = 0x06; // Panjang data
        $bytes[2] = $nodeId; // ID perangkat
        $bytes[3] = 0x21; // Perintah Open Gate
        $bytes[4] = 0x84; // Kode aksi
        $bytes[5] = 0x00; // Data tambahan

        // Hitung LRC (Longitudinal Redundancy Check)
        $LRC = 255;
        for ($i = 2; $i <= 5; $i++) {
            $LRC ^= $bytes[$i];
        }
        $bytes[6] = $LRC;

        // Hitung checksum tambahan
        $sum = array_sum(array_slice($bytes, 2, 5));
        $bytes[7] = $sum % 256;

        // Kirim perintah ke perangkat Soyal 725e melalui TCP
        $socket = @stream_socket_client("tcp://$ipAddress:1621", $errno, $errstr, 5);

        if (!$socket) {
            return response()->json(['success' => false, 'message' => "Gagal terhubung ke perangkat: $errstr"], 500);
        }

        fwrite($socket, pack('C*', ...$bytes));
        stream_set_timeout($socket, 15);

        $response = fread($socket, 8);
        fclose($socket);

        if ($response) {
            // Konversi respons ke Hex String
            $responseHex = '';
            foreach (str_split($response) as $char) {
                $responseHex .= ' ' . $this->asciiToHex($char);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pintu berhasil dibuka!',
                'response' => trim($responseHex) // ASCII to HEX
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada respons dari perangkat.'], 500);
    }

    // Fungsi Konversi ASCII ke HEX
    private function asciiToHex($char)
    {
        return strtoupper(dechex(ord($char)));
    }
}
