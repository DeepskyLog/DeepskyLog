<?php

namespace App\ShareButtons\Presenters;

use Kudashevs\ShareButtons\Presenters\TemplateBasedPresenterMediator as BaseMediator;
use Kudashevs\ShareButtons\Templaters\Templater;
use Kudashevs\ShareButtons\Presenters\TemplateBasedBlockPresenter;
use Kudashevs\ShareButtons\Presenters\TemplateBasedElementPresenter;
use Kudashevs\ShareButtons\Templaters\SimpleColonTemplater;
use Kudashevs\ShareButtons\Exceptions\InvalidOptionValue;

class CustomTemplateBasedPresenterMediator extends BaseMediator
{
    /**
     * We reimplement only the mediator init so we can use our CustomTemplateBasedUrlPresenter
     * while delegating the rest to the base implementation.
     *
     * @param  array{templater?: class-string, url_templater?: class-string}  $options
     * @throws InvalidOptionValue
     */
    protected function initMediator(array $options = []): void
    {
        $this->blockPresenter = new TemplateBasedBlockPresenter;

        $urlTemplaterClass = $options['url_templater'] ?? self::DEFAULT_TEMPLATER_CLASS;
        $urlTemplaterInstance = $this->createTemplater($urlTemplaterClass);

        // Use our custom URL presenter
        $this->urlPresenter = new \App\ShareButtons\Presenters\CustomTemplateBasedUrlPresenter($urlTemplaterInstance);

        $templaterClass = $options['templater'] ?? self::DEFAULT_TEMPLATER_CLASS;
        $templaterInstance = $this->createTemplater($templaterClass);
        $this->elementPresenter = new TemplateBasedElementPresenter($templaterInstance);
    }
}
