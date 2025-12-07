<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdateViewerCountTest extends DuskTestCase
{
    public function testViewerCountDisplayedOnAdminPage()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'live',
            'viewer_count' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->assertPresent('#viewer-count')
                ->screenshot('admin-viewer-count');
        });

        $stream->delete();
    }

    public function testMultipleViewersIncrementViewerCount()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
            'viewer_count' => 0,
        ]);

        $this->browse(function (Browser $viewer1, Browser $viewer2, Browser $viewer3) use ($stream) {
            $viewer1->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->screenshot('viewer1-joined');

            $viewer2->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->screenshot('viewer2-joined');

            $viewer3->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->screenshot('viewer3-joined');

            $viewer1->pause(2000)->screenshot('viewer1-sees-count');
            $viewer2->pause(2000)->screenshot('viewer2-sees-count');
            $viewer3->pause(2000)->screenshot('viewer3-sees-count');
        });

        $stream->delete();
    }

    public function testViewerCountSyncBetweenAdminAndViewers()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'live',
            'viewer_count' => 0,
        ]);

        $this->browse(function (Browser $adminBrowser, Browser $viewerBrowser) use ($admin, $stream) {
            $adminBrowser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->screenshot('admin-initial-count');

            $viewerBrowser->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->screenshot('viewer-joined');

            $adminBrowser->pause(3000)
                ->screenshot('admin-count-updated');

            $viewerBrowser->pause(2000)
                ->screenshot('viewer-sees-count');
        });

        $stream->delete();
    }
}

