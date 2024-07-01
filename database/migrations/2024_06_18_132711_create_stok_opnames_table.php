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
        Schema::create('stok_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_stok_barang')->constrained('stok_barangs')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('sumber_stok');
            $table->integer('stok_tercatat');
            $table->integer('stok_aktual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_opnames');
    }
};
