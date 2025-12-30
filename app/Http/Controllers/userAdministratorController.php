<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class userAdministratorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $perPage = $perPage === 'all' ? User::count() : (int) $perPage;
        $search = $request->get('search');

        $userLogQuery = User::query()
            ->orderBy('id', 'asc');

        if ($search) {
            $userLogQuery->where(function ($query) use ($search) {
                $query->where('username', 'like', '%' . $search . '%')
                    ->orWhere('id', $search);
            });
        }

        $users = $userLogQuery->paginate($perPage);

        return view('userAdmin.index', compact('users'));
    }

    public function create()
    {
        return view('userAdmin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:userlogin',
            'password' => 'required|string|min:6',
            'priv' => 'required|in:0,1', // Restrict priv to 0 or 1
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'priv' => $request->priv,
                'bactive' => [
                    'user' => $request->access_user ? true : false,
                    'device' => $request->access_device ? true : false,
                    'log' => $request->access_log ? true : false,
                    'setting' => $request->access_setting ? true : false,
                    'userAdmin' => $request->access_userAdmin ? true : false,
                    'attendance' => $request->access_attendance ? true : false,
                    'schedule' => $request->access_schedule ? true : false,

                ],
            ]);

            return redirect()->route('userAdmin.index')->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return redirect()->route('userAdmin.index')->with('error', 'Failed to create user.');
        }
    }

    public function edit($encryptedId)
    {
        try {
            $id = decryptId($encryptedId);
            $user = User::findOrFail($id);
            return view('userAdmin.edit', compact('user'));
        } catch (\Exception $e) {
            Log::error('Error editing user: ' . $e->getMessage());
            return redirect()->route('userAdmin.index')->with('error', 'Invalid user ID.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:userlogin,username,' . $id,
            'password' => 'nullable|string|min:6',
            'priv' => 'required|in:0,1', // Restrict priv to 0 or 1
        ]);

        try {
            $user = User::findOrFail($id);
            $user->username = $request->username;

            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            $user->priv = $request->priv;
            $user->bactive = [
                'user' => $request->access_user ? true : false,
                'device' => $request->access_device ? true : false,
                'log' => $request->access_log ? true : false,
                'setting' => $request->access_setting ? true : false,
                'userAdmin' => $request->access_userAdmin ? true : false,
                'attendance' => $request->access_attendance ? true : false,
                'schedule' => $request->access_schedule ? true : false,
            ];

            $user->save();

            return redirect()->route('userAdmin.index')->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return redirect()->route('userAdmin.index')->with('error', 'Failed to update user.');
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }

    public function updateAccess(Request $request)
    {
        try {
            // Only allow users with priv = 1 to update access
            if (!auth()->user()->priv) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'user_id' => 'required|exists:userlogin,id',
                'type' => 'required|in:userData,deviceData,logData,setting,userAdmin,attendance,schedule',
                'status' => 'required|in:0,1', // Ensure status is 0 or 1
            ]);

            // Check if bactive column exists
            if (!Schema::hasColumn('userlogin', 'bactive')) {
                Log::error('bactive column missing in userlogin table');
                return response()->json(['message' => 'bactive column not found in database'], 500);
            }

            $user = User::findOrFail($request->user_id);
            $type = $request->type;
            $status = (bool) $request->status; // Cast to boolean

            // Map the type to the corresponding bactive key
            $fieldMap = [
                'userData' => 'user',
                'deviceData' => 'device',
                'logData' => 'log',
                'setting' => 'setting',
                'userAdmin' => 'userAdmin',
                'attendance' => 'attendance',
                'schedule' => 'schedule',
            ];

            if (!isset($fieldMap[$type])) {
                return response()->json(['message' => 'Invalid access type'], 400);
            }

            // Initialize bactive if null or not an array
            $bactive = is_array($user->bactive) ? $user->bactive : [
                'user' => false,
                'device' => false,
                'log' => true,
                'setting' => false,
                'userAdmin' => false,
                'attendance' => false,
                'schedule' => false,
            ];

            // Update the specific permission
            $bactive[$fieldMap[$type]] = $status;
            $user->bactive = $bactive;
            $user->save();

            return response()->json(['message' => 'Access updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating access: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => $request->user_id,
                'bactive' => $user->bactive ?? 'null',
            ]);
            return response()->json(['message' => 'Failed to update access: ' . $e->getMessage()], 500);
        }
    }
}