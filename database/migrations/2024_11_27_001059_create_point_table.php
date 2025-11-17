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
        Schema::create('point', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('rute_id')
                ->constrained('rute')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedInteger('nomor');
            $table->string('nama')->nullable();
            $table->string('deskripsi')->nullable();
            $table->string('long');
            $table->string('lat');
            $table->geometry('point', 'point')->nullable();
            $table->unsignedSmallInteger('elev');
            $table->unsignedSmallInteger('segmentasi')->nullable();

            $table->boolean('is_waypoint')->default(false);
            $table->boolean('is_lokasi_prediksi_cuaca')->default(false);

            $table->float('penambahan_elevasi')->nullable();
            $table->float('jarak_per_dua_titik')->nullable();
            $table->float('jarak_kumulatif')->nullable();
            $table->float('jarak_total')->nullable();
            $table->float('kemiringan_per_dua_titik')->nullable();
            $table->float('kecepatan_per_dua_titik')->nullable();
            $table->float('waktu_tempuh_per_dua_titik')->nullable();
            $table->float('waktu_tempuh_kumulatif')->nullable();
            $table->float('waktu_tempuh_per_dua_titik_s')->nullable();
            $table->float('waktu_tempuh_kumulatif_s')->nullable();
            $table->float('ee_per_dua_titik_w_per_kg')->nullable();
            $table->float('ee_per_dua_titik_kkal_per_kg_per_s')->nullable();
            $table->float('energi_per_dua_titik')->nullable();
            $table->float('energi_kumulatif')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point');
    }
};
