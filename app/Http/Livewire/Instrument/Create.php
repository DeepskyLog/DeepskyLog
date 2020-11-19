<?php

namespace App\Http\Livewire\Instrument;

use Livewire\Component;
use App\Models\Instrument;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibraryPro\Rules\Concerns\ValidatesMedia;
use Spatie\MediaLibraryPro\Http\Livewire\Concerns\WithMedia;

class Create extends Component
{
    use WithFileUploads;
    use ValidatesMedia;
    use WithMedia;

    public $update;
    public $instrument;
    public $sel_instrument;
    public $name;
    public $type;
    public $diameter;
    public $fd;
    public $focalLength;
    public $fixedMagnification;
    public $media;
    public $mediaComponentNames = ['media'];

    protected $rules = [
        'name'               => ['required', 'min:6'],
        'type'               => 'required',
        'diameter'           => 'required|numeric|gt:0',
        'fd'                 => 'nullable|gte:1|required_without:fixedMagnification',
        'focalLength'        => 'nullable|gte:1|required_without:fixedMagnification',
        'fixedMagnification' => 'nullable|gte:1|required_without:fd',
    ];

    public function mount()
    {
        if ($this->instrument->exists) {
            $this->update                   = true;
            $this->name                     = $this->instrument->name;
            $this->type                     = $this->instrument->type;
            if (Auth::user()->showInches) {
                $this->diameter                 = round($this->instrument->diameter / 25.4);
            } else {
                $this->diameter                 = $this->instrument->diameter;
            }
            $this->fd                       = $this->instrument->fd;
            if ($this->fd != 0) {
                $this->focalLength              = round($this->fd * $this->diameter);
            }
            $this->fixedMagnification       = $this->instrument->fixedMagnification;
        } else {
            $this->update      = false;
        }
    }

    /**
     * Real time validation.
     *
     * @param mixed $propertyName The name of the property
     *
     * @return void
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        if ($propertyName == 'sel_instrument') {
            $this->instrument    = \App\Models\Instrument::where('id', $this->sel_instrument)->first();
            $this->name          = $this->instrument->name;
            $this->type          = $this->instrument->type;
            if (Auth::user()->showInches) {
                $this->diameter      = round($this->instrument->diameter / 25.4, 2);
            } else {
                $this->diameter      = $this->instrument->diameter;
            }
            $this->fd                       = $this->instrument->fd;
            $this->focalLength              = round($this->fd * $this->diameter);
            $this->fixedMagnification       = $this->instrument->fixedMagnification;
        }

        if ($propertyName == 'diameter') {
            $this->focalLength = round($this->fd * $this->diameter);
        }
        if ($propertyName == 'fd') {
            $this->focalLength        = round($this->fd * $this->diameter);
            $this->fixedMagnification = '';
        }
        if ($propertyName == 'focalLength') {
            $this->fd                 = round($this->focalLength / $this->diameter, 2);
            $this->fixedMagnification = '';
        }
        if ($propertyName == 'fixedMagnification') {
            $this->fd          = '';
            $this->focalLength = '';
        }
        if ($this->focalLength == 0) {
            $this->focalLength = '';
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->fd == '') {
            $fd = null;
        } else {
            $fd = $this->fd;
        }

        if ($this->fixedMagnification == '') {
            $fixedMagnification =  null;
        } else {
            $fixedMagnification = $this->fixedMagnification;
        }

        if (Auth::user()->showInches) {
            $diameter      = $this->diameter * 25.4;
        } else {
            $diameter = $this->diameter;
        }

        if ($this->update) {
            // Update the existing instrument
            $this->instrument->update(['name' => $this->name]);
            $this->instrument->update(['type' => $this->type]);
            $this->instrument->update(['diameter' => $diameter]);
            $this->instrument->update(['fd' => $fd]);
            $this->instrument->update(['fixedMagnification' => $fixedMagnification]);
            $instrument = $this->instrument;
            laraflash(_i('Instrument %s updated', $instrument->name))->success();
        } else {
            // Create a new instrument
            $instrument = Instrument::create(
                ['user_id'               => Auth::user()->id,
                    'name'               => $this->name,
                    'type'               => $this->type,
                    'diameter'           => $diameter,
                    'fd'                 => $fd,
                    'fixedMagnification' => $fixedMagnification,
                    'active'             => 1, ]
            );
            laraflash(_i('Instrument %s created', $instrument->name))->success();
        }

        // Upload of the image
        if ($this->media) {
            if (Instrument::find($instrument->id)->getFirstMedia('instrument') != null) {
                // First remove the current image
                Instrument::find($instrument->id)
                                ->getFirstMedia('instrument')
                                ->delete();
            }
            // Update the picture
            Instrument::find($instrument->id)
                ->addFromMediaLibraryRequest($this->media)
                ->toMediaCollection('instrument');
        }

        // View the page with all instruments for the user
        return redirect(route('instrument.index'));
    }

    public function render()
    {
        return view('livewire.instrument.create');
    }
}
