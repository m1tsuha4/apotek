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
            $table->string('id_pembelian', 10);
            $table->string('id_barang',10);
            $table->string('batch');
            $table->date('exp_date');
            $table->integer('jumlah');
            $table->foreignId('id_satuan')->constrained('satuans')->onDelete('cascade');
            $table->string('jenis_diskon')->nullable();
            $table->integer('diskon')->nullable();
            $table->integer('harga');
            $table->integer('total');
            $table->timestamps();

            $table->foreign('id_pembelian')->references('id')->on('pembelians')->onDelete('cascade');
            $table->foreign('id_barang')->references('id')->on('barangs')->onDelete('cascade');
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
