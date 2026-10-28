<?php

namespace App\Support;

/**
 * Turns an SVG into a PNG data URI for dompdf, whose own SVG support is
 * limited: with rsvg-convert when installed, else with ImageMagick.
 */
class SvgRasterizer
{
    /**
     * @return string|null the data URI, or null when no rasterizer worked
     */
    public static function toDataUri(string $svg, int $widthPx, int $heightPx, int $dpi = 144): ?string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dsl_svg_');
        if ($tmp === false) {
            return null;
        }
        $svgPath = $tmp.'.svg';
        rename($tmp, $svgPath);
        file_put_contents($svgPath, $svg);

        try {
            return self::fileToDataUri($svgPath, $widthPx, $heightPx, $dpi);
        } finally {
            @unlink($svgPath);
        }
    }

    /**
     * @return string|null the data URI, or null when no rasterizer worked
     */
    public static function fileToDataUri(string $svgPath, int $widthPx, int $heightPx, int $dpi = 144): ?string
    {
        $pngPath = $svgPath.'.png';

        $candidates = [];
        $rsvg       = trim((string) shell_exec('command -v rsvg-convert 2>/dev/null'));
        if ($rsvg !== '') {
            $candidates[] = escapeshellcmd($rsvg).' -w '.$widthPx.' -h '.$heightPx.' '.escapeshellarg($svgPath).' -o '.escapeshellarg($pngPath);
        }
        foreach (['magick', 'convert'] as $bin) {
            $path = trim((string) shell_exec('command -v '.$bin.' 2>/dev/null'));
            if ($path !== '') {
                $candidates[] = escapeshellcmd($path).' -background white -flatten -density '.$dpi.' -resize '.$widthPx.'x'.$heightPx.' '.escapeshellarg($svgPath).' '.escapeshellarg($pngPath);
            }
        }

        foreach ($candidates as $cmd) {
            @unlink($pngPath);
            exec($cmd.' 2>&1', $out, $code);
            if ($code === 0 && is_file($pngPath)) {
                $bytes = file_get_contents($pngPath);
                @unlink($pngPath);
                if ($bytes !== false && $bytes !== '') {
                    return 'data:image/png;base64,'.base64_encode($bytes);
                }
            }
        }

        @unlink($pngPath);

        return null;
    }
}
