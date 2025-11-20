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
        Schema::create('trail_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
            $table->foreignId('hiking_trail_id')->constrained('rute')->onDelete('cascade');

            // Classification results
            $table->string('weather')->nullable(); // cerah, berawan, hujan
            $table->string('crowd')->nullable(); // sepi, sedang, ramai
            $table->string('visibility')->nullable(); // jelas, kabut_sedang, kabut_tebal

            // Confidence scores (0.0 - 1.0)
            $table->decimal('weather_confidence', 3, 2)->nullable();
            $table->decimal('crowd_confidence', 3, 2)->nullable();
            $table->decimal('visibility_confidence', 3, 2)->nullable();

            // Image & metadata
            $table->string('image_path')->nullable(); // temp storage
            $table->integer('stream_delay_ms')->default(0); // stream delay in milliseconds
            $table->timestamp('classified_at')->nullable();

            // Status tracking
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);

            $table->timestamps();

            // Index untuk query cepat
            $table->index(['live_stream_id', 'classified_at']);
            $table->index('hiking_trail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_classifications');
    }
};
