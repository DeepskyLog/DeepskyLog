<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DslDropdown extends Component
{
    public const DEFAULT_ALIGN = 'right';

    public function __construct(
        public string $width = 'w-48',
        public string $height = 'max-h-60',
        public string $align = self::DEFAULT_ALIGN,
        public bool $persistent = false,
        public ?string $trigger = null
    ) {
    }

    public function render()
    {
        return view('wireui::components.dropdown');
    }

    public function getAlign(): string
    {
        $alignments = [
            'right' => 'origin-top-right right-auto',
            'left' => 'origin-top-left left-0',
            'top-right' => 'origin-top-right right-0 bottom-0',
            'top-left' => 'origin-top-left left-0 bottom-0',
        ];

        return $alignments[$this->align];
    }
}
