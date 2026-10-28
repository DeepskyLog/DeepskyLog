<?php

namespace App\Services\Import;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * The drawings uploaded with an observation import (#817): the images in the
 * zip file that holds the CSV or OAL file (as the zip of ObservationZipBuilder
 * does), or image files uploaded next to it.
 *
 * A drawing is found by the name an observation gives it (OAL's image
 * element, the drawing column of a CSV file): first by its path in the zip,
 * then by its file name. Only the drawings an observation refers to are
 * copied to the public disk, under a name of their own, so nothing of the
 * zip is unpacked where its entry names say.
 */
class ImportDrawings
{
    /** The largest drawing imported. */
    public const MAX_DRAWING_BYTES = 20 * 1024 * 1024;

    /** The most entries a zip file may have. */
    public const MAX_ZIP_ENTRIES = 20000;

    /** The largest file to import (CSV or OAL) in a zip. */
    public const MAX_OBSERVATIONS_BYTES = 50 * 1024 * 1024;

    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private const IMAGE_TYPES = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    private ?ZipArchive $zip = null;

    /** @var array<string, int|UploadedFile> normalised path => entry index in the zip, or the uploaded file */
    private array $drawings = [];

    /** @var array<string, string|null> lower-case file name => normalised path; null when several drawings have that name */
    private array $byName = [];

    /** @var array<string, string> normalised path => name shown */
    private array $names = [];

    /** @var array<string, true> normalised paths of the drawings an observation referred to */
    private array $used = [];

    /** How many observations referred to a drawing. */
    private int $references = 0;

    /**
     * No drawings: the references in the file are only counted.
     */
    public static function none(): self
    {
        return new self();
    }

    /**
     * The image files uploaded next to the file to import.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public static function fromUploads(array $files): self
    {
        $drawings = new self();
        foreach ($files as $file) {
            $name = $file->getClientOriginalName();
            $drawings->add(self::normalise($name), $name, $file);
        }

        return $drawings;
    }

    /**
     * Open a zip file: its images are the drawings, and the content of the
     * file to import is returned with its name.
     *
     * @return array{0: self, 1: string, 2: string} the drawings, the content and the name of the file to import
     *
     * @throws \RuntimeException with a message for the observer when the zip cannot be used
     */
    public static function fromZip(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new \RuntimeException(__('The zip file could not be opened.'));
        }

        if (($entries = $zip->numFiles) > self::MAX_ZIP_ENTRIES) {
            $zip->close();

            throw new \RuntimeException(__('The zip file holds :count files, but at most :max can be imported.', [
                'count' => number_format($entries),
                'max' => number_format(self::MAX_ZIP_ENTRIES),
            ]));
        }

        $drawings = new self();
        $xml = [];
        $csv = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            $path = self::normalise($name);
            $file = basename($path);

            // Folders, and what macOS and Windows add to a zip
            if ($path === '' || str_ends_with($name, '/') || str_starts_with($path, '__macosx/') || str_starts_with($file, '.') || $file === 'thumbs.db') {
                continue;
            }

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                $drawings->add($path, $name, $i);
            } elseif ($extension === 'xml') {
                $xml[$i] = $name;
            } elseif (in_array($extension, ['csv', 'txt'], true)) {
                $csv[$i] = $name;
            }
        }

        // The zip of ObservationZipBuilder holds observations.xml and drawings.csv,
        // which lists the drawings: the OAL file is the one to import.
        $candidates = $xml !== [] ? $xml : $csv;
        if (count($candidates) !== 1) {
            $zip->close();

            throw new \RuntimeException($candidates === []
                ? __('The zip file holds no CSV or OAL file to import.')
                : __('The zip file holds several files to import (:files). Put only one CSV or OAL file in it.', ['files' => implode(', ', $candidates)]));
        }

        $index = array_key_first($candidates);
        $stat = $zip->statIndex($index);
        if ($stat === false || $stat['size'] > self::MAX_OBSERVATIONS_BYTES) {
            $zip->close();

            throw new \RuntimeException(__('The file :file in the zip is larger than 50 MB.', ['file' => $candidates[$index]]));
        }

        $content = $zip->getFromIndex($index, self::MAX_OBSERVATIONS_BYTES);
        if ($content === false) {
            $zip->close();

            throw new \RuntimeException(__('The file :file in the zip could not be read.', ['file' => $candidates[$index]]));
        }

        // Closed by the destructor from here on.
        $drawings->zip = $zip;

        return [$drawings, $content, $candidates[$index]];
    }

    public function __destruct()
    {
        $this->zip?->close();
    }

    /**
     * Whether drawings were uploaded with the file.
     */
    public function isEmpty(): bool
    {
        return $this->names === [];
    }

    /**
     * Copy the drawing a reference names to the public disk.
     *
     * @return array{0: string|null, 1: string|null} the path of the stored drawing, or null and why it was not stored (null when no drawings were uploaded)
     */
    public function store(string $reference): array
    {
        [$path, $problem] = $this->find($reference);
        if ($path === null) {
            return [null, $problem];
        }

        $drawing = $this->drawings[$path];
        $file = $drawing instanceof UploadedFile ? $drawing->getRealPath() : $this->extract($drawing);
        if ($file === null) {
            return [null, "Drawing '{$reference}' is larger than 20 MB."];
        }

        try {
            $info = @getimagesize($file);
            if ($info === false || ! in_array($info[2], self::IMAGE_TYPES, true)) {
                return [null, "Drawing '{$reference}' is no JPEG, PNG, GIF or WebP image."];
            }

            $stored = Storage::disk('public')->putFile('observations', new File($file));

            return $stored ? [$stored, null] : [null, "Drawing '{$reference}' could not be stored."];
        } finally {
            if (! $drawing instanceof UploadedFile) {
                @unlink($file);
            }
        }
    }

    /**
     * Count a reference that is not imported (the observation has a drawing,
     * or another drawing), so its drawing is not reported as unused.
     */
    public function markUsed(string $reference): void
    {
        $this->find($reference);
    }

    /**
     * How many references were looked up.
     */
    public function references(): int
    {
        return $this->references;
    }

    /**
     * The names of the drawings no observation referred to.
     *
     * @return list<string>
     */
    public function unused(): array
    {
        return array_values(array_diff_key($this->names, $this->used));
    }

    /**
     * The normalised path of the drawing a reference names, first by its
     * path, then by its file name, or null and why it is not found.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function find(string $reference): array
    {
        $this->references++;

        if ($this->isEmpty()) {
            return [null, null];
        }

        $path = self::normalise($reference);
        $name = basename($path);
        $path = isset($this->drawings[$path]) ? $path : ($this->byName[$name] ?? null);

        if ($path === null) {
            return [null, array_key_exists($name, $this->byName)
                ? "The upload holds several drawings named '{$name}'."
                : "Drawing '{$reference}' is not in the upload."];
        }

        $this->used[$path] = true;

        return [$path, null];
    }

    private function add(string $path, string $name, int|UploadedFile $drawing): void
    {
        $this->drawings[$path] = $drawing;
        $file = basename($path);
        $this->byName[$file] = array_key_exists($file, $this->byName) ? null : $path;
        $this->names[$path] = $name;
    }

    /**
     * Copy a zip entry to a temporary file, or null when it is too large. The
     * size in the zip's directory is not trusted: the copy stops at the limit.
     */
    private function extract(int $index): ?string
    {
        $stat = $this->zip->statIndex($index);
        if ($stat === false || $stat['size'] > self::MAX_DRAWING_BYTES) {
            return null;
        }

        $in = $this->zip->getStreamIndex($index);
        $file = tempnam(sys_get_temp_dir(), 'drawing');
        $out = fopen($file, 'wb');
        $bytes = 0;

        try {
            while ($in !== false && ! feof($in)) {
                $chunk = fread($in, 1024 * 1024);
                if ($chunk === false) {
                    break;
                }
                $bytes += strlen($chunk);
                if ($bytes > self::MAX_DRAWING_BYTES) {
                    @unlink($file);

                    return null;
                }
                fwrite($out, $chunk);
            }
        } finally {
            fclose($out);
            if ($in !== false) {
                fclose($in);
            }
        }

        return $file;
    }

    /**
     * A reference or a path in the zip as it is looked up: URL-decoded,
     * without a file: scheme, with forward slashes, without leading "./" or
     * "/", in lower case.
     */
    private static function normalise(string $reference): string
    {
        $path = str_replace('\\', '/', rawurldecode(trim($reference)));
        $path = (string) preg_replace('#^file:/*#i', '', $path);
        $path = (string) preg_replace('#^(\./|/)+#', '', $path);

        return mb_strtolower($path);
    }
}
