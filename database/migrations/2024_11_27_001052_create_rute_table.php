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
        Schema::create('rute', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('gunung_id')
                ->constrained('gunung')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('kode_desa');

            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->text('informasi')->nullable();
            $table->text('aturan_dan_larangan')->nullable();
            $table->boolean('is_verified')->default(false);

            $table->geometry('rute', 'linestring')->nullable();

            $table->boolean('is_cuaca_siap')->default(false);
            $table->boolean('is_kalori_siap')->default(false);
            $table->boolean('is_kriteria_jalur_siap')->default(false);

            $table->unsignedSmallInteger('segmentasi')->default(1);

            $table->decimal('a_k', 8, 4)->nullable();
            $table->decimal('b_k', 8, 4)->nullable();
            $table->decimal('c_k', 8, 4)->nullable();
            $table->decimal('d_k', 8, 4)->nullable();

            $table->decimal('a_wt', 8, 4)->nullable();
            $table->decimal('b_wt', 8, 4)->nullable();
            $table->decimal('c_wt', 8, 4)->nullable();
            $table->decimal('d_wt', 8, 4)->nullable();
            $table->decimal('e_wt', 8, 4)->nullable();
            $table->decimal('f_wt', 8, 4)->nullable();

            $table->decimal('a_cps', 8, 4)->nullable();
            $table->decimal('b_cps', 8, 4)->nullable();

            // $table->decimal('a_kr', 8, 4)->nullable();
            // $table->decimal('b_kr', 8, 4)->nullable();
            $table->decimal('c_kr', 8, 4)->nullable();
            $table->decimal('d_kr', 8, 4)->nullable();
            $table->decimal('e_kr', 8, 4)->nullable();
            $table->decimal('f_kr', 8, 4)->nullable();
            $table->decimal('g_kr', 8, 4)->nullable();
            $table->decimal('h_kr', 8, 4)->nullable();

            $table
                ->foreignId('rute_tingkat_kesulitan_id')
                ->nullable()
                ->constrained('rute_tingkat_kesulitan')
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedInteger('comment_count')->default(0);
            $table->decimal('comment_rating', 3, 2)->default(0);

            $table->timestamps();

            $table
                ->foreign('kode_desa')
                ->references('kode')
                ->on('desa')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rute');
    }
};
