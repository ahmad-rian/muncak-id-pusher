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
            $table->decimal('g_wt', 8, 4)->default('0.01')->nullable();
            $table->decimal('h_wt', 8, 4)->default('1.5')->nullable();
            $table->decimal('i_wt', 8, 4)->default('5')->nullable();
            $table->decimal('j_wt', 8, 4)->default('0.25')->nullable();
            $table->decimal('k_wt', 8, 4)->default('0.07')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rute', function (Blueprint $table) {
            $table->dropColumn('g_wt');
            $table->dropColumn('h_wt');
            $table->dropColumn('i_wt');
            $table->dropColumn('j_wt');
            $table->dropColumn('k_wt');
        });
    }
};
