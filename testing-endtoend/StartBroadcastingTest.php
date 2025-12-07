<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StartBroadcastingTest extends DuskTestCase
{
    public function testAdminCanAccessBroadcastPage()
    {
        $admin = User::where('email', 'admin@admin')->first();
        $this->assertNotNull($admin, 'Admin should exist from seeder');

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'title' => 'Test Stream Pusher',
            'status' => 'scheduled',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->screenshot('broadcast-page-loaded')
                ->assertSee($stream->title)
                ->screenshot('broadcast-page-ready');
        });

        $stream->delete();
    }

    public function testVideoPreviewElementExists()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'scheduled',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->assertPresent('#camera-preview')
                ->screenshot('video-preview-element');
        });

        $stream->delete();
    }

    public function testPusherLibraryLoaded()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'scheduled',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000);

            $pusherLoaded = $browser->script('return typeof Pusher !== "undefined";')[0];
            $this->assertTrue($pusherLoaded, 'Pusher library should be loaded');

            $browser->screenshot('pusher-loaded-check');
        });

        $stream->delete();
    }

    public function testViewerCountInitialization()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'scheduled',
            'viewer_count' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->assertPresent('#viewer-count')
                ->screenshot('viewer-count-initial');
        });

        $stream->delete();
    }
}

