<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Exports\BarangExport;
use App\Models\VariasiHargaJual;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barang = Barang::paginate(10);

        return response()->json([
            'success' => true,
            'data'    =>  $barang->load(['satuan','kategori','variasiHargaJual','satuanBarang.satuan']), 
            'last_page' => $barang->lastPage(),
            'message' => 'Data Berhasil Ditemukan!',
        ], 200);
    }
    
    public function beliBarang()
    {
        $barang = Barang::with(['satuan', 'satuanBarang.satuan'])->get();
    
        $data = $barang->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_barang' => $item->nama_barang,
                'harga_beli' => $item->harga_beli,
                'satuan_dasar' => [
                    'id' => $item->satuan->id,
                    'nama_satuan' => $item->satuan->nama_satuan,
                ],
                'satuan_barang' => [
                    'id' => $item->satuanBarang->id,
                    'nama_satuan' => $item->satuanBarang->satuan->nama_satuan,
                ]
            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data Berhasil Ditemukan!',
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
            'id_kategori' => ['required'],
            'id_satuan' => ['required'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'harga_beli' => ['required'],
            'harga_jual' => ['required'],
            'variasi_harga_juals' => 'sometimes|array',
            'variasi_harga_juals.*.min_kuantitas' => 'sometimes',
            'variasi_harga_juals.*.harga' => 'sometimes',
            'satuan_barangs_id_satuan' => 'sometimes',
            'satuan_barangs_jumlah' => 'sometimes',
            'satuan_barangs_harga_beli' => 'sometimes',
            'satuan_barangs_harga_jual' => 'sometimes',
        ]);

        $barang = Barang::create($validatedData);

        foreach ($validatedData['variasi_harga_juals'] as $variasiHargaJual) {
            $barang->variasiHargaJual()->create([
                'id_barang' => $barang->id,
                'min_kuantitas' => $variasiHargaJual['min_kuantitas'],
                'harga' => $variasiHargaJual['harga']
            ]);
        }

        $barang->satuanBarang()->create([
            'id_barang' => $barang->id,
            'id_satuan' => $validatedData['satuan_barangs_id_satuan'],
            'jumlah' => $validatedData['satuan_barangs_jumlah'],
            'harga_beli' => $validatedData['satuan_barangs_harga_beli'],
            'harga_jual' => $validatedData['satuan_barangs_harga_jual']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data Barang Berhasil Ditambahkan!',
            'data' => $barang->load(['satuan', 'kategori', 'variasiHargaJual', 'satuanBarang.satuan']),
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
            'data'    => $barang->load(['satuan','kategori','variasiHargaJual','satuanBarang.satuan']),  
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
    public function update(Request $request, Barang $barang)
    {
        $validatedData = $request->validate([
            'id_kategori' => ['required'],
            'id_satuan' => ['required'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'harga_beli' => ['required'],
            'harga_jual' => ['required'],
            'variasi_harga_juals' => 'sometimes|array',
            'variasi_harga_juals.*.min_kuantitas' => 'sometimes',
            'variasi_harga_juals.*.harga' => 'sometimes',
            'satuan_barangs_id_satuan' => 'sometimes',
            'satuan_barangs_jumlah' => 'sometimes',
            'satuan_barangs_harga_beli' => 'sometimes',
            'satuan_barangs_harga_jual' => 'sometimes'
        ]);
    
        // Update barang data
        $barang->update($validatedData);
 
        // Update or create VariasiHargaJual
        foreach ($validatedData['variasi_harga_juals'] as $index => $variasiHargaJualData) {
            $variasiHargaJual = $barang->variasiHargaJual()->get()[$index] ?? null;
            if ($variasiHargaJual) {
                $variasiHargaJual->update($variasiHargaJualData);
            } else {
                $barang->variasiHargaJual()->create($variasiHargaJualData);
            }
        }
    
        // Update or create SatuanBarang
        $satuanBarang = $barang->satuanBarang;

        if ($satuanBarang) {
            $satuanBarang->update([
                'id_satuan' => $validatedData['satuan_barangs_id_satuan'],
                'jumlah' => $validatedData['satuan_barangs_jumlah'],
                'harga_beli' => $validatedData['satuan_barangs_harga_beli'],
                'harga_jual' => $validatedData['satuan_barangs_harga_jual']
            ]);
        } else {
            $barang->satuanBarang()->create([
                'id_barang' => $barang->id,
                'id_satuan' => $validatedData['satuan_barangs_id_satuan'],
                'jumlah' => $validatedData['satuan_barangs_jumlah'],
                'harga_beli' => $validatedData['satuan_barangs_harga_beli'],
                'harga_jual' => $validatedData['satuan_barangs_harga_jual']
            ]);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Data Barang Berhasil Diupdate!',
            'data' => $barang->load(['satuan', 'kategori', 'variasiHargaJual', 'satuanBarang.satuan']),
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

    public function export()
    {
        return Excel::download(new BarangExport, 'Barang.xlsx');
    }
}
