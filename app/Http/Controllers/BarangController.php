<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Exports\BarangExport;
use App\Imports\BarangImport;
use App\Models\BarangPembelian;
use App\Models\VariasiHargaJual;
use App\Exports\KartuStockExport;
use App\Exports\TemplateBarangExport;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil data yang dipaginate
        $barang = Barang::select('id', 'id_satuan', 'id_kategori', 'nama_barang', 'min_stok_total', 'notif_exp', 'harga_beli', 'harga_jual')
            ->with([
                'satuan:id,nama_satuan',
                'kategori:id,nama_kategori',
            ])
            ->paginate($request->num);

        return response()->json([
            'success' => true,
            'data' => $barang->items(),
            'last_page' => $barang->lastPage(),
            'message' => 'Data Berhasil Ditemukan!',
        ], 200);
    }


    public function searchBarang(Request $request)
    {
        $search = $request->input('search');
        $result = Barang::select('id', 'id_satuan', 'id_kategori', 'nama_barang', 'min_stok_total', 'notif_exp', 'harga_beli', 'harga_jual')
            ->with([
                'satuan:id,nama_satuan',
                'kategori:id,nama_kategori',
            ])
            ->where('nama_barang', 'like', '%' . $search . '%')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Data Berhasil Ditemukan!',
        ]);
    }

    public function beliBarang()
    {
        $barang = Barang::select('id', 'id_satuan', 'nama_barang', 'harga_beli', 'harga_jual')
            ->with([
                'satuan:id,nama_satuan',
                'satuanBarang:id,id_barang,id_satuan,jumlah,harga_beli,harga_jual',
                'satuanBarang.satuan:id,nama_satuan',
            ])
            ->get();

        // $data = $barang->map(function ($item) {
        //     return [
        //         'id' => $item->id,
        //         'nama_barang' => $item->nama_barang,
        //         'harga_beli' => $item->harga_beli,
        //         'satuan_dasar' => [
        //             'id' => $item->satuan->id,
        //             'nama_satuan' => $item->satuan->nama_satuan,
        //         ],
        //         'satuan_barang' => [
        //             'id' => $item->satuanBarang->id,
        //             'nama_satuan' => $item->satuanBarang->satuan->nama_satuan,
        //         ]
        //     ];
        // });

        return response()->json([
            'success' => true,
            'data' => $barang,
            'message' => 'Data Berhasil Ditemukan!',
        ]);
    }

    public function jualBarang()
    {
        $barang = Barang::select('id', 'id_satuan', 'nama_barang',  'harga_jual')
            ->with([
                'satuan:id,nama_satuan',
                'satuanBarang:id,id_barang,id_satuan,jumlah,harga_jual',
                'satuanBarang.satuan:id,nama_satuan',
                'variasiHargaJual:id,id_barang,min_kuantitas,harga',
                'stokBarang' => function ($query) {
                    $query->select('id', 'batch', 'exp_date', 'id_barang', 'stok_apotek')
                        ->where('stok_apotek', '>', 0);
                },
            ])
            ->whereHas('stokBarang', function ($query) {
                $query->where('stok_apotek', '>', 0);
            })
            ->orderBy('exp_date', 'asc')
            ->get();

        $barang->each(function ($item) {
            $item->stok_total = $item->stokBarang->sum('stok_apotek');
        });

        return response()->json([
            'success' => true,
            'data' => $barang,
            'message' => 'Data Berhasil Ditemukan!',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_kategori' => ['required'],
            'id_satuan' => ['required'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'min_stok_total' => 'sometimes',
            'notif_exp' => 'sometimes',
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

        if (isset($validatedData['variasi_harga_juals'])) {
            foreach ($validatedData['variasi_harga_juals'] as $variasiHargaJual) {
                $barang->variasiHargaJual()->create([
                    'id_barang' => $barang->id,
                    'min_kuantitas' => $variasiHargaJual['min_kuantitas'],
                    'harga' => $variasiHargaJual['harga']
                ]);
            }
        }

        if (isset($validatedData['satuan_barangs_id_satuan']) && isset($validatedData['satuan_barangs_jumlah']) && isset($validatedData['satuan_barangs_harga_beli']) && isset($validatedData['satuan_barangs_harga_jual'])) {
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
            'message' => 'Data Barang Berhasil Ditambahkan!',
            'data' => $barang,
        ], 200);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $barang = Barang::select('id', 'id_satuan', 'id_kategori', 'nama_barang', 'min_stok_total', 'notif_exp', 'harga_beli', 'harga_jual')
            ->with([
                'satuan:id,nama_satuan',
                'kategori:id,nama_kategori',
                'variasiHargaJual:id,id_barang,min_kuantitas,harga',
                'satuanBarang:id,id_barang,id_satuan,jumlah,harga_beli,harga_jual',
                'satuanBarang.satuan:id,nama_satuan',
            ])
            ->where('id', $id)
            ->first();
        return response()->json([
            'success' => true,
            'data'    => $barang,
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
            'id_kategori' => ['sometimes'],
            'id_satuan' => ['sometimes'],
            'nama_barang' => ['sometimes', 'string', 'max:255'],
            'harga_beli' => ['sometimes'],
            'harga_jual' => ['sometimes'],
            'min_stok_total' => 'sometimes',
            'notif_exp' => 'sometimes',
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
        if (isset($validatedData['variasi_harga_juals'])) {
            foreach ($validatedData['variasi_harga_juals'] as $index => $variasiHargaJualData) {
                $variasiHargaJual = $barang->variasiHargaJual()->get()[$index] ?? null;
                if ($variasiHargaJual) {
                    $variasiHargaJual->update($variasiHargaJualData);
                } else {
                    $barang->variasiHargaJual()->create($variasiHargaJualData);
                }
            }
        }
        // Update or create SatuanBarang
        $satuanBarang = $barang->satuanBarang;
        if (isset($validatedData['satuan_barangs_id_satuan']) && isset($validatedData['satuan_barangs_jumlah']) && isset($validatedData['satuan_barangs_harga_beli']) && isset($validatedData['satuan_barangs_harga_jual'])) {
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
        }



        return response()->json([
            'status' => true,
            'message' => 'Data Barang Berhasil Diupdate!',
            'data' => $barang,
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ]);

        try {
            Excel::import(new BarangImport, $request->file('file'));

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Berhasil Diimport!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengimport data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detailKartuStok(Barang $barang, Request $request)
    {
        $satuanDasar = $barang->id_satuan;
        if(isset($barang->satuanBarang))
        {
            $satuanBesar = $barang->satuanBarang->id_satuan;
            $jumlahBesar = $barang->satuanBarang->jumlah;
        }
    
        // Purchases
        $purchases = $barang->barangPembelian()
            ->join('pembelians', 'barang_pembelians.id_pembelian', '=', 'pembelians.id')
            ->join('satuans', 'barang_pembelians.id_satuan', '=', 'satuans.id')
            ->select(
                'barang_pembelians.exp_date',
                'pembelians.tanggal',
                'barang_pembelians.batch',
                \DB::raw('SUM(barang_pembelians.jumlah) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('barang_pembelians.exp_date', 'pembelians.tanggal', 'barang_pembelians.batch', 'satuans.id')
            ->paginate($request->num);
    
        // Sales
        $sales = $barang->barangPenjualan()
            ->join('penjualans', 'barang_penjualans.id_penjualan', '=', 'penjualans.id')
            ->join('satuans', 'barang_penjualans.id_satuan', '=', 'satuans.id')
            ->join('stok_barangs', 'barang_penjualans.id_stok_barang', '=', 'stok_barangs.id')
            ->select(
                'penjualans.tanggal',
                'stok_barangs.batch',
                'stok_barangs.exp_date',
                \DB::raw('SUM(barang_penjualans.jumlah) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('penjualans.tanggal', 'stok_barangs.batch', 'stok_barangs.exp_date', 'satuans.id')
            ->paginate($request->num);
    
        // Purchase Returns
        $purchaseReturns = $barang->barangPembelian()
            ->join('pembelians', 'barang_pembelians.id_pembelian', '=', 'pembelians.id')
            ->join('satuans', 'barang_pembelians.id_satuan', '=', 'satuans.id')
            ->join('retur_pembelians', 'barang_pembelians.id_pembelian', '=', 'retur_pembelians.id_pembelian')
            ->join('barang_retur_pembelians', 'barang_pembelians.id', '=', 'barang_retur_pembelians.id_barang_pembelian')
            ->select(
                'retur_pembelians.tanggal',
                'barang_pembelians.batch',
                'barang_pembelians.exp_date',
                \DB::raw('SUM(barang_retur_pembelians.jumlah_retur) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('retur_pembelians.tanggal', 'barang_pembelians.batch', 'barang_pembelians.exp_date', 'satuans.id')
            ->paginate($request->num);
    
        // Sales Returns
        $salesReturns = $barang->barangPenjualan()
            ->join('penjualans', 'barang_penjualans.id_penjualan', '=', 'penjualans.id')
            ->join('satuans', 'barang_penjualans.id_satuan', '=', 'satuans.id')
            ->join('stok_barangs', 'barang_penjualans.id_stok_barang', '=', 'stok_barangs.id')
            ->join('retur_penjualans', 'penjualans.id', '=', 'retur_penjualans.id_penjualan')
            ->join('barang_retur_penjualans', 'barang_penjualans.id', '=', 'barang_retur_penjualans.id_barang_penjualan')
            ->select(
                'retur_penjualans.tanggal',
                'stok_barangs.batch',
                'stok_barangs.exp_date',
                \DB::raw('SUM(barang_retur_penjualans.jumlah_retur) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('retur_penjualans.tanggal', 'stok_barangs.batch', 'stok_barangs.exp_date', 'satuans.id')
            ->paginate($request->num);
    
        // Combine purchases, sales, purchase returns, and sales returns data
        $stockDetails = [];
    
        // Process Purchases
        foreach ($purchases as $purchase) {
            $quantity = $purchase->satuan_id == $satuanDasar
                ? $purchase->jumlah
                : $purchase->jumlah * $jumlahBesar;
    
            $stockDetails[] = [
                'tanggal' => $purchase->tanggal,
                'batch' => $purchase->batch,
                'exp_date' => $purchase->exp_date,
                'masuk' => $quantity,
                'keluar' => 0,
                'jenis_transaksi' => 'pembelian',
            ];
        }
    
        // Process Sales
        foreach ($sales as $sale) {
            $quantity = $sale->satuan_id == $satuanDasar
                ? $sale->jumlah
                : $sale->jumlah * $jumlahBesar;
    
            $stockDetails[] = [
                'tanggal' => $sale->tanggal,
                'batch' => $sale->batch,
                'exp_date' => $sale->exp_date,
                'masuk' => 0,
                'keluar' => $quantity,
                'jenis_transaksi' => 'penjualan',
            ];
        }
    
        // Process Purchase Returns (keluar untuk retur)
        foreach ($purchaseReturns as $return) {
            $quantity = $return->satuan_id == $satuanDasar
                ? $return->jumlah
                : $return->jumlah * $jumlahBesar;
    
            $stockDetails[] = [
                'tanggal' => $return->tanggal,
                'batch' => $return->batch,
                'exp_date' => $return->exp_date,
                'masuk' => 0, // Retur pembelian tidak menambah barang
                'keluar' => $quantity, // Retur pembelian mengurangi stok (keluar)
                'jenis_transaksi' => 'retur_pembelian',
            ];
        }
    
        // Process Sales Returns (masuk untuk retur penjualan)
        foreach ($salesReturns as $return) {
            $quantity = $return->satuan_id == $satuanDasar
                ? $return->jumlah
                : $return->jumlah * $jumlahBesar;
    
            $stockDetails[] = [
                'tanggal' => $return->tanggal,
                'batch' => $return->batch,
                'exp_date' => $return->exp_date,
                'masuk' => $quantity, // Retur penjualan menambah barang
                'keluar' => 0,
                'jenis_transaksi' => 'retur_penjualan',
            ];
        }
    
        // Sort by date
        usort($stockDetails, function ($a, $b) {
            return strtotime($a['tanggal']) - strtotime($b['tanggal']);
        });
    
        // Calculate remaining stock
        $totalMasuk = 0;
        $totalKeluar = 0;
    
        foreach ($stockDetails as &$details) {
            $totalMasuk += $details['masuk'];
            $totalKeluar += $details['keluar'];
            $details['sisa'] = $totalMasuk - $totalKeluar;
        }
    
        $commonData = $purchases->first() ? [
            'nama_barang' => $barang->nama_barang,
            'nama_satuan' => $barang->satuan->nama_satuan,
        ] : [];
    
        return response()->json([
            'status' => true,
            'data' => array_merge($commonData, ['list' => array_values($stockDetails)]),
            'last_page' => $purchases->lastPage(),
            'message' => 'Data Berhasil Ditemukan!',
        ]);
    }
    



    // public function detailKartuStok(Barang $barang)
    // {
    //     // Get all purchase records for the given item, grouped by date and batch
    //     $purchases = Pembelian::where('barang_pembelians.id_barang', $barang->id)
    //         ->join('barang_pembelians', 'pembelians.id', '=', 'barang_pembelians.id_pembelian')
    //         ->join('barangs', 'barang_pembelians.id_barang', '=', 'barangs.id')
    //         ->join('satuans', 'barangs.id_satuan', '=', 'satuans.id')
    //         ->select('barangs.nama_barang', 'satuans.nama_satuan', 'barangs.exp_date', 'barang_pembelians.tanggal', 'barang_pembelians.batch', \DB::raw('SUM(barang_pembelians.jumlah) as masuk'))
    //         ->groupBy('barangs.nama_barang', 'satuans.nama_satuan', 'barangs.exp_date', 'barang_pembelians.tanggal', 'barang_pembelians.batch')
    //         ->get()
    //         ->keyBy(function ($item) {
    //             return $item->tanggal . '-' . $item->batch;
    //         });

    //     // Get all sales records for the given item, grouped by date and batch
    //     $sales = Penjualan::where('barang_penjualans.id_barang', $barang->id)
    //         ->join('barang_penjualans', 'penjualans.id', '=', 'barang_penjualans.id_penjualan')
    //         ->select('barang_penjualans.tanggal', 'barang_penjualans.batch', \DB::raw('SUM(barang_penjualans.jumlah) as keluar'))
    //         ->groupBy('barang_penjualans.tanggal', 'barang_penjualans.batch')
    //         ->get()
    //         ->keyBy(function ($item) {
    //             return $item->tanggal . '-' . $item->batch;
    //         });

    //     // Combine purchases and sales data
    //     $stockDetails = [];
    //     foreach ($purchases as $key => $purchase) {
    //         $stockDetails[$key] = [
    //             'exp_date' => $purchase->exp_date,
    //             'tanggal' => $purchase->tanggal,
    //             'batch' => $purchase->batch,
    //             'masuk' => $purchase->masuk,
    //             'keluar' => $sales->has($key) ? $sales[$key]->keluar : 0,
    //         ];
    //     }

    //     foreach ($sales as $key => $sale) {
    //         if (!isset($stockDetails[$key])) {
    //             $stockDetails[$key] = [
    //                 'exp_date' => $barang->exp_date,  // Assuming exp_date should be included from barang
    //                 'tanggal' => $sale->tanggal,
    //                 'batch' => $sale->batch,
    //                 'masuk' => 0,
    //                 'keluar' => $sale->keluar,
    //             ];
    //         }
    //     }

    //     // Calculate remaining stock
    //     foreach ($stockDetails as &$details) {
    //         $details['sisa'] = $details['masuk'] - $details['keluar'];
    //     }

    //     // Include the common data in the response
    //     $commonData = $purchases->first() ? [
    //         'nama_barang' => $purchases->first()->nama_barang,
    //         'nama_satuan' => $purchases->first()->nama_satuan,
    //     ] : [];

    //     return response()->json([
    //         'status' => true,
    //         'data' => array_merge($commonData, ['list' => array_values($stockDetails)]),
    //     ]);
    // }



    public function kartuStok(barang $barang)
    {
        return Excel::download(new KartuStockExport($barang), 'KartuStok.xlsx');
    }

    public function aturNotif(Request $request)
    {
        $validatedData = $request->validate([
            'notif_exp' => 'required',
            'min_stok_total' => 'required'
        ]);

        $barangs = Barang::all();

        foreach ($barangs as $barang) {
            $barang->update($validatedData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Atur Peringatan Notifikasi berhasil diperbarui!'
        ]);
    }

    public function downloadTemplateBarang()
    {
        return Excel::download(new TemplateBarangExport, 'TemplateBarang.xlsx');
    }
}
