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
}
