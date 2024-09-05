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
        Schema::create('barang_retur_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('id_retur_penjualan', 10);
            $table->foreignId('id_barang_penjualan')->constrained('barang_penjualans')->onDelete('cascade');
            $table->integer('jumlah_retur');
            $table->integer('total');
            $table->timestamps();

            $table->foreign('id_retur_penjualan')->references('id')->on('retur_penjualans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_retur_penjualans');
    }
};
