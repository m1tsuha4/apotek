<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pergerakan_stok_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('id_penjualan',10)->nullable();
            $table->string('id_barang',10);
            $table->string('id_retur_penjualan', 10)->nullable();
            $table->foreignId('id_stok_barang')->nullable()->constrained('stok_barangs')->onDelete('cascade');
            $table->integer('harga');
            $table->integer('pergerakan_stok');
            $table->integer('stok_keseluruhan')->nullable();
            $table->timestamps();

            $table->foreign('id_penjualan')->references('id')->on('penjualans')->onDelete('cascade');
            $table->foreign('id_barang')->references('id')->on('barangs')->onDelete('cascade');
            $table->foreign('id_retur_penjualan')->references('id')->on('retur_penjualans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pergerakan_stok_penjualans');
    }
};
