<?php

use App\Console\Commands\GenerateSitemap;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('sitemap:generate', function () {
    $this->call(GenerateSitemap::class);
});