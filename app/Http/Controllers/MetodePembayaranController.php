<?php

namespace App\Http\Controllers;

use App\Models\MetodePembayaran;
use Illuminate\Http\Request;

class MetodePembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $metodePembayaran = MetodePembayaran::select('id','nama_metode')->get();
        return response ()->json([
            'success' => true,
            'data' => $metodePembayaran,
            'message' => 'List Data MetodePembayaran'
        ]);
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
        $request->validate([
            'nama_metode' => 'required'
        ]);

        $metodePembayaran = MetodePembayaran::create([
            'nama_metode' => $request->nama_metode
        ]);

        if ($metodePembayaran){
            return response ()->json([
                'success' => true,
                'data' => $metodePembayaran,
                'message' => 'Data Berhasil Ditambahkan'
            ]);
        }

        return response ()->json([
            'success' => false,
            'message' => 'Data Gagal Ditambahkan'
        ]);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(MetodePembayaran $metodePembayaran)
    {
        $metodePembayaran = MetodePembayaran::findOrFail($metodePembayaran->id);
        return response ()->json([
            'success' => true,
            'data' => $metodePembayaran,
            'message' => 'Data Berhasil Ditemukan'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MetodePembayaran $metodePembayaran)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MetodePembayaran $metodePembayaran)
    {
        $validatedData = $request->validate([
            'nama_metode' => ['sometimes', 'string', 'max:255'],
        ]);

        $metodePembayaran = MetodePembayaran::where('id', $metodePembayaran->id)->first();

        if(!$metodePembayaran) {
            return response ()->json([
                'success' => false,
                'message' => 'Data Gagal Diupdate'
            ]);
        }

        $metodePembayaran->update($validatedData);

        return response ()->json([
            'success' => true,
            'data' => $metodePembayaran,
            'message' => 'Data Berhasil Diupdate'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetodePembayaran $metodePembayaran)
    {
        $metodePembayaran->delete();

        return response ()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus'
        ]);
    }
}
