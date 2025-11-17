<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupLiveStreamChunks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'livestream:cleanup
                          {--all : Clean up all chunks}
                          {--age=1 : Clean up chunks older than X hours (default: 1)}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old live stream chunks to free up disk space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting live stream chunks cleanup...');

        $streamDir = storage_path('app/live-streams');

        if (!file_exists($streamDir)) {
            $this->info('No live-streams directory found. Nothing to clean.');
            return 0;
        }

        $cleanupAll = $this->option('all');
        $maxAge = (int) $this->option('age');
        $cutoffTime = Carbon::now()->subHours($maxAge)->timestamp;

        $totalSize = 0;
        $totalFiles = 0;

        // Scan all stream directories
        $streamDirs = glob($streamDir . '/*', GLOB_ONLYDIR);

        foreach ($streamDirs as $dir) {
            $streamId = basename($dir);
            $chunks = glob($dir . '/chunk_*.webm');

            foreach ($chunks as $chunkPath) {
                $shouldDelete = false;

                if ($cleanupAll) {
                    $shouldDelete = true;
                } else {
                    $fileTime = filemtime($chunkPath);
                    if ($fileTime < $cutoffTime) {
                        $shouldDelete = true;
                    }
                }

                if ($shouldDelete) {
                    $size = filesize($chunkPath);
                    $totalSize += $size;
                    $totalFiles++;

                    unlink($chunkPath);
                    $this->line("  Deleted: {$chunkPath} (" . round($size / 1024, 2) . " KB)");
                }
            }

            // Remove empty directories
            if (count(glob($dir . '/*')) === 0) {
                rmdir($dir);
                $this->line("  Removed empty directory: stream {$streamId}");
            }
        }

        $totalSizeMB = round($totalSize / 1024 / 1024, 2);

        $this->info("\n✅ Cleanup completed!");
        $this->info("   Files deleted: {$totalFiles}");
        $this->info("   Space freed: {$totalSizeMB} MB");

        return 0;
    }
}
