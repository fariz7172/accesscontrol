<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AddShiftUserController;
use App\Http\Controllers\apiController;
use App\Http\Controllers\attendanceLogController;
use App\Http\Controllers\AttendanceQueryController;
use App\Http\Controllers\AttendanceSheetController;
use App\Http\Controllers\AttendSumaryController;
use App\Http\Controllers\AttendUserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\cleanDeviceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DayzoneController;
use App\Http\Controllers\DayzoneDetailController;
use App\Http\Controllers\departemenController;
use App\Http\Controllers\deviceController;
use App\Http\Controllers\DeviceGroupController;
use App\Http\Controllers\holidayController;
use App\Http\Controllers\importCsvController;
use App\Http\Controllers\invalidLogController;
use App\Http\Controllers\LeaveProcessController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\logDataController;
use App\Http\Controllers\logUserController;
use App\Http\Controllers\MemberScheduleController;
use App\Http\Controllers\openDoorController;
use App\Http\Controllers\PtAvailabilityController;
use App\Http\Controllers\PtScheduleController;
use App\Http\Controllers\rekapController;
use App\Http\Controllers\sessionController;
use App\Http\Controllers\setDatabaseController;
use App\Http\Controllers\settingPathController;
use App\Http\Controllers\SetUserTimeoneController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftPatternController;
use App\Http\Controllers\userAdministratorController;
use App\Http\Controllers\userProfileController;
use App\Http\Controllers\UserStatusController;
use App\Http\Controllers\WeekzoneController;
use App\Http\Controllers\WeekzoneDetailController;
use App\Models\devicelogModel;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes (no auth required)
Route::get('/', [sessionController::class, 'index'])->name('sesi');
Route::get('/sesi', [sessionController::class, 'index'])->name('sesi');
Route::get('member', [sessionController::class, 'member'])->name('member');
Route::post('sesi/login', [sessionController::class, 'login'])->name('login');
Route::post('sesi/member-login', [sessionController::class, 'memberLogin'])->name('member.login');
Route::get('sesi/logout', [sessionController::class, 'logout'])->name('logout');


Route::get('/pt_schedule/export', [PtScheduleController::class, 'export'])->name('pt_schedule.export');

// Rute untuk menampilkan daftar ketersediaan dan jadwal (akses publik dengan filter)

Route::get('/attendance/query', [AttendanceQueryController::class, 'index'])->name('attendance.index');
Route::post('/attendance/query', [AttendanceQueryController::class, 'query'])->name('attendance.query');


Route::post('/updateEnvSetting', [sessionController::class, 'updateEnvSetting'])->name('updateEnvSetting');
Route::get('/log-user-frontend', [logUserController::class, 'index'])->name('logUserFrontend');
Route::get('/log-user-frontend/export', [logUserController::class, 'exportUserLogs'])->name('exportUserLogs');
Route::get('/log-user-frontend/latest', [logUserController::class, 'getLatestLogs1'])->name('getLatestLogs1');
// Not logged in page
Route::get('/not-logged-in', function () {
    return view('not_login');
})->name('not-logged-in');


Route::middleware(['auth:member'])->group(function () {
    Route::get('/member', [sessionController::class, 'member'])->name('member.index');
    Route::get('/member/create', [sessionController::class, 'memberCreate'])->name('member.create');
    Route::post('/member', [sessionController::class, 'memberStore'])->name('member.store');
    Route::get('/member/{id}/edit', [sessionController::class, 'memberEdit'])->name('member.edit');
    Route::put('/member/{id}', [sessionController::class, 'memberUpdate'])->name('member.update');
    Route::delete('/leaveprocessMember/{id}', [sessionController::class, 'destroy'])->name('leaveprocessMember.destroy');
    Route::get('/member/export/pdf', [sessionController::class, 'memberExportPDF'])->name('member.export.pdf');
    Route::get('/member/export-pdf/{id}', [sessionController::class, 'memberExportPDFById'])->name('member.exportPDFById');
});

