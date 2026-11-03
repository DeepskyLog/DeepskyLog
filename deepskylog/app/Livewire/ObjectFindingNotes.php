<?php

namespace App\Livewire;

use App\Models\ObjectFindingNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The finding notes of a deep-sky object on the object page: the most liked
 * first, SHOWN of them until "Show all"; observers add, like, edit and
 * delete them (ObjectFindingNote holds the rules).
 */
class ObjectFindingNotes extends Component
{
    /** The notes shown before "Show all". */
    public const SHOWN = 5;

    #[Locked]
    public string $objectName;

    public string $text = '';

    public bool $showAll = false;

    public ?int $editingId = null;

    public string $editText = '';

    public function mount(string $objectName): void
    {
        $this->objectName = $objectName;
    }

    public function add(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        ObjectFindingNote::add($user, $this->objectName, $this->text);
        $this->reset('text');
    }

    public function toggleLike(int $id): void
    {
        $note = $this->note($id);
        abort_unless($note->canBeLikedBy(Auth::user()), 403);

        $note->toggleLike(Auth::user());
    }

    public function startEdit(int $id): void
    {
        $note = $this->note($id);
        abort_unless($note->canBeEditedBy(Auth::user()), 403);

        $this->resetErrorBag();
        $this->editingId = $note->id;
        $this->editText = $note->text;
    }

    public function saveEdit(): void
    {
        if ($this->editingId === null) {
            return;
        }
        $note = $this->note($this->editingId);
        abort_unless($note->canBeEditedBy(Auth::user()), 403);

        try {
            $note->edit($this->editText);
        } catch (ValidationException $e) {
            $this->addError('editText', $e->validator->errors()->first('text'));

            return;
        }
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editText');
    }

    public function delete(int $id): void
    {
        $note = $this->note($id);
        abort_unless($note->canBeDeletedBy(Auth::user()), 403);

        $note->delete();
        if ($this->editingId === $id) {
            $this->cancelEdit();
        }
    }

    /** A note of this object; another object's id is not found. */
    private function note(int $id): ObjectFindingNote
    {
        return ObjectFindingNote::where('object_name', $this->objectName)->findOrFail($id);
    }

    public function render()
    {
        $user = Auth::user();
        $notes = ObjectFindingNote::forObject($this->objectName, $user)->get();

        return view('livewire.object-finding-notes', [
            'notes' => $this->showAll ? $notes : $notes->take(self::SHOWN),
            'total' => $notes->count(),
            'user' => $user,
            'canAdd' => $user !== null
                && $notes->where('user_id', $user->id)->count() < ObjectFindingNote::MAX_PER_OBSERVER,
        ]);
    }
}
