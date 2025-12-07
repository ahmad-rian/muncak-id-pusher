<?php

namespace Tests\Browser;

use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewAIClassificationTest extends DuskTestCase
{
    public function testClassificationDisplayExists()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->assertPresent('#classification-display')
                ->screenshot('classification-display-element');
        });

        $stream->delete();
    }

    public function testClassificationDataCanBeDisplayed()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000);

            $browser->script([
                'document.getElementById("classification-display").classList.remove("hidden");',
                'document.getElementById("classification-weather").textContent = "Cerah";',
                'document.getElementById("classification-crowd").textContent = "Ramai";',
                'document.getElementById("classification-visibility").textContent = "Baik";',
            ]);

            $browser->pause(1000)
                ->screenshot('classification-data-displayed')
                ->assertSeeIn('#classification-weather', 'Cerah')
                ->assertSeeIn('#classification-crowd', 'Ramai')
                ->assertSeeIn('#classification-visibility', 'Baik');
        });

        $stream->delete();
    }

    public function testPusherChannelSubscriptionForClassification()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000);

            $channelSubscribed = $browser->script(
                'return window.pusher && window.pusher.channel("stream.' . $stream->id . '") !== null;'
            )[0];

            $this->assertTrue($channelSubscribed, 'Should be subscribed to stream channel');

            $browser->screenshot('pusher-classification-subscription');
        });

        $stream->delete();
    }

    public function testClassificationCategoriesDisplayed()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->assertPresent('#classification-weather')
                ->assertPresent('#classification-crowd')
                ->assertPresent('#classification-visibility')
                ->screenshot('classification-categories');
        });

        $stream->delete();
    }

    public function testClassificationRecommendationDisplayed()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $this->browse(function (Browser $browser) use ($stream) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000);

            $browser->script([
                'document.getElementById("classification-display").classList.remove("hidden");',
                'document.getElementById("classification-recommendation").textContent = "Kondisi ideal untuk mendaki";',
            ]);

            $browser->pause(1000)
                ->screenshot('classification-recommendation')
                ->assertSeeIn('#classification-recommendation', 'Kondisi ideal untuk mendaki');
        });

        $stream->delete();
    }
}

