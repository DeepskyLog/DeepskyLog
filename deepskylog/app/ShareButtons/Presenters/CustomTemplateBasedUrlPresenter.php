<?php

namespace App\ShareButtons\Presenters;

use Kudashevs\ShareButtons\Presenters\TemplateBasedUrlPresenter as BasePresenter;
use Kudashevs\ShareButtons\Templaters\Templater;

class CustomTemplateBasedUrlPresenter extends BasePresenter
{
    public function __construct(Templater $templater)
    {
        parent::__construct($templater);
    }

    /**
     * Override encoding to use rawurlencode so spaces are encoded as %20.
     *
     * @param  array<string, string>  $replacements
     * @return array<string, string>
     */
    protected function encodeReplacements(array $replacements): array
    {
        return array_map(function (string $value): string {
            return $this->isRaw()
                ? (string) $value
                : rawurlencode($value);
        }, $replacements);
    }
}
