<?php

namespace Database\Seeders;

use App\Models\LiveStream;
use App\Models\Rute;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveStreamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first hiking trail and user for demo
        $hikingTrail = Rute::first();
        $user = User::first();

        // Create sample live streams
        $streams = [
            [
                'title' => 'Live dari Jalur Pendakian Ranu Kumbolo',
                'description' => 'Saksikan perjalanan kami melalui Ranu Kumbolo menuju puncak Semeru!',
                'hiking_trail_id' => $hikingTrail?->id,
                'location' => 'Ranu Kumbolo, Jawa Timur',
                'broadcaster_id' => $user?->id,
                'status' => 'offline',
                'current_quality' => '720p',
            ],
            [
                'title' => 'Sunrise di Jalur Senaru',
                'description' => 'Nikmati pemandangan matahari terbit dari jalur Senaru, Gunung Rinjani.',
                'hiking_trail_id' => $hikingTrail?->id,
                'location' => 'Pos 3 Senaru, Lombok',
                'broadcaster_id' => $user?->id,
                'status' => 'offline',
                'current_quality' => '1080p',
            ],
            [
                'title' => 'Pendakian Jalur Selo',
                'description' => 'Live streaming pendakian Gunung Merapi via jalur Selo - rute klasik yang menantang.',
                'hiking_trail_id' => $hikingTrail?->id,
                'location' => 'Basecamp Selo, Boyolali',
                'broadcaster_id' => $user?->id,
                'status' => 'offline',
                'current_quality' => '720p',
            ],
        ];

        foreach ($streams as $stream) {
            LiveStream::create($stream);
        }

        $this->command->info('Sample live streams created successfully!');
    }
}
