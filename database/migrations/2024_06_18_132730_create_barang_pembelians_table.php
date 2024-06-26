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
        Schema::create('barang_pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pembelian')->constrained('pembelians')->onDelete('cascade');
            $table->foreignId('id_barang')->constrained('barangs')->onDelete('cascade');
            $table->integer('jumlah');
            $table->integer('satuan');
            $table->integer('diskon');
            $table->integer('harga');
            $table->integer('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_pembelians');
    }
};
