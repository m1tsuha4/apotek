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
        Schema::create('barangs', function (Blueprint $table) {
            $table->string('id', 10)->primary();
            $table->unsignedBigInteger('id_kategori');
            $table->unsignedBigInteger('id_satuan');
            $table->string('nama_barang');
            $table->integer('min_stok_total')->nullable();
            $table->integer('notif_exp')->nullable();
            $table->integer('harga_beli'); 
            $table->integer('harga_jual');
            $table->timestamps();

            $table->foreign('id_kategori')->references('id')->on('kategoris')->onDelete('cascade');
            $table->foreign('id_satuan')->references('id')->on('satuans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
