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
        Schema::create('barang_retur_pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('id_retur_pembelian',10);
            $table->foreign('id_retur_pembelian')->references('id')->on('retur_pembelians')->onDelete('cascade');
            $table->integer('jumlah_retur');
            $table->integer('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_retur_pembelians');
    }
};
