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
            $table->string('slug')->unique()->nullable()->after('title');
        });

        // Generate slugs for existing streams
        $streams = \App\Models\LiveStream::all();
        foreach ($streams as $stream) {
            $stream->slug = \Illuminate\Support\Str::slug($stream->title) . '-' . \Illuminate\Support\Str::random(6);
            $stream->saveQuietly();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
