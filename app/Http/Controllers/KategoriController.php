<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allKategori = Kategori::select('id', 'nama_kategori')->get();
        return response()->json([
            'success' => true,
            'data'    => $allKategori,
            'message' => 'Data Berhasil ditemukan!',
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', 'unique:' . Kategori::class],
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => $request->nama_kategori
        ]);

        if ($kategori) {
            return response()->json([
                'success' => true,
                'data'    => $kategori,
                'message' => 'Data Berhasil ditambahkan!',
            ], 201);
        }

        //return JSON process insert failed
        return response()->json([
            'success' => false,
            'message' => 'Data Gagal ditambahkan!',
        ], 409);
    }

    /**
     * Display the specified resource.
     */
    public function show(Kategori $kategori)
    {
        $kategori = Kategori::findOrFail($kategori->id);
        return response()->json([
            'success' => true,
            'data'    => $kategori,
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kategori $kategori)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_kategori' => ['sometimes', 'string', 'max:255'],
        ]);

        $kategori = Kategori::where('id', $id)->first();

        if(!$kategori){
            return response()->json([
                'success' => false,
                'message' => 'Data Tidak Ditemukan!'
            ], 404);
        }
        
        $kategori->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil diupdate!',
            'data'    => $kategori
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kategori $kategori)
    {
        $kategori->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }
}
