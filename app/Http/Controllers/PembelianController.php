<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Pembelian;
use App\Models\StokBarang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Exports\PembelianExport;
use Illuminate\Support\Facades\DB;
use App\Models\PembayaranPembelian;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\LaporanKeuanganKeluar;
use App\Models\PergerakanStokPembelian;
use App\Models\ReturPembelian;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pembelian = Pembelian::with('jenis:id,nama_jenis', 'vendor:id,nama_perusahaan', 'sales:id,nama_sales')
            ->select('id', 'id_vendor', 'id_sales', 'id_jenis', 'referensi', 'tanggal', 'status', 'tanggal_jatuh_tempo', 'total')
            ->orderBy('created_at', 'desc')
            ->paginate($request->num);
        return response()->json([
            'success' => true,
            'data' => $pembelian->items(),
            'last_page' => $pembelian->lastPage(),
            'message' => 'Data pembelian berhasil ditemukan',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function generateId()
    {
        $newId = Pembelian::generateId();
        return response()->json([
            'success' => true,
            'data' => $newId,
            'message' => 'ID pembelian berhasil digenerate',
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'id_vendor' => 'required',
            'id_sales' => 'sometimes',
            'id_jenis' => 'required',
            'tanggal' => 'required',
            'status' => 'required',
            'tanggal_jatuh_tempo' => 'required',
            'net_termin' => 'required',
            'referensi' => 'sometimes',
            'sub_total' => 'required',
            'total_diskon_satuan' => 'sometimes',
            'diskon' => 'sometimes',
            'total' => 'required',
            'catatan' => 'sometimes',
            'barang_pembelians' => 'required|array',
            'barang_pembelians.*.id_barang' => 'required',
            'barang_pembelians.*.batch' => 'required',
            'barang_pembelians.*.exp_date' => 'required',
            'barang_pembelians.*.jumlah' => 'required',
            'barang_pembelians.*.id_satuan' => 'required',
            'barang_pembelians.*.jenis_diskon' => 'sometimes',
            'barang_pembelians.*.diskon' => 'sometimes',
            'barang_pembelians.*.harga' => 'required',
            'barang_pembelians.*.total' => 'required'
        ]);

        DB::beginTransaction();

        try {

            $pembelian = Pembelian::create($validatedData);

            foreach ($validatedData['barang_pembelians'] as $barangPembelianData) {
                $pembelian->barangPembelian()->create([
                    'id_barang' => $barangPembelianData['id_barang'],
                    'batch' => $barangPembelianData['batch'],
                    'exp_date' => $barangPembelianData['exp_date'],
                    'jumlah' => $barangPembelianData['jumlah'],
                    'id_satuan' => $barangPembelianData['id_satuan'],
                    'jenis_diskon' => $barangPembelianData['jenis_diskon'],
                    'diskon' => $barangPembelianData['diskon'],
                    'harga' => $barangPembelianData['harga'],
                    'total' => $barangPembelianData['total']
                ]);

                if ($validatedData['id_jenis'] == '2') {
                    $satuanDasar = Barang::where('id', $barangPembelianData['id_barang'])->value('id_satuan');
                    $hargaAsli = Barang::where('id', $barangPembelianData['id_barang'])->value('harga_beli');
                    $totalStok = StokBarang::where('id_barang', $barangPembelianData['id_barang'])->sum('stok_total');

                    if ($barangPembelianData['harga'] != $hargaAsli) {
                        // Update the harga_beli with the new price
                        Barang::where('id', $barangPembelianData['id_barang'])->update(['harga_beli' => $barangPembelianData['harga']]);
                    }

                    if ($barangPembelianData['id_satuan'] == $satuanDasar) {
                        StokBarang::create([
                            'id_barang' => $barangPembelianData['id_barang'],
                            'batch' => $barangPembelianData['batch'],
                            'exp_date' => $barangPembelianData['exp_date'],
                            'stok_apotek' => $barangPembelianData['jumlah'],
                            'stok_total' => $barangPembelianData['jumlah']
                        ]);

                        PergerakanStokPembelian::create([
                            'id_pembelian' => $pembelian->id,
                            'id_barang' => $barangPembelianData['id_barang'],
                            'harga' => $barangPembelianData['harga'],
                            'pergerakan_stok' => $barangPembelianData['jumlah'],
                            'stok_keseluruhan' => $totalStok + $barangPembelianData['jumlah']
                        ]);
                    } else {
                        $satuanBesar = SatuanBarang::where('id_barang', $barangPembelianData['id_barang'])->value('jumlah');
                        $stok = $barangPembelianData['jumlah'] * $satuanBesar;
                        StokBarang::create([
                            'id_barang' => $barangPembelianData['id_barang'],
                            'batch' => $barangPembelianData['batch'],
                            'exp_date' => $barangPembelianData['exp_date'],
                            'stok_apotek' => $stok,
                            'stok_total' => $stok
                        ]);

                        PergerakanStokPembelian::create([
                            'id_pembelian' => $pembelian->id,
                            'id_barang' => $barangPembelianData['id_barang'],
                            'harga' => $barangPembelianData['harga'],
                            'pergerakan_stok' => $stok,
                            'stok_keseluruhan' => $totalStok + $stok
                        ]);
                    }
                }
            }

            if ($validatedData['id_jenis'] == '2') {
                LaporanKeuanganKeluar::create([
                    'id_pembelian' => $pembelian->id,
                    'utang' => $validatedData['total']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $pembelian,
                'message' => 'Pembelian Berhasil!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan ' . $e->getMessage(),
            ]);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'id_vendor' => 'required',
    //         'id_sales' => 'required',
    //         'id_jenis' => 'required',
    //         'tanggal' => 'required',
    //         'status' => 'required',
    //         'tanggal_jatuh_tempo' => 'required',
    //         'referensi' => 'sometimes',
    //         'sub_total' => 'required',
    //         'total_diskon_satuan' => 'sometimes',
    //         'diskon' => 'sometimes',
    //         'total' => 'required',
    //         'catatan' => 'sometimes',
    //         'barang_pembelians' => 'required|array',
    //         'barang_pembelians.*.id_barang' => 'required',
    //         'barang_pembelians.*.batch' => 'required',
    //         'barang_pembelians.*.exp_date' => 'required',
    //         'barang_pembelians.*.jumlah' => 'required',
    //         'barang_pembelians.*.id_satuan' => 'required',
    //         'barang_pembelians.*.jenis_diskon' => 'sometimes',
    //         'barang_pembelians.*.diskon' => 'sometimes',
    //         'barang_pembelians.*.harga' => 'required',
    //         'barang_pembelians.*.total' => 'required'
    //     ]);

    //     $pembelian = Pembelian::create($validatedData);

    //     foreach ($validatedData['barang_pembelians'] as $barangPembelianData) {
    //         $pembelian->barangPembelian()->create([
    //             'id_barang' => $barangPembelianData['id_barang'],
    //             'batch' => $barangPembelianData['batch'],
    //             'exp_date' => $barangPembelianData['exp_date'],
    //             'jumlah' => $barangPembelianData['jumlah'],
    //             'id_satuan' => $barangPembelianData['id_satuan'],
    //             'jenis_diskon' => $barangPembelianData['jenis_diskon'],
    //             'diskon' => $barangPembelianData['diskon'],
    //             'harga' => $barangPembelianData['harga'],
    //             'total' => $barangPembelianData['total']
    //         ]);

    //         $satuanDasar = Barang::where($barangPembelianData['id_barang'])->value('id_satuan');

    //         $hargaAsli = Barang::where($barangPembelianData['id_barang'])->value('harga_beli');

    //         if ($barangPembelianData['harga'] != $hargaAsli) {
    //             $selisih = $barangPembelianData['harga'] - $hargaAsli;

    //             // Create a new record in the PergerakanStokPembelian table
    //             PergerakanStokPembelian::create([
    //                 'id_pembelian' => $pembelian->id,
    //                 'pergerakan_stok' => $selisih
    //             ]);

    //             // Update the harga_beli with the new price
    //             Barang::where('id', $barangPembelianData['id_barang'])->update(['harga_beli' => $barangPembelianData['harga']]);
    //         }

    //         if ($barangPembelianData['id_satuan'] == $satuanDasar) {
    //             StokBarang::create([
    //                 'id_barang' => $barangPembelianData['id_barang'],
    //                 'batch' => $barangPembelianData['batch'],
    //                 'exp_date' => $barangPembelianData['exp_date'],
    //                 'stok_gudang' => $barangPembelianData['jumlah'],
    //                 'stok_total' => $barangPembelianData['jumlah']
    //             ]);
    //         } else {
    //             $satuanBesar = SatuanBarang::where('id_barang', $barangPembelianData['id_barang'])->value('jumlah');
    //             $stok = $barangPembelianData['jumlah'] * $satuanBesar;
    //             StokBarang::create([
    //                 'id_barang' => $barangPembelianData['id_barang'],
    //                 'batch' => $barangPembelianData['batch'],
    //                 'exp_date' => $barangPembelianData['exp_date'],
    //                 'stok_gudang' => $stok,
    //                 'stok_total' => $stok
    //             ]);
    //         }
    //     }

    //     if ($validatedData['id_jenis'] == '2') {
    //         LaporanKeuanganKeluar::create([
    //             'id_pembelian' => $pembelian->id,
    //             'utang' => $validatedData['total']
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $pembelian,
    //         'message' => 'Pembelian Berhasil!',
    //     ], 200);
    // }

    /**
     * Display the specified resource.
     */
    public function show(Pembelian $pembelian)
    {

        $pembayaranPembelian = PembayaranPembelian::where('id_pembelian', $pembelian->id)->sum('total_dibayar');

        $data = [
            'id' => $pembelian->id,
            'status' => $pembelian->status,
            'id_vendor' => $pembelian->id_vendor,
            'nama_perusahaan' => $pembelian->vendor->nama_perusahaan,
            'id_sales' => $pembelian->id_sales,
            'nama_sales' => $pembelian->sales ? $pembelian->sales->nama_sales : null,
            'tanggal' => $pembelian->tanggal,
            'tanggal_jatuh_tempo' => $pembelian->tanggal_jatuh_tempo,
            'jenis' => $pembelian->jenis->nama_jenis,
            'catatan' => $pembelian->catatan,
            'sub_total' => $pembelian->sub_total,
            'diskon' => $pembelian->diskon,
            'total' => $pembelian->total,
            'net_termin' => $pembelian->net_termin,
            'referensi' => $pembelian->referensi,
            'sisa_tagihan' => $pembelian->total - $pembayaranPembelian,
            'barangPembelian' => $pembelian->barangPembelian->map(function ($barangPembelian) {
                return [
                    'id' => $barangPembelian->id,
                    'id_barang' => $barangPembelian->id_barang,
                    'nama_barang' => $barangPembelian->barang->nama_barang,
                    'batch' => $barangPembelian->batch,
                    'exp_date' => $barangPembelian->exp_date,
                    'jumlah' => $barangPembelian->jumlah,
                    'id_satuan' => $barangPembelian->id_satuan,
                    'nama_satuan' => $barangPembelian->satuan->nama_satuan,
                    'jenis_diskon' => $barangPembelian->jenis_diskon,
                    'diskon' => $barangPembelian->diskon,
                    'harga' => $barangPembelian->harga,
                    'total' => $barangPembelian->total
                ];
            }),
            'pembayaranPembelian' => $pembelian->pembayaranPembelian->map(function ($pembayaranPembelian) {
                return [
                    'id' => $pembayaranPembelian->id,
                    'id_pembelian' => $pembayaranPembelian->id_pembelian,
                    'tanggal_pembayaran' => $pembayaranPembelian->tanggal_pembayaran,
                    'metode_pembayaran' => $pembayaranPembelian->metodePembayaran->nama_metode,
                    'total_dibayar' => $pembayaranPembelian->total_dibayar,
                    'referensi_pembayaran' => $pembayaranPembelian->referensi_pembayaran
                ];
            }),
            'returPembelian' => $pembelian->returPembelian->map(function ($returPembelian) {
                return [
                    'id' => $returPembelian->id,
                    'total_retur' => $returPembelian->total_retur
                ];
            })
        ];
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data pembelian berhasil ditemukan',
        ]);
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
        $validatedData = $request->validate([
            'id_vendor' => 'sometimes',
            'id_sales' => 'sometimes',
            'id_jenis' => 'sometimes',
            'tanggal' => 'sometimes',
            'status' => 'sometimes',
            'tanggal_jatuh_tempo' => 'sometimes',
            'net_termin' => 'sometimes',
            'referensi' => 'sometimes',
            'sub_total' => 'sometimes',
            'diskon' => 'sometimes',
            'total_diskon_satuan' => 'sometimes',
            'total' => 'sometimes',
            'catatan' => 'sometimes',
            'barang_pembelians' => 'sometimes|array',
            'barang_pembelians.*.id_barang' => 'sometimes',
            'barang_pembelians.*.batch' => 'sometimes',
            'barang_pembelians.*.exp_date' => 'sometimes',
            'barang_pembelians.*.jumlah' => 'sometimes',
            'barang_pembelians.*.id_satuan' => 'sometimes',
            'barang_pembelians.*.jenis_diskon' => 'sometimes',
            'barang_pembelians.*.diskon' => 'sometimes',
            'barang_pembelians.*.harga' => 'sometimes',
            'barang_pembelians.*.total' => 'sometimes'
        ]);

        DB::beginTransaction();

        try {
            //variabel baru
            $total_lama = $pembelian->total;
            $total_baru = $validatedData['total'];

            $pembelian->update($validatedData);

            foreach ($validatedData['barang_pembelians'] as $index => $barangPembelianData) {
                $barangPembelian = $pembelian->barangPembelian()->get()[$index] ?? null;
                if ($barangPembelian) {
                    $barangPembelian->update($barangPembelianData);
                } else {
                    $barangPembelian = $pembelian->barangPembelian()->create($barangPembelianData);
                }

                if ($validatedData['id_jenis'] == '2') {
                    $satuanDasar = Barang::where('id', $barangPembelianData['id_barang'])->value('id_satuan');
                    $idSatuanBesar = SatuanBarang::where('id_barang', $barangPembelianData['id_barang'])->value('id_satuan');
                    $hargaAsli = Barang::where('id', $barangPembelianData['id_barang'])->value('harga_beli');
                    $totalStok = StokBarang::where('id_barang', $barangPembelianData['id_barang'])->where('batch', '!=', $barangPembelianData['batch'])->sum('stok_total');

                    if ($barangPembelianData['harga'] != $hargaAsli) {
                        Barang::where('id', $barangPembelianData['id_barang'])->update(['harga_beli' => $barangPembelianData['harga']]);
                    }

                    $stokBarang = StokBarang::where('id_barang', $barangPembelianData['id_barang'])->where('batch', $barangPembelianData['batch'])->first();
                    $pergerakanStok = PergerakanStokPembelian::where('id_pembelian', $pembelian->id)->where('id_barang', $barangPembelianData['id_barang'])->first();

                    if ($barangPembelianData['id_satuan'] == $satuanDasar) {
                        $stok = $barangPembelianData['jumlah'];

                        if ($stokBarang) {
                            $stokBarang->update([
                                'exp_date' => $barangPembelianData['exp_date'],
                                'stok_apotek' => $stok,
                                'stok_total' => $stok
                            ]);
                            $pergerakanStok->update([
                                'harga' => $barangPembelianData['harga'],
                                'pergerakan_stok' => $barangPembelianData['jumlah'],
                                'stok_keseluruhan' => $totalStok + $barangPembelianData['jumlah']
                            ]);
                        } else {
                            StokBarang::create([
                                'id_barang' => $barangPembelianData['id_barang'],
                                'batch' => $barangPembelianData['batch'],
                                'exp_date' => $barangPembelianData['exp_date'],
                                'stok_apotek' => $stok,
                                'stok_total' => $stok
                            ]);
                            PergerakanStokPembelian::create([
                                'id_pembelian' => $pembelian->id,
                                'id_barang' => $barangPembelianData['id_barang'],
                                'harga' => $barangPembelianData['harga'],
                                'pergerakan_stok' => $barangPembelianData['jumlah'],
                                'stok_keseluruhan' => $totalStok + $barangPembelianData['jumlah']
                            ]);
                        }
                    } elseif ($barangPembelianData['id_satuan'] == $idSatuanBesar) {
                        $satuanBesar = SatuanBarang::where('id_barang', $barangPembelianData['id_barang'])->value('jumlah');
                        $stok = $barangPembelianData['jumlah'] * $satuanBesar;

                        if ($stokBarang) {
                            $stokBarang->update([
                                'exp_date' => $barangPembelianData['exp_date'],
                                'stok_apotek' => $stok,
                                'stok_total' => $stok
                            ]);
                            if ($pergerakanStok) {
                                $pergerakanStok->update([
                                    'harga' => $barangPembelianData['harga'],
                                    'pergerakan_stok' => $stok,
                                    'stok_keseluruhan' => $totalStok + $stok
                                ]);
                            }
                        } else {
                            StokBarang::create([
                                'id_barang' => $barangPembelianData['id_barang'],
                                'batch' => $barangPembelianData['batch'],
                                'exp_date' => $barangPembelianData['exp_date'],
                                'stok_apotek' => $stok,
                                'stok_total' => $stok
                            ]);
                            PergerakanStokPembelian::create([
                                'id_pembelian' => $pembelian->id,
                                'id_barang' => $barangPembelianData['id_barang'],
                                'harga' => $barangPembelianData['harga'],
                                'pergerakan_stok' => $stok,
                                'stok_keseluruhan' => $totalStok + $stok
                            ]);
                        }
                    }
                }
            }

            if ($validatedData['id_jenis'] == '2') {
                $total_dibayar = PembayaranPembelian::where('id_pembelian', $pembelian->id)->sum('total_dibayar');

                $laporanKeuangan = LaporanKeuanganKeluar::where('id_pembelian', $pembelian->id)->firstOrFail();

                $current_pengeluaran = $laporanKeuangan->pengeluaran;
                $current_utang = $laporanKeuangan->utang;

                $sisa = $total_lama - $total_baru;
                $sisa_abs = abs($sisa);

                if ($sisa < 0) {
                    $laporanKeuangan->update([
                        'utang' => $current_utang + $sisa_abs,
                    ]);
                } elseif ($sisa > 0) {
                    if ($sisa <= $current_utang) {
                        $laporanKeuangan->update([
                            'utang' => $current_utang - $sisa_abs,
                        ]);
                    }

                    if ($sisa > 0 && $current_pengeluaran > 0) {
                        $laporanKeuangan->update([
                            'pengeluaran' => $current_pengeluaran - $sisa_abs,
                        ]);
                    }
                }

                if ($total_dibayar == $validatedData['total']) {
                    $pembelian->update([
                        'status' => 'Lunas',
                    ]);
                } elseif ($total_dibayar == 0) {
                    $pembelian->update([
                        'status' => 'Belum Dibayar',
                    ]);
                } elseif ($total_dibayar < $validatedData['total']) {
                    $pembelian->update([
                        'status' => 'Dibayar Sebagian',
                    ]);
                }

                // LaporanKeuanganKeluar::updateOrCreate(
                //     [
                //         'id_pembelian' => $pembelian->id
                //     ],
                //     [
                //         'utang' => $validatedData['total']
                //     ]
                // );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $pembelian,
                'message' => 'Pembelian Berhasil!',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan ' . $th->getMessage(),
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembelian $pembelian)
    {
        $pembelian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }

    public function export()
    {
        return Excel::download(new PembelianExport, 'pembelian.xlsx');
    }

    public function returPembelian(Pembelian $pembelian)
    {
        $data = [
            'id' => $pembelian->id,
            'barangPembelian' => $pembelian->barangPembelian->map(function ($barangPembelian) {
                $jumlah_retur = ReturPembelian::where('id_pembelian', $barangPembelian->id_pembelian)
                    ->join('barang_retur_pembelians', 'retur_pembelians.id', '=', 'barang_retur_pembelians.id_retur_pembelian')
                    ->where('barang_retur_pembelians.id_barang_pembelian', $barangPembelian->id)
                    ->sum('barang_retur_pembelians.jumlah_retur');
                $jumlah_bisa_retur = $barangPembelian->jumlah - $jumlah_retur;
                return [
                    'id' => $barangPembelian->id,
                    'id_barang' => $barangPembelian->id_barang,
                    'nama_barang' => $barangPembelian->barang->nama_barang,
                    'batch' => $barangPembelian->batch,
                    'jumlah' => $barangPembelian->jumlah,
                    'jumlah_bisa_retur' => $jumlah_bisa_retur,
                    'id_satuan' => $barangPembelian->id_satuan,
                    'nama_satuan' => $barangPembelian->satuan->nama_satuan,
                    'jenis_diskon' => $barangPembelian->jenis_diskon,
                    'diskon' => $barangPembelian->diskon,
                    'harga' => $barangPembelian->harga,
                    'total' => $barangPembelian->total
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'messages' => 'Data Retur Berhasil ditampilkan!'
        ]);
    }

    public function setPembelian(Pembelian $pembelian)
    {
        DB::beginTransaction();

        try {

            $pembelian->update(['id_jenis' => '2']);

            foreach ($pembelian->barangPembelian as $item) {
                $barang = Barang::find($item->id_barang);
                $satuanDasar = $barang->id_satuan;
                $hargaAsli = $barang->harga_beli;
                $totalStok = StokBarang::where('id_barang', $item->id_barang)->sum('stok_total');

                // Update harga_beli jika berbeda
                if ($item->harga != $hargaAsli) {
                    $barang->update(['harga_beli' => $item->harga]);
                }

                // Cek apakah satuan barang sama dengan satuan dasar
                $stok = $item->id_satuan == $satuanDasar
                    ? $item->jumlah
                    : $item->jumlah * SatuanBarang::where('id_barang', $item->id_barang)->value('jumlah');

                // Update stok barang
                StokBarang::create([
                    'id_barang' => $item->id_barang,
                    'batch' => $item->batch,
                    'exp_date' => $item->exp_date,
                    'stok_apotek' => $stok,
                    'stok_total' => $stok,
                ]);

                // Catat pergerakan stok
                PergerakanStokPembelian::create([
                    'id_pembelian' => $pembelian->id,
                    'id_barang' => $item->id_barang,
                    'harga' => $item->harga,
                    'pergerakan_stok' => $stok,
                    'stok_keseluruhan' => $totalStok + $stok,
                ]);
            }

            // Buat laporan keuangan
            LaporanKeuanganKeluar::create([
                'id_pembelian' => $pembelian->id,
                'utang' => $pembelian->total,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $pembelian,
                'message' => 'Pembelian Berhasil!',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
