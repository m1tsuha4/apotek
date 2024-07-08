<?php

namespace App\Http\Controllers;

use App\Models\PembayaranPembelian;
use App\Models\Pembelian;
use Illuminate\Http\Request;

class PembayaranPembelianController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pembelian' => 'required',
            'id_metode_pembayaran' => 'required',
            'total_dibayar' => 'required',
            'tanggal_pembayaran' => 'required',
            'referensi_pembayaran' => 'sometimes',
        ]);

        $pembayaranPembelian = PembayaranPembelian::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $pembayaranPembelian,
            'message' => 'Data pembayaran pembelian ditambahkan!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PembayaranPembelian $pembayaranPembelian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PembayaranPembelian $pembayaranPembelian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $total_dibayar = PembayaranPembelian::where('id_pembelian', $id)->sum('total_dibayar');
        $pembelian = Pembelian::findOrFail($id);

        if ($total_dibayar == 0) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada pembayaran yang dilakukan.',
            ]);
        } elseif ($total_dibayar == $pembelian->total) {
            $pembelian->update([
                'status' => 'Lunas',
            ]);
            return response()->json([
                'success' => true,
                'data' => $pembelian->status,
                'message' => 'Data pembayaran pembelian diperbarui menjadi Lunas!',
            ]);
        } elseif ($total_dibayar < $pembelian->total) {
            $pembelian->update([
                'status' => 'Pembayaran Sebagian',
            ]);
            return response()->json([
                'success' => true,
                'data' => $pembelian->status,
                'message' => 'Data pembayaran pembelian diperbarui menjadi Pembayaran Sebagian!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan dalam pembaruan status pembayaran!',
            ]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PembayaranPembelian $pembayaranPembelian)
    {
        //
    }
}
