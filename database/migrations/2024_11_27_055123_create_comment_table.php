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
        Schema::create('comment', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('rute_id')
                ->constrained('rute')
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table
                ->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table
                ->foreignId('point_id')
                ->nullable()
                ->constrained('point')
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->text('content');
            $table->boolean('is_approved')->default(true);
            $table->unsignedTinyInteger('rating');
            $table->json('kondisi_rute')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment');
    }
};
