<?php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use App\Models\Weekzone;
use App\Models\WeekzoneDetail;
use App\Models\Dayzone;
use App\Models\deviceGateModel;
use App\Models\WeekzoneDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeekzoneDetailController extends Controller
{


    public function index()
    {
        $weekzoneDetails = WeekzoneDetail::with([
            'weekzone',
            'day1',
            'day2',
            'day3',
            'day4',
            'day5',
            'day6',
            'day7'
        ])->paginate(9);

        $weekzones = Weekzone::all();
        $dayzones = Dayzone::all();
        $deviceGates = deviceGateModel::select('name', 'type', 'sn')->get();

        // Untuk modal push (hanya ID 1-8)
        $weekzoneDetailsForApi = WeekzoneDetail::whereBetween('ID', [1, 8])
            ->with(['weekzone', 'day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7'])
            ->get();

        return view('weekzone.weekzonedetail.index', compact(
            'weekzoneDetails',
            'weekzones',
            'dayzones',
            'deviceGates',
            'weekzoneDetailsForApi'
        ));
    }

    public function create()
    {
        $weekzones = Weekzone::all();
        $dayzones = Dayzone::all();
        return view('weekzone.weekzonedetail.create', compact('weekzones', 'dayzones'));
    }

    public function store(Request $request)
    {
        if (WeekzoneDetail::count() >= 9) {
            return redirect()->back()->withErrors(['error' => 'Cannot create more than 9 weekzone detail records.'])->withInput();
        }

        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:weekzonedetail,ID',
            'wz' => 'required|integer|exists:weekzone,ID',
            'day1' => 'nullable|integer|exists:dayzone,ID',
            'day2' => 'nullable|integer|exists:dayzone,ID',
            'day3' => 'nullable|integer|exists:dayzone,ID',
            'day4' => 'nullable|integer|exists:dayzone,ID',
            'day5' => 'nullable|integer|exists:dayzone,ID',
            'day6' => 'nullable|integer|exists:dayzone,ID',
            'day7' => 'nullable|integer|exists:dayzone,ID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        WeekzoneDetail::create($request->all());
        return redirect()->route('weekzone.index')->with('success', 'Weekzone Detail created successfully.');
    }

    public function edit($id)
    {
        $weekzoneDetail = WeekzoneDetail::findOrFail($id);
        $weekzones = Weekzone::all();
        $dayzones = Dayzone::all();
        return view('weekzone.weekzonedetail.edit', compact('weekzoneDetail', 'weekzones', 'dayzones'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:weekzonedetail,ID,' . $id,
            'wz' => 'required|integer|exists:weekzone,ID',
            'day1' => 'nullable|integer|exists:dayzone,ID',
            'day2' => 'nullable|integer|exists:dayzone,ID',
            'day3' => 'nullable|integer|exists:dayzone,ID',
            'day4' => 'nullable|integer|exists:dayzone,ID',
            'day5' => 'nullable|integer|exists:dayzone,ID',
            'day6' => 'nullable|integer|exists:dayzone,ID',
            'day7' => 'nullable|integer|exists:dayzone,ID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $weekzoneDetail = WeekzoneDetail::findOrFail($id);
        $weekzoneDetail->update($request->all());
        return redirect()->route('weekzone.index')->with('success', 'Weekzone Detail updated successfully.');
    }

    public function destroy($id)
    {
        $weekzoneDetail = WeekzoneDetail::findOrFail($id);
        $weekzoneDetail->delete();
        return redirect()->route('weekzone.index')->with('success', 'Weekzone Detail deleted successfully.');
    }


    public function pushToApi(Request $request)
    {
        // 1️⃣ Validasi input
        $validator = Validator::make($request->all(), [
            'sn' => 'required|array|min:1',
            'weekzone_id' => 'required|array|min:1',
        ], [
            'sn.required' => 'Pilih minimal satu device.',
            'weekzone_id.required' => 'Pilih minimal satu weekzone untuk dikirim.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // 2️⃣ Ambil URL API dari tabel api (id = 15)
            $api = \App\Models\ApiModel::find(15);
            if (!$api) {
                return redirect()->back()->withErrors(['error' => 'API configuration (ID=15) not found.']);
            }

            $apiUrl = $api->name; // contoh: http://localhost:787/sztimmy/weekzone

            // 3️⃣ Ambil data weekzone detail yang dipilih
            $weekzoneDetails = \App\Models\WeekzoneDetail::whereIn('ID', $request->weekzone_id)->get();

            if ($weekzoneDetails->isEmpty()) {
                return redirect()->back()->withErrors(['error' => 'Weekzone detail tidak ditemukan.']);
            }

            // 4️⃣ Siapkan payload (list data untuk dikirim)
            $payload = [];

            foreach ($request->sn as $sn) {
                foreach ($weekzoneDetails as $detail) {
                    // Ambil nama week (field 'wz')
                    $weekName = $detail->wz;

                    // Ambil semua hari (day1..day7)
                    $days = [];
                    for ($i = 1; $i <= 7; $i++) {
                        $field = 'day' . $i;
                        $days[] = $detail->{$field} ?? 0; // Jika null, isi 0
                    }

                    // Bentuk string "1;2;3;..."
                    $weekString = implode(';', $days);

                    $payload[] = [
                        "sn" => $sn,
                        "WeekName" => $weekName,
                        "week" => $weekString
                    ];
                }
            }

            // 5️⃣ Kirim ke API menggunakan HTTP POST
            $response = Http::timeout(10)->post($apiUrl, $payload);

            // 6️⃣ Logging untuk debugging
            Log::info('Push Weekzone Payload:', $payload);
            Log::info('API Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // 7️⃣ Cek hasil
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Data Successfully Send To API.');
            } else {
                return redirect()->back()->withErrors([
                    'error' => 'Gagal mengirim data ke API. Status: ' . $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PushToApi Error:', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }
}
