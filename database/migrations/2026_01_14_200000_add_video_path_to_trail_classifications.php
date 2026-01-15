<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trail_classifications', function (Blueprint $table) {
            // Video path for 5-second clips
            $table->string('video_path')->nullable()->after('image_path');
            // Video duration in seconds
            $table->integer('video_duration')->nullable()->after('video_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trail_classifications', function (Blueprint $table) {
            $table->dropColumn(['video_path', 'video_duration']);
        });
    }
};
