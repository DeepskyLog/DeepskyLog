<?php

namespace App\Http\Controllers;

use App\Models\ObjectFindingNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Finding notes from the page at the eyepiece, which has no Livewire: a new
 * note (a form, back to the page) and a like (JSON). The object page uses
 * the Livewire ObjectFindingNotes.
 */
class ObjectFindingNoteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'object_name' => 'required|string|max:255',
            'text' => 'required|string|max:1000',
        ]);

        ObjectFindingNote::add($request->user(), $validated['object_name'], $validated['text']);

        return back()->with('success', __('Thank you: your finding note was added.'));
    }

    public function toggleLike(Request $request, ObjectFindingNote $note): JsonResponse
    {
        abort_unless($note->canBeLikedBy($request->user()), 403);

        $liked = $note->toggleLike($request->user());

        return response()->json(['liked' => $liked, 'count' => $note->likes_count]);
    }
}
