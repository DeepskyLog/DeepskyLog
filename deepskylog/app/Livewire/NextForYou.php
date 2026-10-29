<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Services\NextForYou as Next;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * "Next for you" (/observing-lists/next-for-you): the objects observers who
 * saw the same objects as the observer saw too, that their standard
 * telescope shows (App\Services\NextForYou), with a button that makes them
 * an observing list. The ranking is calculated on the queue, and so are
 * missing contrast reserves; the page polls until they are there.
 */
class NextForYou extends Component
{
    public string $name = '';

    public bool $public = false;

    public function retry(): void
    {
        Next::for(Auth::user())->retry();
    }

    public function create()
    {
        $this->validate(['name' => ['nullable', 'string', 'max:250']]);
        $user = Auth::user();
        abort_unless($user && $user->can('create', ObservingList::class), 403);

        $next   = Next::for($user);
        $result = $next->unavailable() === null ? $next->result() : null;
        $list   = $result ? $next->createList($result, Str::limit(trim($this->name), 250, ''), $this->public) : null;
        if (! $list) {
            $this->addError('name', __('There are no objects to put on a list.'));

            return null;
        }
        session()->flash('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));

        return redirect()->route('observing-list.show', $list);
    }

    public function render()
    {
        $next        = Next::for(Auth::user());
        $unavailable = $next->unavailable();
        $result      = $unavailable === null ? $next->result() : null;
        if ($unavailable === null && ! $result && ! $next->pending()) {
            $next->queue();
            // a sync queue (tests, no worker configured) has it now
            $result = $next->result();
        }

        return view('livewire.next-for-you', [
            'next'        => $next,
            'unavailable' => $unavailable,
            'failed'      => $unavailable === null && ! $result && $next->failed(),
            'result'      => $result,
            'shown'       => $result ? $next->objects($result) : null,
        ]);
    }
}
