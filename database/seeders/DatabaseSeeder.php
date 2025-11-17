<?php

namespace Database\Seeders;

use App\Models\Gunung;
use App\Models\KabupatenKota;
use App\Models\Point;
use App\Models\PointRuteKabupatenKota;
use App\Models\Rute;
use App\Models\RuteKabupatenKota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // wilayah
        $this->call(WilayahSeeder::class);

        // gunung rute point
        $this->call(GunungSeeder::class);

        // user
        $this->call(UserSeeder::class);

        // media
        // $this->call(MediaSeeder::class);

        // visitor
        for ($i = 0; $i < 50; $i++) {
            \App\Models\Visitor::create([
                'ip_address' => fake()->ipv4,
                'path'       => fake()->url,
                'created_at' => fake()->dateTimeBetween('-3 weeks', 'now'),
                'updated_at' => fake()->dateTimeBetween('-3 weeks', 'now')
            ]);
        }
    }
}
