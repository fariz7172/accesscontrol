<?php

namespace App\Http\Controllers;

use App\Models\PtSchedule;
use App\Models\userProfileModel;
use App\Models\PtAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Exports\PtScheduleExport;
use Maatwebsite\Excel\Facades\Excel;

class PtScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = PtSchedule::with(['personalTrainer', 'member']);

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('CREATED_AT', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('CREATED_AT', '<=', $request->end_date);
        }

        $schedules = $query->paginate(50);

        // Append filter parameters to pagination links
        $schedules->appends($request->only(['start_date', 'end_date']));

        return view('pt_schedule.index', compact('schedules'));
    }



    public function create()
    {
        $personalTrainers = userProfileModel::where('user_type', '2')->get();
        $members = userProfileModel::where('user_type', '1')->get();
        Log::info('Fetched data for create schedule', [
            'personalTrainers' => $personalTrainers->toArray(),
            'members' => $members->toArray()
        ]);
        return view('pt_schedule.create', compact('personalTrainers', 'members'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'PT_ID' => [
                'required',
                'exists:usersprofile,ID',
                function ($attribute, $value, $fail) {
                    $trainer = userProfileModel::where('ID', $value)->where('user_type', '2')->first();
                    if (!$trainer) {
                        $fail('The selected Personal Trainer is not valid or not a trainer.');
                    }
                },
                function ($attribute, $value, $fail) use ($request) {
                    // Check if PT_ID exists in pt_availability
                    $availability = PtAvailability::where('PT_ID', $value)->first();
                    if (!$availability) {
                        $fail('Trainer Belum Ada Jadwal');
                    }

                    // Get START_TIME and END_TIME from request
                    $startTime = Carbon::parse($request->input('START_TIME_formatted'));
                    $endTime = Carbon::parse($request->input('END_TIME_formatted'));
                    $dow = $startTime->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)

                    // Fetch active availabilities for the PT_ID and DOW
                    $availabilities = PtAvailability::where('PT_ID', $value)
                        ->where('DOW', $dow)
                        ->where('IS_ACTIVE', 1)
                        ->get();

                    // Check if START_TIME and END_TIME are within an available slot
                    $isValid = false;
                    foreach ($availabilities as $avail) {
                        $availStart = Carbon::parse($avail->START_TIME)->setDate($startTime->year, $startTime->month, $startTime->day);
                        $availEnd = Carbon::parse($avail->END_TIME)->setDate($startTime->year, $startTime->month, $startTime->day);
                        if ($startTime->gte($availStart) && $endTime->lte($availEnd)) {
                            $isValid = true;
                            break;
                        }
                    }

                    if (!$isValid) {
                        $fail('Jadwal Belum Tersedia');
                    }

                    // Check if START_TIME is less than any availability START_TIME
                    $hasValidStartTime = false;
                    foreach ($availabilities as $avail) {
                        $availStart = Carbon::parse($avail->START_TIME)->setDate($startTime->year, $startTime->month, $startTime->day);
                        if ($startTime->gte($availStart)) {
                            $hasValidStartTime = true;
                            break;
                        }
                    }

                    if (!$hasValidStartTime) {
                        $fail('Tentukan Waktu Sesuai Jadwal Masuk Trainer');
                    }
                },
            ],
            'MEMBER_ID' => 'required|array|min:1',
            'MEMBER_ID.*' => [
                'required',
                'exists:usersprofile,ID',
                function ($attribute, $value, $fail) use ($request) {
                    $member = userProfileModel::where('ID', $value)->where('user_type', '1')->first();
                    if (!$member) {
                        $fail("The selected Member ID {$value} is not valid or not a member.");
                    }

                    // Check for existing schedule with same START_TIME and END_TIME for the member
                    $startTime = $request->input('START_TIME_formatted');
                    $endTime = $request->input('END_TIME_formatted');
                    $existingSchedule = PtSchedule::where('MEMBER_ID', $value)
                        ->where('START_TIME', $startTime)
                        ->where('END_TIME', $endTime)
                        ->exists();

                    if ($existingSchedule) {
                        $fail("Member ID {$value} sudah terdaftar di jam yang lain.");
                    }
                },
            ],
            'START_TIME_formatted' => 'required|date_format:Y-m-d H:i:s',
            'END_TIME_formatted' => 'required|date_format:Y-m-d H:i:s|after:START_TIME_formatted',
            'BOOKED_AT_formatted' => 'required|date_format:Y-m-d H:i:s',
            'STATUS' => 'required|in:0,1',
            'NOTE' => 'nullable|string|max:255',
        ], [
            'START_TIME_formatted.date_format' => 'Start Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME_formatted.date_format' => 'End Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME_formatted.after' => 'End Time must be after Start Time.',
            'BOOKED_AT_formatted.date_format' => 'Booked At must be in the format YYYY-MM-DD HH:MM:SS.',
            'MEMBER_ID.required' => 'At least one Member must be selected.',
            'MEMBER_ID.*.exists' => 'One or more selected Members are invalid.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for storing schedule', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create a schedule for each selected MEMBER_ID
        foreach ($request->input('MEMBER_ID') as $memberId) {
            PtSchedule::create([
                'PT_ID' => $request->PT_ID,
                'MEMBER_ID' => $memberId,
                'START_TIME' => $request->START_TIME_formatted,
                'END_TIME' => $request->END_TIME_formatted,
                'BOOKED_AT' => $request->BOOKED_AT_formatted ?: Carbon::now('Asia/Jakarta'),
                'STATUS' => $request->STATUS,
                'NOTE' => $request->NOTE,
                'CREATED_AT' => Carbon::now('Asia/Jakarta'),
            ]);
        }

        Log::info('Schedules created', ['data' => $request->all()]);
        return redirect()->route('pt_schedule.index')->with('success', 'Schedules created successfully.');
    }

    public function show($id)
    {
        $schedule = PtSchedule::with(['personalTrainer', 'member'])->findOrFail($id);
        Log::info('Fetched schedule details', ['schedule' => $schedule->toArray()]);
        return view('pt_schedule.show', compact('schedule'));
    }

    public function edit($id)
    {
        $schedule = PtSchedule::findOrFail($id);
        $personalTrainers = userProfileModel::where('user_type', '2')->get();
        $members = userProfileModel::where('user_type', '1')->get();
        Log::info('Fetched data for edit schedule', [
            'schedule' => $schedule->toArray(),
            'personalTrainers' => $personalTrainers->toArray(),
            'members' => $members->toArray()
        ]);
        return view('pt_schedule.edit', compact('schedule', 'personalTrainers', 'members'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'PT_ID' => [
                'required',
                'exists:usersprofile,ID',
                function ($attribute, $value, $fail) {
                    $trainer = userProfileModel::where('ID', $value)->where('user_type', '2')->first();
                    if (!$trainer) {
                        $fail('The selected Personal Trainer is not valid or not a trainer.');
                    }
                },
            ],
            'MEMBER_ID' => [
                'required',
                'exists:usersprofile,ID',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $member = userProfileModel::where('ID', $value)->where('user_type', '1')->first();
                    if (!$member) {
                        $fail('The selected Member is not valid or not a member.');
                    }

                    // Check for existing schedule with same START_TIME and END_TIME for the member, excluding current schedule
                    $startTime = $request->input('START_TIME_formatted');
                    $endTime = $request->input('END_TIME_formatted');
                    $existingSchedule = PtSchedule::where('MEMBER_ID', $value)
                        ->where('START_TIME', $startTime)
                        ->where('END_TIME', $endTime)
                        ->where('ID', '!=', $id)
                        ->exists();

                    if ($existingSchedule) {
                        $fail('Anda Sudah Terdaftar Di Jam Yang lain');
                    }
                },
            ],
            'START_TIME_formatted' => 'required|date_format:Y-m-d H:i:s',
            'END_TIME_formatted' => 'required|date_format:Y-m-d H:i:s|after:START_TIME_formatted',
            'BOOKED_AT_formatted' => 'required|date_format:Y-m-d H:i:s',
            'STATUS' => 'required|in:0,1',
            'NOTE' => 'nullable|string|max:255',
        ], [
            'START_TIME_formatted.date_format' => 'Start Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME_formatted.date_format' => 'End Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME_formatted.after' => 'End Time must be after Start Time.',
            'BOOKED_AT_formatted.date_format' => 'Booked At must be in the format YYYY-MM-DD HH:MM:SS.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for updating schedule', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $schedule = PtSchedule::findOrFail($id);
        $schedule->update([
            'PT_ID' => $request->PT_ID,
            'MEMBER_ID' => $request->MEMBER_ID,
            'START_TIME' => $request->START_TIME_formatted,
            'END_TIME' => $request->END_TIME_formatted,
            'BOOKED_AT' => $request->BOOKED_AT_formatted ?: Carbon::now('Asia/Jakarta'),
            'STATUS' => $request->STATUS,
            'NOTE' => $request->NOTE,
        ]);

        Log::info('Schedule updated', ['id' => $id, 'data' => $request->all()]);
        return redirect()->route('pt_schedule.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy($id)
    {
        $schedule = PtSchedule::findOrFail($id);
        $schedule->delete();
        Log::info('Schedule deleted', ['id' => $id]);
        return redirect()->route('pt_schedule.index')->with('success', 'Schedule deleted successfully.');
    }

    public function export(Request $request)
    {
        // Query data sama seperti di index (supaya filter tanggal tetap berlaku)
        $query = PtSchedule::with(['personalTrainer', 'member']);

        if ($request->filled('start_date')) {
            $query->whereDate('CREATED_AT', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('CREATED_AT', '<=', $request->end_date);
        }

        $schedules = $query->get();

        // Nama file dengan tanggal sekarang
        $fileName = 'pt_schedules_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        // Gunakan class PtScheduleExport
        return Excel::download(new \App\Exports\PtScheduleExport($schedules), $fileName);
    }
}
