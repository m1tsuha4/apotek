<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Models\VariasiHargaJual;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barang = Barang::all();

        return response()->json([
            'success' => true,
            'data'    =>  $barang->load(['variasiHargaJual','satuanBarang']), 
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
            'variasi_harga_juals.*.min_kuantitas' => 'required',
            'variasi_harga_juals.*.harga' => 'required',
            'satuan_barangs' => 'required|array', 
            'satuan_barangs.*.id_satuan' => 'required',
            'satuan_barangs.*.harga_beli' => 'required',
            'satuan_barangs.*.harga_jual' => 'required', 
        ]);

        $barang = Barang::create($validatedData);

        foreach ($validatedData['variasi_harga_juals'] as $variasiHargaJual) {
            $barang->variasiHargaJual()->create([
                'id_barang' => $barang->id,
                'min_kuantitas' => $variasiHargaJual['min_kuantitasi'],
                'harga' => $variasiHargaJual['harga']
            ]);
        }

        
        foreach ($validatedData['satuan_barangs'] as $satuanBarang) {
            $barang->satuanBarang()->create([
                'id_barang' => $barang->id,
                'id_satuan' => $satuanBarang['id_satuan'],
                'harga_beli' => $satuanBarang['harga_beli'],
                'harga_jual' => $satuanBarang['harga_jual']
            ]);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Data Barang Berhasil Ditambahkan!',
            'data' => $barang->load(['variasiHargaJual','satuanBarang']), 
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $barang = Barang::where('id', $id)->first();
        return response()->json([
            'success' => true,
            'data'    =>  $barang->load(['variasiHargaJual','satuanBarang']), 
            'message' => 'Data Berhasil Ditemukan!',
        ], 200);
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
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'id_kategori' => ['required'],
            'id_satuan' => ['required'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'harga_beli' => ['required'],
            'harga_jual' => ['required'],
            'variasi_harga_juals' => 'required|array',
            'variasi_harga_juals.*.id' => 'sometimes|required|exists:variasi_harga_juals,id',
            'variasi_harga_juals.*.min_kuantitas' => 'required',
            'variasi_harga_juals.*.harga' => 'required',
            'satuan_barangs' => 'required|array',
            'satuan_barangs.*.id' => 'sometimes|required|exists:satuan_barangs,id',
            'satuan_barangs.*.id_satuan' => 'required',
            'satuan_barangs.*.harga_beli' => 'required',
            'satuan_barangs.*.harga_jual' => 'required',
        ]);

        $barang = Barang::findOrFail($id);
        $barang->update($validatedData);

        // Update or create VariasiHargaJual
        foreach ($validatedData['variasi_harga_juals'] as $variasiHargaJualData) {
            if (isset($variasiHargaJualData['id'])) {
                $variasiHargaJual = VariasiHargaJual::findOrFail($variasiHargaJualData['id']);
                $variasiHargaJual->update($variasiHargaJualData);
            } else {
                $barang->variasiHargaJual()->create($variasiHargaJualData);
            }
        }

        // Update or create SatuanBarang
        foreach ($validatedData['satuan_barangs'] as $satuanBarangData) {
            if (isset($satuanBarangData['id'])) {
                $satuanBarang = SatuanBarang::findOrFail($satuanBarangData['id']);
                $satuanBarang->update($satuanBarangData);
            } else {
                $barang->satuanBarang()->create($satuanBarangData);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Barang Berhasil Diupdate!',
            'data' => $barang->load(['variasiHargaJual', 'satuanBarang']),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }
}
