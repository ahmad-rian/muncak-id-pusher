<?php

namespace App\Console\Commands;

use App\Models\Gunung;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.xml file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = public_path('sitemap.xml');

        $sitemap = SitemapGenerator::create(env('APP_URL'))->getSitemap();
        $sitemap->writeToFile($path);

        $this->info('Sitemap generated successfully!');
    }
}
