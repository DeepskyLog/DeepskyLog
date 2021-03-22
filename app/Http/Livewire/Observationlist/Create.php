<?php

namespace App\Http\Livewire\Observationlist;

use Livewire\Component;
use App\Models\ObservationList;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $observationList;
    public $update;
    public $name;
    public $slug;
    public $description;
    public String $origDescription = '';
    public $discoverable;
    public array $tags = [];
    public $newTag;

    protected $rules = [
        'name'               => ['required', 'min:6', 'max:100'],
        'newTag'             => ['min:3'],
    ];

    public function mount()
    {
        if ($this->observationList->exists) {
            $this->update               = true;
            $this->slug                 = $this->observationList->slug;
            $this->name                 = $this->observationList->name;
            $this->origDescription      = $this->observationList->description;
            $this->description['body']  = $this->origDescription;
            $this->discoverable         = $this->observationList->discoverable;
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
    }

    public function save()
    {
        $this->validate();

        if ($this->discoverable) {
            $disc = 1;
        } else {
            $disc = 0;
        }

        $addedTag = null;

        if ($this->newTag) {
            $addedTag = \Spatie\Tags\Tag::findOrCreate($this->newTag, 'ObservationList');
            $addedTag->setTranslation('name', 'de', $this->newTag);
            $addedTag->setTranslation('name', 'nl', $this->newTag);
            $addedTag->setTranslation('name', 'sv', $this->newTag);
            $addedTag->setTranslation('name', 'es', $this->newTag);
            $addedTag->setTranslation('name', 'fr', $this->newTag);
            $addedTag->setTranslation('name', 'en', $this->newTag);
            $addedTag->save();
        }

        if ($this->update) {
            // Update the existing observation list
            $this->observationList->update(['name' => $this->name]);
            $this->observationList->update(['description' => $this->description['body']]);
            $this->observationList->update(['discoverable' => $disc]);
            if (sizeof($this->tags) > 0) {
                $this->observationList->syncTagsWithType($this->tags, 'ObservationList');
            }
            if ($this->newTag) {
                $this->observationList->attachTag($addedTag);
            }
            laraflash(_i('Observation list %s updated', $this->name))->success();
        } else {
            // Create a new observation list
            $list = ObservationList::create(
                ['user_id'               => Auth::user()->id,
                    'name'               => $this->name,
                    'description'        => $this->description['body'],
                    'discoverable'       => $disc, ]
            );
            $this->observationList = ObservationList::where('slug', $list->slug)->first();
            if (sizeof($this->tags) > 0) {
                $this->observationList->syncTagsWithType($this->tags, 'ObservationList');
            }
            if ($this->newTag) {
                $this->observationList->attachTag($addedTag);
            }

            Auth::user()->update(['activeList' => $list->slug]);
            laraflash(_i('Observation list %s created', $list->name))->success();
        }

        // View the page with all observation lists for the user
        return redirect(route('observationList.index'));
    }

    public function render()
    {
        return view('livewire.observationlist.create');
    }
}
