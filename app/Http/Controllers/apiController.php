<?php

namespace App\Http\Controllers;

use App\Models\ApiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class apiController extends Controller
{
    public function index()
    {
        $api = ApiModel::all();

        return view('api.index', compact('api'));
    }
    public function create()
    {
        return view('api.create');
    }

    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|string|max:255',
            'desc' => 'required|string',
        ]);

        ApiModel::create($request->all());

        return redirect()->route('api.index')
            ->with('success', 'API URL created successfully.');
    }

    public function edit($id)
    {
        $api = ApiModel::findOrFail($id);
        return view('api.edit', compact('api'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'required|string',
        ]);

        try {
            $api = ApiModel::findOrFail($id);
            $api->update([
                'name' => $request->name,
                'desc' => $request->desc,
            ]);

            return redirect()->route('api.index')
                ->with('success', 'API URL updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update API URL: ' . $e->getMessage());
            return redirect()->route('api.index')
                ->with('error', 'Failed to update API URL. Please try again.');
        }
    }

    public function destroy($id)
    {
        $api = ApiModel::findOrFail($id);
        $api->delete();

        return redirect()->route('api.index')
            ->with('success', 'API URL deleted successfully');
    }
    // public function getApiUrl()
    // {
    //     $api = ApiModel::find(5); // Ambil data dengan id = 5
    //     if ($api && $api->name) {
    //         return response()->json(['url' => $api->name], 200);
    //     } else {
    //         return response()->json(['error' => 'API URL not found'], 404);
    //     }
    // }
}
