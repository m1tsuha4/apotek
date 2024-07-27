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
        Schema::create('laporan_keuangan_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('id_pembelian', 10);
            $table->float('pengeluaran');
            $table->float('utang');
            $table->timestamps();

            $table->foreign('id_pembelian')->references('id')->on('pembelians')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_keuangan_keluars');
    }
};
