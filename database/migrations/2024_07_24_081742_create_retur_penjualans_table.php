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
        Schema::create('retur_penjualans', function (Blueprint $table) {
            $table->string('id',10)->primary();
            $table->string('id_penjualan',10);
            $table->date('tanggal');
            $table->string('referensi')->nullable();
            $table->integer('total_retur');
            $table->timestamps();

            $table->foreign('id_penjualan')->references('id')->on('penjualans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_penjualans');
    }
};
