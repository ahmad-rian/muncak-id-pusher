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
        // Add indexes to live_streams table
        Schema::table('live_streams', function (Blueprint $table) {
            $table->index('status', 'idx_live_streams_status');
            $table->index('viewer_count', 'idx_live_streams_viewer_count');
            $table->index(['status', 'viewer_count'], 'idx_live_streams_status_viewers');
            $table->index('created_at', 'idx_live_streams_created_at');
            $table->index('hiking_trail_id', 'idx_live_streams_trail_id');
        });

        // Add indexes to chat_messages table
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index('live_stream_id', 'idx_chat_messages_stream_id');
            $table->index('created_at', 'idx_chat_messages_created_at');
            $table->index(['live_stream_id', 'created_at'], 'idx_chat_messages_stream_created');
        });

        // Add indexes to trail_classifications table
        Schema::table('trail_classifications', function (Blueprint $table) {
            $table->index('status', 'idx_trail_classifications_status');
            $table->index('hiking_trail_id', 'idx_trail_classifications_trail_id');
            $table->index('classified_at', 'idx_trail_classifications_classified_at');
            $table->index(['status', 'hiking_trail_id'], 'idx_trail_classifications_status_trail');
            $table->index(['status', 'classified_at'], 'idx_trail_classifications_status_date');
        });

        // Add indexes to stream_analytics table
        Schema::table('stream_analytics', function (Blueprint $table) {
            $table->index('live_stream_id', 'idx_stream_analytics_stream_id');
            $table->index('timestamp', 'idx_stream_analytics_timestamp');
            $table->index(['live_stream_id', 'timestamp'], 'idx_stream_analytics_stream_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from live_streams table
        Schema::table('live_streams', function (Blueprint $table) {
            $table->dropIndex('idx_live_streams_status');
            $table->dropIndex('idx_live_streams_viewer_count');
            $table->dropIndex('idx_live_streams_status_viewers');
            $table->dropIndex('idx_live_streams_created_at');
            $table->dropIndex('idx_live_streams_trail_id');
        });

        // Remove indexes from chat_messages table
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_chat_messages_stream_id');
            $table->dropIndex('idx_chat_messages_created_at');
            $table->dropIndex('idx_chat_messages_stream_created');
        });

        // Remove indexes from trail_classifications table
        Schema::table('trail_classifications', function (Blueprint $table) {
            $table->dropIndex('idx_trail_classifications_status');
            $table->dropIndex('idx_trail_classifications_trail_id');
            $table->dropIndex('idx_trail_classifications_classified_at');
            $table->dropIndex('idx_trail_classifications_status_trail');
            $table->dropIndex('idx_trail_classifications_status_date');
        });

        // Remove indexes from stream_analytics table
        Schema::table('stream_analytics', function (Blueprint $table) {
            $table->dropIndex('idx_stream_analytics_stream_id');
            $table->dropIndex('idx_stream_analytics_timestamp');
            $table->dropIndex('idx_stream_analytics_stream_time');
        });
    }
};
