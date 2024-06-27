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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_vendor')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('id_metode_bayar')->constrained('metode_pembayarans')->onDelete('cascade')->nullable();
            $table->date('tanggal');
            $table->string('status');
            $table->date('tanggal_jatuh_tempo');
            $table->string('referensi')->nullable();
            $table->integer('sub_total');
            $table->integer('diskon')->nullable();
            $table->integer('total');
            $table->string('catatan')->nullable();
            $table->boolean('quotation');
            $table->integer('total_dibayar')->nullable();
            $table->date('tanggal_pembayaran')->nullable();
            $table->string('referensi_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
