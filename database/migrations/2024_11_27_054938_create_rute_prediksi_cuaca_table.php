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
        Schema::create('rute_prediksi_cuaca', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('rute_id')
                ->constrained('rute')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

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

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rute_prediksi_cuaca');
    }
};
