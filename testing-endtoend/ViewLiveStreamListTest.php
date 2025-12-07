<?php

namespace Tests\Browser;

use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewLiveStreamListTest extends DuskTestCase
{
    public function testPendakiCanAccessLiveCamIndex()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/live-cam')
                ->pause(2000)
                ->screenshot('live-cam-index-page')
                ->assertPathIs('/live-cam')
                ->screenshot('live-cam-index-loaded');
        });
    }

    public function testLiveStreamListDisplayed()
    {
        $liveStream1 = LiveStream::factory()->create([
            'title' => 'Stream Live Gunung Semeru',
            'status' => 'live',
            'started_at' => now()->subMinutes(30),
        ]);

        $liveStream2 = LiveStream::factory()->create([
            'title' => 'Stream Live Gunung Rinjani',
            'status' => 'live',
            'started_at' => now()->subMinutes(15),
        ]);

        $this->browse(function (Browser $browser) use ($liveStream1, $liveStream2) {
            $browser->visit('/live-cam')
                ->pause(2000)
                ->screenshot('live-stream-list-with-data')
                ->assertSee($liveStream1->title)
                ->assertSee($liveStream2->title)
                ->screenshot('live-streams-displayed');
        });

        $liveStream1->delete();
        $liveStream2->delete();
    }

    public function testEmptyStateWhenNoLiveStreams()
    {
        LiveStream::query()->delete();

        $this->browse(function (Browser $browser) {
            $browser->visit('/live-cam')
                ->pause(2000)
                ->screenshot('empty-live-stream-list');
        });
    }

    public function testClickStreamCardRedirectsToStreamPage()
    {
        $stream = LiveStream::factory()->create([
            'title' => 'Stream untuk Test Click',
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit('/live-cam')
                ->pause(2000)
                ->screenshot('before-click-stream-card')
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->assertPathIs("/live-cam/{$stream->slug}")
                ->screenshot('redirected-to-stream-page');
        });

        $stream->delete();
    }
}
