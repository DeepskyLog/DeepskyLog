<?php

namespace App\View\Components\DslDropdown;

use Illuminate\View\Component;

class DslDropdownHeader extends Component
{
    public bool $separator;

    public string $classes;

    public ?string $label;

    public function __construct(bool $separator = false, ?string $label = null)
    {
        $this->separator = $separator;
        $this->label = $label;
        $this->classes = $this->getClasses();
    }

    protected function getClasses(): string
    {
        return 'block pl-2 pt-2 pb-1 text-xs text-secondary-600 sticky top-0 bg-white
                dark:bg-secondary-800 dark:text-secondary-400';
    }

    public function render()
    {
        return view('wireui::components.dropdown.header');
    }
}
