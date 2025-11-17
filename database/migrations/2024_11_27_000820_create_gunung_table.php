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
        Schema::create('gunung', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kabupaten_kota');
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('long')->nullable();
            $table->string('lat')->nullable();
            $table->unsignedSmallInteger('elev')->nullable();
            $table->geometry('point', 'point')->nullable();
            $table->timestamps();

            $table
                ->foreign('kode_kabupaten_kota')
                ->references('kode')
                ->on('kabupaten_kota')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gunung');
    }
};
