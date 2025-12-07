<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StopBroadcastingTest extends DuskTestCase
{
    public function testAdminCanAccessStopBroadcastPage()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'live',
            'started_at' => now()->subMinutes(10),
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->screenshot('broadcast-live-page')
                ->assertSee($stream->title);
        });

        $stream->delete();
    }

    public function testDatabaseUpdatesAfterStop()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $startTime = now()->subMinutes(15);
        $stream = LiveStream::create([
            'title' => 'Test Stop Stream',
            'broadcaster_id' => $admin->id,
            'status' => 'live',
            'started_at' => $startTime,
        ]);

        $stream->update([
            'status' => 'offline',
            'ended_at' => now(),
        ]);

        $stream->refresh();

        $this->assertEquals('offline', $stream->status);
        $this->assertNotNull($stream->ended_at);

        $stream->delete();
    }

    public function testStreamStatusChanges()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'scheduled',
        ]);

        $stream->update(['status' => 'live', 'started_at' => now()]);
        $this->assertEquals('live', $stream->fresh()->status);

        $stream->update(['status' => 'offline', 'ended_at' => now()]);
        $this->assertEquals('offline', $stream->fresh()->status);

        $stream->delete();
    }
}

