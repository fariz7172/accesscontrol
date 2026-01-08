<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class settingPathController extends Controller
{
    public function index()
    {
        return view('setting.index');
    }

    public function submit(Request $request)
    {
        // Validasi input
        $request->validate([
            'path' => 'required|string',
        ]);

        // Mengambil nilai path dari input form
        $path = rtrim($request->input('path'), '\\'); // Menghapus tanda '\' di akhir untuk konsistensi

        // Mengecek apakah path mengandung spasi
        if (strpos($path, ' ') !== false) {
            return redirect()->route('settingPath')->with('error', 'NAMA FOLDER TIDAK BOLEH MENGUNAKAN SPASI');
        }

        try {
            // Menjalankan perintah PowerShell untuk membuka direktori (jika diperlukan)
            $command = "C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe -Command \"Start-Process powershell -ArgumentList '-NoExit', 'cd \"$path\"'\"";
            // Gunakan exec() daripada pclose(popen()) untuk lebih jelas dan stabil
            exec("start /B " . $command);

            // Mendapatkan nilai terakhir dari kolom ADDR_of_CTL
            $lastFid = DB::table('usersprofile')->max('ID');
            $fidStart = $lastFid ? $lastFid + 1 : 1;

            // Script PowerShell dengan nilai $fidStart
            $displayCode = <<<POWERSHELL
            \$folderPath = "$path"
            
            # Mengambil semua file dengan ekstensi .jpg di folder yang ditentukan
            Get-ChildItem -Path \$folderPath -Filter "*.jpg" | ForEach-Object {
                \$name = \$_.BaseName
                \$name = \$name -replace ",", ""   # Menghapus koma
                \$name = \$name -replace "\\.(?!jpg\$)", ""  # Menghapus titik kecuali di ekstensi .jpg
                \$newName = \$name + ".jpg"
                \$oldFilePath = \$_.FullName
                \$newFilePath = Join-Path \$folderPath \$newName
                if (\$oldFilePath -ne \$newFilePath) {
                    Rename-Item -Path \$oldFilePath -NewName \$newName
                    Write-Host "File '\$oldFilePath' diubah menjadi '\$newFilePath'"
                }
            }
            
            # Menulis header ke file CSV di jalur yang diinput oleh pengguna
            "ID,type,photo,NAME,Depid" | Set-Content "$path\\FILE_csv.csv"
            
            # Inisialisasi nilai awal untuk fid
            \$ID = $fidStart
            Get-ChildItem -Path \$folderPath -Filter "*.jpg" | ForEach-Object {
                \$name = \$_.Name -replace ".jpg", ""
                \$name = \$name -replace ",", " "
                \$path = \$_.FullName -replace '\\\\', '/'
                \$path = \$path -replace ",", " "
                \$type = "1"
                \$Depid = "1"
                "\$ID,\$type,\$path,\$name,\$Depid" | Add-Content "$path\\FILE_csv.csv"
                \$ID++
            }
            POWERSHELL;

            // Simpan skrip PowerShell ke file sementara
            $scriptPath = storage_path('app/script.ps1');
            file_put_contents($scriptPath, $displayCode);

            // Jalankan skrip PowerShell
            exec("powershell -ExecutionPolicy Bypass -File \"$scriptPath\"");

            // Pembersihan file script setelah eksekusi
            unlink($scriptPath);

            // Menampilkan kode PowerShell di view dan mengirimkan pesan sukses
            return view('setting.index', compact('displayCode'))->with('success', 'Perintah PowerShell telah dijalankan.');
        } catch (\Exception $e) {
            // Mengirimkan pesan error jika terjadi kesalahan
            return redirect()->route('settingPath')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
