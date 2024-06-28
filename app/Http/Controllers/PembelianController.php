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
        $pembelian = Pembelian::all();
        return response()->json([
            'success' => true,
            'data' => $pembelian->load(['barangPembelian']),
            'message' => 'Data pembelian berhasil ditemukan',
        ]);
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
            'id_sales' => 'required',
            'id_jenis' => 'required',
            'tanggal' => 'required',
            'status' => 'required',
            'tanggal_jatuh_tempo' => 'required',
            'referensi' => 'sometimes',
            'sub_total' => 'required',
            'diskon' => 'sometimes',
            'total' => 'required',
            'catatan' => 'sometimes',
            'barang_pembelians' => 'required|array',
            'barang_pembelians.*.id_barang' => 'required',
            'barang_pembelians.*.jumlah' => 'required',
            'barang_pembelians.*.id_satuan' => 'required',
            'barang_pembelians.*.diskon' => 'required',
            'barang_pembelians.*.harga' => 'required',
            'barang_pembelians.*.total' => 'required'
        ]);

        $pembelian = Pembelian::create($validatedData);

        foreach ($validatedData['barang_pembelians'] as $barangPembelianData) {
            $pembelian->barangPembelian()->create([
                'id_barang' => $barangPembelianData['id_barang'],
                'jumlah' => $barangPembelianData['jumlah'],
                'id_satuan' => $barangPembelianData['id_satuan'],
                'diskon' => $barangPembelianData['diskon'],
                'harga' => $barangPembelianData['harga'],
                'total' => $barangPembelianData['total']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $pembelian->load(['barangPembelian']),
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
