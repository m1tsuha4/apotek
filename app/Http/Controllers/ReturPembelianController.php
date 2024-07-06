<?php

namespace App\Http\Controllers;

use App\Models\ReturPembelian;
use Illuminate\Http\Request;

class ReturPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $returPembelians = ReturPembelian::all();
   
        // $data = [
        //     'id' => $returPembelians->id,
        //     'id_pembelian' => $returPembelians->id_pembelian,
        //     'tanggal' => $returPembelians->tanggal,
        //     'referensi' => $returPembelians->referensi,
        //     'total_retur' => $returPembelians->total_retur,

        // ];
        return response()->json([
            'success' => true,
            'data' => $returPembelians->load('barangReturPembelian'),
            'message' => 'Data Berhasil ditemukan!',
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
        $validatedData = $request->validate([
            'id_pembelian' => ['required'],
            'tanggal' => ['required'],
            'referensi' => ['sometimes'],
            'total_retur' => ['required'],
            'barang_retur_pembelians' => 'required|array',
            'barang_retur_pembelians.*.jumlah_retur' => ['required'],
            'barang_retur_pembelians.*.total' => ['required'],
        ]);

        $returPembelian = ReturPembelian::create($validatedData);

        foreach($validatedData['barang_retur_pembelians'] as $barangReturPembelian) {
            $returPembelian->barangReturPembelian()->create($barangReturPembelian);
        }

        return response()->json([
            'success' => true,
            'data' => $returPembelian->load('barangReturPembelian'),
            'message' => 'Data retur pembelian berhasil ditambahkan',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ReturPembelian $returPembelian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReturPembelian $returPembelian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReturPembelian $returPembelian)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturPembelian $returPembelian)
    {
        //
    }
}
