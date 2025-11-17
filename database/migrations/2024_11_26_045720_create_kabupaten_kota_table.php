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
        Schema::create('kabupaten_kota', function (Blueprint $table) {
            $table->string('kode')->primary();
            $table->string('kode_provinsi');
            $table->string('nama');
            $table->string('nama_lain')->nullable();
            $table->string('slug')->unique();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();

            $table
                ->foreign('kode_provinsi')
                ->references('kode')
                ->on('provinsi')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kabupaten_kota');
    }
};
