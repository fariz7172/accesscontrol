<?php

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



Route::post('/soyal/book/v1/1/adduser', [userProfileController::class, 'registerUser']);
Route::post('/soyal/book/v1/1/deleteuser', [userProfileController::class, 'deleteUser']);
Route::post('/open-gate', [userProfileController::class, 'openGate']);
Route::post('/api/soyal/book/v1/1/adduser', [deviceController::class, 'adduser']);

Route::post('/sync-members', [userProfileController::class, 'syncMembersFromApi']);