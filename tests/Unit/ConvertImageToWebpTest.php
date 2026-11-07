<?php

namespace Tests\Unit;

use App\Actions\ConvertImageToWebp;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use ReflectionMethod;
use Tests\TestCase;

class ConvertImageToWebpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_converts_jpeg_image_to_webp(): void
    {
        $file = UploadedFile::fake()->image('cover.jpg', 120, 80);

        $action = new ConvertImageToWebp;
        $storedPath = $action->execute($file);

        $this->assertStringStartsWith('covers/', $storedPath);
        $this->assertStringEndsWith('.webp', $storedPath);
        Storage::disk('public')->assertExists($storedPath);

        $content = Storage::disk('public')->get($storedPath);
        $this->assertStringStartsWith('RIFF', $content);
        $this->assertSame('WEBP', substr($content, 8, 4));

        $gdImage = imagecreatefromstring($content);
        $this->assertInstanceOf(GdImage::class, $gdImage);
        $this->assertSame(120, imagesx($gdImage));
        $this->assertSame(80, imagesy($gdImage));
    }

    public function test_preserves_png_alpha_transparency(): void
    {
        $img = imagecreatetruecolor(50, 50);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefilledrectangle($img, 0, 0, 50, 50, $transparent);

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_png_');
        file_put_contents($tmpFile, $pngData);

        $file = new UploadedFile($tmpFile, 'transparent.png', 'image/png', null, true);

        $action = new ConvertImageToWebp;
        $storedPath = $action->execute($file);

        Storage::disk('public')->assertExists($storedPath);

        $converted = imagecreatefromstring(Storage::disk('public')->get($storedPath));
        $this->assertInstanceOf(GdImage::class, $converted);

        $alpha = (imagecolorat($converted, 25, 25) >> 24) & 0x7F;
        $this->assertSame(127, $alpha);

        @unlink($tmpFile);
    }

    public function test_handles_webp_input(): void
    {
        $img = imagecreatetruecolor(60, 40);
        $red = imagecolorallocate($img, 255, 0, 0);
        imagefilledrectangle($img, 0, 0, 60, 40, $red);

        ob_start();
        imagewebp($img);
        $webpData = ob_get_clean();

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_webp_');
        file_put_contents($tmpFile, $webpData);

        $file = new UploadedFile($tmpFile, 'sample.webp', 'image/webp', null, true);

        $action = new ConvertImageToWebp;
        $storedPath = $action->execute($file);

        $this->assertStringStartsWith('covers/', $storedPath);
        $this->assertStringEndsWith('.webp', $storedPath);
        Storage::disk('public')->assertExists($storedPath);

        $converted = imagecreatefromstring(Storage::disk('public')->get($storedPath));
        $this->assertInstanceOf(GdImage::class, $converted);
        $this->assertSame(60, imagesx($converted));
        $this->assertSame(40, imagesy($converted));

        @unlink($tmpFile);
    }

    public function test_normalizes_exif_orientation_rotations(): void
    {
        $action = new ConvertImageToWebp;
        $refMethod = new ReflectionMethod($action, 'normalizeOrientation');
        $refMethod->setAccessible(true);

        // Orientation 6: 90 deg CW, should rotate and swap dimensions (100x50 -> 50x100)
        $img = imagecreatetruecolor(100, 50);
        $rotated = $refMethod->invoke($action, $img, 6);
        $this->assertInstanceOf(GdImage::class, $rotated);
        $this->assertSame(50, imagesx($rotated));
        $this->assertSame(100, imagesy($rotated));

        // Orientation 8: 270 deg CW, should rotate and swap dimensions (100x50 -> 50x100)
        $img8 = imagecreatetruecolor(100, 50);
        $rotated8 = $refMethod->invoke($action, $img8, 8);
        $this->assertInstanceOf(GdImage::class, $rotated8);
        $this->assertSame(50, imagesx($rotated8));
        $this->assertSame(100, imagesy($rotated8));

        // Orientation 3: 180 deg, retains dimensions (100x50 -> 100x50)
        $img3 = imagecreatetruecolor(100, 50);
        $rotated3 = $refMethod->invoke($action, $img3, 3);
        $this->assertInstanceOf(GdImage::class, $rotated3);
        $this->assertSame(100, imagesx($rotated3));
        $this->assertSame(50, imagesy($rotated3));
    }

    public function test_converts_image_from_disk_path(): void
    {
        $file = UploadedFile::fake()->image('old-cover.jpg', 80, 80);
        Storage::disk('public')->put('covers/old-cover.jpg', $file->getContent());

        $action = new ConvertImageToWebp;
        $storedPath = $action->execute('covers/old-cover.jpg');

        $this->assertStringStartsWith('covers/', $storedPath);
        $this->assertStringEndsWith('.webp', $storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_throws_exception_on_invalid_image_payload(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $action = new ConvertImageToWebp;
        $action->execute('invalid-image-content-here');
    }
}
