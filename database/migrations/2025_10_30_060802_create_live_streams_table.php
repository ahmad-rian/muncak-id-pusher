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
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('mountain_id')->nullable()->constrained('gunung')->onDelete('set null');
            $table->string('location')->nullable();
            $table->foreignId('broadcaster_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['live', 'offline', 'scheduled'])->default('offline');
            $table->enum('current_quality', ['360p', '720p', '1080p'])->default('720p');
            $table->integer('viewer_count')->default(0);
            $table->integer('total_views')->default(0);
            $table->string('stream_key')->unique();
            $table->string('pusher_channel_id')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_streams');
    }
};
