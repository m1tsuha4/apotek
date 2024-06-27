<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validatedData = $request->validate([
            'id_vendor' => 'required',
            'id_metode_bayar' => 'required',
            'tanggal' => 'required',
            'status' => 'required',
            'tanggal_jatuh_tempo' => 'required',
            'barang_pembelians' => 'rquired|array',
            'barang_pembelians.*.id_barang' => 'required',
            'barang_pembelians.*.jumlah' => 'required',
            'barang_pembelians.*.satuan' => 'required',
            'barang_pembelians.*.harga' => 'required'
        ]);

        $pembelian = Pembelian::create($validatedData);

        foreach ($validatedData['barang_pembelians'] as $barangPembelianData) {
            $pembelian->barangPembelian()->create([
                'id_barang' => $barangPembelianData['id_barang'],
                'jumlah' => $barangPembelianData['jumlah'],
                'satuan' => $barangPembelianData['satuan'],
                'harga' => $barangPembelianData['harga']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembelian Berhasil!',
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembelian $pembelian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pembelian $pembelian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pembelian $pembelian)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembelian $pembelian)
    {
        //
    }
}
