<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StokBarang;
use Illuminate\Http\Request;
use App\Exports\StokBarangExport;
use Maatwebsite\Excel\Facades\Excel;

class StokBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $stok = Barang::select('id', 'id_kategori', 'id_satuan', 'nama_barang')
            ->with(['kategori:id,nama_kategori', 'satuan:id,nama_satuan', 'stokBarang:id,batch,exp_date,id_barang,stok_total'])
            ->paginate($request->num);
        
        $data = $stok->items();

        foreach ($data as $item) {
            $item->total_stok = StokBarang::where('id_barang', $item->id)->sum('stok_total');
        }
    
        return response()->json([
            'success' => true,
            'data' => $stok->items(),
            'last_page' => $stok->lastPage(),
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
            'id' => 'required',
            'jumlah' => 'required',
            'dari' => 'required',
            'ke' => 'required',
        ]);

        if ($request->dari === $request->ke) {
            return response()->json([
                'success' => false,
                'message' => 'Sumber dan tujuan harus berbeda',
            ], 400);
        }

        $stokBarang = StokBarang::findOrFail($request->id);

        if ($request->dari == 'gudang') {
            if ($stokBarang->stok_gudang < $request->jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok gudang tidak mencukupi',
                ], 400);
            }
            $stokBarang->stok_gudang -= $request->jumlah;
            $stokBarang->stok_apotek += $request->jumlah;
        } else {
            if ($stokBarang->stok_apotek < $request->jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok apotek tidak mencukupi',
                ], 400);
            }
            $stokBarang->stok_apotek -= $request->jumlah;
            $stokBarang->stok_gudang += $request->jumlah;
        }

        $stokBarang->save();

        return response()->json([
            'success' => true,
            'message' => 'Stok barang berhasil ditransfer!',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id_barang)
    {
        $barang = Barang::select('id','id_satuan', 'nama_barang')->with('satuan:id,nama_satuan')->findOrFail($id_barang);
        $barang_total = StokBarang::where('id_barang', $id_barang)->sum('stok_total');
        $barang_gudang = StokBarang::where('id_barang', $id_barang)->sum('stok_gudang');
        $barang_apotek = StokBarang::where('id_barang', $id_barang)->sum('stok_apotek');
        $stok_entries = StokBarang::where('id_barang', $id_barang)
            ->get(['id', 'batch','exp_date', 'stok_gudang', 'stok_apotek', 'stok_total']);

        return response()->json([
            'success' => true,
            'data' => [
                'barang' => $barang,
                'total_stok' => $barang_total,
                'stok_gudang' => $barang_gudang,
                'stok_apotek' => $barang_apotek,
                'stok_entries' => $stok_entries
            ],
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StokBarang $stokBarang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StokBarang $stokBarang)
    {
        $validatedData = $request->validate([
            'min_stok_gudang' => 'sometimes',
            'notif_exp' => 'sometimes',
        ]);

        $stokBarang->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $stokBarang->load(['barang', 'barang.kategori', 'barang.satuan']),
            'message' => 'Data Berhasil diperbarui!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StokBarang $stokBarang)
    {
        $stokBarang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }

    public function export()
    {
        return Excel::download(new StokBarangExport, 'StokBarang.xlsx');
    }

    public function deleteStokBarang(Request $request, StokBarang $stokBarang)
    {
        $stokBarang->where('id_barang','=', $request->id_barang)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }
}
