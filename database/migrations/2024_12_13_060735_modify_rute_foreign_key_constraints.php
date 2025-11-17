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
            $table->dropForeign(['rute_tingkat_kesulitan_id']);
            $table->dropForeign(['kode_desa']);

            $table->foreign('rute_tingkat_kesulitan_id')
                ->references('id')
                ->on('rute_tingkat_kesulitan')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('kode_desa')
                ->references('kode')
                ->on('desa')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rute', function (Blueprint $table) {
            $table->dropForeign(['rute_tingkat_kesulitan_id']);
            $table->dropForeign(['kode_desa']);

            $table->foreign('rute_tingkat_kesulitan_id')
                ->references('id')
                ->on('rute_tingkat_kesulitan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('kode_desa')
                ->references('kode')
                ->on('desa')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
