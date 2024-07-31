<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AksesController;
use App\Http\Controllers\JenisController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\ReturPenjualanController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\MetodePembayaranController;
use App\Http\Controllers\VariasiHargaJualController;
use App\Http\Controllers\PembayaranPembelianController;
use App\Http\Controllers\PembayaranPenjualanController;
use App\Http\Controllers\PergerakanStokPembelianController;
use App\Http\Controllers\PergerakanStokPenjualanController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Auth
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('superadmin')->name('register');

    //Akses and Users
    Route::get('akses', [AksesController::class, 'index']);
    Route::post('akses', [AksesController::class, 'store']);
    Route::put('update-akses-users', [AksesController::class, 'update']);
    Route::delete('users/{user}', [AksesController::class, 'destroy'])->middleware('superadmin');
    Route::get('list-users', [AksesController::class, 'getUsers']);
    Route::get('akses-user', [AksesController::class, 'getAksesUser']);


    //Role
    Route::get('roles', [RoleController::class, 'index'])->middleware('superadmin');

    //Kategori
    Route::get('kategori', [KategoriController::class, 'index'])->middleware('hak_akses:6');
    Route::post('kategori', [KategoriController::class, 'store'])->middleware('hak_akses:5');
    Route::get('kategori/{kategori}', [KategoriController::class, 'show']);
    Route::put('kategori/{kategori}', [KategoriController::class, 'update'])->middleware('hak_akses:7');
    Route::delete('kategori/{kategori}', [KategoriController::class, 'destroy'])->middleware('hak_akses:8');


    //Satuan
    Route::get('satuan', [SatuanController::class, 'index']);
    Route::post('satuan', [SatuanController::class, 'store']);
    Route::get('satuan/{satuan}', [SatuanController::class, 'show']);
    Route::put('satuan/{satuan}', [SatuanController::class, 'update']);
    Route::delete('satuan/{satuan}', [SatuanController::class, 'destroy']);

    //Barang
    Route::get('beli-barang', [BarangController::class, 'beliBarang']);
    Route::get('jual-barang', [BarangController::class, 'jualBarang']);

    Route::get('barang', [BarangController::class, 'index']);
    Route::post('barang', [BarangController::class, 'store']);
    Route::get('barang/{barang}', [BarangController::class, 'show']);
    Route::put('barang/{barang}', [BarangController::class, 'update']);
    Route::delete('barang/{barang}', [BarangController::class, 'destroy']);

    Route::post('barang-import', [BarangController::class, 'import']);
    Route::get('detail-kartu-stok/{barang}', [BarangController::class, 'detailKartuStok']);
    Route::get('kartu-stok/{barang}', [BarangController::class, 'kartuStok']);
    Route::get('search-barang', [BarangController::class, 'searchBarang']);
    Route::post('atur-notif', [BarangController::class, 'aturNotif']);
    Route::get('barang-export', [BarangController::class, 'export']);


    //Variasi Harga Jual
    Route::delete('variasi-harga-jual/{variasi_harga_jual}', [VariasiHargaJualController::class, 'destroy']);


    //Pelanggan
    Route::get('nama-pelanggan', [PelangganController::class, 'getPelanggan']);
    Route::get('pelanggan', [PelangganController::class, 'index']);
    Route::post('pelanggan', [PelangganController::class, 'store']);
    Route::get('pelanggan/{pelanggan}', [PelangganController::class, 'show']);
    Route::put('pelanggan/{pelanggan}', [PelangganController::class, 'update']);
    Route::delete('pelanggan/{pelanggan}', [PelangganController::class, 'destroy']);

    //Vendor
    Route::get('nama-vendor', [VendorController::class, 'getVendor']);
    Route::get('vendor', [VendorController::class, 'index']);
    Route::post('vendor', [VendorController::class, 'store']);
    Route::get('vendor/{vendor}', [VendorController::class, 'show']);
    Route::put('vendor/{vendor}', [VendorController::class, 'update']);
    Route::delete('vendor/{vendor}', [VendorController::class, 'destroy']);

    //Sales
    Route::get('sales', [SalesController::class, 'index']);
    Route::delete('sales/{sales}', [SalesController::class, 'destroy']);

    //Metode Pembayaran
    Route::get('metode-pembayaran', [MetodePembayaranController::class, 'index']);
    Route::post('metode-pembayaran', [MetodePembayaranController::class, 'store']);
    Route::get('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'show']);
    Route::put('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'update']);
    Route::delete('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'destroy']);

    //Pembelian
    Route::get('pembelian-id', [PembelianController::class, 'generateId']);

    Route::get('pembelian', [PembelianController::class, 'index']);
    Route::post('pembelian', [PembelianController::class, 'store']);
    Route::get('pembelian/{pembelian}', [PembelianController::class, 'show']);
    Route::put('pembelian/{pembelian}', [PembelianController::class, 'update']);
    Route::delete('pembelian/{pembelian}', [PembelianController::class, 'destroy']);

    Route::get('pembelian-export', [PembelianController::class, 'export']);
    Route::put('set-pembelian/{pembelian}', [PembelianController::class, 'setPembelian']);
    Route::get('retur-barang-pembelian/{pembelian}', [PembelianController::class, 'returPembelian']);

    //Pembayaran Pembelian
    Route::post('pembayaran-pembelian', [PembayaranPembelianController::class, 'store']);

    //Retur Pembelian
    Route::get('retur-pembelian-id', [ReturPembelianController::class, 'generateId']);
    Route::get('retur-pembelian', [ReturPembelianController::class, 'index']);
    Route::get('retur-pembelian/{retur_pembelian}', [ReturPembelianController::class, 'show']);
    Route::put('retur-pembelian/{retur_pembelian}', [ReturPembelianController::class, 'update']);
    Route::delete('retur-pembelian/{retur_pembelian}', [ReturPembelianController::class, 'destroy']);

    //Stok Barang
    Route::get('stok-barang', [StokBarangController::class, 'index']);
    Route::post('stok-barang', [StokBarangController::class, 'store']);
    Route::get('stok-barang/{stok_barang}', [StokBarangController::class, 'show']);
    Route::put('stok-barang/{stok_barang}', [StokBarangController::class, 'update']);
    Route::delete('stok-barang/{stok_barang}', [StokBarangController::class, 'destroy']);

    Route::get('stok-barang-export', [StokBarangController::class, 'export']);
    Route::delete('delete-stokBarang', [StokBarangController::class, 'deleteStokBarang']);

    //Stok Opname
    Route::get('stok-opname', [StokOpnameController::class, 'index']);
    Route::post('stok-opname', [StokOpnameController::class, 'store']);

    Route::get('stok-opname-export', [StokOpnameController::class, 'export']);
    Route::post('stock-opname-import', [StokOpnameController::class, 'import']);

    //Jenis
    Route::get('jenis', [JenisController::class, 'index']);

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
    Route::post('karyawan-import', [KaryawanController::class, 'import']);

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
