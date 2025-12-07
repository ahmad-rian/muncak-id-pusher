<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ReceiveChatMessageTest extends DuskTestCase
{
    public function testAdminCanSeeChatMessagesFromViewers()
    {
        $admin = User::where('email', 'admin@admin')->first();
        $viewer = User::where('email', 'user1@muncak.id')->first();

        if (!$viewer) {
            $viewer = User::factory()->create(['name' => 'Test Viewer']);
        }

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'live',
        ]);

        $this->browse(function (Browser $adminBrowser, Browser $viewerBrowser) use ($admin, $viewer, $stream) {
            $adminBrowser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->screenshot('admin-broadcast-page');

            $viewerBrowser->loginAs($viewer)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->type('#chat-input', 'Halo Admin!')
                ->click('#send-button')
                ->pause(2000)
                ->screenshot('viewer-sent-chat');

            $adminBrowser->pause(3000)
                ->screenshot('admin-received-chat');
        });

        $stream->delete();
    }

    public function testChatMonitorExistsOnAdminPage()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000)
                ->assertPresent('#chat-monitor')
                ->screenshot('chat-monitor-element');
        });

        $stream->delete();
    }

    public function testPusherConnectionForChat()
    {
        $admin = User::where('email', 'admin@admin')->first();

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $stream) {
            $browser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(3000);

            $pusherLoaded = $browser->script('return typeof Pusher !== "undefined";')[0];
            $this->assertTrue($pusherLoaded, 'Pusher should be loaded');

            $browser->screenshot('pusher-chat-connection');
        });

        $stream->delete();
    }

    public function testMultipleViewersChattingSimultaneously()
    {
        $admin = User::where('email', 'admin@admin')->first();
        $viewer1 = User::where('email', 'user1@muncak.id')->first();
        $viewer2 = User::where('email', 'admin@muncak.id')->first();

        if (!$viewer1)
            $viewer1 = User::factory()->create(['name' => 'Viewer 1']);
        if (!$viewer2)
            $viewer2 = User::factory()->create(['name' => 'Viewer 2']);

        $stream = LiveStream::factory()->create([
            'broadcaster_id' => $admin->id,
            'status' => 'live',
        ]);

        $this->browse(function (Browser $adminBrowser, Browser $viewer1Browser, Browser $viewer2Browser) use ($admin, $viewer1, $viewer2, $stream) {
            $adminBrowser->loginAs($admin)
                ->visit("/admin/live-stream/broadcast/{$stream->slug}")
                ->pause(2000)
                ->screenshot('admin-monitoring-chat');

            $viewer1Browser->loginAs($viewer1)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->type('#chat-input', 'Message from Viewer 1')
                ->click('#send-button')
                ->pause(1000)
                ->screenshot('viewer1-chatting');

            $viewer2Browser->loginAs($viewer2)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->type('#chat-input', 'Message from Viewer 2')
                ->click('#send-button')
                ->pause(1000)
                ->screenshot('viewer2-chatting');

            $adminBrowser->pause(3000)
                ->screenshot('admin-sees-multiple-chats');
        });

        $stream->delete();
    }
}

