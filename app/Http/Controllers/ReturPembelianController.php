<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\StokBarang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Models\ReturPembelian;
use App\Models\BarangPembelian;
use Illuminate\Support\Facades\DB;
use App\Models\PembayaranPembelian;
use App\Models\LaporanKeuanganKeluar;
use App\Models\PergerakanStokPembelian;

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
                'id_vendor' => $returPembelian->pembelian->id_vendor,
                'vendor' => $returPembelian->pembelian->vendor->nama_perusahaan,
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
    public function generateId()
    {
        $newId = ReturPembelian::generateId();
        return response()->json([
            'success' => true,
            'data' => $newId,
            'message' => 'ID retur pembelian berhasil digenerate',
        ]);
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
            'barang_retur_pembelians.*.id_barang' => ['required'],
            'barang_retur_pembelians.*.batch' => ['required'],
            'barang_retur_pembelians.*.jumlah_retur' => ['required'],
            'barang_retur_pembelians.*.id_satuan' => ['required'],
            'barang_retur_pembelians.*.total' => ['required'],
        ]);

        DB::beginTransaction();

        try {

            $returPembelian = ReturPembelian::create($validatedData);

            foreach ($validatedData['barang_retur_pembelians'] as $barangReturPembelian) {

                $barang_pembelian = BarangPembelian::where('id_pembelian', $validatedData['id_pembelian'])->where('id_barang', $barangReturPembelian['id_barang'])->where('batch', $barangReturPembelian['batch'])->first();

                $returPembelian->barangReturPembelian()->create([
                    'id_barang' => $barangReturPembelian['id_barang'],
                    'id_barang_pembelian' => $barang_pembelian->id,
                    'jumlah_retur' => $barangReturPembelian['jumlah_retur'],
                    'id_satuan' => $barangReturPembelian['id_satuan'],
                    'total' => $barangReturPembelian['total'],
                ]);

                $stokBarang = StokBarang::where('id_barang', $barangReturPembelian['id_barang'])
                    ->where('batch', $barangReturPembelian['batch'])
                    ->first();

                $totalStok = StokBarang::where('id_barang', $barangReturPembelian['id_barang'])
                    ->where('batch', $barangReturPembelian['batch'])->sum('stok_total');

                $satuanDasar = Barang::where('id', $barangReturPembelian['id_barang'])->value('id_satuan');

                $jumlahBarangPembelian = BarangPembelian::where('id_pembelian', $validatedData['id_pembelian'])->where('id_barang', $barangReturPembelian['id_barang'])->where('batch', $barangReturPembelian['batch'])->value('jumlah');

                if ($stokBarang) {
                    if ($barangReturPembelian['id_satuan'] == $satuanDasar) {
                        if ($barangReturPembelian['jumlah_retur'] > $jumlahBarangPembelian) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Jumlah retur melebihi jumlah yang dibeli',
                            ], 400);
                        }
                        // Jika satuan retur sama dengan satuan dasar, kurangi langsung dengan jumlah retur
                        $stokBarang->stok_apotek -= $barangReturPembelian['jumlah_retur'];
                        $stokBarang->stok_total -= $barangReturPembelian['jumlah_retur'];
                        PergerakanStokPembelian::create([
                            'id_retur_pembelian' => $returPembelian->id,
                            'id_barang' => $barangReturPembelian['id_barang'],
                            'harga' => $barang_pembelian->harga,
                            'pergerakan_stok' => $barangReturPembelian['jumlah_retur'],
                            'stok_keseluruhan' => $totalStok - $barangReturPembelian['jumlah_retur'],
                        ]);
                    } else {
                        // Jika satuan retur berbeda dengan satuan dasar, konversi jumlah retur
                        $satuanBesar = SatuanBarang::where('id_barang', $barangReturPembelian['id_barang'])
                            ->where('id_satuan', $barangReturPembelian['id_satuan'])
                            ->value('jumlah');

                        if ($barangReturPembelian['jumlah_retur'] * $satuanBesar > $satuanBesar * $jumlahBarangPembelian) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Jumlah retur melebihi yang dibeli',
                            ], 400);
                        }
                        $stok = $barangReturPembelian['jumlah_retur'] * $satuanBesar;
                        $stokBarang->stok_apotek -= $stok;
                        $stokBarang->stok_total -= $stok;
                        PergerakanStokPembelian::create([
                            'id_retur_pembelian' => $returPembelian->id,
                            'id_barang' => $barangReturPembelian['id_barang'],
                            'harga' => $barang_pembelian->harga,
                            'pergerakan_stok' => $stok,
                            'stok_keseluruhan' => $totalStok - $stok,
                        ]);
                    }
                    $stokBarang->save();
                } else {
                    DB::rollBack();
                    // Handle the case where the stock doesn't exist
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok barang tidak ditemukan',
                    ], 400);
                }
            }

            // Retrieve the total amount of the purchase
            $pembelian = Pembelian::findOrFail($validatedData['id_pembelian']);

            // Sum the current total payments
            $total_dibayar = PembayaranPembelian::where('id_pembelian', $validatedData['id_pembelian'])->sum('total_dibayar');

            // Calculate new total after adding the new payment
            $new_total_dibayar = $total_dibayar + $validatedData['total_retur'];

            $laporanKeuangan = LaporanKeuanganKeluar::where('id_pembelian', $validatedData['id_pembelian'])->firstOrFail();

            $current_pengeluaran = $laporanKeuangan->pengeluaran;
            $current_utang = $laporanKeuangan->utang;

            // if ($new_total_dibayar > $pembelian->total) {
            //     DB::rollBack();
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Total retur melebihi total pembelian',
            //     ], 400);
            // }

            $remaining_retur = $validatedData['total_retur'];

            if ($current_utang > 0) {
                if ($remaining_retur <= $current_utang) {
                    $laporanKeuangan->update([
                        'utang' => $current_utang - $remaining_retur,
                    ]);
                    $remaining_retur = 0;
                } else {
                    $laporanKeuangan->update([
                        'utang' => 0,
                    ]);
                    $remaining_retur -= $current_utang;
                }
            }

            if ($remaining_retur > 0 && $current_pengeluaran > 0) {
                $laporanKeuangan->update([
                    'pengeluaran' => $current_pengeluaran - $remaining_retur,
                ]);
            }

            if ($new_total_dibayar >= $pembelian->total) {
                $pembelian->update([
                    'status' => 'Lunas',
                ]);
            } else {
                $pembelian->update([
                    'status' => 'Dibayar Sebagian',
                ]);
            }

            // Create the new payment
            $pembayaranPembelian = PembayaranPembelian::updateOrCreate(
                [
                    'id_pembelian' => $validatedData['id_pembelian'],
                    'id_metode_pembayaran' => 1,
                ],
                [
                    'total_dibayar' => $validatedData['total_retur'],
                    'referensi_pembayaran' => '-',
                    'tanggal_pembayaran' => $validatedData['tanggal'],
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $returPembelian,
                'message' => 'Data retur pembelian berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalalahan: ' . $e->getMessage(),
            ]);
        }
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
            'barang_retur_pembelians.*.id_barang' => ['sometimes'],
            'barang_retur_pembelians.*.batch' => ['sometimes'],
            'barang_retur_pembelians.*.jumlah_retur' => ['sometimes'],
            'barang_retur_pembelians.*.id_satuan' => ['sometimes'],
            'barang_retur_pembelians.*.total' => ['sometimes'],
        ]);

        DB::beginTransaction();

        try {

            $returPembelian->update($validatedData);

            foreach ($validatedData['barang_retur_pembelians'] as $index => $barangReturPembelianData) {
                $barangReturPembelian = $returPembelian->barangReturPembelian()->get()[$index] ?? null;

                if ($barangReturPembelian) {
                    // Update the existing return item
                    $stokBarang = StokBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                        ->where('batch', $barangReturPembelianData['batch'])
                        ->first();

                    $totalStok = StokBarang::where('id_barang', $barangReturPembelianData['id_barang'])->where('batch', '!=', $barangReturPembelianData['batch'])->sum('stok_total');
                    $pergerakanStok = PergerakanStokPembelian::where('id_retur_pembelian', $returPembelian->id)->where('id_barang', $barangReturPembelianData['id_barang'])->first();
                    // dd($pergerakanStok);

                    if ($stokBarang) {
                        // Calculate the stock difference
                        $jumlahReturLama = $barangReturPembelian->jumlah_retur;
                        $jumlahReturBaru = $barangReturPembelianData['jumlah_retur'];

                        $satuanDasar = Barang::where('id', $barangReturPembelianData['id_barang'])->value('id_satuan');

                        if ($barangReturPembelianData['id_satuan'] == $satuanDasar) {
                            $stokBarang->stok_apotek += $jumlahReturLama;
                            $stokBarang->stok_apotek -= $jumlahReturBaru;
                            $pergerakanStok->update([
                                'pergerakan_stok' => $barangReturPembelianData['jumlah_retur'],
                                'stok_keseluruhan' => $totalStok - $barangReturPembelianData['jumlah_retur'],
                            ]);
                        } else {
                            $satuanBesar = SatuanBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                                ->where('id_satuan', $barangReturPembelianData['id_satuan'])
                                ->value('jumlah');
                            $stokBarang->stok_apotek += $jumlahReturLama * $satuanBesar;
                            $stokBarang->stok_apotek -= $jumlahReturBaru * $satuanBesar;
                            $stokBarang->stok_total += $jumlahReturLama * $satuanBesar;
                            $stokBarang->stok_total -= $jumlahReturBaru * $satuanBesar;
                            $pergerakanStok->update([
                                'pergerakan_stok' => $barangReturPembelianData['jumlah_retur'] * $satuanBesar,
                                'stok_keseluruhan' => $totalStok - $barangReturPembelianData['jumlah_retur'] * $satuanBesar,
                            ]);
                        }

                        $stokBarang->save();
                        $pergerakanStok->save();
                    }

                    $barangReturPembelian->update($barangReturPembelianData);
                } else {
                    $barang_pembelian = BarangPembelian::where('id_pembelian', $validatedData['id_pembelian'])->where('id_barang', $barangReturPembelianData['id_barang'])->where('batch', $barangReturPembelianData['batch'])->first();

                    // Create a new return item
                    $barangReturPembelian = $returPembelian->barangReturPembelian()->create([
                        'id_barang' => $barangReturPembelianData['id_barang'],
                        'id_barang_pembelian' => $barang_pembelian->id,
                        'jumlah_retur' => $barangReturPembelianData['jumlah_retur'],
                        'id_satuan' => $barangReturPembelianData['id_satuan'],
                        'total' => $barangReturPembelianData['total'],
                    ]);

                    $stokBarang = StokBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                        ->where('batch', $barangReturPembelianData['batch'])
                        ->first();

                    $totalStok = StokBarang::where('id_barang', $barangReturPembelianData['id_barang'])->where('batch', '!=', $barangReturPembelianData['batch'])->sum('stok_total');
                    $pergerakanStok = PergerakanStokPembelian::where('id_retur_pembelian', $returPembelian->id)->where('id_barang', $barangReturPembelianData['id_barang'])->first();

                    $satuanDasar = Barang::where('id', $barangReturPembelianData['id_barang'])->value('id_satuan');

                    if ($stokBarang) {
                        if ($barangReturPembelianData['id_satuan'] == $satuanDasar) {
                            $stokBarang->stok_apotek -= $barangReturPembelianData['jumlah_retur'];
                            $stokBarang->stok_total -= $barangReturPembelianData['jumlah_retur'];
                            if (!$pergerakanStok) {
                                $pergerakanStok = PergerakanStokPembelian::create([
                                    'id_retur_pembelian' => $returPembelian->id,
                                    'id_barang' => $barangReturPembelianData['id_barang'],
                                    'harga' => $barang_pembelian->harga,
                                    'pergerakan_stok' => $barangReturPembelianData['jumlah_retur'],
                                    'stok_keseluruhan' => $totalStok - $barangReturPembelianData['jumlah_retur'],
                                ]);
                            } else {
                                $pergerakanStok->update([
                                    'pergerakan_stok' => $barangReturPembelianData['jumlah_retur'],
                                    'stok_keseluruhan' => $totalStok - $barangReturPembelianData['jumlah_retur'],
                                ]);
                            }
                        } else {
                            $satuanBesar = SatuanBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                                ->where('id_satuan', $barangReturPembelianData['id_satuan'])
                                ->value('jumlah');
                            $stokBarang->stok_apotek -= $barangReturPembelianData['jumlah_retur'] * $satuanBesar;
                            $stokBarang->stok_total -= $barangReturPembelianData['jumlah_retur'] * $satuanBesar;
                            if (!$pergerakanStok) {
                                $pergerakanStok = PergerakanStokPembelian::create([
                                    'id_retur_pembelian' => $returPembelian->id,
                                    'id_barang' => $barangReturPembelianData['id_barang'],
                                    'harga' => $barang_pembelian->harga,
                                    'pergerakan_stok' => $barangReturPembelianData['jumlah_retur'] * $satuanBesar,
                                    'stok_keseluruhan' => $totalStok - $barangReturPembelianData['jumlah_retur'] * $satuanBesar,
                                ]);
                            } else {
                                $pergerakanStok->update([
                                    'pergerakan_stok' => $barangReturPembelianData['jumlah_retur'] * $satuanBesar,
                                    'stok_keseluruhan' => $totalStok - $barangReturPembelianData['jumlah_retur'] * $satuanBesar,
                                ]);
                            }
                        }
                        $stokBarang->save();
                    } else {
                        DB::rollBack();
                        // Handle the case where the stock doesn't exist
                        return response()->json([
                            'success' => false,
                            'message' => 'Stok barang tidak ditemukan',
                        ], 400);
                    }
                }
            }

            // Retrieve the total amount of the purchase
            $pembelian = Pembelian::findOrFail($validatedData['id_pembelian']);

            // Sum the current total payments
            $total_dibayar = PembayaranPembelian::where('id_pembelian', $validatedData['id_pembelian'])->sum('total_dibayar');

            // Calculate new total after adding the new payment
            $new_total_dibayar = $total_dibayar + $validatedData['total_retur'];

            $laporanKeuangan = LaporanKeuanganKeluar::where('id_pembelian', $validatedData['id_pembelian'])->firstOrFail();

            $current_pengeluaran = $laporanKeuangan->pengeluaran;
            $current_utang = $laporanKeuangan->utang;

            if ($new_total_dibayar > $pembelian->total) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Total retur melebihi total pembelian',
                ], 400);
            }

            $remaining_retur = $validatedData['total_retur'];

            if ($current_utang > 0) {
                if ($remaining_retur <= $current_utang) {
                    $laporanKeuangan->update([
                        'utang' => $current_utang - $remaining_retur,
                    ]);
                    $remaining_retur = 0;
                } else {
                    $laporanKeuangan->update([
                        'utang' => 0,
                    ]);
                    $remaining_retur -= $current_utang;
                }
            }

            if ($remaining_retur > 0 && $current_pengeluaran > 0) {
                $laporanKeuangan->update([
                    'pengeluaran' => $current_pengeluaran - $remaining_retur,
                ]);
            }

            if ($new_total_dibayar == $pembelian->total) {
                $pembelian->update([
                    'status' => 'Lunas',
                ]);
            } else {
                $pembelian->update([
                    'status' => 'Dibayar Sebagian',
                ]);
            }

            // Create the new payment
            $pembayaranPembelian = PembayaranPembelian::updateOrCreate(
                [
                    'id_pembelian' => $validatedData['id_pembelian'],
                    'id_metode_pembayaran' => 1,
                ],
                [
                    'total_dibayar' => $validatedData['total_retur'],
                    'referensi_pembayaran' => '-',
                    'tanggal_pembayaran' => $validatedData['tanggal'],
                ]
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $returPembelian->load('barangReturPembelian'),
                'message' => 'Data retur pembelian berhasil diupdate',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturPembelian $returPembelian)
    {
        DB::beginTransaction();

        try {
            // Mengembalikan stok barang yang diretur
            foreach ($returPembelian->barangReturPembelian as $barangRetur) {
                $stokBarang = StokBarang::where('id_barang', $barangRetur->id_barang)
                    ->where('batch', $barangRetur->barangPembelian->batch)
                    ->first();

                if ($stokBarang) {
                    $satuanDasar = Barang::where('id', $barangRetur->id_barang)->value('id_satuan');

                    if ($barangRetur->id_satuan == $satuanDasar) {
                        $stokBarang->stok_apotek += $barangRetur->jumlah_retur;
                        $stokBarang->stok_total += $barangRetur->jumlah_retur;
                    } else {
                        $satuanBesar = SatuanBarang::where('id_barang', $barangRetur->id_barang)
                            ->where('id_satuan', $barangRetur->id_satuan)
                            ->value('jumlah');

                        $stokBarang->stok_apotek += $barangRetur->jumlah_retur * $satuanBesar;
                        $stokBarang->stok_total += $barangRetur->jumlah_retur * $satuanBesar;
                    }

                    $stokBarang->save();
                }
            }

            // Update laporan keuangan
            $laporanKeuangan = LaporanKeuanganKeluar::where('id_pembelian', $returPembelian->id_pembelian)->firstOrFail();

            $current_pengeluaran = $laporanKeuangan->pengeluaran;
            $current_utang = $laporanKeuangan->utang;

            $remaining_retur = $returPembelian->total_retur;

            if ($current_utang > 0) {
                if ($remaining_retur <= $current_utang) {
                    $laporanKeuangan->update([
                        'utang' => $current_utang - $remaining_retur,
                    ]);
                    $remaining_retur = 0;
                } else {
                    $laporanKeuangan->update([
                        'utang' => 0,
                    ]);
                    $remaining_retur -= $current_utang;
                }
            }

            if ($remaining_retur > 0 && $current_pengeluaran > 0) {
                $laporanKeuangan->update([
                    'pengeluaran' => $current_pengeluaran - $remaining_retur,
                ]);
            }

            // Kurangi total dibayar pada pembayaran
            $pembayaran = PembayaranPembelian::where('id_pembelian', $returPembelian->id_pembelian)
                ->where('id_metode_pembayaran', 1)
                ->first();

            if ($pembayaran) {
                $pembayaran->total_dibayar -= $returPembelian->total_retur;
                $pembayaran->save();
            }

            // Hapus data retur pembelian
            $returPembelian->barangReturPembelian()->delete();
            $returPembelian->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur pembelian berhasil dibatalkan, stok dikembalikan, dan laporan keuangan diperbarui.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
