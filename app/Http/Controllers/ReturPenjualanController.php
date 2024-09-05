<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\StokBarang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Models\ReturPenjualan;
use App\Models\BarangPenjualan;
use Illuminate\Support\Facades\DB;
use App\Models\PembayaranPenjualan;
use App\Models\LaporanKeuanganMasuk;

class ReturPenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $returPenjualans = ReturPenjualan::with(['penjualan.pelanggan', 'barangReturPenjualan', 'penjualan.barangPenjualan'])
        ->orderBy('created_at', 'desc')
        ->paginate($request->num);

        $data = collect($returPenjualans->items())->map(function ($returPenjualan) {
            $jumlah = $returPenjualan->penjualan->barangPenjualan->sum('jumlah');
            $jumlah_retur = $returPenjualan->barangReturPenjualan->sum('jumlah_retur');

            return [
                'id' => $returPenjualan->id,
                'id_penjualan' => $returPenjualan->id_penjualan,
                'tanggal' => $returPenjualan->tanggal,
                'id_pelanggan' => $returPenjualan->penjualan->id_pelanggan,
                'vendor' => $returPenjualan->penjualan->pelanggan->nama_pelanggan,
                'no_telepon' => $returPenjualan->penjualan->pelanggan->no_telepon,
                'referensi' => $returPenjualan->referensi,
                'jumlah' => $jumlah,
                'jumlah_retur' => $jumlah_retur,
                'total' => $returPenjualan->total_retur
            ];
        })->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'last_page' => $returPenjualans->lastPage(),
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function generateId()
    {
        $newId = ReturPenjualan::generateId();
        return response()->json([
            'success' => true,
            'data' => $newId,
            'message' => 'ID retur penjualan berhasil digenerate',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_penjualan' => 'required',
            'tanggal' => 'required',
            'referensi' => 'sometimes',
            'total_retur' => 'required',
            'barang_retur_penjualans' => 'required|array',
            'barang_retur_penjualans.*.id_barang' => 'required',
            'barang_retur_penjualans.*.batch' => 'required',
            'barang_retur_penjualans.*.jumlah_retur' => 'required',
            'barang_retur_penjualans.*.id_satuan' => 'required',
            'barang_retur_penjualans.*.total' => 'required',
        ]);

        DB::beginTransaction();

        try {

            $returPenjualan = ReturPenjualan::create($validatedData);

            foreach ($validatedData['barang_retur_penjualans'] as $barangReturPenjualan) {
                $barang_penjualan = BarangPenjualan::where('id_penjualan', $validatedData['id_penjualan'])->where('id_barang', $barangReturPenjualan['id_barang'])->first();

                $returPenjualan->barangReturPenjualan()->create([
                    'id_retur_penjualan' => $returPenjualan->id,
                    'id_barang_penjualan' => $barang_penjualan->id,
                    'jumlah_retur' => $barangReturPenjualan['jumlah_retur'],
                    'total' => $barangReturPenjualan['total'],
                ]);

                $stokBarang = StokBarang::where('id_barang', $barangReturPenjualan['id_barang'])->where('batch', $barangReturPenjualan['batch'])->first();

                $satuanDasar = Barang::where('id', $barangReturPenjualan['id_barang'])->value('id_satuan');

                $jumlahBarangPenjualan = BarangPenjualan::where('id_penjualan', $validatedData['id_penjualan'])->where('id_barang', $barangReturPenjualan['id_barang'])->sum('jumlah');

                if ($stokBarang) {
                    if ($barangReturPenjualan['id_satuan'] == $satuanDasar) {
                        if ($barangReturPenjualan['jumlah_retur'] > $jumlahBarangPenjualan) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Jumlah retur melebihi jumlah penjualan',
                            ]);
                        }
                        // If the return is in the base unit
                        $stokBarang->stok_apotek += $barangReturPenjualan['jumlah_retur'];
                        $stokBarang->stok_total += $barangReturPenjualan['jumlah_retur'];
                    } else {
                        // If the return is in a larger unit, convert to the base unit
                        $satuanBesar = SatuanBarang::where('id_barang', $barangReturPenjualan['id_barang'])
                            ->where('id_satuan', $barangReturPenjualan['id_satuan'])
                            ->value('jumlah');

                        if ($barangReturPenjualan['jumlah_retur'] * $satuanBesar > $satuanBesar * $jumlahBarangPenjualan) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Jumlah retur melebihi jumlah penjualan',
                            ]);
                        }

                        $stokBarang->stok_apotek += $satuanBesar * $barangReturPenjualan['jumlah_retur'];
                        $stokBarang->stok_total += $satuanBesar * $barangReturPenjualan['jumlah_retur'];
                    }

                    $stokBarang->save();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok barang tidak ditemukan',
                    ], 400);
                }
            }

            // Retrieve the total amount of the purchase
            $penjualan = Penjualan::findOrFail($validatedData['id_penjualan']);

            // Sum the current total payments
            $total_dibayar = PembayaranPenjualan::where('id_penjualan', $validatedData['id_penjualan'])->sum('total_dibayar');

            // Calculate new total after adding the new payment
            $new_total_dibayar = $total_dibayar + $validatedData['total_retur'];

            $laporanKeuangan = LaporanKeuanganMasuk::where('id_penjualan', $validatedData['id_penjualan'])->firstOrFail();

            $current_pemasukkan = $laporanKeuangan->pemasukkan;
            $current_piutang = $laporanKeuangan->piutang;

            // Check if the new total exceeds the purchase total
            // if ($new_total_dibayar > $penjualan->total) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Jumlah pembayaran melebihi total tagihan!',
            //     ], 400);
            // }

            // Handle the return process based on current payment status
            $remaining_retur = $validatedData['total_retur'];

            if ($current_piutang > 0) {
                if ($remaining_retur <= $current_piutang) {
                    // Decrease piutang by the return amount
                    $laporanKeuangan->update([
                        'piutang' => $current_piutang - $remaining_retur,
                    ]);
                    $remaining_retur = 0;
                } else {
                    // Decrease piutang and adjust remaining return amount
                    $laporanKeuangan->update([
                        'piutang' => 0,
                    ]);
                    $remaining_retur -= $current_piutang;
                }
            }

            if ($remaining_retur > 0 && $current_pemasukkan > 0) {
                // Decrease pemasukkan by the remaining return amount
                $laporanKeuangan->update([
                    'pemasukkan' => $current_pemasukkan - $remaining_retur,
                ]);
            }

            // Update the status of the purchase
            if ($new_total_dibayar >= $penjualan->total) {
                $penjualan->update([
                    'status' => 'Lunas',
                ]);
            } else {
                $penjualan->update([
                    'status' => 'Dibayar Sebagian',
                ]);
            }

            // Create the new payment
            $pembayaranPenjualan = PembayaranPenjualan::updateOrCreate(
                [
                    'id_penjualan' => $validatedData['id_penjualan'],
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
                'data' => $returPenjualan,
                'message' => 'Retur Berhasil ditambahkan!',
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ReturPenjualan $returPenjualan)
    {
        $returPenjualan->load('penjualan', 'penjualan.pelanggan', 'barangReturPenjualan', 'penjualan.barangPenjualan', 'penjualan.barangPenjualan.barang', 'penjualan.barangPenjualan.satuan', 'penjualan.barangPenjualan.stokBarang');

        return response()->json([
            'success' => true,
            'data' => $returPenjualan,
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReturPenjualan $returPenjualan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReturPenjualan $returPenjualan)
    {
        $validatedData = $request->validate([
            'id_penjualan' => 'sometimes',
            'tanggal' => 'sometimes',
            'referensi' => 'sometimes',
            'total_retur' => 'sometimes',
            'barang_retur_penjualans' => 'sometimes|array',
            'barang_retur_penjualans.*.id_barang' => 'sometimes',
            'barang_retur_penjualans.*.batch' => 'sometimes',
            'barang_retur_penjualans.*.jumlah_retur' => 'sometimes',
            'barang_retur_penjualans.*.id_satuan' => 'sometimes',
            'barang_retur_penjualans.*.total' => 'sometimes',
        ]);

        DB::beginTransaction();

        try {

            $returPenjualan->update($validatedData);

            foreach ($validatedData['barang_retur_penjualans'] as $index => $barangReturPenjualanData) {
                $barangReturPenjualan = $returPenjualan->barangReturPenjualan()->get()[$index] ?? null;

                if ($barangReturPenjualan) {
                    $stokBarang = StokBarang::where('id_barang', $barangReturPenjualanData['id_barang'])->where('batch', $barangReturPenjualanData['batch'])->first();

                    if ($stokBarang) {
                        $jumlahReturLama = $barangReturPenjualan->jumlah_retur;
                        $jumlahReturBaru = $barangReturPenjualanData['jumlah_retur'];

                        $satuanDasar = Barang::where('id', $barangReturPenjualanData['id_barang'])->value('id_satuan');

                        if ($barangReturPenjualanData['id_satuan'] == $satuanDasar) {
                            // Adjustment for base unit
                            $stokBarang->stok_apotek -= $jumlahReturLama;
                            $stokBarang->stok_apotek += $jumlahReturBaru;
                            $stokBarang->stok_total -= $jumlahReturLama;
                            $stokBarang->stok_total += $jumlahReturBaru;
                        } else {
                            // Adjustment for larger unit
                            $satuanBesar = SatuanBarang::where('id_barang', $barangReturPenjualanData['id_barang'])
                                ->where('id_satuan', $barangReturPenjualanData['id_satuan'])
                                ->value('jumlah');
                            $stokBarang->stok_apotek -= $satuanBesar * $jumlahReturLama;
                            $stokBarang->stok_apotek += $satuanBesar * $jumlahReturBaru;
                            $stokBarang->stok_total -= $satuanBesar * $jumlahReturLama;
                            $stokBarang->stok_total += $satuanBesar * $jumlahReturBaru;
                        }

                        $stokBarang->save();
                    }

                    $barangReturPenjualan->update($barangReturPenjualanData);
                } else {

                    $barang_penjualan = BarangPenjualan::where('id_penjualan', $validatedData['id_penjualan'])->where('id_barang', $barangReturPenjualanData['id_barang'])->first();
                    $barangReturPenjualan = $returPenjualan->barangReturPenjualan()->create([
                        'id_retur_penjualan' => $returPenjualan->id,
                        'id_barang_penjualan' => $barang_penjualan->id,
                        'jumlah_retur' => $barangReturPenjualanData['jumlah_retur'],
                        'id_satuan' => $barangReturPenjualanData['id_satuan'],
                    ]);

                    $stokBarang = StokBarang::where('id_barang', $barangReturPenjualanData['id_barang'])->where('batch', $barangReturPenjualanData['batch'])->first();

                    $satuanDasar = Barang::where('id', $barangReturPenjualanData['id_barang'])->value('id_satuan');

                    if ($stokBarang) {
                        if ($barangReturPenjualanData['id_satuan'] == $satuanDasar) {
                            $stokBarang->stok_apotek -= $barangReturPenjualanData['jumlah_retur'];
                            $stokBarang->stok_total -= $barangReturPenjualanData['jumlah_retur'];
                        } else {
                            $satuanBesar = SatuanBarang::where('id_barang', $barangReturPenjualanData['id_barang'])->where('id_satuan', $barangReturPenjualanData['id_satuan'])->value('jumlah');
                            $stokBarang->stok_apotek -= $satuanBesar * $barangReturPenjualanData['jumlah_retur'];
                            $stokBarang->stok_total -= $satuanBesar * $barangReturPenjualanData['jumlah_retur'];
                        }

                        $stokBarang->save();
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Stok barang tidak ditemukan!',
                        ]);
                    }
                }
            }

            // Retrieve the total amount of the purchase
            $penjualan = Penjualan::findOrFail($validatedData['id_penjualan']);

            // Sum the current total payments
            $total_dibayar = PembayaranPenjualan::where('id_penjualan', $validatedData['id_penjualan'])->sum('total_dibayar');

            // Calculate new total after adding the new payment
            $new_total_dibayar = $total_dibayar + $validatedData['total_retur'];

            $laporanKeuangan = LaporanKeuanganMasuk::where('id_penjualan', $validatedData['id_penjualan'])->firstOrFail();

            $current_pemasukkan = $laporanKeuangan->pemasukkan;
            $current_piutang = $laporanKeuangan->piutang;

            // Check if the new total exceeds the purchase total
            if ($new_total_dibayar > $penjualan->total) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran melebihi total tagihan!',
                ], 400);
            }

            // Handle the return process based on current payment status
            $remaining_retur = $validatedData['total_retur'];

            if ($current_piutang > 0) {
                if ($remaining_retur <= $current_piutang) {
                    // Decrease piutang by the return amount
                    $laporanKeuangan->update([
                        'piutang' => $current_piutang - $remaining_retur,
                    ]);
                    $remaining_retur = 0;
                } else {
                    // Decrease piutang and adjust remaining return amount
                    $laporanKeuangan->update([
                        'piutang' => 0,
                    ]);
                    $remaining_retur -= $current_piutang;
                }
            }

            if ($remaining_retur > 0 && $current_pemasukkan > 0) {
                // Decrease pemasukkan by the remaining return amount
                $laporanKeuangan->update([
                    'pemasukkan' => $current_pemasukkan - $remaining_retur,
                ]);
            }

            // Update the status of the purchase
            if ($new_total_dibayar == $penjualan->total) {
                $penjualan->update([
                    'status' => 'Lunas',
                ]);
            } else {
                $penjualan->update([
                    'status' => 'Dibayar Sebagian',
                ]);
            }

            // Create the new payment
            $pembayaranPenjualan = PembayaranPenjualan::updateOrCreate(
                [
                    'id_penjualan' => $validatedData['id_penjualan'],
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
                'message' => 'Data retur penjualan diperbarui!',
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturPenjualan $returPenjualan)
    {
        DB::beginTransaction();

        try {
            // Kembalikan stok barang yang diretur
            foreach ($returPenjualan->barangReturPenjualan as $barangRetur) {
                $stokBarang = StokBarang::where('id_barang', $barangRetur->barangPenjualan->id_barang)
                    ->where('batch', $barangRetur->barangPenjualan->stokBarang->batch)
                    ->first();
                $satuanDasar = Barang::where('id', $barangRetur->id_barang)->value('id_satuan');

                if ($stokBarang) {
                    if ($barangRetur->id_satuan == $satuanDasar) {
                        // Kembalikan stok sesuai satuan dasar
                        $stokBarang->stok_apotek -= $barangRetur->jumlah_retur;
                        $stokBarang->stok_total -= $barangRetur->jumlah_retur;
                    } else {
                        // Kembalikan stok sesuai satuan besar
                        $satuanBesar = SatuanBarang::where('id_barang', $barangRetur->id_barang)
                            ->where('id_satuan', $barangRetur->id_satuan)
                            ->value('jumlah');
                        $stokBarang->stok_apotek -= $barangRetur->jumlah_retur * $satuanBesar;
                        $stokBarang->stok_total -= $barangRetur->jumlah_retur * $satuanBesar;
                    }

                    $stokBarang->save();
                } else {
                    throw new \Exception('Stok barang tidak ditemukan!');
                }
            }

            // Perbarui laporan keuangan dan pembayaran
            $laporanKeuangan = LaporanKeuanganMasuk::where('id_penjualan', $returPenjualan->id_penjualan)->firstOrFail();
            $current_pemasukkan = $laporanKeuangan->pemasukkan;
            $current_piutang = $laporanKeuangan->piutang;

            $remaining_retur = $returPenjualan->total_retur;

            if ($current_piutang > 0) {
                if ($remaining_retur <= $current_piutang) {
                    $laporanKeuangan->update([
                        'piutang' => $current_piutang - $remaining_retur,
                    ]);
                    $remaining_retur = 0;
                } else {
                    $laporanKeuangan->update([
                        'piutang' => 0,
                    ]);
                    $remaining_retur -= $current_piutang;
                }
            }

            if ($remaining_retur > 0 && $current_pemasukkan > 0) {
                $laporanKeuangan->update([
                    'pemasukkan' => $current_pemasukkan - $remaining_retur,
                ]);
            }

            // Hapus pembayaran terkait retur
            $pembayaran = PembayaranPenjualan::where('id_penjualan', $returPenjualan->id_penjualan)
                ->where('id_metode_pembayaran', 1)
                ->first();
            if ($pembayaran) {
                $pembayaran->delete();
            }

            // Hapus retur penjualan
            $returPenjualan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur penjualan berhasil dibatalkan dan stok serta laporan keuangan diperbarui!',
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
