<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Photos of equipment and locations on the public disk. They get a random
 * name: a name made from the user and equipment slugs could be the name of
 * another observer's photo ("john" + "doe x" and "john-doe" + "x"), which the
 * upload would then overwrite. The extension comes from the content, so a file
 * the server would run or render (.php, .html) is never stored under its name.
 */
class UploadedPhoto
{
    /**
     * Store the photo in $directory and return its path. When it replaces the
     * picture of $owner, the old file is removed, unless another row of the
     * same model still uses it (old slug names could be shared).
     */
    public static function store(UploadedFile $photo, string $directory, ?Model $owner = null): string
    {
        $path = $photo->storePublicly($directory, 'public');

        $old = $owner?->exists ? $owner->getOriginal('picture') : null;
        if (! empty($old) && $old !== $path && str_starts_with($old, $directory.'/')
            && ! $owner->newQuery()->where('picture', $old)->whereKeyNot($owner->getKey())->exists()) {
            Storage::disk('public')->delete($old);
        }

        return $path;
    }
}
