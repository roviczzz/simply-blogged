<?php

namespace App\Console\Commands;

use App\Actions\ConvertImageToWebp;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('posts:convert-covers-to-webp {--dry-run : Preview images that would be converted without modifying files or database records}')]
#[Description('Convert existing non-WebP post cover images to WebP format')]
class ConvertPostCoversToWebp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:convert-covers-to-webp {--dry-run : Preview images that would be converted without modifying files or database records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing non-WebP post cover images to WebP format';

    /**
     * Execute the console command.
     */
    public function handle(ConvertImageToWebp $converter): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $posts = Post::query()
            ->whereNotNull('cover_image')
            ->where('cover_image', '!=', '')
            ->where('cover_image', 'not like', '%.webp')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No posts found with non-WebP cover images.');

            return self::SUCCESS;
        }

        $diskName = config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public';
        $disk = Storage::disk($diskName);

        if ($isDryRun) {
            $this->info('Dry run mode enabled. No files or database records will be modified.');

            foreach ($posts as $post) {
                if (! $disk->exists($post->cover_image)) {
                    $this->warn("Post #{$post->id} ({$post->title}): Source image [{$post->cover_image}] not found on {$diskName} disk. Skipping.");

                    continue;
                }

                $this->line("Would convert: Post #{$post->id} ({$post->title}) [{$post->cover_image}]");
            }

            $this->info("Dry run complete: {$posts->count()} post(s) inspected.");

            return self::SUCCESS;
        }

        $convertedCount = 0;

        foreach ($posts as $post) {
            $oldPath = $post->cover_image;

            if (! $disk->exists($oldPath)) {
                $this->warn("Post #{$post->id} ({$post->title}): Source image [{$oldPath}] not found on {$diskName} disk. Skipping.");

                continue;
            }

            try {
                $newPath = $converter->execute($oldPath, disk: $diskName, quality: 80);

                if (! $disk->exists($newPath) || $disk->size($newPath) === 0) {
                    $this->error("Post #{$post->id}: Converted file [{$newPath}] is invalid or empty. Aborting conversion for this post.");

                    continue;
                }

                $post->update(['cover_image' => $newPath]);

                if ($oldPath !== $newPath && $disk->exists($oldPath)) {
                    $disk->delete($oldPath);
                }

                $convertedCount++;
                $this->info("Converted Post #{$post->id}: [{$oldPath}] -> [{$newPath}]");
            } catch (Throwable $e) {
                $this->error("Post #{$post->id}: Conversion failed: {$e->getMessage()}");
            }
        }

        $this->info("Conversion complete. {$convertedCount} post(s) successfully converted to WebP.");

        return self::SUCCESS;
    }
}
