<?php

namespace App\Actions;

use GdImage;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use SplFileInfo;

class ConvertImageToWebp
{
    /**
     * Convert an image to WebP format, normalize EXIF orientation, and persist to storage.
     *
     * @param  mixed  $file  Uploaded file, path on disk, or raw binary contents
     * @param  ?string  $disk  The storage disk name, defaulting to the configured media disk
     * @param  string  $directory  The storage directory prefix
     * @param  int  $quality  WebP compression quality (0-100)
     * @return string The storage relative path to the converted WebP image
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function execute(
        mixed $file,
        ?string $disk = null,
        string $directory = 'covers',
        int $quality = 80
    ): string {
        $disk ??= config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public';

        [$contents, $orientation] = $this->extractImagePayload($file, $disk);

        $image = @imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            throw new InvalidArgumentException('Unable to decode image data.');
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $image = $this->normalizeOrientation($image, $orientation);

        ob_start();
        $encoded = imagewebp($image, null, $quality);
        $webpData = ob_get_clean();

        if (! $encoded || empty($webpData)) {
            throw new RuntimeException('Failed to encode image to WebP format.');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk($disk)->put($path, $webpData);

        return $path;
    }

    /**
     * Invokable handler delegating to execute.
     */
    public function __invoke(
        mixed $file,
        ?string $disk = null,
        string $directory = 'covers',
        int $quality = 80
    ): string {
        return $this->execute($file, $disk, $directory, $quality);
    }

    /**
     * Extract raw image contents and optional EXIF orientation from the given file input.
     *
     * @return array{0: string, 1: ?int}
     */
    protected function extractImagePayload(mixed $file, string $disk): array
    {
        $contents = null;
        $orientation = null;

        if ($file instanceof UploadedFile || $file instanceof File || $file instanceof SplFileInfo) {
            $realPath = $file->getRealPath() ?: $file->getPathname();

            if (filled($realPath) && file_exists($realPath)) {
                $contents = file_get_contents($realPath);
                $orientation = $this->readOrientationFromPath($realPath);
            }
        } elseif (is_string($file)) {
            if (file_exists($file) && is_file($file)) {
                $contents = file_get_contents($file);
                $orientation = $this->readOrientationFromPath($file);
            } elseif (Storage::disk($disk)->exists($file)) {
                $contents = Storage::disk($disk)->get($file);
                $stream = Storage::disk($disk)->readStream($file);
                if (is_resource($stream)) {
                    $orientation = $this->readOrientationFromStream($stream);
                    fclose($stream);
                }
            } else {
                $contents = $file;
                $stream = fopen('php://temp', 'w+b');
                if (is_resource($stream)) {
                    fwrite($stream, $file);
                    rewind($stream);
                    $orientation = $this->readOrientationFromStream($stream);
                    fclose($stream);
                }
            }
        }

        if (! is_string($contents) || $contents === '') {
            throw new InvalidArgumentException('Unable to read image payload from input.');
        }

        return [$contents, $orientation];
    }

    /**
     * Read EXIF orientation tag from a file path.
     */
    protected function readOrientationFromPath(string $path): ?int
    {
        $exif = @exif_read_data($path);

        if (is_array($exif) && isset($exif['Orientation'])) {
            return (int) $exif['Orientation'];
        }

        return null;
    }

    /**
     * Read EXIF orientation tag from a resource stream.
     *
     * @param  resource  $stream
     */
    protected function readOrientationFromStream($stream): ?int
    {
        $exif = @exif_read_data($stream);

        if (is_array($exif) && isset($exif['Orientation'])) {
            return (int) $exif['Orientation'];
        }

        return null;
    }

    /**
     * Normalize image orientation based on standard EXIF orientation codes.
     */
    protected function normalizeOrientation(GdImage $image, ?int $orientation): GdImage
    {
        if ($orientation === null || $orientation <= 1) {
            return $image;
        }

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

        switch ($orientation) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $rotated = imagerotate($image, 180, $transparent);
                if ($rotated instanceof GdImage) {
                    $image = $rotated;
                }
                break;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                break;
            case 5:
                imageflip($image, IMG_FLIP_VERTICAL);
                $rotated = imagerotate($image, 270, $transparent);
                if ($rotated instanceof GdImage) {
                    $image = $rotated;
                }
                break;
            case 6:
                $rotated = imagerotate($image, 270, $transparent);
                if ($rotated instanceof GdImage) {
                    $image = $rotated;
                }
                break;
            case 7:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $rotated = imagerotate($image, 270, $transparent);
                if ($rotated instanceof GdImage) {
                    $image = $rotated;
                }
                break;
            case 8:
                $rotated = imagerotate($image, 90, $transparent);
                if ($rotated instanceof GdImage) {
                    $image = $rotated;
                }
                break;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }
}
