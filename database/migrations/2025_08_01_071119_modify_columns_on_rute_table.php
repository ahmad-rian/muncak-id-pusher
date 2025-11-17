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
        Schema::table('rute', function (Blueprint $table) {
            $table->dropForeign(['kode_desa']);

            $table
                ->foreignId('negara_id')
                ->after('gunung_id')
                ->nullable()
                ->constrained('negara')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('lokasi')->nullable()->after('kode_desa');

            $table->string('kode_desa')->nullable()->change();

            $table
                ->foreign('kode_desa')
                ->references('kode')
                ->on('desa')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rute', function (Blueprint $table) {
            $table->dropForeign(['negara_id']);
            $table->dropForeign(['kode_desa']);

            $table->dropColumn(['negara_id', 'lokasi']);

            $table->string('kode_desa')->nullable(false)->change();

            $table
                ->foreign('kode_desa')
                ->references('kode')
                ->on('desa')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
