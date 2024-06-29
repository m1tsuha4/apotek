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
            $table->string('id_pembelian', 10);
            $table->foreign('id_pembelian')->references('id')->on('pembelians')->onDelete('cascade');
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
