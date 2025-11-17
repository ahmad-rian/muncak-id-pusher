<?php

namespace App\Console\Commands;

use App\Models\Gunung;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Console\Command;

class SetupLiveCamDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'livecam:setup-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup demo live streams for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Live Cam demo data...');

        // Check if user exists
        $user = User::first();
        if (!$user) {
            $this->error('No users found! Please create a user first.');
            return 1;
        }

        // Check if mountain exists
        $mountain = Gunung::first();
        if (!$mountain) {
            $this->warn('No mountains found! Streams will be created without mountain reference.');
        }

        // Create demo streams
        $streams = [
            [
                'title' => 'Live from Gunung Semeru Summit',
                'description' => 'Join us as we climb to the peak of Mount Semeru, the highest mountain in Java! Experience breathtaking views and challenging terrain.',
                'mountain_id' => $mountain?->id,
                'location' => 'Semeru Base Camp, East Java',
                'broadcaster_id' => $user->id,
                'status' => 'offline',
                'current_quality' => '720p',
            ],
            [
                'title' => 'Sunrise at Rinjani Crater',
                'description' => 'Watch the magical sunrise from Mount Rinjani crater rim. One of the most beautiful views in Indonesia!',
                'mountain_id' => $mountain?->id,
                'location' => 'Rinjani Crater Rim, Lombok',
                'broadcaster_id' => $user->id,
                'status' => 'offline',
                'current_quality' => '1080p',
            ],
            [
                'title' => 'Climbing Gunung Merapi',
                'description' => 'Live stream from the active volcano - Gunung Merapi expedition. Safety first!',
                'mountain_id' => $mountain?->id,
                'location' => 'Merapi Monitoring Post, Yogyakarta',
                'broadcaster_id' => $user->id,
                'status' => 'offline',
                'current_quality' => '720p',
            ],
        ];

        $bar = $this->output->createProgressBar(count($streams));
        $bar->start();

        foreach ($streams as $streamData) {
            LiveStream::create($streamData);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Demo setup complete!');
        $this->newLine();
        $this->table(
            ['ID', 'Title', 'Mountain', 'Broadcaster'],
            LiveStream::with(['mountain', 'broadcaster'])
                ->latest()
                ->take(3)
                ->get()
                ->map(fn($stream) => [
                    $stream->id,
                    $stream->title,
                    $stream->mountain?->nama ?? 'N/A',
                    $stream->broadcaster?->name ?? 'N/A',
                ])
        );

        $this->newLine();
        $this->info('Access your streams at:');
        $this->line('  • List: http://localhost/live-cam');
        foreach (LiveStream::latest()->take(3)->get() as $stream) {
            $this->line("  • Stream #{$stream->id}: http://localhost/live-cam/{$stream->id}");
            $this->line("  • Broadcast #{$stream->id}: http://localhost/live-cam/{$stream->id}/broadcast");
        }

        return 0;
    }
}
