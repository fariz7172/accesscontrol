<?php

namespace App\Http\Controllers;

use App\Models\PtAvailability;
use App\Models\userProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PtAvailabilityController extends Controller
{
    public function index()
    {
        $availabilities = PtAvailability::with(['personalTrainer' => function ($query) {
            $query->where('user_type', '2');
        }])->get();

        Log::info('Fetched availabilities', ['availabilities' => $availabilities->toArray()]);

        return view('pt_availability.index', compact('availabilities'));
    }

    public function create()
    {
        $personalTrainers = userProfileModel::where('user_type', '2')->get();
        Log::info('Personal Trainers fetched for create', ['trainers' => $personalTrainers->toArray()]);

        return view('pt_availability.create', compact('personalTrainers'));
    }

    public function store(Request $request)
    {
        // Transform datetime-local input to match Y-m-d H:i format
        $data = $request->all();
        $data['START_TIME'] = str_replace('T', ' ', $request->input('START_TIME')) . ':00';
        $data['END_TIME'] = str_replace('T', ' ', $request->input('END_TIME')) . ':00';

        $validator = Validator::make($data, [
            'PT_ID' => [
                'required',
                'exists:usersprofile,ID',
                function ($attribute, $value, $fail) use ($data) {
                    $trainer = userProfileModel::where('ID', $value)->where('user_type', '2')->first();
                    if (!$trainer) {
                        $fail('The selected Personal Trainer is not valid or not a trainer.');
                    }

                    // Check for existing availability with same PT_ID, START_TIME, and END_TIME
                    $existingAvailability = PtAvailability::where('PT_ID', $value)
                        ->where('START_TIME', $data['START_TIME'])
                        ->where('END_TIME', $data['END_TIME'])
                        ->exists();

                    if ($existingAvailability) {
                        $fail('Jadwal Trainer Yang dipilih Sudah Tersedia');
                    }
                },
            ],
            'DOW' => 'required|in:1,2,3,4,5,6,7',
            'START_TIME' => 'required|date_format:Y-m-d H:i:s',
            'END_TIME' => 'required|date_format:Y-m-d H:i:s|after:START_TIME',
            'IS_ACTIVE' => 'required|boolean',
        ], [
            'START_TIME.date_format' => 'Start Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME.date_format' => 'End Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME.after' => 'End Time must be after Start Time.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for storing availability', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        PtAvailability::create($data);
        Log::info('Availability created', ['data' => $data]);

        return redirect()->route('pt_availability.index')->with('success', 'Availability created successfully.');
    }

    public function edit($id)
    {
        $availability = PtAvailability::findOrFail($id);
        $personalTrainers = userProfileModel::where('user_type', '2')->get();
        Log::info('Fetched availability and trainers for edit', [
            'availability' => $availability->toArray(),
            'trainers' => $personalTrainers->toArray()
        ]);

        return view('pt_availability.edit', compact('availability', 'personalTrainers'));
    }

    public function update(Request $request, $id)
    {
        // Transform datetime-local input to match Y-m-d H:i format
        $data = $request->all();
        $data['START_TIME'] = str_replace('T', ' ', $request->input('START_TIME')) . ':00';
        $data['END_TIME'] = str_replace('T', ' ', $request->input('END_TIME')) . ':00';

        $validator = Validator::make($data, [
            'PT_ID' => [
                'required',
                'exists:usersprofile,ID',
                function ($attribute, $value, $fail) use ($data, $id) {
                    $trainer = userProfileModel::where('ID', $value)->where('user_type', '2')->first();
                    if (!$trainer) {
                        $fail('The selected Personal Trainer is not valid or not a trainer.');
                    }

                    // Check for existing availability with same PT_ID, START_TIME, and END_TIME, excluding current record
                    $existingAvailability = PtAvailability::where('PT_ID', $value)
                        ->where('START_TIME', $data['START_TIME'])
                        ->where('END_TIME', $data['END_TIME'])
                        ->where('ID', '!=', $id)
                        ->exists();

                    if ($existingAvailability) {
                        $fail('Jadwal Trainer Yang dipilih Sudah Tersedia');
                    }
                },
            ],
            'DOW' => 'required|in:1,2,3,4,5,6,7',
            'START_TIME' => 'required|date_format:Y-m-d H:i:s',
            'END_TIME' => 'required|date_format:Y-m-d H:i:s|after:START_TIME',
            'IS_ACTIVE' => 'required|boolean',
        ], [
            'START_TIME.date_format' => 'Start Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME.date_format' => 'End Time must be in the format YYYY-MM-DD HH:MM:SS.',
            'END_TIME.after' => 'End Time must be after Start Time.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for updating availability', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $availability = PtAvailability::findOrFail($id);
        $availability->update($data);
        Log::info('Availability updated', ['id' => $id, 'data' => $data]);

        return redirect()->route('pt_availability.index')->with('success', 'Availability updated successfully.');
    }

    public function destroy($id)
    {
        $availability = PtAvailability::findOrFail($id);
        $availability->delete();
        Log::info('Availability deleted', ['id' => $id]);

        return redirect()->route('pt_availability.index')->with('success', 'Availability deleted successfully.');
    }
}
