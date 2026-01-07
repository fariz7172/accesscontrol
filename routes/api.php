<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\deviceController;
use App\Http\Controllers\userProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// JWT Authentication Routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

// Attendance Report API Routes (Protected with JWT)
Route::group(['prefix' => 'attendance', 'middleware' => 'auth:api'], function () {
    Route::post('report', [AttendanceReportController::class, 'getUserAttendance']);
    Route::get('users', [AttendanceReportController::class, 'getUserList']);
    Route::post('statistics', [AttendanceReportController::class, 'getAttendanceStatistics']);
});

// Open Gate API Route (Protected with JWT)
Route::post('gate/open', [\App\Http\Controllers\openDoorController::class, 'openGateApi'])
    ->middleware('auth:api');

Route::post('/soyal/book/v1/1/adduser', [userProfileController::class, 'registerUser']);
Route::post('/soyal/book/v1/1/deleteuser', [userProfileController::class, 'deleteUser']);
Route::post('/open-gate', [userProfileController::class, 'openGate']);
Route::post('/api/soyal/book/v1/1/adduser', [deviceController::class, 'adduser']);

Route::post('/sync-members', [userProfileController::class, 'syncMembersFromApi']);