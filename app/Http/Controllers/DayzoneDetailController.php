<?php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use App\Models\Dayzone;
use App\Models\DayzoneDetail;
use App\Models\deviceGateModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class DayzoneDetailController extends Controller
{
    public function index()
    {
        $dayzoneDetails = DayzoneDetail::with('dayzone')->paginate(10);
        $dayzones = Dayzone::all();
        $deviceGates = deviceGateModel::select('name', 'sn')->get();
        return view('dayzone.dayzonedetail.index', compact('dayzoneDetails', 'dayzones', 'deviceGates'));
    }

    public function create()
    {
        $dayzones = Dayzone::all();
        return view('dayzone.dayzonedetail.create', compact('dayzones'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:dayzonedetail,ID',
            'dz' => 'required|integer|exists:dayzone,ID',
            'Stz1' => 'required|date_format:H:i',
            'Etz1' => 'required|date_format:H:i',
            'Stz2' => 'required|date_format:H:i',
            'Etz2' => 'required|date_format:H:i',
            'Stz3' => 'required|date_format:H:i',
            'Etz3' => 'required|date_format:H:i',
            'Stz4' => 'required|date_format:H:i',
            'Etz4' => 'required|date_format:H:i',
            'Stz5' => 'required|date_format:H:i',
            'Etz5' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        foreach (['Stz1', 'Etz1', 'Stz2', 'Etz2', 'Stz3', 'Etz3', 'Stz4', 'Etz4', 'Stz5', 'Etz5'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = $data[$field] . ':00';
            }
        }

        DayzoneDetail::create($data);
        return redirect()->route('dayzonedetail.index')->with('success', 'Dayzone Detail created successfully.');
    }

    public function edit($id)
    {
        $dayzoneDetail = DayzoneDetail::findOrFail($id);
        foreach (['Stz1', 'Etz1', 'Stz2', 'Etz2', 'Stz3', 'Etz3', 'Stz4', 'Etz4', 'Stz5', 'Etz5'] as $field) {
            if (!is_null($dayzoneDetail->$field)) {
                $dayzoneDetail->$field = substr($dayzoneDetail->$field, 0, 5);
            }
        }
        $dayzones = Dayzone::all();
        return view('dayzone.dayzonedetail.edit', compact('dayzoneDetail', 'dayzones'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|unique:dayzonedetail,ID,' . $id,
            'dz' => 'required|integer|exists:dayzone,ID',
            'Stz1' => 'required|date_format:H:i',
            'Etz1' => 'required|date_format:H:i',
            'Stz2' => 'required|date_format:H:i',
            'Etz2' => 'required|date_format:H:i',
            'Stz3' => 'required|date_format:H:i',
            'Etz3' => 'required|date_format:H:i',
            'Stz4' => 'required|date_format:H:i',
            'Etz4' => 'required|date_format:H:i',
            'Stz5' => 'required|date_format:H:i',
            'Etz5' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        foreach (['Stz1', 'Etz1', 'Stz2', 'Etz2', 'Stz3', 'Etz3', 'Stz4', 'Etz4', 'Stz5', 'Etz5'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = $data[$field] . ':00';
            }
        }

        $dayzoneDetail = DayzoneDetail::findOrFail($id);
        $dayzoneDetail->update($data);
        return redirect()->route('dayzonedetail.index')->with('success', 'Dayzone Detail updated successfully.');
    }

    public function destroy($id)
    {
        $dayzoneDetail = DayzoneDetail::findOrFail($id);
        $dayzoneDetail->delete();
        return redirect()->route('dayzonedetail.index')->with('success', 'Dayzone Detail deleted successfully.');
    }

    // === PERBAIKAN: Push To API (FULL SYNC) ===
    public function pushToApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sn' => 'required|array|min:1',
            'sn.*' => 'exists:devicegate,sn',
        ], [
            'sn.required' => 'Pilih minimal satu device.',
            'sn.min' => 'Pilih minimal satu device.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $sns = $request->sn;

        // Ambil SEMUA DayzoneDetail ID 1-8
        $allDetails = DayzoneDetail::whereBetween('ID', [1, 8])->get();

        if ($allDetails->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data Dayzone Detail (ID 1-8) untuk dikirim.');
        }

        // Ambil URL dari tabel api, id = 14
        $apiConfig = ApiModel::find(14);
        if (!$apiConfig || empty($apiConfig->name)) {
            return redirect()->back()->with('error', 'API URL tidak ditemukan (id: 14).');
        }
        $apiUrl = $apiConfig->name;

        // Format waktu: HH:MM:SS → HH:MM
        $formatTime = fn($time) => $time && $time !== '00:00:00' ? substr($time, 0, 5) : '00:00';

        $payloads = [];

        foreach ($sns as $sn) {
            // Inisialisasi default semua Day1–Day8
            $payload = array_fill_keys(
                ['Day1', 'Day2', 'Day3', 'Day4', 'Day5', 'Day6', 'Day7', 'Day8'],
                '00:00~00:00;00:00~00:00;00:00~00:00;00:00~00:00;00:00~00:00'
            );
            $payload['sn'] = $sn;

            // Isi SEMUA Dayzone dari database
            foreach ($allDetails as $detail) {
                $dayKey = "Day{$detail->ID}";
                $timeSlots = [];

                for ($i = 1; $i <= 5; $i++) {
                    $start = $detail->{"Stz{$i}"};
                    $end = $detail->{"Etz{$i}"};
                    $startFmt = $formatTime($start);
                    $endFmt = $formatTime($end);

                    $timeSlots[] = ($startFmt !== '00:00' || $endFmt !== '00:00')
                        ? "{$startFmt}~{$endFmt}"
                        : '00:00~00:00';
                }

                $payload[$dayKey] = implode(';', $timeSlots);
            }

            $payloads[] = $payload;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl, $payloads);

            if ($response->successful()) {
                return redirect()->back()->with(
                    'success',
                    'Semua Dayzone (ID 1-8) berhasil dikirim ke ' . count($sns) . ' device.'
                );
            } else {
                return redirect()->back()->with('error', 'Gagal mengirim ke API: ' . $response->body());
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}