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
            $table->json('result')->nullable()->after('rute_id');

            $table->dropColumn([
                'yesterday',
                'today',
                'next_day',
                'next_two_day',
                'cuaca_0_0',
                'cuaca_0_3',
                'cuaca_0_6',
                'cuaca_0_9',
                'cuaca_0_12',
                'cuaca_0_15',
                'cuaca_0_18',
                'cuaca_0_21',
                'cuaca_1_0',
                'cuaca_1_3',
                'cuaca_1_6',
                'cuaca_1_9',
                'cuaca_1_12',
                'cuaca_1_15',
                'cuaca_1_18',
                'cuaca_1_21',
                'cuaca_2_0',
                'cuaca_2_3',
                'cuaca_2_6',
                'cuaca_2_9',
                'cuaca_2_12',
                'cuaca_2_15',
                'cuaca_2_18',
                'cuaca_2_21',
                'cuaca_min_1_0',
                'cuaca_min_1_3',
                'cuaca_min_1_6',
                'cuaca_min_1_9',
                'cuaca_min_1_12',
                'cuaca_min_1_15',
                'cuaca_min_1_18',
                'cuaca_min_1_21'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rute_prediksi_cuaca', function (Blueprint $table) {
            $table->dropColumn('result');

            $table->json('yesterday')->nullable();
            $table->json('today')->nullable();
            $table->json('next_day')->nullable();
            $table->json('next_two_day')->nullable();

            $table->json('cuaca_0_0')->nullable();
            $table->json('cuaca_0_3')->nullable();
            $table->json('cuaca_0_6')->nullable();
            $table->json('cuaca_0_9')->nullable();
            $table->json('cuaca_0_12')->nullable();
            $table->json('cuaca_0_15')->nullable();
            $table->json('cuaca_0_18')->nullable();
            $table->json('cuaca_0_21')->nullable();

            $table->json('cuaca_1_0')->nullable();
            $table->json('cuaca_1_3')->nullable();
            $table->json('cuaca_1_6')->nullable();
            $table->json('cuaca_1_9')->nullable();
            $table->json('cuaca_1_12')->nullable();
            $table->json('cuaca_1_15')->nullable();
            $table->json('cuaca_1_18')->nullable();
            $table->json('cuaca_1_21')->nullable();

            $table->json('cuaca_2_0')->nullable();
            $table->json('cuaca_2_3')->nullable();
            $table->json('cuaca_2_6')->nullable();
            $table->json('cuaca_2_9')->nullable();
            $table->json('cuaca_2_12')->nullable();
            $table->json('cuaca_2_15')->nullable();
            $table->json('cuaca_2_18')->nullable();
            $table->json('cuaca_2_21')->nullable();

            $table->json('cuaca_min_1_0')->nullable();
            $table->json('cuaca_min_1_3')->nullable();
            $table->json('cuaca_min_1_6')->nullable();
            $table->json('cuaca_min_1_9')->nullable();
            $table->json('cuaca_min_1_12')->nullable();
            $table->json('cuaca_min_1_15')->nullable();
            $table->json('cuaca_min_1_18')->nullable();
            $table->json('cuaca_min_1_21')->nullable();
        });
    }
};
