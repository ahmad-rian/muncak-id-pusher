<?php

namespace Database\Seeders;

use App\Models\LiveStream;
use App\Models\Gunung;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveStreamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first mountain and user for demo
        $mountain = Gunung::first();
        $user = User::first();

        // Create sample live streams
        $streams = [
            [
                'title' => 'Live from Gunung Semeru Summit',
                'description' => 'Join us as we climb to the peak of Mount Semeru, the highest mountain in Java!',
                'mountain_id' => $mountain?->id,
                'location' => 'Semeru Base Camp, East Java',
                'broadcaster_id' => $user?->id,
                'status' => 'offline',
                'current_quality' => '720p',
            ],
            [
                'title' => 'Sunrise at Rinjani',
                'description' => 'Experience the breathtaking sunrise view from Mount Rinjani crater rim.',
                'mountain_id' => $mountain?->id,
                'location' => 'Rinjani Crater Rim, Lombok',
                'broadcaster_id' => $user?->id,
                'status' => 'offline',
                'current_quality' => '1080p',
            ],
            [
                'title' => 'Climbing Gunung Merapi',
                'description' => 'Live stream from the active volcano - Gunung Merapi expedition.',
                'mountain_id' => $mountain?->id,
                'location' => 'Merapi Monitoring Post, Yogyakarta',
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
