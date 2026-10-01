<?php

namespace App\Http\Controllers;

use App\Services\SharePreviewImage;

class SharePreviewController extends Controller
{
    /**
     * The preview of a sketch or session picture that social media show for a shared page.
     */
    public function show(string $path)
    {
        $preview = SharePreviewImage::render($path);
        abort_if($preview === null, 404);

        return response()->file($preview, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
