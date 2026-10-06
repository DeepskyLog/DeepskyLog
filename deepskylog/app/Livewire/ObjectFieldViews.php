<?php

namespace App\Livewire;

use App\Http\Controllers\ObjectFieldViewController;
use App\Models\DeepskyObject;
use App\Models\Instrument;
use App\Services\FieldViews\FieldViewBuilder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The view of an object with the naked eye, in the finder and in the
 * telescope, for the equipment, location and moment chosen on the page.
 * The choices are kept in the query string, which the PDF link carries
 * along.
 */
class ObjectFieldViews extends Component
{
    #[Locked]
    public string $objectName = '';

    #[Url]
    public ?string $set = null;

    #[Url]
    public ?string $instrument = null;

    #[Url]
    public ?string $eyepiece = null;

    #[Url]
    public ?string $lens = null;

    #[Url]
    public ?string $finder = null;

    #[Url]
    public ?string $location = null;

    #[Url]
    public ?string $datetime = null;

    /** The night ('Y-m-d' of its evening) shown at the highest point of the object. */
    #[Url]
    public ?string $night = null;

    public function mount(DeepskyObject $object): void
    {
        $this->objectName = $object->name;
    }

    /**
     * Another telescope brings its own finder, eyepieces and lens.
     */
    public function updatedInstrument(): void
    {
        $this->finder   = null;
        $this->eyepiece = null;
        $this->lens     = null;
    }

    /**
     * Another instrument set brings its own equipment and locations.
     */
    public function updatedSet(): void
    {
        $this->instrument = null;
        $this->eyepiece   = null;
        $this->lens       = null;
        $this->finder     = null;
        $this->location   = null;
    }

    public function updatedLens(): void
    {
        $this->eyepiece = null;
    }

    public function now(): void
    {
        $this->night    = null;
        $this->datetime = 'now';
    }

    /**
     * Back to the highest point of the object, in the night of the moment
     * shown.
     */
    public function highest(?string $night = null): void
    {
        $this->night    = is_string($night) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $night) ? $night : null;
        $this->datetime = null;
    }

    /**
     * Makes the finder chosen on the page the finder of the telescope.
     */
    public function coupleFinder(): void
    {
        $instrument = Instrument::where('user_id', Auth::id())->find((int) $this->instrument);
        $finder     = Instrument::where('user_id', Auth::id())->where('fixedMagnification', '>', 1)->find((int) $this->finder);
        if (! $instrument || ! $finder || $finder->id === $instrument->id) {
            return;
        }

        $instrument->update(['finder_id' => $finder->id]);
        session()->flash('field-views-message', __(':finder is now the finderscope of :instrument.', [
            'finder'     => html_entity_decode($finder->fullName()),
            'instrument' => html_entity_decode($instrument->fullName()),
        ]));
    }

    public function render(FieldViewBuilder $builder)
    {
        $object = DeepskyObject::where('name', $this->objectName)->firstOrFail();

        $options = [
            'set'        => $this->set,
            'night'      => $this->night,
            'instrument' => $this->instrument,
            'eyepiece'   => $this->eyepiece,
            'lens'       => $this->lens === '' ? 'none' : $this->lens,
            'finder'     => $this->finder,
            'location'   => $this->location,
            'datetime'   => $this->datetime,
        ];
        $result = $builder->build(Auth::user(), $object, $options);
        if ($this->datetime === 'now') {
            // Keep the moment, not "now": the link should show what is seen.
            $this->datetime = $result['moment']->format('Y-m-d\TH:i');
        }

        // Show what was chosen for the empty fields.
        if ($this->set !== 'all') {
            $this->set = $result['set'] ? (string) $result['set']->id : null;
        }
        $this->instrument = $result['instrument'] ? (string) $result['instrument']->id : null;
        $this->eyepiece   = $result['eyepiece'] ? (string) $result['eyepiece']->id : null;
        $this->location   = $result['location'] ? (string) $result['location']->id : null;
        $this->lens       = $result['lens'] ? (string) $result['lens']->id : 'none';
        if ($this->finder !== 'none') {
            $this->finder = $result['finder'] ? (string) $result['finder']->id : null;
        }
        $datetime = $result['moment']->format('Y-m-d\TH:i');

        $pdfQuery = array_filter([
            'set'        => $this->set,
            'instrument' => $this->instrument,
            'eyepiece'   => $this->eyepiece,
            'lens'       => $this->lens,
            'finder'     => $this->finder,
            'location'   => $this->location,
            'datetime'   => $datetime,
        ], fn ($value) => $value !== null && $value !== '');

        return view('livewire.object-field-views', [
            'result'          => $result,
            'datetimeValue'   => $datetime,
            'pdfUrl'          => route('object.field-views.pdf', ['slug' => $object->slug] + array_intersect_key($pdfQuery, array_flip(ObjectFieldViewController::OPTIONS))),
            'canCoupleFinder' => $result['instrument'] && $result['finder'] && $result['instrument']->finder_id !== $result['finder']->id,
        ]);
    }
}
