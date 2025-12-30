<?php

namespace App\Http\Controllers;

use App\Models\PtSchedule;
use App\Models\userProfileModel;
use App\Models\PtAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MemberScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:member');
    }

    public function index()
    {
        $member = Auth::guard('member')->user();
        if (!$member || $member->user_type != '1') {
            Log::warning('Unauthorized access attempt to member schedule index', ['user_id' => $member ? $member->ID : null]);
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengakses halaman ini.');
        }

        $schedules = PtSchedule::with(['personalTrainer'])
            ->where('MEMBER_ID', $member->ID)
            ->whereIn('STATUS', [0, 1])
            ->orderBy('START_TIME', 'asc')
            ->get();

        $availabilities = PtAvailability::with(['personalTrainer'])
            ->where('IS_ACTIVE', 1)
            ->get();

        $personalTrainers = userProfileModel::where('user_type', '2')->get();

        Log::info('Fetched member schedules, availabilities, and trainers', [
            'member_id' => $member->ID,
            'schedules' => $schedules->toArray(),
            'availabilities' => $availabilities->toArray(),
            'personalTrainers' => $personalTrainers->toArray()
        ]);

        return view('member_schedule.index', compact('schedules', 'member', 'availabilities', 'personalTrainers'));
    }


    public function book(Request $request)
    {
        $member = Auth::guard('member')->user();
        if (!$member || $member->user_type != '1') {
            Log::warning('Unauthorized access attempt to book schedule', ['user_id' => $member ? $member->ID : null]);
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk memesan jadwal.');
        }

        $validator = Validator::make($request->all(), [
            'availability_id' => 'required|exists:pt_availability,ID',
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
            'START_TIME_formatted' => 'required|date_format:Y-m-d H:i:s',
            'END_TIME_formatted' => 'required|date_format:Y-m-d H:i:s|after:START_TIME_formatted',
            'BOOKED_AT' => 'required|date_format:Y-m-d',
            'STATUS' => 'required|in:0,1,2',
            'NOTE' => 'nullable|string|max:255',
        ], [
            'START_TIME_formatted.date_format' => 'Start Time harus dalam format YYYY-MM-DD HH:MM:SS.',
            'END_TIME_formatted.date_format' => 'End Time harus dalam format YYYY-MM-DD HH:MM:SS.',
            'END_TIME_formatted.after' => 'End Time harus setelah Start Time.',
            'BOOKED_AT.date_format' => 'Booked At harus dalam format YYYY-MM-DD.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for booking schedule', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $availability = PtAvailability::where('ID', $request->availability_id)
            ->where('PT_ID', $request->PT_ID)
            ->where('IS_ACTIVE', 1)
            ->first();

        if (!$availability) {
            Log::warning('Invalid or inactive trainer availability', [
                'availability_id' => $request->availability_id,
                'PT_ID' => $request->PT_ID
            ]);
            return redirect()->back()->withErrors('Ketersediaan trainer tidak valid atau tidak aktif.')->withInput();
        }

        // Combine BOOKED_AT date with availability times
        $bookedDate = Carbon::parse($request->BOOKED_AT);
        $availabilityStartDateTime = Carbon::parse($availability->START_TIME);
        $availabilityEndDateTime = Carbon::parse($availability->END_TIME);
        $requestStartDateTime = Carbon::parse($request->START_TIME_formatted);
        $requestEndDateTime = Carbon::parse($request->END_TIME_formatted);

        // Check if the requested times match the availability times
        if (
            $availabilityStartDateTime->format('Y-m-d H:i:s') != $requestStartDateTime->format('Y-m-d H:i:s') ||
            $availabilityEndDateTime->format('Y-m-d H:i:s') != $requestEndDateTime->format('Y-m-d H:i:s')
        ) {
            Log::warning('Mismatched start or end time', [
                'availability_id' => $request->availability_id,
                'provided_start' => $requestStartDateTime,
                'provided_end' => $requestEndDateTime
            ]);
            return redirect()->back()->withErrors('Waktu yang dipilih tidak sesuai dengan ketersediaan trainer.')->withInput();
        }

        // Check if BOOKED_AT date is greater than the END_TIME date
        if ($bookedDate->gt($availabilityEndDateTime->startOfDay())) {
            Log::warning('BOOKED_AT date exceeds availability END_TIME date', [
                'booked_at' => $bookedDate->format('Y-m-d'),
                'availability_end_date' => $availabilityEndDateTime->format('Y-m-d')
            ]);
            return redirect()->back()->withErrors('Jadwal Melebihi Waktu yang ditentukan')->withInput();
        }

        // Check for overlapping schedules for the trainer with strict inequalities
        $existingTrainerSchedule = PtSchedule::where('PT_ID', $request->PT_ID)
            ->where('START_TIME', '<', $requestEndDateTime)
            ->where('END_TIME', '>', $requestStartDateTime)
            ->where('STATUS', '!=', 2)
            ->exists();

        if ($existingTrainerSchedule) {
            Log::warning('Schedule conflict detected for trainer', [
                'PT_ID' => $request->PT_ID,
                'start_time' => $requestStartDateTime,
                'end_time' => $requestEndDateTime
            ]);
            return redirect()->back()->withErrors('Trainer sudah memiliki jadwal pada waktu yang dipilih.')->withInput();
        }

        // Check for existing member schedule at the same exact time
        $existingMemberSchedule = PtSchedule::where('MEMBER_ID', $member->ID)
            ->where('START_TIME', $requestStartDateTime)
            ->where('END_TIME', $requestEndDateTime)
            ->where('STATUS', '!=', 2)
            ->exists();

        if ($existingMemberSchedule) {
            Log::warning('Member already has a schedule at the same time', [
                'member_id' => $member->ID,
                'start_time' => $requestStartDateTime,
                'end_time' => $requestEndDateTime
            ]);
            return redirect()->back()->withErrors('Jadwal Anda Sudah Ada di Jam Lain')->withInput();
        }

        PtSchedule::create([
            'PT_ID' => $request->PT_ID,
            'MEMBER_ID' => $member->ID,
            'START_TIME' => $requestStartDateTime->format('Y-m-d H:i:s'),
            'END_TIME' => $requestEndDateTime->format('Y-m-d H:i:s'),
            'BOOKED_AT' => $bookedDate->format('Y-m-d H:i:s'),
            'STATUS' => $request->STATUS,
            'NOTE' => $request->NOTE,
            'CREATED_AT' => Carbon::now('Asia/Jakarta'),
        ]);

        Log::info('Member schedule booked', [
            'member_id' => $member->ID,
            'data' => $request->all(),
            'start_datetime' => $requestStartDateTime,
            'end_datetime' => $requestEndDateTime
        ]);

        return redirect()->route('member_schedule.index')->with('success', 'Jadwal berhasil dipesan.');
    }


    public function create()
    {
        $member = Auth::guard('member')->user();
        if (!$member || $member->user_type != '1') {
            Log::warning('Unauthorized access attempt to member schedule create', ['user_id' => $member ? $member->ID : null]);
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengakses halaman ini.');
        }

        $personalTrainers = userProfileModel::where('user_type', '2')->get();
        $availabilities = PtAvailability::where('IS_ACTIVE', 1)
            ->with(['personalTrainer'])
            ->get();

        Log::info('Fetched data for create member schedule', [
            'member_id' => $member->ID,
            'personalTrainers' => $personalTrainers->toArray(),
            'availabilities' => $availabilities->toArray()
        ]);

        return view('member_schedule.create', compact('personalTrainers', 'member', 'availabilities'));
    }

    public function store(Request $request)
    {
        $member = Auth::guard('member')->user();
        if (!$member || $member->user_type != '1') {
            Log::warning('Unauthorized access attempt to store member schedule', ['user_id' => $member ? $member->ID : null]);
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk membuat jadwal.');
        }

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
            'START_TIME' => 'required|date_format:Y-m-d\TH:i',
            'END_TIME' => 'required|date_format:Y-m-d\TH:i|after:START_TIME',
            'BOOKED_AT' => 'required|date_format:Y-m-d\TH:i',
            'STATUS' => 'required|in:0,1,2',
            'NOTE' => 'nullable|string|max:255',
        ], [
            'START_TIME.date_format' => 'Waktu Mulai harus dalam format YYYY-MM-DDTHH:MM.',
            'END_TIME.date_format' => 'Waktu Selesai harus dalam format YYYY-MM-DDTHH:MM.',
            'END_TIME.after' => 'Waktu Selesai harus setelah Waktu Mulai.',
            'BOOKED_AT.date_format' => 'Waktu Pemesanan harus dalam format YYYY-MM-DDTHH:MM.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for storing member schedule', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startTime = Carbon::createFromFormat('Y-m-d\TH:i', $request->START_TIME);
        $endTime = Carbon::createFromFormat('Y-m-d\TH:i', $request->END_TIME);
        $bookedAt = Carbon::createFromFormat('Y-m-d\TH:i', $request->BOOKED_AT);
        $dayOfWeek = $startTime->dayOfWeekIso;

        $availability = PtAvailability::where('PT_ID', $request->PT_ID)
            ->where('DOW', $dayOfWeek)
            ->where('IS_ACTIVE', 1)
            ->where('START_TIME', '<=', $startTime->format('H:i:s'))
            ->where('END_TIME', '>=', $endTime->format('H:i:s'))
            ->first();

        if (!$availability) {
            Log::warning('Invalid trainer availability for schedule', [
                'PT_ID' => $request->PT_ID,
                'DOW' => $dayOfWeek,
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s')
            ]);
            return redirect()->back()->withErrors('Trainer tidak tersedia pada waktu yang dipilih.')->withInput();
        }

        $existingSchedule = PtSchedule::where('PT_ID', $request->PT_ID)
            ->where('START_TIME', '<=', $endTime)
            ->where('END_TIME', '>=', $startTime)
            ->where('STATUS', '!=', 2)
            ->exists();

        if ($existingSchedule) {
            Log::warning('Schedule conflict detected', [
                'PT_ID' => $request->PT_ID,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            return redirect()->back()->withErrors('Trainer sudah memiliki jadwal pada waktu yang dipilih.')->withInput();
        }

        PtSchedule::create([
            'PT_ID' => $request->PT_ID,
            'MEMBER_ID' => $member->ID,
            'START_TIME' => $startTime->format('Y-m-d H:i:s'),
            'END_TIME' => $endTime->format('Y-m-d H:i:s'),
            'BOOKED_AT' => $bookedAt->format('Y-m-d H:i:s'),
            'STATUS' => $request->STATUS,
            'NOTE' => $request->NOTE,
            'CREATED_AT' => Carbon::now('Asia/Jakarta'),
        ]);

        Log::info('Member schedule created', [
            'member_id' => $member->ID,
            'data' => $request->all()
        ]);

        return redirect()->route('member_schedule.index')->with('success', 'Jadwal berhasil dibuat.');
    }

    public function edit($id)
    {
        $member = Auth::guard('member')->user();
        if (!$member || $member->user_type != '1') {
            Log::warning('Unauthorized access attempt to edit member schedule', ['user_id' => $member ? $member->ID : null]);
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk mengedit jadwal.');
        }

        $schedule = PtSchedule::where('ID', $id)
            ->where('MEMBER_ID', $member->ID)
            ->firstOrFail();

        $personalTrainers = userProfileModel::where('user_type', '2')->get();
        $availabilities = PtAvailability::where('IS_ACTIVE', 1)
            ->with(['personalTrainer'])
            ->get();

        Log::info('Fetched data for edit member schedule', [
            'member_id' => $member->ID,
            'schedule' => $schedule->toArray(),
            'personalTrainers' => $personalTrainers->toArray(),
            'availabilities' => $availabilities->toArray()
        ]);

        return view('member_schedule.edit', compact('schedule', 'personalTrainers', 'member', 'availabilities'));
    }

    public function update(Request $request, $id)
    {
        $member = Auth::guard('member')->user();
        if (!$member || $member->user_type != '1') {
            Log::warning('Unauthorized access attempt to update member schedule', ['user_id' => $member ? $member->ID : null]);
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk memperbarui jadwal.');
        }

        $schedule = PtSchedule::where('ID', $id)
            ->where('MEMBER_ID', $member->ID)
            ->firstOrFail();

        if ($schedule->STATUS == 1) {
            Log::warning('Attempt to edit completed schedule', ['schedule_id' => $id, 'member_id' => $member->ID]);
            return redirect()->route('member_schedule.index')->withErrors('Jadwal yang sudah selesai tidak dapat diedit.');
        }

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
            'START_TIME' => 'required|date_format:Y-m-d\TH:i',
            'END_TIME' => 'required|date_format:Y-m-d\TH:i|after:START_TIME',
            'BOOKED_AT' => 'required|date_format:Y-m-d\TH:i',
            'STATUS' => 'required|in:0,1,2',
            'NOTE' => 'nullable|string|max:255',
        ], [
            'START_TIME.date_format' => 'Waktu Mulai harus dalam format YYYY-MM-DDTHH:MM.',
            'END_TIME.date_format' => 'Waktu Selesai harus dalam format YYYY-MM-DDTHH:MM.',
            'END_TIME.after' => 'Waktu Selesai harus setelah Waktu Mulai.',
            'BOOKED_AT.date_format' => 'Waktu Pemesanan harus dalam format YYYY-MM-DDTHH:MM.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for updating member schedule', ['errors' => $validator->errors()->toArray()]);
            return redirect()->route('member_schedule.index')->withErrors($validator)->withInput();
        }

        $startTime = Carbon::createFromFormat('Y-m-d\TH:i', $request->START_TIME);
        $endTime = Carbon::createFromFormat('Y-m-d\TH:i', $request->END_TIME);
        $bookedAt = Carbon::createFromFormat('Y-m-d\TH:i', $request->BOOKED_AT);
        $dayOfWeek = $startTime->dayOfWeekIso;

        $availability = PtAvailability::where('PT_ID', $request->PT_ID)
            ->where('DOW', $dayOfWeek)
            ->where('IS_ACTIVE', 1)
            ->where('START_TIME', '<=', $startTime->format('H:i:s'))
            ->where('END_TIME', '>=', $endTime->format('H:i:s'))
            ->first();

        if (!$availability) {
            Log::warning('Invalid trainer availability for schedule update', [
                'PT_ID' => $request->PT_ID,
                'DOW' => $dayOfWeek,
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s')
            ]);
            return redirect()->route('member_schedule.index')->withErrors('Trainer tidak tersedia pada waktu yang dipilih.')->withInput();
        }

        $existingSchedule = PtSchedule::where('PT_ID', $request->PT_ID)
            ->where('ID', '!=', $id)
            ->where('START_TIME', '<=', $endTime)
            ->where('END_TIME', '>=', $startTime)
            ->where('STATUS', '!=', 2)
            ->exists();

        if ($existingSchedule) {
            Log::warning('Schedule conflict detected on update', [
                'PT_ID' => $request->PT_ID,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            return redirect()->route('member_schedule.index')->withErrors('Trainer sudah memiliki jadwal pada waktu yang dipilih.')->withInput();
        }

        $schedule->update([
            'PT_ID' => $request->PT_ID,
            'MEMBER_ID' => $member->ID,
            'START_TIME' => $startTime->format('Y-m-d H:i:s'),
            'END_TIME' => $endTime->format('Y-m-d H:i:s'),
            'BOOKED_AT' => $bookedAt->format('Y-m-d H:i:s'),
            'STATUS' => $request->STATUS,
            'NOTE' => $request->NOTE,
            'UPDATED_AT' => Carbon::now('Asia/Jakarta'),
        ]);

        Log::info('Member schedule updated', [
            'member_id' => $member->ID,
            'schedule_id' => $id,
            'data' => $request->all()
        ]);

        return redirect()->route('member_schedule.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $member = Auth::guard('member')->user();
        if (!$member || $member->user_type != '1') {
            Log::warning('Unauthorized access attempt to delete member schedule', ['user_id' => $member ? $member->ID : null]);
            return redirect('sesi')->withErrors('Anda harus login sebagai member untuk menghapus jadwal.');
        }

        $schedule = PtSchedule::where('ID', $id)
            ->where('MEMBER_ID', $member->ID)
            ->firstOrFail();

        if ($schedule->STATUS == 1) {
            Log::warning('Attempt to delete completed schedule', ['schedule_id' => $id, 'member_id' => $member->ID]);
            return redirect()->route('member_schedule.index')->withErrors('Jadwal yang sudah selesai tidak dapat dihapus.');
        }

        $schedule->delete();
        Log::info('Member schedule deleted', ['schedule_id' => $id, 'member_id' => $member->ID]);

        return redirect()->route('member_schedule.index')->with('success', 'Jadwal berhasil dihapus.');
    }
}
