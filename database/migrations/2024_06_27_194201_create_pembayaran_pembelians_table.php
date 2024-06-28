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
        Schema::create('pembayaran_pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pembelian')->constrained('pembelians')->onDelete('cascade');
            $table->foreignId('id_metode_pembayaran')->constrained('metode_pembayarans')->onDelete('cascade');
            $table->integer('total_dibayar');
            $table->date('tanggal_pembayaran');
            $table->string('referensi_pembayaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pembelians');
    }
};