Route::get('/memberSchedule', [MemberScheduleController::class, 'index'])->name('member_schedule.index');
Route::get('/memberSchedule/create', [MemberScheduleController::class, 'create'])->name('member_schedule.create');
Route::post('/memberSchedule', [MemberScheduleController::class, 'store'])->name('member_schedule.store');
Route::get('/memberSchedule/{id}/edit', [MemberScheduleController::class, 'edit'])->name('member_schedule.edit');
Route::put('/memberSchedule/{id}', [MemberScheduleController::class, 'update'])->name('member_schedule.update');
Route::delete('/memberSchedule/{id}', [MemberScheduleController::class, 'destroy'])->name('member_schedule.destroy');
Route::post('/memberSchedule/book', [MemberScheduleController::class, 'book'])->name('member_schedule.book');

Route::get('/trainer', [sessionController::class, 'trainer'])->name('trainer.index')->middleware('auth:member');


// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard (no specific bactive permission required)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/metrics', [DashboardController::class, 'getMetrics'])->name('dashboard.metrics');



    // User-related routes (require bactive:user)
    Route::middleware(['check.user.access', 'bactive:user'])->group(function () {
        // User Profile
        Route::get('/userProfile', [userProfileController::class, 'index'])->name('userProfile');
        Route::get('/userProfile/create', [userProfileController::class, 'create'])->name('userProfile.create');
        Route::post('/userProfile', [userProfileController::class, 'store'])->name('userProfile.store');
        Route::get('/userProfiles/{id}/edit', [userProfileController::class, 'edit'])->name('userProfile.edit');
        Route::put('/userProfile/{id}', [userProfileController::class, 'update'])->name('userProfile.update');
        Route::get('/user-profile/photo/{id}', [userProfileController::class, 'showPhoto'])->name('userProfile.photo');
        Route::get('/get-user-dates/{userId}', [userProfileController::class, 'getUserDates']);
        Route::delete('/delete-users', [userProfileController::class, 'deleteUsers'])->name('delete.users');
        Route::delete('/user-log/bulk-delete', [userProfileController::class, 'bulkDelete'])->name('userLog.bulkDelete');
        // //////////////////////////////////////////
        Route::get('/get-fingerprint/{userId}', [userProfileController::class, 'getFingerprint'])->name('get.fingerprint');
        Route::get('/get-fp/{userId}', [userProfileController::class, 'getFingerprint']);
        Route::get('/get-fp-data/{userId}', [userProfileController::class, 'getFpData'])->name('get.fp.data');
        Route::get('/get-fp-data-by-sn/{userId}/{sn}', [userProfileController::class, 'getFpDataBySn'])->name('getFpDataBySn');
        Route::get('/ping-check', function () {
            return response()->json(['status' => 'ok']);
        });
        // routes/web.php
        Route::post('/check-device-status', [userProfileController::class, 'checkDeviceStatus']);
        // //////////////////////////////////////////////////////////////
        Route::post('/save-to-user-data', [userProfileController::class, 'saveToUserData'])->name('save.to.user.data');
        Route::get('/get-user-and-device-data/{userId}/{deviceId}', [userProfileController::class, 'getUserAndDeviceData']);
        Route::resource('userProfiles', userProfileController::class);
        Route::get('/userProfile/photo/{id}', [userProfileController::class, 'getPhoto'])->name('userProfile.photo');
        Route::post('/save-to-usersxdevice', [userProfileController::class, 'saveToUsersDevice'])->name('save.to.usersxdevice');
        Route::post('/delete-from-devicegroup-devicegate', [userProfileController::class, 'deleteFromDeviceGroupDeviceGate'])->name('devicegroup.devicegate.delete');
        Route::get('/user-profiles/devices-by-group/{deviceGroupId}', [userProfileController::class, 'getDevicesByGroup'])->name('userProfiles.devicesByGroup');
        Route::post('/delete-from-usersxdevice', [userProfileController::class, 'deleteFromUsersDevice'])->name('userProfile.deleteFromUsersDevice');
        Route::get('/get-cardnumber/{userId}', [userProfileController::class, 'getCardNumber'])->name('get.cardnumber');
        Route::post('/delete-user-picture', [userProfileController::class, 'deleteUserPicture'])->name('delete.user.picture');
        Route::get('/user-profile/photo/{userId}', [userProfileController::class, 'getProfilePhoto'])->name('userProfile.photo');
        Route::post('/user-profiles/generate-passwords', [userProfileController::class, 'generatePasswords'])
            ->name('userProfiles.generatePasswords');
        Route::post('userProfiles/processPasswordBatch', [userProfileController::class, 'processPasswordBatch'])->name('userProfiles.processPasswordBatch');
        Route::get('userProfile/reset-password/{id}', [userProfileController::class, 'resetPassword'])->name('userProfile.resetPassword');
        Route::post('/userProfile/checkAndUpdatePhoto', [userProfileController::class, 'checkAndUpdatePhoto'])->name('userProfile.checkAndUpdatePhoto');
        Route::get('/get-user-profile-dates/{userId}', [UserProfileController::class, 'getUserProfileDates']);
        Route::get('/check-userdata-fp/{userId}/{sn}', [userProfileController::class, 'checkUserDataFp']);

        Route::post('/user-profile/insert-weekzone', [userProfileController::class, 'insertWeekzoneToMachine'])
            ->name('userProfile.insertWeekzone');
        Route::get('/get-weekzone/{userId}/{sn}', [userProfileController::class, 'getWeekzoneByUserAndSn'])
            ->name('get.weekzone');
        Route::post('/delete-from-weekzoneuser', [userProfileController::class, 'deleteFromWeekzoneUser'])
            ->name('delete.from.weekzoneuser');
        // Branches
        Route::resource('branches', BranchController::class);

        // Departments
        Route::resource('departemen', departemenController::class);

        // Import CSV
        Route::get('/import/progress', [importCsvController::class, 'getImportProgress'])->name('import.progress');
        Route::get('/import-csv', [importCsvController::class, 'index'])->name('import.csv');
        Route::post('/import-csv', [importCsvController::class, 'import'])->name('import.csv.post');

        // Setting Path
        Route::get('/settingPath', [settingPathController::class, 'index'])->name('settingPath');
        Route::post('/settingPath/submit', [settingPathController::class, 'submit'])->name('settingPath.submit');
        Route::post('/runPowerShell', [settingPathController::class, 'runPowerShell'])->name('runPowerShell');
    });

    // Device-related routes (require bactive:device)
    Route::middleware(['check.user.access', 'bactive:device'])->group(function () {
        // Device
        Route::get('/device', [deviceController::class, 'index'])->name('device');
        Route::post('/device', [deviceController::class, 'store'])->name('device.store');
        Route::put('/device/{id}', [deviceController::class, 'update'])->name('device.update');
        Route::get('/device/{encryptedId}/edit', [deviceController::class, 'edit'])->name('device.edit');
        Route::delete('/device/{id}', [deviceController::class, 'destroy'])->name('device.destroy');
        Route::post('/save-to-devicelog', [deviceController::class, 'devicelog'])->name('devicelog');
        Route::post('/devices/open-tcp', [deviceController::class, 'openTCP'])->name('devices.open');
        Route::post('/devices/check-card', [deviceController::class, 'checkCardStatus'])->name('devices.checkCard');
        Route::get('/devices/logs', [deviceController::class, 'showLogs'])->name('devices.logs');
        Route::get('/serial/card', [deviceController::class, 'serialCard'])->name('serial.card');
        Route::post('/process/card', [deviceController::class, 'processCardNumber'])->name('process.card');
        Route::get('/serial/card-little-endian', [deviceController::class, 'serialCardLitleEndian'])->name('serial.serialCardLitleEndian');
        Route::post('/device/check-connection', [deviceController::class, 'checkConnection'])->name('device.checkConnection');
        Route::post('/device/set-time', [deviceController::class, 'setTime'])->name('device.setTime');
        Route::post('/device/reboot', [deviceController::class, 'reboot'])->name('device.reboot');
        Route::post('/device/ping', [deviceController::class, 'ping'])->name('device.ping');
        Route::get('/device/users-modal', [deviceController::class, 'getUsersForModal'])
            ->name('device.users-modal');
        Route::get('/device/users/modal', [deviceController::class, 'getUsersForModal'])
            ->name('device.users.modal');
        // routes/web.php
        Route::post('/save-user-to-profile', [deviceController::class, 'saveUserToProfile'])
            ->name('save.user.to.profile');
        Route::post('/device/save-relation', [deviceController::class, 'saveUserDeviceRelation'])->name('device.saveUserDeviceRelation');
        Route::post('/device/adduser', [deviceController::class, 'adduser'])->name('device.adduser');
        Route::post('/device/save-user-to-device', [deviceController::class, 'saveUserToDevice'])->name('device.saveUserToDevice');
        Route::post('/devicelog/store', [deviceController::class, 'storeDevicelog'])
            ->name('devicelog.store');
        // Pagination Modal Add User Lift
        Route::get('/device/users/lift-search', [deviceController::class, 'searchUsersForLift'])
            ->name('device.users.lift-search');


        Route::get('/get-next-userid', function () {
            $lastId = \App\Models\userProfileModel::max('ID');
            $nextId = $lastId ? $lastId + 1 : 1;
            return response()->json(['next_id' => $nextId]);
        })->name('get.next.userid');

        Route::resource('dayzone', DayzoneController::class);
        Route::resource('dayzonedetail', DayzoneDetailController::class);
        Route::post('dayzonedetail/push-to-api', [DayzoneDetailController::class, 'pushToApi'])->name('dayzonedetail.push-to-api');
        Route::resource('weekzone', WeekzoneController::class);
        Route::resource('weekzonedetail', WeekzoneDetailController::class);
        Route::post('weekzonedetail/push-to-api', [WeekzoneDetailController::class, 'pushToApi'])->name('weekzonedetail.push-to-api');

        // web.php
        Route::post('dayzonedetail/push-to-api', [DayzoneDetailController::class, 'pushToApi'])
            ->name('dayzonedetail.push-to-api');

        Route::get('/set-user-timeone', [SetUserTimeoneController::class, 'index'])
            ->name('setusertimeone.index');

        Route::get('/set-user-timeone/users/{deviceId}', [SetUserTimeoneController::class, 'getUsersByDevice'])
            ->name('setusertimeone.users');

        Route::get('/set-user-timeone/weekzones/{userId}', [SetUserTimeoneController::class, 'getUserWeekzones'])
            ->name('setusertimeone.weekzones');

        // routes/web.php
        Route::post('/setusertimeone/assign-weekzone', [SetUserTimeoneController::class, 'assignWeekzone'])
            ->name('setusertimeone.assignWeekzone');

        // routes/web.php
        Route::get('/set-user-timeone/all-users', [SetUserTimeoneController::class, 'getAllUsers'])
            ->name('setusertimeone.allusers');
        Route::post('/set-user-timeone/users', [SetUserTimeoneController::class, 'getUsersByDevices'])
            ->name('setusertimeone.users');

        Route::get('/setusertimeone', [SetUserTimeoneController::class, 'index'])->name('setusertimeone.index');
        Route::post('/setusertimeone/users', [SetUserTimeoneController::class, 'getUsersByDevices'])->name('setusertimeone.users');
        Route::get('/setusertimeone/allusers', [SetUserTimeoneController::class, 'getAllUsers'])->name('setusertimeone.allusers');
        Route::get('/setusertimeone/weekzones/{userId}', [SetUserTimeoneController::class, 'getUserWeekzones'])->name('setusertimeone.weekzones');
        Route::post('/setusertimeone/assign', [SetUserTimeoneController::class, 'assignWeekzone'])->name('setusertimeone.assignWeekzone');
        // Device Group
        // ////////////////Check Js 
        Route::resource('deviceGroup', DeviceGroupController::class);
        Route::post('/deviceGroup/addDeviceToGroup', [DeviceGroupController::class, 'addDeviceToGroup'])->name('deviceGroup.addDeviceToGroup');
        Route::get('/deviceGroup/{id}/editDeviceToGroup', [DeviceGroupController::class, 'editDeviceToGroup'])->name('deviceGroup.editDeviceToGroup');
        Route::post('/deviceGroup/{id}/updateDeviceToGroup', [DeviceGroupController::class, 'updateDeviceToGroup'])->name('deviceGroup.updateDeviceToGroup');
        Route::delete('/deviceGroup/{deviceGateId}/removeDeviceFromGroup', [DeviceGroupController::class, 'removeDeviceFromGroup'])->name('deviceGroup.removeDeviceFromGroup');
        Route::post('/save-to-devicegroup-devicegate', [DeviceGroupController::class, 'storeGroup']);

        Route::get('/deviceGroup', [DeviceGroupController::class, 'index'])->name('deviceGroup');
        Route::get('/deviceGroup/create', [DeviceGroupController::class, 'create'])->name('deviceGroup.create');
        Route::post('/deviceGroup', [DeviceGroupController::class, 'store'])->name('deviceGroup.store');
        Route::get('/deviceGroup/{id}/edit', [DeviceGroupController::class, 'edit'])->name('deviceGroup.edit');
        Route::put('/deviceGroup/{id}', [DeviceGroupController::class, 'update'])->name('deviceGroup.update');
        Route::delete('/deviceGroup/{id}', [DeviceGroupController::class, 'destroy'])->name('deviceGroup.destroy');
        Route::post('/deviceGroup/addDeviceToGroup', [DeviceGroupController::class, 'addDeviceToGroup'])->name('deviceGroup.addDeviceToGroup');
        Route::get('/deviceGroup/{id}/editDeviceToGroup', [DeviceGroupController::class, 'editDeviceToGroup'])->name('deviceGroup.editDeviceToGroup');
        Route::post('/deviceGroup/{id}/updateDeviceToGroup', [DeviceGroupController::class, 'updateDeviceToGroup'])->name('deviceGroup.updateDeviceToGroup');
        Route::delete('/deviceGroup/{deviceGateId}/removeDeviceFromGroup', [DeviceGroupController::class, 'removeDeviceFromGroup'])->name('deviceGroup.removeDeviceFromGroup');
        Route::post('/save-to-devicegroup-devicegate', [DeviceGroupController::class, 'storeGroup']);

        // Open Door
        Route::get('/openDoor', [openDoorController::class, 'index'])->name('openDoor');
        Route::post('/devices/open/{id}', [openDoorController::class, 'openDevice'])->name('devices.open');
        Route::post('/devices/openGate', [openDoorController::class, 'openGate'])->name('devices.openGate');

        // Clean Device
        Route::get('/cleanDevice', [cleanDeviceController::class, 'index'])->name('cleanDevice');
        Route::post('/cleanDevice/clean/{id}', [cleanDeviceController::class, 'cleanDevice'])->name('cleanDevice.clean');
    });

    // Log-related routes (require bactive:log)
    Route::middleware(['check.user.access', 'bactive:log'])->group(function () {
        // Log Data
        Route::get('/logData', [logDataController::class, 'logData'])->name('logData');
        Route::get('/export-data-log', [logDataController::class, 'exportLogDataExcel'])->name('export.data.log');
        Route::get('/logDevice', [logDataController::class, 'logDevice'])->name('logDevice');
        Route::get('/export-device-log', [logDataController::class, 'exportLogDeviceExcel'])->name('export.device.log');
        Route::get('/export-user-logs', [logDataController::class, 'export'])->name('exportUserLogs');
        Route::get('/log-user', [logDataController::class, 'index'])->name('logUser');
        Route::get('/get-latest-logs', [logDataController::class, 'getLatestLogs'])->name('getLatestLogs');
        Route::get('/user-logs/json', [logDataController::class, 'getUserLogsJson']);
        Route::get('/get-counts', [logDataController::class, 'getCounts'])->name('getCounts');
        Route::post('/log-user/check-status', [logDataController::class, 'checkStatus'])->name('checkStatus');
        Route::get('/get-latest-logs-attendance', [logDataController::class, 'getLatestLogsattendance'])->name('getLatestLogsattendance');

        Route::get('/logInvalid', [invalidLogController::class, 'index'])->name('logInvalid');
        Route::get('/log-invalid/export', [invalidLogController::class, 'export'])->name('exportInvalidUserLogs');
        Route::get('/log-invalid/latest', [invalidLogController::class, 'getLatestLogsInvalid'])->name('getLatestLogsInvalid');


        // Rekap
        Route::get('/rekap', [rekapController::class, 'index'])->name('rekap.index');

        Route::get('/attendanceLog', [attendanceLogController::class, 'index'])->name('attendanceLog');
        Route::get('/attendanceLog/export', [attendanceLogController::class, 'export'])->name('attendanceLog.export');
    });

    // Setting-related routes (require bactive:setting)
    Route::middleware(['check.user.access', 'bactive:setting'])->group(function () {
        // Set Database
        Route::get('/setDatabase', [setDatabaseController::class, 'showEnv'])->name('setDatabase');
        Route::post('/updateEnv', [setDatabaseController::class, 'updateEnv'])->name('updateEnv');

        // API
        Route::get('/api', [apiController::class, 'index'])->name('api.index');
        Route::get('/api/create', [apiController::class, 'create'])->name('api.create');
        Route::post('/api/store', [apiController::class, 'store'])->name('api.store');
        Route::get('/api/{id}/edit', [apiController::class, 'edit'])->name('api.edit');
        Route::put('/api/{id}', [apiController::class, 'update'])->name('api.update');
        Route::delete('/api/{id}', [apiController::class, 'destroy'])->name('api.destroy');
    });

    // User Admin routes (require bactive:userAdmin)
    Route::middleware(['check.user.access', 'bactive:userAdmin'])->group(function () {
        Route::get('/userAdmin', [userAdministratorController::class, 'index'])->name('userAdmin.index');
        Route::get('/userAdmin/create', [userAdministratorController::class, 'create'])->name('userAdmin.create');
        Route::post('/userAdmin/store', [userAdministratorController::class, 'store'])->name('userAdmin.store');
        Route::get('/userAdmin/{id}/edit', [userAdministratorController::class, 'edit'])->name('userAdmin.edit');
        Route::put('/userAdmin/{id}', [userAdministratorController::class, 'update'])->name('userAdmin.update');
        Route::delete('/userAdmin/{id}', [userAdministratorController::class, 'destroy'])->name('userAdmin.destroy');
        Route::post('/update-access', [userAdministratorController::class, 'updateAccess'])->name('userAdmin.updateAccess');
    });

    Route::middleware(['check.user.access', 'bactive:schedule'])->group(function () {
        Route::resource('pt_availability', PtAvailabilityController::class);
        Route::resource('pt_schedule', PtScheduleController::class);
    });



    Route::middleware(['check.user.access', 'bactive:attendance'])->group(function () {
        Route::resource('holiday', holidayController::class)->except(['show']);
        Route::get('/holiday/export', [holidayController::class, 'export'])->name('holiday.export');
        Route::resource('leaveprocess', LeaveProcessController::class)->except(['show']);
        Route::patch('leaveprocess/{leaveprocess}/status', [LeaveProcessController::class, 'updateStatus'])->name('leaveprocess.updateStatus');
        Route::get('/leaveprocess/export', [LeaveProcessController::class, 'export'])->name('leaveprocess.export');
        Route::resource('leavetype', LeaveTypeController::class);
        Route::delete('/leaveprocess/{id}', [LeaveProcessController::class, 'destroy'])->name('leaveprocess.destroy');

        Route::resource('shift', ShiftController::class);
        Route::resource('shiftpattern', ShiftPatternController::class);
        Route::resource('addshiftuser', AttendUserController::class)->only(['index', 'edit', 'update', 'create']); // Restrict to index, edit, update
        Route::resource('attendantSheet', AttendanceSheetController::class);

        Route::patch('attendant-sheet/update-DutyProccess', [AttendanceSheetController::class, 'updateDutyProccess'])->name('attendantSheet.updateDutyProccess');
        Route::patch('attendant-sheet/update-remark', [AttendanceSheetController::class, 'updateRemark'])->name('attendantSheet.updateRemark');
        Route::get('/attendant-sheet/employees-by-department', [AttendanceSheetController::class, 'getEmployeesByDepartment'])->name('attendantSheet.getEmployeesByDepartment');
        Route::get('/attendantSheet/export', [AttendanceSheetController::class, 'export'])->name('attendantSheet.export');
        Route::post('/addshiftuser/bulk-assign', [AttendUserController::class, 'bulkAssign'])->name('addshiftuser.bulkAssign');
        Route::get('/shift-pattern/{id}', [AttendUserController::class, 'getShiftPatternDetails'])->name('shift.pattern.details');
        Route::post('/add-report-new-pattern', [AttendUserController::class, 'addReportNewPattern'])->name('addshiftuser.addReport');
        Route::get('/addshiftuser/recap', [AttendUserController::class, 'recap'])->name('addshiftuser.recap');

        Route::post('/addshiftuser/export', [AttendUserController::class, 'export'])->name('addshiftuser.export');
        Route::patch('/attend/update-shift-code', [AttendUserController::class, 'updateShiftCode'])->name('attend.updateShiftCode');
        Route::get('/get-shift-details/{shiftCode}', [AttendUserController::class, 'getShiftDetails'])->name('get.shift.details');
        Route::patch('attendant-sheet/update-duty-process', [AttendanceSheetController::class, 'updateDutyProcess'])->name('attendantSheet.updateDutyProcess');

        Route::resource('summary', AttendSumaryController::class);
        Route::match(['get', 'post'], '/attendance-summary', [AttendSumaryController::class, 'index'])->name('summary.index');
        Route::get('/attendant-sheet/employees-by-department', [AttendSumaryController::class, 'getEmployeesByDepartment'])->name('attendantSheet.getEmployeesByDepartment');
        Route::post('/attendance-summary/generate', [AttendSumaryController::class, 'generate'])->name('summary.generate');
        Route::post('summary/export', [AttendSumaryController::class, 'export'])->name('summary.export');

        Route::get('/absen', [AbsenController::class, 'index'])->name('absen.index');
        Route::post('/absen/tambah-laporan', [AbsenController::class, 'tambahLaporan'])->name('absen.tambahLaporan');
        Route::post('/absen/bulk-assign', [AbsenController::class, 'bulkAssign'])->name('absen.bulkAssign');
        Route::post('/absen/export', [AbsenController::class, 'export'])->name('absen.export');
        Route::get('/absen/recap', [AbsenController::class, 'recap'])->name('absen.recap');
        Route::get('/shift-details/{shiftCode}', [AbsenController::class, 'getShiftDetails'])
            ->name('get.shift.details');

        Route::get('/user-status', [UserStatusController::class, 'index'])->name('user-status.index');
        Route::get('/user-status/latest', [UserStatusController::class, 'getLatestStatuses'])->name('user-status.latest');
        Route::post('/user-status/update', [UserStatusController::class, 'updateStatus'])->name('user-status.update');
        Route::post('/user-status/update-status', [UserStatusController::class, 'updateStatus'])->name('user-status.update');
        Route::get('/user-status/export', [UserStatusController::class, 'exportExcel'])->name('user-status.export');
        Route::get('/user-status/hourly', [UserStatusController::class, 'hourly'])->name('user-status.hourly');
    });
});
