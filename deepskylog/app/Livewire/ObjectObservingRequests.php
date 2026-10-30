<?php

namespace App\Livewire;

use App\Models\ObservingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The observing requests of a deep-sky object on the object page: the open
 * ones, and a form to ask other observers to observe it
 * (ObservingRequest::ask() holds the rules).
 */
class ObjectObservingRequests extends Component
{
    #[Locked]
    public string $objectName;

    public bool $asking = false;

    public string $note = '';

    public ?int $minApertureMm = null;

    public int $months = ObservingRequest::DEFAULT_MONTHS;

    public function mount(string $objectName): void
    {
        $this->objectName = $objectName;
    }

    public function ask(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        try {
            ObservingRequest::ask($user, $this->objectName, $this->note, $this->minApertureMm ?: null, (int) $this->months);
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->messages() as $field => $messages) {
                $this->addError(match ($field) { 'min_aperture_mm' => 'minApertureMm', default => $field }, $messages[0]);
            }

            return;
        }
        $this->reset('asking', 'note', 'minApertureMm', 'months');
    }

    public function closeRequest(int $id): void
    {
        $request = ObservingRequest::where('object_name', $this->objectName)->findOrFail($id);
        abort_unless($request->canBeClosedBy(Auth::user()), 403);

        $request->close();
    }

    public function render()
    {
        $user = Auth::user();
        $requests = ObservingRequest::open()
            ->where('object_name', $this->objectName)
            ->with('user:id,name,slug,username')
            ->withCount('answers')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.object-observing-requests', [
            'requests' => $requests,
            'user' => $user,
            'canAsk' => $user !== null && ! $requests->contains('user_id', $user->id),
        ]);
    }
}
