<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allSatuan = Satuan::select('id', 'nama_satuan')->get();
        return response()->json([
            'success' => true,
            'data'    => $allSatuan,
            'message' => 'Data Berhasil ditemukan!',
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_satuan' => ['required', 'string', 'max:255', 'unique:' . Satuan::class],
        ]);

        $satuan = Satuan::create([
            'nama_satuan' => $request->nama_satuan
        ]);

        if($satuan) {
            return response()->json([
                'success' => true,
                'data'    => $satuan,
                'message' => 'Data Berhasil ditambahkan!',
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Satuan $satuan)
    {
        $satuan = Satuan::findOrFail($satuan->id);
        return response()->json([
            'success' => true,
            'data'    => $satuan,
            'message' => 'Data Berhasil ditemukan!',
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Satuan $satuan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_satuan' => ['sometimes', 'string', 'max:255'],
        ]);

        $satuan = Satuan::where('id', $id)->first();

        if(!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan!',
            ], 404);
        }

        $satuan->update($validatedData);

        return response()->json([
            'success' => true,
            'data'    => $satuan,
            'message' => 'Data Berhasil diupdate!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Satuan $satuan)
    {
        $satuan->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ], 200);
    }
}
