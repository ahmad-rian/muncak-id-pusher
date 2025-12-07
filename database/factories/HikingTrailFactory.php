<?php

namespace Database\Factories;

use App\Models\Rute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rute>
 */
class HikingTrailFactory extends Factory
{
    protected $model = Rute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake()->words(3, true);

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama),
            'deskripsi' => fake()->paragraph(),
            'lokasi' => fake()->city(),
            'is_verified' => true,
            'is_cuaca_siap' => false,
            'is_kalori_siap' => false,
            'is_kriteria_jalur_siap' => false,
            'comment_count' => 0,
            'comment_rating' => 0,
        ];
    }
}
