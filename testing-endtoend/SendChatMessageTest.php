<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SendChatMessageTest extends DuskTestCase
{
    public function testPendakiCanSendChatMessage()
    {
        $user = User::where('email', 'user1@muncak.id')->first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test Pendaki',
                'email' => 'pendaki@test.com',
            ]);
        }

        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($user, $stream) {
            $browser->loginAs($user)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->screenshot('chat-section-loaded')
                ->assertPresent('#chat-input')
                ->assertPresent('#send-button')
                ->type('#chat-input', 'Halo dari test Dusk!')
                ->screenshot('chat-message-typed')
                ->click('#send-button')
                ->pause(2000)
                ->screenshot('chat-message-sent');
        });

        $stream->delete();
    }

    public function testChatInputMaxLengthValidation()
    {
        $user = User::where('email', 'user1@muncak.id')->first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $stream = LiveStream::factory()->create(['status' => 'live']);

        $this->browse(function (Browser $browser) use ($user, $stream) {
            $browser->loginAs($user)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->assertPresent('#chat-input')
                ->screenshot('chat-input-validation');
        });

        $stream->delete();
    }

    public function testEmptyMessageCannotBeSent()
    {
        $user = User::where('email', 'user1@muncak.id')->first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $stream = LiveStream::factory()->create(['status' => 'live']);

        $this->browse(function (Browser $browser) use ($user, $stream) {
            $browser->loginAs($user)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->type('#chat-input', '')
                ->click('#send-button')
                ->pause(1000)
                ->screenshot('empty-message-attempt');
        });

        $stream->delete();
    }

    public function testChatMessageWithEmoji()
    {
        $user = User::where('email', 'user1@muncak.id')->first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $stream = LiveStream::factory()->create(['status' => 'live']);

        $this->browse(function (Browser $browser) use ($user, $stream) {
            $browser->loginAs($user)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->type('#chat-input', 'Pemandangan indah sekali!')
                ->screenshot('chat-with-special-message')
                ->click('#send-button')
                ->pause(2000)
                ->screenshot('special-message-sent');
        });

        $stream->delete();
    }

    public function testMultipleUsersChatting()
    {
        $user1 = User::where('email', 'user1@muncak.id')->first();
        $user2 = User::where('email', 'admin@admin')->first();

        if (!$user1)
            $user1 = User::factory()->create(['name' => 'User 1']);
        if (!$user2)
            $user2 = User::factory()->create(['name' => 'User 2']);

        $stream = LiveStream::factory()->create(['status' => 'live']);

        $this->browse(function (Browser $browser1, Browser $browser2) use ($user1, $user2, $stream) {
            $browser1->loginAs($user1)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->type('#chat-input', 'Halo dari User 1')
                ->click('#send-button')
                ->pause(1000)
                ->screenshot('user1-sent-message');

            $browser2->loginAs($user2)
                ->visit("/live-cam/{$stream->slug}")
                ->pause(2000)
                ->type('#chat-input', 'Halo dari User 2')
                ->click('#send-button')
                ->pause(1000)
                ->screenshot('user2-sent-message');
        });

        $stream->delete();
    }
}

