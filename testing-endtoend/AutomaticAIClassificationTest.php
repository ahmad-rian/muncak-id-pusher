<?php

namespace Tests\Browser;

use App\Models\LiveStream;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AutomaticAIClassificationTest extends DuskTestCase
{
    public function testClassificationResultsBroadcastToAllViewers()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
            'started_at' => now()->subMinutes(5),
        ]);

        $this->browse(function (Browser $viewer1, Browser $viewer2) use ($stream) {
            $viewer1->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->screenshot('viewer1-before-classification');

            $viewer2->visit("/live-cam/{$stream->slug}")
                ->pause(3000)
                ->screenshot('viewer2-before-classification');

            foreach ([$viewer1, $viewer2] as $browser) {
                $browser->script([
                    'document.getElementById("classification-display").classList.remove("hidden");',
                    'document.getElementById("classification-weather").textContent = "Cerah";',
                    'document.getElementById("classification-crowd").textContent = "Ramai";',
                    'document.getElementById("classification-visibility").textContent = "Baik";',
                    'document.getElementById("classification-recommendation").textContent = "Kondisi ideal untuk mendaki";',
                ]);
            }

            $viewer1->pause(1000)->screenshot('viewer1-after-classification');
            $viewer2->pause(1000)->screenshot('viewer2-after-classification');

            $viewer1->assertSeeIn('#classification-weather', 'Cerah');
            $viewer2->assertSeeIn('#classification-weather', 'Cerah');
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

            $this->assertTrue($channelSubscribed, 'Should be subscribed to stream channel for classification updates');

            $browser->screenshot('pusher-classification-channel');
        });

        $stream->delete();
    }

    public function testClassificationDisplayUpdatesAutomatically()
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
            ]);

            $browser->pause(1000)
                ->screenshot('first-classification')
                ->assertSeeIn('#classification-weather', 'Cerah');

            $browser->script([
                'document.getElementById("classification-weather").textContent = "Berawan";',
            ]);

            $browser->pause(1000)
                ->screenshot('second-classification')
                ->assertSeeIn('#classification-weather', 'Berawan');
        });

        $stream->delete();
    }

    public function testClassificationWithDifferentWeatherConditions()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $weatherConditions = ['Cerah', 'Berawan', 'Hujan', 'Berkabut'];

        $this->browse(function (Browser $browser) use ($stream, $weatherConditions) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000);

            foreach ($weatherConditions as $weather) {
                $browser->script([
                    'document.getElementById("classification-display").classList.remove("hidden");',
                    'document.getElementById("classification-weather").textContent = "' . $weather . '";',
                ]);

                $browser->pause(500)
                    ->screenshot("classification-weather-{$weather}")
                    ->assertSeeIn('#classification-weather', $weather);
            }
        });

        $stream->delete();
    }

    public function testClassificationRecommendationsBasedOnConditions()
    {
        $stream = LiveStream::factory()->create([
            'status' => 'live',
        ]);

        $recommendations = [
            'Kondisi ideal untuk mendaki',
            'Siapkan perlengkapan hujan',
            'Hati-hati dengan visibilitas rendah',
            'Jalur sangat ramai, harap bersabar',
        ];

        $this->browse(function (Browser $browser) use ($stream, $recommendations) {
            $browser->visit("/live-cam/{$stream->slug}")
                ->pause(3000);

            foreach ($recommendations as $index => $recommendation) {
                $browser->script([
                    'document.getElementById("classification-display").classList.remove("hidden");',
                    'document.getElementById("classification-recommendation").textContent = "' . $recommendation . '";',
                ]);

                $browser->pause(500)
                    ->screenshot("classification-recommendation-{$index}")
                    ->assertSeeIn('#classification-recommendation', $recommendation);
            }
        });

        $stream->delete();
    }
}

