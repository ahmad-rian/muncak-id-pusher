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
        Schema::table('gunung', function (Blueprint $table) {
            $table->dropForeign(['kode_kabupaten_kota']);

            $table
                ->foreignId('negara_id')
                ->after('id')
                ->nullable()
                ->constrained('negara')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('lokasi')->nullable()->after('kode_kabupaten_kota');

            $table->string('kode_kabupaten_kota')->nullable()->change();

            $table
                ->foreign('kode_kabupaten_kota')
                ->references('kode')
                ->on('kabupaten_kota')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gunung', function (Blueprint $table) {
            $table->dropForeign(['negara_id']);
            $table->dropForeign(['kode_kabupaten_kota']);

            $table->dropColumn(['negara_id', 'lokasi']);

            $table->string('kode_kabupaten_kota')->nullable(false)->change();

            $table
                ->foreign('kode_kabupaten_kota')
                ->references('kode')
                ->on('kabupaten_kota')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
