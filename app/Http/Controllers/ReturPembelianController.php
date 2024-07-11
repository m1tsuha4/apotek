<?php

namespace App\Http\Controllers;

use App\Models\ReturPembelian;
use Illuminate\Http\Request;

class ReturPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $returPembelians = ReturPembelian::with(['pembelian.sales.vendor', 'barangReturPembelian', 'pembelian.barangPembelian'])->paginate($request->num);

        $data = collect($returPembelians->items())->map(function ($returPembelian) {
            $jumlah = $returPembelian->pembelian->barangPembelian->sum('jumlah');
            $jumlah_retur = $returPembelian->barangReturPembelian->sum('jumlah_retur');
        
            return [
                'id' => $returPembelian->id,
                'id_pembelian' => $returPembelian->id_pembelian,
                'tanggal' => $returPembelian->tanggal,
                'id_sales' => $returPembelian->pembelian->id_sales,
                'nama_sales' => $returPembelian->pembelian->sales->nama_sales,
                'vendor' => $returPembelian->pembelian->sales->vendor->nama_perusahaan,
                'referensi' => $returPembelian->referensi,
                'jumlah' => $jumlah,
                'jumlah_retur' => $jumlah_retur,
                'total' => $returPembelian->total_retur
            ];
        })->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'last_page' => $returPembelians->lastPage(),
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

        foreach ($validatedData['barang_retur_pembelians'] as $barangReturPembelian) {
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
        $returPembelian->load([
            'pembelian',
            'barangReturPembelian',
            'pembelian.barangPembelian',
            'pembelian.barangPembelian.satuan',
            'pembelian.barangPembelian.barang',
        ]);
    
        // Hapus properti created_at dan updated_at dari model utama dan relasi
        $returPembelian->makeHidden(['created_at', 'updated_at']);
        $returPembelian->pembelian->makeHidden(['created_at', 'updated_at']);
        foreach ($returPembelian->barangReturPembelian as $barangRetur) {
            $barangRetur->makeHidden(['created_at', 'updated_at']);
        }
        foreach ($returPembelian->pembelian->barangPembelian as $barangPembelian) {
            $barangPembelian->makeHidden(['created_at', 'updated_at']);
            $barangPembelian->satuan->makeHidden(['created_at', 'updated_at']);
            $barangPembelian->barang->makeHidden(['created_at', 'updated_at']);
        }
    
        return response()->json([
            'success' => true,
            'data' => $returPembelian,
            'message' => 'Data Berhasil ditemukan!',
        ]);
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
        $validatedData = $request->validate([
            'id_pembelian' => ['sometimes'],
            'tanggal' => ['sometimes'],
            'referensi' => ['sometimes'],
            'total_retur' => ['sometimes'],
            'barang_retur_pembelians' => 'sometimes|array',
            'barang_retur_pembelians.*.jumlah_retur' => ['sometimes'],
            'barang_retur_pembelians.*.total' => ['sometimes'],
        ]);

        $returPembelian->update($validatedData);

        foreach($validatedData['barang_retur_pembelians'] as $index => $barangReturPembelianData) {
            $barangReturPembelian = $returPembelian->barangReturPembelian()->get()[$index] ?? null;
            if ($barangReturPembelian) {
                $barangReturPembelian->update($barangReturPembelianData);
            } else {
                $returPembelian->barangReturPembelian()->create($barangReturPembelianData);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $returPembelian->load('barangReturPembelian'),
            'message' => 'Data retur pembelian berhasil diupdate',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturPembelian $returPembelian)
    {
        $returPembelian->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }
}
