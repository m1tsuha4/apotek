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
            $table->string('id', 10)->primary();
            $table->foreignId('id_sales')->constrained('sales')->onDelete('cascade');
            $table->foreignId('id_jenis')->constrained('jenis')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('status');
            $table->date('tanggal_jatuh_tempo');
            $table->string('referensi')->nullable();
            $table->integer('sub_total');
            $table->integer('diskon')->nullable();
            $table->integer('total');
            $table->string('catatan')->nullable();
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
