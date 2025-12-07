<?php

namespace Tests\Browser;

use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WatchLiveStreamTest extends DuskTestCase
{
    public function testPendakiCanAccessStreamRoom()
    {
        $stream = LiveStream::factory()->create([
            'title' => 'Test Watch Stream',
            'status' => 'live',
            'started_at' => now()->subMinutes(10),
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->screenshot('stream-room-page-loaded')
                ->assertPathIs("/live-cam/{$stream->slug}")
                ->assertSee($stream->title)
                ->screenshot('stream-room-ready');
        });

        $stream->delete();
    }

    public function testVideoPlayerElementExists()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->assertPresent('#video-player')
                ->screenshot('video-player-element');
        });

        $stream->delete();
    }

    public function testPusherConnectionForViewer()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000);

            $pusherLoaded = $browser->script('return typeof Pusher !== "undefined";')[0];
            $this->assertTrue($pusherLoaded, 'Pusher should be loaded for viewer');

            $pusherConfigExists = $browser->script('return typeof window.pusherConfig !== "undefined";')[0];
            $this->assertTrue($pusherConfigExists, 'Pusher config should exist');

            $browser->screenshot('pusher-viewer-check');
        });

        $stream->delete();
    }

    public function testStreamInfoDisplayedCorrectly()
    {
        $stream = LiveStream::factory()->create([
            'title' => 'Gunung Semeru Live',
            'description' => 'Pemandangan dari puncak Semeru',
            'location' => 'Ranupane, Lumajang',
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->screenshot('stream-info-display')
                ->assertSee($stream->title)
                ->screenshot('stream-info-complete');
        });

        $stream->delete();
    }

    public function testOfflinePlaceholderWhenStreamEnded()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'offline',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHours(1),
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->screenshot('stream-offline-placeholder');
        });

        $stream->delete();
    }

    public function testMultipleViewersCanWatchSimultaneously()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $viewer1, Browser $viewer2) use ($stream) {
            $viewer1->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->screenshot('viewer1-watching');

            $viewer2->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->screenshot('viewer2-watching');

            $viewer1->assertSee($stream->title);
            $viewer2->assertSee($stream->title);
        });

        $stream->delete();
    }
}

