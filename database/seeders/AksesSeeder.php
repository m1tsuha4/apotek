<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('akses')->insert(
            [
                ["hak_akses" => "Dashboard Stok Barang"],
                ["hak_akses" => "Dashboard Keuangan"],
                ["hak_akses" => "Dashboard Notif Exp Date"],
                ["hak_akses" => "Dashboard Notif Stok Barang"],

                ["hak_akses" => "Create Kategori"],
                ["hak_akses" => "Read Kategori"],
                ["hak_akses" => "Update Kategori"],
                ["hak_akses" => "Delete Kategori"],
                ["hak_akses" => "Detail Kategori"],

                ["hak_akses" => "Create Satuan"],
                ["hak_akses" => "Read Satuan"],
                ["hak_akses" => "Update Satuan"],
                ["hak_akses" => "Delete Satuan"],
                ["hak_akses" => "Detail Satuan"],

                ["hak_akses" => "Create Barang"],
                ["hak_akses" => "Read Barang"],
                ["hak_akses" => "Update Barang"],
                ["hak_akses" => "Delete Barang"],
                ["hak_akses" => "Detail Barang"],
                ["hak_akses" => "Export Barang"],
                ["hak_akses" => "Import Barang"],

                ["hak_akses" => "Get Pergerakan Stok Pembelian"],
                ["hak_akses" => "Get Pergerakan Stok Penjualan"],

                ["hak_akses" => "Read Stok Barang"],
                ["hak_akses" => "Transfer Stok Barang"],
                ["hak_akses" => "Penyesuain Stok Barang"],
                ["hak_akses" => "Delete Stok Barang"],
                ["hak_akses" => "Detail Stok Barang"],
                ["hak_akses" => "Export Stok Barang"],

                ["hak_akses" => "Read Stok Opname"],
                ["hak_akses" => "Export Stok Opname"],
                ["hak_akses" => "Import Stok Opname"],

                ["hak_akses" => "Create Pembelian"],
                ["hak_akses" => "Read Pembelian"],
                ["hak_akses" => "Update Pembelian"],
                ["hak_akses" => "Delete Pembelian"],
                ["hak_akses" => "Detail Pembelian"],
                ["hak_akses" => "Export Pembelian"],

                ["hak_akses" => "Create Retur Pembelian"],
                ["hak_akses" => "Read Retur Pembelian"],
                ["hak_akses" => "Update Retur Pembelian"],
                ["hak_akses" => "Delete Retur Pembelian"],
                ["hak_akses" => "Detail Retur Pembelian"],

                ["hak_akses" => "Create Penjualan"],
                ["hak_akses" => "Read Penjualan"],
                ["hak_akses" => "Update Penjualan"],
                ["hak_akses" => "Delete Penjualan"],
                ["hak_akses" => "Detail Penjualan"],
                ["hak_akses" => "Export Penjualan"],

                ["hak_akses" => "Create Retur Penjualan"],
                ["hak_akses" => "Read Retur Penjualan"],
                ["hak_akses" => "Update Retur Penjualan"],
                ["hak_akses" => "Delete Retur Penjualan"],
                ["hak_akses" => "Detail Retur Penjualan"],

                ["hak_akses" => "Create Metode Pembayaran"],
                ["hak_akses" => "Read Metode Pembayaran"],
                ["hak_akses" => "Update Metode Pembayaran"],
                ["hak_akses" => "Delete Metode Pembayaran"],
                ["hak_akses" => "Detail Metode Pembayaran"],
                
                ["hak_akses" => "Create Pelanggan"],
                ["hak_akses" => "Read Pelanggan"],
                ["hak_akses" => "Update Pelanggan"],
                ["hak_akses" => "Delete Pelanggan"],
                ["hak_akses" => "Detail Pelanggan"],
                
                ["hak_akses" => "Create Karyawan"],
                ["hak_akses" => "Read Karyawan"],
                ["hak_akses" => "Update Karyawan"],
                ["hak_akses" => "Delete Karyawan"],
                ["hak_akses" => "Detail Karyawan"],
                ["hak_akses" => "Import Karyawan"],

                ["hak_akses" => "Create Vendor"],
                ["hak_akses" => "Read Vendor"],
                ["hak_akses" => "Update Vendor"],
                ["hak_akses" => "Delete Vendor"],
                ["hak_akses" => "Detail Vendor"],

                ["hak_akses" => "Create Sales"],
                ["hak_akses" => "Read Sales"],
                ["hak_akses" => "Update Sales"],
                ["hak_akses" => "Delete Sales"],
                ["hak_akses" => "Detail Sales"],

                ["hak_akses" => "Read Laporan Keuangan"],
                ["hak_akses" => "Read List Laporan Keuangan"],
            ]
        );
    }
}
