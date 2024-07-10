<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VariasiHargaJualController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
    Route::get('beli-barang', [\App\Http\Controllers\BarangController::class, 'beliBarang']);
    Route::apiResource('barang', \App\Http\Controllers\BarangController::class);
    Route::get('barang-export', [\App\Http\Controllers\BarangController::class, 'export']);
    Route::post('barang-import', [\App\Http\Controllers\BarangController::class, 'import']);
    Route::get('detail-kartu-stok/{barang}', [\App\Http\Controllers\BarangController::class, 'detailKartuStok']);
    Route::get('kartu-stok/{barang}', [\App\Http\Controllers\BarangController::class, 'kartuStok']);

    //Variasi Harga Jual
    Route::delete('variasi-harga-jual/{variasi_harga_jual}', [VariasiHargaJualController::class, 'destroy']);


    //Pelanggan
    Route::apiResource('pelanggan', \App\Http\Controllers\PelangganController::class);

    //Vendor
    Route::apiResource('vendor', VendorController::class);

    //Sales
    Route::get('sales', [SalesController::class, 'index']);
    Route::delete('sales/{sales}', [SalesController::class, 'destroy']);

    //Metode Pembayaran
    Route::apiResource('metode-pembayaran', \App\Http\Controllers\MetodePembayaranController::class);

    //Pembelian
    Route::apiResource('pembelian', \App\Http\Controllers\PembelianController::class);
    Route::get('pembelian-export', [\App\Http\Controllers\PembelianController::class, 'export']);
    Route::get('retur-barang-pembelian/{pembelian}', [\App\Http\Controllers\PembelianController::class, 'returPembelian']);

    //Pembayaran Pembelian
    Route::apiResource('pembayaran-pembelian', \App\Http\Controllers\PembayaranPembelianController::class);

    //Retur Pembelian
    Route::apiResource('retur-pembelian', \App\Http\Controllers\ReturPembelianController::class);

    //Stok Barang
    Route::apiResource('stok-barang', \App\Http\Controllers\StokBarangController::class);
    Route::get('stok-barang-export', [\App\Http\Controllers\StokBarangController::class, 'export']);

    //Stok Opname
    Route::apiResource('stok-opname', \App\Http\Controllers\StokOpnameController::class);
    Route::get('stok-opname-export', [\App\Http\Controllers\StokOpnameController::class, 'export']);


    //Jenis
    Route::apiResource('jenis', \App\Http\Controllers\JenisController::class)->only('index');

    //Dashboard
    Route::get('dashboard-keuangan', [\App\Http\Controllers\DashboardController::class, 'keuangan']);
    Route::get('dashboard-stok-barang', [\App\Http\Controllers\DashboardController::class, 'stokBarang']);
    Route::get('dashboard-notif-stok', [\App\Http\Controllers\DashboardController::class, 'notifStok']);
    Route::get('dashboard-notif-exp', [\App\Http\Controllers\DashboardController::class, 'notifExp']);
});
