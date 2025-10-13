<?php

namespace App\Providers;

use App\ShareButtons\Presenters\CustomTemplateBasedPresenterMediator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Kudashevs\ShareButtons\ShareButtons;
use ReflectionClass;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Replace the ShareButtons presenter with a custom mediator to change URL encoding
        $this->app->singleton(ShareButtons::class, function ($app) {
            $options = config('share-buttons') ?? [];

            $instance = new ShareButtons($options);

            // Create custom mediator and inject it into the ShareButtons instance
            $mediator = new CustomTemplateBasedPresenterMediator($options);

            $ref = new ReflectionClass($instance);
            if ($ref->hasProperty('presenter')) {
                $prop = $ref->getProperty('presenter');
                $prop->setAccessible(true);
                $prop->setValue($instance, $mediator);
            }

            return $instance;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicitly register Livewire components that might not be discovered
        // automatically due to custom namespaces or caching issues.
        if (class_exists(Livewire::class)) {
            if (class_exists(\App\Livewire\InstrumentTable::class)) {
                Livewire::component('instrument-table', \App\Livewire\InstrumentTable::class);
            }

            if (class_exists(\App\Http\Livewire\AladinSelects::class)) {
                Livewire::component('aladin-selects', \App\Http\Livewire\AladinSelects::class);
            }

            // Additional explicit registrations for app Livewire components can be added here as needed.
        }

        Model::preventLazyLoading(! $this->app->environment('production'));
    }
}
