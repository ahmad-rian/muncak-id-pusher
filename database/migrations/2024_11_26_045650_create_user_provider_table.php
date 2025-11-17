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
        Schema::create('user_provider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_email');
            $table->string('provider_name')->nullable();
            $table->string('provider_id');
            $table->text('provider_token');
            $table->string('provider_refresh_token')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_provider');
    }
};
