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
        Schema::table('live_streams', function (Blueprint $table) {
            // Drop old mountain_id foreign key and column
            $table->dropForeign(['mountain_id']);
            $table->dropColumn('mountain_id');

            // Add new hiking_trail_id (rute_id)
            $table->foreignId('hiking_trail_id')->nullable()->after('broadcaster_id')->constrained('rute')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            // Drop hiking_trail_id
            $table->dropForeign(['hiking_trail_id']);
            $table->dropColumn('hiking_trail_id');

            // Restore mountain_id
            $table->foreignId('mountain_id')->nullable()->after('broadcaster_id')->constrained('gunung')->onDelete('set null');
        });
    }
};
