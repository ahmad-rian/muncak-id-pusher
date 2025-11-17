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
        Schema::table('rute_prediksi_cuaca', function (Blueprint $table) {
            $table->json('yesterday')->nullable()->after('rute_id');
            $table->json('today')->nullable()->after('yesterday');
            $table->json('next_day')->nullable()->after('today');
            $table->json('next_two_day')->nullable()->after('next_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rute_prediksi_cuaca', function (Blueprint $table) {
            $table->dropColumn('yesterday');
            $table->dropColumn('today');
            $table->dropColumn('next_day');
            $table->dropColumn('next_two_day');
        });
    }
};
