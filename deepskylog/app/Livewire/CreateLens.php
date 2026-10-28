<?php

namespace App\Livewire;

use App\Models\Lens;
use App\Models\LensMake;
use App\Support\UploadedPhoto;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateLens extends Component
{
    use WithFileUploads;

    public $lens;

    public $name;

    public $lens_make;

    public $lens_new_make;

    public $factor;

    public $description;

    public $proposed_name;

    #[Validate('image')]
    public $photo;

    public function mount(): void
    {
        if ($this->lens) {
            abort_unless($this->lens->canBeEditedBy(Auth::user()), 403);

            $this->name = $this->lens->name;

            $this->factor = floatval($this->lens->factor);
            $this->lens_make = $this->lens->make_id;

            $this->description = $this->lens->description;
        }
    }

    public function render()
    {
        if ($this->lens) {
            return view('livewire.create-lens', ['update' => true]);
        }

        return view('livewire.create-lens', ['update' => false]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['lens_make', 'lens_new_make', 'factor'])) {
            $this->updateProposedName();
        }
    }

    public function updateProposedName(): void
    {
        if ($this->lens_make == null || $this->lens_make == 1) {
            $make = trim((string) $this->lens_new_make);
        } else {
            $make = LensMake::find($this->lens_make)?->name;
        }

        $parts = [$make];
        if (is_numeric($this->factor) && $this->factor > 0) {
            $factor = (float) $this->factor;
            $parts[] = rtrim(rtrim(number_format($factor, 2, '.', ''), '0'), '.').'x';
            $parts[] = self::kind($factor);
        }

        $this->proposed_name = implode(' ', array_filter($parts));

        // Only put the proposed name in the name field if a new lens is being created
        if (! $this->lens) {
            $this->name = $this->proposed_name;
        }
    }

    /**
     * The kind of lens a factor suggests. Coma correctors magnify a little
     * (the Tele Vue Paracorr 1.15x), Barlow lenses start at 1.5x.
     */
    public static function kind(float $factor): string
    {
        if ($factor < 1) {
            return 'Focal reducer';
        }
        if ($factor < 1.5) {
            return 'Coma corrector';
        }

        return 'Barlow';
    }

    public function save()
    {
        if ($this->lens) {
            abort_unless($this->lens->canBeEditedBy(Auth::user()), 403);
        }

        if ($this->lens_make != 1 && $this->lens_make) {
            $make = $this->lens_make;
        } elseif ($this->lens_new_make != '') {
            if (! $this->lens_new_make) {
                return redirect()->back()->withErrors(['lens_new_make' => 'Please select a make or enter a new one']);
            }
            $make_name = $this->lens_new_make;

            // Create a new make
            $make = LensMake::firstOrCreate(['name' => trim($make_name)])->id;
        } else {
            $make = 1;
        }

        $data = $this->validate([
            'name' => 'required|min:3',
            'factor' => 'required|numeric|gt:0',
            'photo' => 'nullable|image',
            'description' => 'nullable|string',
        ]);

        $photoPath = null;
        if ($this->photo) {
            $photoPath = UploadedPhoto::store($this->photo, 'photos/lens', $this->lens);
        }

        $data['make_id'] = $make;

        if ($this->photo) {
            $data['picture'] = $photoPath;
        }

        if ($this->lens) {
            $this->lens->update($data);
            session()->flash('message', __('Lens updated successfully.'));

            // Return to /lens/{user-slug}/{lens-slug} page
            return redirect('/lens/'.$this->lens->user->slug.'/'.$this->lens->slug);
        } else {
            $data['user_id'] = Auth::id();
            $data['observer'] = Auth::user()->username;
            $lens = Lens::create($data);

            session()->flash('message', __('Lens created successfully.'));

            // Return to /lens/{user-slug}/{lens-slug} page
            return redirect('/lens/'.Auth()->user()->slug.'/'.$lens->slug);
        }

    }
}
