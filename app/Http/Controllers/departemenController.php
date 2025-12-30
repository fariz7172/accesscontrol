<?php

namespace App\Http\Controllers;

use App\Models\departmentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class departemenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departemen = departmentModel::paginate(10);
        return view('departemen.index', compact('departemen'));
    }


    public function store(Request $request)
    {
        Log::info('Request data:', $request->all());

        $request->validate([

            'number' => 'required|numeric|unique:departemen,number',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:100',
        ], [
            'number.unique' => 'Data number already exists',
        ]);

        departmentModel::create($request->all());
        return redirect()->back()->with('success', 'departemen berhasil ditambahkan!');
    }
    public function edit($encryptedId)
    {
        // Mendekripsi ID yang diterima
        $id = decryptId($encryptedId);

        // Jika dekripsi gagal, tampilkan halaman error
        if ($id === null) {
            return view('errorHandler', ['error' => 'The payload is invalid.']);
        }

        $departemen = departmentModel::findOrFail($id);
        return view('departemen.edit', compact('departemen'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:100',
        ]);

        $departemen = departmentModel::findOrFail($id);
        $departemen->update($request->all());
        return redirect('departemen')->with('success', 'departemen berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $departemen = departmentModel::findOrFail($id);
        $departemen->delete();
        return redirect()->back()->with('success', 'departemen berhasil dihapus!');
    }
}
