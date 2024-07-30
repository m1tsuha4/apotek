<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\ReturPenjualanController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\VariasiHargaJualController;
use App\Http\Controllers\PembayaranPenjualanController;
use App\Http\Controllers\PergerakanStokPembelianController;
use App\Http\Controllers\PergerakanStokPenjualanController;
use App\Http\Controllers\StokBarangController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('barang-export', [BarangController::class, 'export']);

//Auth
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');




Route::middleware(['auth:sanctum'])->group(function () {
    //Role
    Route::apiResource('roles', \App\Http\Controllers\RoleController::class)->only('index');

    //Kategori
    Route::apiResource('kategori', \App\Http\Controllers\KategoriController::class);

    //Satuan
    Route::apiResource('satuan', \App\Http\Controllers\SatuanController::class);

    //Barang
    Route::get('beli-barang', [BarangController::class, 'beliBarang']);
    Route::get('jual-barang', [BarangController::class, 'jualBarang']);
    Route::apiResource('barang', BarangController::class);
    Route::post('barang-import', [BarangController::class, 'import']);
    Route::get('detail-kartu-stok/{barang}', [BarangController::class, 'detailKartuStok']);
    Route::get('kartu-stok/{barang}', [BarangController::class, 'kartuStok']);
    Route::get('search-barang', [BarangController::class, 'searchBarang']);
    Route::post('atur-notif', [BarangController::class, 'aturNotif']);

    //Variasi Harga Jual
    Route::delete('variasi-harga-jual/{variasi_harga_jual}', [VariasiHargaJualController::class, 'destroy']);


    //Pelanggan
    Route::get('nama-pelanggan', [PelangganController::class, 'getPelanggan']);
    Route::apiResource('pelanggan', PelangganController::class);

    //Vendor
    Route::get('nama-vendor', [VendorController::class, 'getVendor']);
    Route::apiResource('vendor', VendorController::class);

    //Sales
    Route::get('sales', [SalesController::class, 'index']);
    Route::delete('sales/{sales}', [SalesController::class, 'destroy']);

    //Metode Pembayaran
    Route::apiResource('metode-pembayaran', \App\Http\Controllers\MetodePembayaranController::class);

    //Pembelian
    Route::get('pembelian-id', [PembelianController::class, 'generateId']);
    Route::apiResource('pembelian', PembelianController::class);
    Route::get('pembelian-export', [PembelianController::class, 'export']);
    Route::put('set-pembelian/{pembelian}', [PembelianController::class, 'setPembelian']);
    Route::get('retur-barang-pembelian/{pembelian}', [PembelianController::class, 'returPembelian']);

    //Pembayaran Pembelian
    Route::apiResource('pembayaran-pembelian', \App\Http\Controllers\PembayaranPembelianController::class);

    //Retur Pembelian
    Route::get('retur-pembelian-id', [ReturPembelianController::class, 'generateId']);
    Route::apiResource('retur-pembelian', ReturPembelianController::class);

    //Stok Barang
    Route::apiResource('stok-barang', StokBarangController::class);
    Route::get('stok-barang-export', [StokBarangController::class, 'export']);
    Route::delete('delete-stokBarang', [StokBarangController::class, 'deleteStokBarang']);

    //Stok Opname
    Route::apiResource('stok-opname', StokOpnameController::class);
    Route::get('stok-opname-export', [StokOpnameController::class, 'export']);
    Route::post('stock-opname-import', [StokOpnameController::class, 'import']);

    //Jenis
    Route::apiResource('jenis', \App\Http\Controllers\JenisController::class)->only('index');

    //Dashboard
    Route::get('dashboard-keuangan', [\App\Http\Controllers\DashboardController::class, 'keuangan']);
    Route::get('dashboard-stok-barang', [\App\Http\Controllers\DashboardController::class, 'stokBarang']);
    Route::get('dashboard-notif-stok', [\App\Http\Controllers\DashboardController::class, 'notifStok']);
    Route::get('dashboard-notif-exp', [\App\Http\Controllers\DashboardController::class, 'notifExp']);

    //Change password
    Route::post('change-password', [AuthController::class, 'changePassword']);

    //Karyawan
    Route::get('karyawan', [KaryawanController::class, 'index']);
    Route::get('karyawan/{karyawan}', [KaryawanController::class, 'show']);
    Route::post('karyawan', [KaryawanController::class, 'store']);
    Route::put('karyawan/{karyawan}', [KaryawanController::class, 'update']);
    Route::delete('karyawan/{karyawan}', [KaryawanController::class, 'destroy']);

    //Penjualan
    Route::get('penjualan-id', [PenjualanController::class, 'generateId']);
    Route::post('penjualan-stok-detail', [PenjualanController::class, 'getStockDetails']);
    Route::get('penjualan', [PenjualanController::class, 'index']);
    Route::get('penjualan/{penjualan}', [PenjualanController::class, 'show']);
    Route::post('penjualan', [PenjualanController::class, 'store']);
    Route::put('penjualan/{penjualan}', [PenjualanController::class, 'update']);
    Route::delete('penjualan/{penjualan}', [PenjualanController::class, 'destroy']);
    Route::put('set-penjualan/{penjualan}', [PenjualanController::class, 'setPenjualan']);
    Route::get('retur-barang-penjualan/{penjualan}', [PenjualanController::class, 'returPenjualan']);
    Route::get('penjualan-export', [PenjualanController::class, 'export']);

    //Pembayaran Penjualan
    Route::post('pembayaran-penjualan', [PembayaranPenjualanController::class, 'store']);

    //Retur Penjualan
    Route::get('retur-penjualan-id', [ReturPenjualanController::class, 'generateId']);
    Route::get('retur-penjualan', [ReturPenjualanController::class, 'index']);
    Route::get('retur-penjualan/{retur_penjualan}', [ReturPenjualanController::class, 'show']);
    Route::post('retur-penjualan', [ReturPenjualanController::class, 'store']);
    Route::put('retur-penjualan/{retur_penjualan}', [ReturPenjualanController::class, 'update']);
    Route::delete('retur-penjualan/{retur_penjualan}', [ReturPenjualanController::class, 'destroy']);

    //Laporan Keuangan
    Route::get('laporan-keuangan', [LaporanKeuanganController::class, 'index']);
    Route::get('laporan-keuangan-export', [LaporanKeuanganController::class, 'export']);

    //Transaksi dan Stok
    Route::get('transaksi-stok-pembelian', [PergerakanStokPembelianController::class, 'index']);
    Route::get('transaksi-stok-penjualan', [PergerakanStokPenjualanController::class, 'index']);
});
