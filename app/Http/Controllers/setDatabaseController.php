<?php

namespace App\Http\Controllers;

use App\Models\userProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class setDatabaseController extends Controller
{
    public function index()
    {
        $user = userProfileModel::all();
        return view('setDatabase.index', compact('user'));
    }

    public function updateEnv(Request $request)
    {
        // Ambil input dari form
        $newDatabase = $request->input('DB_DATABASE');
        $newConnection = $request->input('DB_CONNECTION');
        $newHost = $request->input('DB_HOST');
        $newPort = $request->input('DB_PORT');
        $newUsername = $request->input('DB_USERNAME');
        $newPassword = $request->input('DB_PASSWORD');
        $newRegisterAPI = $request->input('REGISTER_API');
        $newDeleteAPIID = $request->input('DELETE_API');

        // Path file .env
        $path = base_path('.env');

        // Membaca isi file .env
        $envContent = file_get_contents($path);

        // Update nilai DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, REGISTER_API, DELETE_API
        $envContent = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=' . $newConnection, $envContent);
        $envContent = preg_replace('/^DB_HOST=.*$/m', 'DB_HOST=' . $newHost, $envContent);
        $envContent = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $newPort, $envContent);
        $envContent = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $newDatabase, $envContent);
        $envContent = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $newUsername, $envContent);
        $envContent = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $newPassword, $envContent);
        $envContent = preg_replace('/^REGISTER_API=.*$/m', 'REGISTER_API=' . $newRegisterAPI, $envContent);
        $envContent = preg_replace('/^DELETE_API=.*$/m', 'DELETE_API=' . $newDeleteAPIID, $envContent);

        // Menyimpan kembali file .env
        file_put_contents($path, $envContent);

        // Menjalankan perintah Artisan setelah update
        try {
            // Jalankan perintah Artisan untuk clear cache dan optimize
            Artisan::call('config:cache');
            Artisan::call('route:clear');
            Artisan::call('optimize');

            return response()->json(['success' => true, 'message' => 'Environment variables updated and cache cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating environment variables: ' . $e->getMessage()]);
        }
    }

    public function showEnv()
    {
        // Path file .env
        $path = base_path('.env');

        // Membaca file .env
        $envContent = file_get_contents($path);

        // Ambil nilai environment variables
        preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $databaseMatches);
        preg_match('/^DB_CONNECTION=(.*)$/m', $envContent, $connectionMatches);
        preg_match('/^DB_HOST=(.*)$/m', $envContent, $hostMatches);
        preg_match('/^DB_PORT=(.*)$/m', $envContent, $portMatches);
        preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $usernameMatches);
        preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $passwordMatches);
        preg_match('/^REGISTER_API=(.*)$/m', $envContent, $registerAPIMatches);
        preg_match('/^DELETE_API=(.*)$/m', $envContent, $deleteAPIMatches);

        // Set nilai default jika tidak ditemukan
        $dbDatabase = isset($databaseMatches[1]) ? $databaseMatches[1] : '';
        $dbConnection = isset($connectionMatches[1]) ? $connectionMatches[1] : '';
        $dbHost = isset($hostMatches[1]) ? $hostMatches[1] : '';
        $dbPort = isset($portMatches[1]) ? $portMatches[1] : '';
        $dbUsername = isset($usernameMatches[1]) ? $usernameMatches[1] : '';
        $dbPassword = isset($passwordMatches[1]) ? $passwordMatches[1] : '';
        $dbRegisterAPI = isset($registerAPIMatches[1]) ? $registerAPIMatches[1] : '';
        $dbDeleteAPI = isset($deleteAPIMatches[1]) ? $deleteAPIMatches[1] : '';

        // Kirim nilai ke view
        return view('setDatabase.index', compact('dbDatabase', 'dbConnection', 'dbHost', 'dbPort', 'dbUsername', 'dbPassword', 'dbRegisterAPI', 'dbDeleteAPI'));
    }
}
