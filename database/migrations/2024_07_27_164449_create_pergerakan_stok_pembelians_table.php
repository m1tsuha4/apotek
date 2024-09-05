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
        Schema::create('pergerakan_stok_pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('id_pembelian', 10)->nullable();
            $table->string('id_barang',10);
            $table->string('id_retur_pembelian',10)->nullable();
            $table->integer('harga');
            $table->integer('pergerakan_stok');
            $table->integer('stok_keseluruhan')->nullable();
            $table->timestamps();

            $table->foreign('id_pembelian')->references('id')->on('pembelians')->onDelete('cascade');
            $table->foreign('id_barang')->references('id')->on('barangs')->onDelete('cascade');
            $table->foreign('id_retur_pembelian')->references('id')->on('retur_pembelians')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pergerakan_stok_pembelians');
    }
};
