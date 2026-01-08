<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\userProfileModel;
use App\Models\pictureModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class importCsvController extends Controller
{
    public function index()
    {
        return view('importCsv.index');
    }

    public function import(Request $request)
    {
        ini_set('max_execution_time', 1500);
        $totalRows = 0;
        $currentRow = 0;
        $successCount = 0;

        try {
            $request->validate([
                'csv_file' => 'required|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();

            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                fgetcsv($handle); // Skip header
                while (fgetcsv($handle) !== FALSE) {
                    $totalRows++;
                }
                rewind($handle);
                fgetcsv($handle); // Skip header again

                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    try {
                        if (count($data) < 5) {
                            Log::warning("Baris tidak valid: " . json_encode($data));
                            continue;
                        }

                        $ID = $data[0];
                        $type = $data[1];
                        $picturePath = $data[2];
                        $NAME = $data[3];
                        $Depid = $data[4];

                        if (!file_exists($picturePath)) {
                            Log::error("Gambar tidak ditemukan: " . $picturePath);
                            continue;
                        }

                        $image = Image::make($picturePath);
                        $image->orientate();
                        $image->resize(420, 630);
                        $pictureBase64 = 'data:image/jpeg;base64,' . base64_encode($image->encode('jpg', 75));

                        $userProfile = userProfileModel::updateOrCreate(
                            ['ID' => $ID],
                            [
                                'NAME' => $NAME,
                                'Depid' => $Depid,
                                'photo' => $pictureBase64,
                            ]
                        );

                        $successCount++;
                        Log::info("Berhasil memproses ID {$ID}");
                    } catch (\Exception $e) {
                        Log::error("Gagal memproses baris: " . json_encode($data) . " | Error: " . $e->getMessage());
                        continue;
                    }

                    $currentRow++;
                    $progress = ($currentRow / $totalRows) * 100;
                    Cache::put('import_progress', $progress, now()->addMinutes(30));
                    Cache::put('success_count', $successCount, now()->addMinutes(30));
                    usleep(500000);
                }

                fclose($handle);
            }

            session(['import_progress' => 100]);
            session(['success_count' => $successCount]);

            return redirect()->route('import.csv')->with('success', 'Data berhasil diimpor atau diperbarui.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()]);
        }
    }



    public function getImportProgress()
    {
        $progress = Cache::get('import_progress', 0);
        $successCount = Cache::get('success_count', 0);
        return response()->json([
            'progress' => $progress,
            'success_count' => $successCount,
        ]);
    }
}
