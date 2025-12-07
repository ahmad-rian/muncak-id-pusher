<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\LiveStream;
use App\Models\Rute;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CreateLiveStreamTest extends DuskTestCase
{
    public function testAdminCanCreateNewLiveStreamViaDatabase()
    {
        $admin = User::where('email', 'admin@admin')->first();
        $this->assertNotNull($admin, 'Admin user should exist from seeder');
        $stream = LiveStream::create([
            'title' => 'Live Stream Test Pusher',
            'description' => 'Deskripsi live stream untuk testing dengan Pusher',
            'location' => 'Basecamp Gunung Test',
            'broadcaster_id' => $admin->id,
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('live_streams', [
            'title' => 'Live Stream Test Pusher',
            'description' => 'Deskripsi live stream untuk testing dengan Pusher',
            'location' => 'Basecamp Gunung Test',
            'broadcaster_id' => $admin->id,
            'status' => 'scheduled',
        ]);
        $stream->delete();
    }

    public function testAdminCanViewLiveStreamList()
    {
        $admin = User::where('email', 'admin@admin')->first();
        $streams = LiveStream::factory()->count(3)->create([
            'broadcaster_id' => $admin->id,
        ]);
        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/filament/live-streams')
                ->pause(2000)
                ->screenshot('live-stream-filament-list');
        });
        foreach ($streams as $stream) {
            $stream->delete();
        }
    }

    public function testLiveStreamFactoryWorks()
    {
        $admin = User::where('email', 'admin@admin')->first();
        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
        ]);
        $this->assertNotNull($stream->id);
        $this->assertEquals($admin->id, $stream->broadcaster_id);
        $this->assertNotNull($stream->slug);
        $this->assertNotNull($stream->stream_key);
        $stream->delete();
    }
}


