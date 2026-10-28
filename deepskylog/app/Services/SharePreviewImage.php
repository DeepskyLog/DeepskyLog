<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * The image that social media show in the preview of a shared page (og:image, twitter:image).
 *
 * Sketches and session pictures can be several MB, and WhatsApp drops a preview image above
 * roughly 300 KB. A large image is therefore shared as a JPEG of at most MAX_SIDE pixels and
 * MAX_BYTES, made on the first request of the share-preview route (by the crawler of the site the
 * page is shared on) and kept in the local disk under DIRECTORY.
 */
class SharePreviewImage
{
    public const MAX_SIDE = 1200;

    public const MAX_BYTES = 300 * 1024;

    public const DIRECTORY = 'share-previews';

    // Larger images are not decoded: GD needs about 4 bytes a pixel.
    private const MAX_PIXELS = 50_000_000;

    // The public images that may be previewed (relative to public/), besides the public disk (storage/).
    private const PUBLIC_DIRECTORIES = ['images/drawings/', 'images/cometdrawings/', 'images/sessions/'];

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * The og:image of an image of the site ('/images/…', '/storage/…' or a full URL): its preview
     * when it is too large, else the image itself. Width and height are those of the shared image
     * when they are known, null for an image that is not on this server.
     *
     * @return array{url: string, width: int|null, height: int|null}|null
     */
    public static function for(?string $source): ?array
    {
        if ($source === null || trim($source) === '') {
            return null;
        }

        $relative = self::relativePath($source);
        $file     = $relative !== null ? self::localFile($relative) : null;
        if ($file === null) {
            return ['url' => str_starts_with($source, 'http') ? $source : url($source), 'width' => null, 'height' => null];
        }

        $size   = @getimagesize($file);
        $width  = $size[0] ?? null;
        $height = $size[1] ?? null;
        if (!self::needsPreview($file, $width, $height)) {
            return ['url' => url($relative), 'width' => $width, 'height' => $height];
        }

        // Only the size is known here; the preview may come out smaller still to stay below MAX_BYTES.
        $scale = $width && $height ? min(1, self::MAX_SIDE / max($width, $height)) : 1;

        return [
            'url'    => route('share-preview', ['path' => $relative]),
            'width'  => $width ? (int) round($width * $scale) : null,
            'height' => $height ? (int) round($height * $scale) : null,
        ];
    }

    /**
     * The path of the image relative to public/ ('images/drawings/1.jpg', 'storage/sketches/a.jpg'),
     * or null when the source is not on this server.
     */
    public static function relativePath(string $source): ?string
    {
        $path = $source;
        if (preg_match('#^https?://#i', $source)) {
            $host  = parse_url($source, PHP_URL_HOST);
            $hosts = array_filter([
                parse_url((string) config('app.url'), PHP_URL_HOST),
                parse_url((string) config('filesystems.disks.public.url'), PHP_URL_HOST),
                request()?->getHost(),
            ]);
            if (!in_array(strtolower((string) $host), array_map('strtolower', $hosts), true)) {
                return null;
            }
            $path = (string) parse_url($source, PHP_URL_PATH);
        }

        $path = ltrim(rawurldecode(strtok($path, '?#')), '/');

        return $path !== '' ? $path : null;
    }

    /**
     * The file of a path relative to public/, when it is an image of one of the directories that
     * may be previewed; null for anything else (including paths that climb out with '..').
     */
    public static function localFile(string $relative): ?string
    {
        if (str_contains($relative, "\0") || !in_array(strtolower(pathinfo($relative, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) {
            return null;
        }

        if (str_starts_with($relative, 'storage/')) {
            $root = Storage::disk('public')->path('');
            $file = Storage::disk('public')->path(substr($relative, strlen('storage/')));
        } else {
            $directory = collect(self::PUBLIC_DIRECTORIES)->first(fn ($dir) => str_starts_with($relative, $dir));
            if ($directory === null) {
                return null;
            }
            $root = public_path($directory);
            $file = public_path($relative);
        }

        $realRoot = realpath($root);
        $realFile = realpath($file);
        if ($realRoot === false || $realFile === false || !is_file($realFile)
                                || !str_starts_with($realFile, rtrim($realRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $realFile;
    }

    /**
     * The preview file of an image (relative to public/), made when it is missing or older than the
     * image; null when the path may not be previewed or the image cannot be read.
     */
    public static function render(string $relative): ?string
    {
        $file = self::localFile($relative);
        if ($file === null) {
            return null;
        }

        $disk   = Storage::disk('local');
        $target = self::DIRECTORY . '/' . sha1($relative) . '.jpg';
        if ($disk->exists($target) && $disk->lastModified($target) >= filemtime($file)) {
            return $disk->path($target);
        }

        $jpeg = self::encode($file);
        if ($jpeg === null) {
            return null;
        }

        // Written under another name first, so a request at the same time never gets half a file.
        $temporary = $target . '.' . bin2hex(random_bytes(4)) . '.tmp';
        $disk->put($temporary, $jpeg);
        $disk->move($temporary, $target);

        return $disk->path($target);
    }

    private static function needsPreview(string $file, ?int $width, ?int $height): bool
    {
        return filesize($file) > self::MAX_BYTES
            || ($width && $height && max($width, $height) > self::MAX_SIDE)
            || !in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    }

    /**
     * The image as a JPEG of at most MAX_SIDE pixels and MAX_BYTES: the quality goes down first,
     * then the size.
     */
    private static function encode(string $file): ?string
    {
        $size = @getimagesize($file);
        if (!$size || $size[0] < 1 || $size[1] < 1 || $size[0] * $size[1] > self::MAX_PIXELS) {
            return null;
        }

        self::raiseMemoryLimit($size[0] * $size[1] * 5 + 64 * 1024 * 1024);

        $source = @imagecreatefromstring((string) file_get_contents($file));
        if ($source === false) {
            return null;
        }

        try {
            $side = min(self::MAX_SIDE, max($size[0], $size[1]));
            while (true) {
                $scale  = $side / max($size[0], $size[1]);
                $width  = max(1, (int) round($size[0] * $scale));
                $height = max(1, (int) round($size[1] * $scale));

                $canvas = imagecreatetruecolor($width, $height);
                // A transparent PNG gets a white background, as on the page.
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
                imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

                foreach ([85, 75, 65, 55] as $quality) {
                    ob_start();
                    imagejpeg($canvas, null, $quality);
                    $jpeg = (string) ob_get_clean();
                    if (strlen($jpeg) <= self::MAX_BYTES) {
                        imagedestroy($canvas);

                        return $jpeg;
                    }
                }
                imagedestroy($canvas);

                if ($side <= 300) {
                    return $jpeg;
                }
                $side = (int) round($side * 0.8);
            }
        } finally {
            imagedestroy($source);
        }
    }

    private static function raiseMemoryLimit(int $needed): void
    {
        $limit = ini_get('memory_limit');
        if ($limit === false || $limit === '-1') {
            return;
        }

        $bytes = (int) $limit;
        $unit  = strtolower(substr(trim($limit), -1));
        $bytes *= match ($unit) {
            'g'     => 1024 ** 3,
            'm'     => 1024 ** 2,
            'k'     => 1024,
            default => 1,
        };

        if (memory_get_usage() + $needed > $bytes) {
            ini_set('memory_limit', (string) (memory_get_usage() + $needed));
        }
    }
}
