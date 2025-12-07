<?php

namespace Database\Factories;

use App\Models\LiveStream;
use App\Models\User;
use App\Models\Rute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LiveStream>
 */
class LiveStreamFactory extends Factory
{
    protected $model = LiveStream::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'location' => fake()->city(),
            'broadcaster_id' => User::factory(),
            'hiking_trail_id' => null,
            'status' => 'scheduled',
            'current_quality' => '720p',
            'viewer_count' => 0,
            'total_views' => 0,
            'thumbnail_url' => null,
            'started_at' => null,
            'ended_at' => null,
        ];
    }

    /**
     * Indicate that the stream is live.
     */
    public function live(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'live',
            'started_at' => now()->subMinutes(rand(5, 60)),
        ]);
    }

    /**
     * Indicate that the stream has ended.
     */
    public function ended(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'ended',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHours(1),
        ]);
    }

    /**
     * Indicate that the stream is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'scheduled',
            'started_at' => null,
            'ended_at' => null,
        ]);
    }
}
