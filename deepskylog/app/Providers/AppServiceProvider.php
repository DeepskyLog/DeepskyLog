<?php

namespace App\Providers;

use App\ShareButtons\Presenters\CustomTemplateBasedPresenterMediator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Kudashevs\ShareButtons\ShareButtons;
use ReflectionClass;
use Livewire\Livewire;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;

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
        // Auto-register Livewire components from both App\Livewire and App\Http\Livewire
        // This handles mixed namespaces in the project and avoids 'Unable to find component' errors
        // when config or directory structure changes.
        if (class_exists(Livewire::class)) {
            $namespaces = [
                'App\\Livewire' => app_path('Livewire'),
                'App\\Http\\Livewire' => app_path('Http/Livewire'),
            ];

            foreach ($namespaces as $baseNamespace => $dir) {
                if (! File::exists($dir)) {
                    continue;
                }

                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    $relative = str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $relative = str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
                    $class = $baseNamespace . '\\' . preg_replace('/\\.php$/', '', $relative);

                    if (! class_exists($class)) {
                        continue;
                    }

                    // Only register classes that extend Livewire\Component
                    if (! is_subclass_of($class, LivewireComponent::class)) {
                        continue;
                    }

                    // Generate kebab name from class short name including nested segments
                    $short = str_replace($baseNamespace . '\\', '', $class);
                    $kebab = Str::kebab(str_replace('\\', '-', $short));

                    Livewire::component($kebab, $class);
                }
            }
        }

        Model::preventLazyLoading(! $this->app->environment('production'));
    }
}
