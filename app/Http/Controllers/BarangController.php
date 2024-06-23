<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allBarang = Barang::all();

        return response()->json([
            'success' => true,
            'data'    => $allBarang,
            'message' => 'Data Berhasil Ditemukan!',
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
            'id_kategori' => ['required'],
            'id_satuan' => ['required'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'harga_beli' => ['required'],
            'harga_jual' => ['required'],
            'variasi_harga_juals' => 'required|array', 
            'variasi_harga_juals.*.min_kuantitasi' => 'required',
            'variasi_harga_juals.*.harga' => 'required',
            'satuan_barangs' => 'required|array', 
            'satuan_barangs.*.harga_beli' => 'required',
            'satuan_barangs.*.harga_jual' => 'required', 
        ]);

        $barang = Barang::create($validatedData);

        foreach ($validatedData['variasi_harga_juals'] as $variasiHargaJual) {
            $barang->variasiHargaJual()->create([
                'min_kuantitasi' => $variasiHargaJual['min_kuantitasi'],
                'harga' => $variasiHargaJual['harga']
            ]);
        }

        
        foreach ($validatedData['satuan_barangs'] as $satuanBarang) {
            $barang->satuanBarang()->create([
                'harga_beli' => $satuanBarang['harga_beli'],
                'harga_jual' => $satuanBarang['harga_jual']
            ]);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Data Barang Berhasil Ditambahkan!',
            'data' => $barang->load(['variasiHargaJual','satuanBarang']), // Muat relasi members
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        //
    }
}
