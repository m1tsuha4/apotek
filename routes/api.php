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
use App\Http\Controllers\DashboardController;
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

Route::get('invoice/{penjualan}', [PenjualanController::class, 'invoice']);

//Auth
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('superadmin')->name('register');

    //Akses and Users
    Route::get('akses', [AksesController::class, 'index']);
    Route::post('akses', [AksesController::class, 'store'])->middleware('superadmin');
    Route::put('update-akses-users', [AksesController::class, 'update'])->middleware('superadmin');
    Route::delete('users/{user}', [AksesController::class, 'destroy'])->middleware('superadmin');
    Route::get('list-users', [AksesController::class, 'getUsers']);
    Route::get('akses-user', [AksesController::class, 'getAksesUser']);


    //Role
    Route::get('roles', [RoleController::class, 'index'])->middleware('superadmin');

    //Kategori
    Route::get('kategori', [KategoriController::class, 'index'])->middleware('hak_akses:6');
    Route::post('kategori', [KategoriController::class, 'store'])->middleware('hak_akses:5');
    Route::get('kategori/{kategori}', [KategoriController::class, 'show'])->middleware('hak_akses:9');
    Route::put('kategori/{kategori}', [KategoriController::class, 'update'])->middleware('hak_akses:7');
    Route::delete('kategori/{kategori}', [KategoriController::class, 'destroy'])->middleware('hak_akses:8');


    //Satuan
    Route::get('satuan', [SatuanController::class, 'index'])->middleware('hak_akses:11');
    Route::post('satuan', [SatuanController::class, 'store'])->middleware('hak_akses:10');
    Route::get('satuan/{satuan}', [SatuanController::class, 'show'])->middleware('hak_akses:14');
    Route::put('satuan/{satuan}', [SatuanController::class, 'update'])->middleware('hak_akses:12');
    Route::delete('satuan/{satuan}', [SatuanController::class, 'destroy'])->middleware('hak_akses:13');

    //Barang
    Route::get('beli-barang', [BarangController::class, 'beliBarang']);
    Route::get('jual-barang', [BarangController::class, 'jualBarang']);

    Route::get('barang', [BarangController::class, 'index'])->middleware('hak_akses:16');
    Route::post('barang', [BarangController::class, 'store'])->middleware('hak_akses:15');
    Route::get('barang/{barang}', [BarangController::class, 'show'])->middleware('hak_akses:19');
    Route::put('barang/{barang}', [BarangController::class, 'update'])->middleware('hak_akses:17');
    Route::delete('barang/{barang}', [BarangController::class, 'destroy'])->middleware('hak_akses:18');

    Route::post('barang-import', [BarangController::class, 'import'])->middleware('hak_akses:21');
    Route::get('detail-kartu-stok/{barang}', [BarangController::class, 'detailKartuStok']);
    Route::get('kartu-stok/{barang}', [BarangController::class, 'kartuStok']);
    Route::get('search-barang', [BarangController::class, 'searchBarang']);
    Route::post('atur-notif', [BarangController::class, 'aturNotif']);
    Route::get('barang-export', [BarangController::class, 'export'])->middleware('hak_akses:20');


    //Variasi Harga Jual
    Route::delete('variasi-harga-jual/{variasi_harga_jual}', [VariasiHargaJualController::class, 'destroy']);


    //Pelanggan
    Route::get('nama-pelanggan', [PelangganController::class, 'getPelanggan']);
    Route::get('pelanggan', [PelangganController::class, 'index'])->middleware('hak_akses:61');
    Route::post('pelanggan', [PelangganController::class, 'store'])->middleware('hak_akses:60');
    Route::get('pelanggan/{pelanggan}', [PelangganController::class, 'show'])->middleware('hak_akses:64');
    Route::put('pelanggan/{pelanggan}', [PelangganController::class, 'update'])->middleware('hak_akses:62');
    Route::delete('pelanggan/{pelanggan}', [PelangganController::class, 'destroy'])->middleware('hak_akses:63');

    //Vendor
    Route::get('nama-vendor', [VendorController::class, 'getVendor']);
    Route::get('vendor', [VendorController::class, 'index'])->middleware('hak_akses:72');
    Route::post('vendor', [VendorController::class, 'store'])->middleware('hak_akses:71');
    Route::get('vendor/{vendor}', [VendorController::class, 'show'])->middleware('hak_akses:75');
    Route::put('vendor/{vendor}', [VendorController::class, 'update'])->middleware('hak_akses:73');
    Route::delete('vendor/{vendor}', [VendorController::class, 'destroy'])->middleware('hak_akses:74');

    //Sales
    Route::get('sales', [SalesController::class, 'index'])->middleware('hak_akses:77');
    Route::delete('sales/{sales}', [SalesController::class, 'destroy'])->middleware('hak_akses:79');

    //Metode Pembayaran
    Route::get('metode-pembayaran', [MetodePembayaranController::class, 'index'])->middleware('hak_akses:56');
    Route::post('metode-pembayaran', [MetodePembayaranController::class, 'store'])->middleware('hak_akses:55');
    Route::get('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'show'])->middleware('hak_akses:59');
    Route::put('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'update'])->middleware('hak_akses:57');
    Route::delete('metode-pembayaran/{metode_pembayaran}', [MetodePembayaranController::class, 'destroy'])->middleware('hak_akses:58');

    //Pembelian
    Route::get('pembelian-id', [PembelianController::class, 'generateId']);

    Route::get('pembelian', [PembelianController::class, 'index'])->middleware('hak_akses:34');
    Route::post('pembelian', [PembelianController::class, 'store'])->middleware('hak_akses:33');
    Route::get('pembelian/{pembelian}', [PembelianController::class, 'show'])->middleware('hak_akses:37');
    Route::put('pembelian/{pembelian}', [PembelianController::class, 'update'])->middleware('hak_akses:35');
    Route::delete('pembelian/{pembelian}', [PembelianController::class, 'destroy'])->middleware('hak_akses:36');

    Route::get('pembelian-export', [PembelianController::class, 'export'])->middleware('hak_akses:38');
    Route::put('set-pembelian/{pembelian}', [PembelianController::class, 'setPembelian']);
    Route::get('retur-barang-pembelian/{pembelian}', [PembelianController::class, 'returPembelian']);

    //Pembayaran Pembelian
    Route::post('pembayaran-pembelian', [PembayaranPembelianController::class, 'store']);

    //Retur Pembelian
    Route::get('retur-pembelian-id', [ReturPembelianController::class, 'generateId']);
    Route::get('retur-pembelian', [ReturPembelianController::class, 'index'])->middleware('hak_akses:40');
    Route::post('retur-pembelian', [ReturPembelianController::class, 'store'])->middleware('hak_akses:39');
    Route::get('retur-pembelian/{retur_pembelian}', [ReturPembelianController::class, 'show'])->middleware('hak_akses:43');
    Route::put('retur-pembelian/{retur_pembelian}', [ReturPembelianController::class, 'update'])->middleware('hak_akses:41');
    Route::delete('retur-pembelian/{retur_pembelian}', [ReturPembelianController::class, 'destroy'])->middleware('hak_akses:42');

    //Stok Barang
    Route::get('stok-barang', [StokBarangController::class, 'index'])->middleware('hak_akses:24');
    Route::post('stok-barang', [StokBarangController::class, 'store'])->middleware('hak_akses:25');
    Route::get('stok-barang/{stok_barang}', [StokBarangController::class, 'show'])->middleware('hak_akses:28');
    Route::put('stok-barang/{stok_barang}', [StokBarangController::class, 'update'])->middleware('hak_akses:26');
    Route::delete('stok-barang/{stok_barang}', [StokBarangController::class, 'destroy'])->middleware('hak_akses:27');

    Route::get('stok-barang-export', [StokBarangController::class, 'export'])->middleware('hak_akses:29');
    Route::delete('delete-stokBarang', [StokBarangController::class, 'deleteStokBarang']);

    //Stok Opname
    Route::get('stok-opname', [StokOpnameController::class, 'index'])->middleware('hak_akses:30');
    Route::post('stok-opname', [StokOpnameController::class, 'store']);

    Route::get('stok-opname-export', [StokOpnameController::class, 'export'])->middleware('hak_akses:31');
    Route::post('stock-opname-import', [StokOpnameController::class, 'import'])->middleware('hak_akses:32');

    //Jenis
    Route::get('jenis', [JenisController::class, 'index']);

    //Dashboard
    Route::get('search-dashboard', [DashboardController::class, 'searchStokBarang']);
    Route::get('dashboard-keuangan', [DashboardController::class, 'keuangan'])->middleware('hak_akses:2');
    Route::get('dashboard-stok-barang', [DashboardController::class, 'stokBarang'])->middleware('hak_akses:1');
    Route::get('dashboard-notif-stok', [DashboardController::class, 'notifStok'])->middleware('hak_akses:3');
    Route::get('dashboard-notif-exp', [DashboardController::class, 'notifExp'])->middleware('hak_akses:4');

    //Change password
    Route::post('change-password', [AuthController::class, 'changePassword']);

    //Karyawan
    Route::get('karyawan', [KaryawanController::class, 'index'])->middleware('hak_akses:66');
    Route::get('karyawan/{karyawan}', [KaryawanController::class, 'show'])->middleware('hak_akses:69');
    Route::post('karyawan', [KaryawanController::class, 'store'])->middleware('hak_akses:65');
    Route::put('karyawan/{karyawan}', [KaryawanController::class, 'update'])->middleware('hak_akses:67');
    Route::delete('karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->middleware('hak_akses:68');
    Route::post('karyawan-import', [KaryawanController::class, 'import'])->middleware('hak_akses:70');

    //Penjualan
    Route::get('penjualan-id', [PenjualanController::class, 'generateId']);
    Route::post('penjualan-stok-detail', [PenjualanController::class, 'getStockDetails']);

    Route::get('penjualan', [PenjualanController::class, 'index'])->middleware('hak_akses:45');
    Route::get('penjualan/{penjualan}', [PenjualanController::class, 'show'])->middleware('hak_akses:48');
    Route::post('penjualan', [PenjualanController::class, 'store'])->middleware('hak_akses:44');
    Route::put('penjualan/{penjualan}', [PenjualanController::class, 'update'])->middleware('hak_akses:46');
    Route::delete('penjualan/{penjualan}', [PenjualanController::class, 'destroy'])->middleware('hak_akses:47');
    
    Route::put('set-penjualan/{penjualan}', [PenjualanController::class, 'setPenjualan']);
    Route::get('retur-barang-penjualan/{penjualan}', [PenjualanController::class, 'returPenjualan']);
    Route::get('penjualan-export', [PenjualanController::class, 'export'])->middleware('hak_akses:49');
   

    //Pembayaran Penjualan
    Route::post('pembayaran-penjualan', [PembayaranPenjualanController::class, 'store']);

    //Retur Penjualan
    Route::get('retur-penjualan-id', [ReturPenjualanController::class, 'generateId']);
    Route::get('retur-penjualan', [ReturPenjualanController::class, 'index'])->middleware('hak_akses:50');
    Route::get('retur-penjualan/{retur_penjualan}', [ReturPenjualanController::class, 'show'])->middleware('hak_akses:54');
    Route::post('retur-penjualan', [ReturPenjualanController::class, 'store'])->middleware('hak_akses:50');
    Route::put('retur-penjualan/{retur_penjualan}', [ReturPenjualanController::class, 'update'])->middleware('hak_akses:52');
    Route::delete('retur-penjualan/{retur_penjualan}', [ReturPenjualanController::class, 'destroy'])->middleware('hak_akses:53');

    //Laporan Keuangan
    Route::get('laporan-keuangan', [LaporanKeuanganController::class, 'index'])->middleware('hak_akses:81');
    Route::get('laporan-keuangan-export', [LaporanKeuanganController::class, 'export'])->middleware('hak_akses:82');

    //Transaksi dan Stok
    Route::get('transaksi-stok-pembelian', [PergerakanStokPembelianController::class, 'index']);
    Route::get('transaksi-stok-penjualan', [PergerakanStokPenjualanController::class, 'index']);
});
