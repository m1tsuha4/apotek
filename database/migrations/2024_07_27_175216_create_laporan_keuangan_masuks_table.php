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
        Schema::create('laporan_keuangan_masuks', function (Blueprint $table) {
            $table->id();
            $table->string('id_penjualan',10);
            $table->float('pemasukkan');
            $table->float('piutang');
            $table->timestamps();

            
            $table->foreign('id_penjualan')->references('id')->on('penjualans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_keuangan_masuks');
    }
};
