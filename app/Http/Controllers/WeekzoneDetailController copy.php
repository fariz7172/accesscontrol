<?php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use App\Models\Weekzone;
use App\Models\WeekzoneDetail;
use App\Models\Dayzone;
use App\Models\DayzoneDetail;
use App\Models\deviceGateModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class WeekzoneDetailController extends Controller
{
    public function index()
    {
        $weekzoneDetails = WeekzoneDetail::with(['weekzone', 'day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7'])->paginate(9);
        $weekzones = Weekzone::all();
        $dayzones = Dayzone::all();
        $deviceGates = deviceGateModel::select('name', 'type', 'sn')->get();
        return view('weekzone.weekzonedetail.index', compact('weekzoneDetails', 'weekzones', 'dayzones', 'deviceGates'));
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
        $validator = Validator::make($request->all(), [
            'sn' => 'required|exists:devicegate,sn',
            'weekzone' => 'required|array',
            'weekzone.*.*' => 'nullable|integer|exists:dayzone,ID'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $sn = $request->sn;
        $weekzoneData = $request->weekzone;

        // Ambil URL dari tabel api, id = 15
        $apiConfig = ApiModel::find(15);

        if (!$apiConfig || empty($apiConfig->name)) {
            return redirect()->back()->with('error', 'API URL tidak ditemukan untuk id 15');
        }

        $apiUrl = $apiConfig->name; // http://localhost:787/sztimmy/weekzone

        // Initialize payload with default empty week slots
        $payload = [
            'sn' => $sn,
            'week1' => '0;0;0;0;0;0;0',
            'week2' => '0;0;0;0;0;0;0',
            'week3' => '0;0;0;0;0;0;0',
            'week4' => '0;0;0;0;0;0;0',
            'week5' => '0;0;0;0;0;0;0',
            'week6' => '0;0;0;0;0;0;0',
            'week7' => '0;0;0;0;0;0;0',
            'week8' => '0;0;0;0;0;0;0'
        ];

        // Process each week (1-8)
        foreach ($weekzoneData as $weekIndex => $days) {
            if ($weekIndex >= 1 && $weekIndex <= 8) {
                $dayIds = array_map(function ($day) {
                    return $day ?? '0';
                }, array_pad($days, 7, '0'));
                $payload["week{$weekIndex}"] = implode(';', $dayIds);
            }
        }

        try {
            $response = Http::post($apiUrl, $payload);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Weekzone data berhasil dikirim ke API');
            } else {
                return redirect()->back()->with('error', 'Gagal mengirim data ke API: ' . $response->body());
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
