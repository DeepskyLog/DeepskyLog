<?php

namespace App\Providers;

use App\ShareButtons\Presenters\CustomTemplateBasedPresenterMediator;
use App\Support\SchemaColumns;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Kudashevs\ShareButtons\ShareButtons;
use ReflectionClass;
use Livewire\Livewire;
use Illuminate\Support\Facades\Event;
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
        // Data of the observation cards, kept for one request (or queue job).
        $this->app->scoped(\App\Services\Observations\ObservationCardData::class);

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
            foreach ($this->livewireComponentMap() as $name => $class) {
                Livewire::component($name, $class);
            }
        }

        // A migration can add or drop columns (the atlas columns of `objects`).
        Event::listen(MigrationsEnded::class, fn () => SchemaColumns::forget(['objects']));

        Model::preventLazyLoading(! $this->app->environment('production'));
    }

    /**
     * The kebab names and classes of the Livewire components, read from
     * bootstrap/cache/livewire-components.php. The file also holds the
     * modification times of the component directories: adding, removing or
     * renaming a component changes the time of its directory, so the map is
     * rebuilt after a deploy without clearing anything.
     *
     * @return array<string, class-string>
     */
    private function livewireComponentMap(): array
    {
        $manifest = $this->app->bootstrapPath('cache/livewire-components.php');

        if (is_file($manifest)) {
            $cached = @include $manifest;
            if (is_array($cached) && isset($cached['directories'], $cached['components'])) {
                $fresh = true;
                foreach ($cached['directories'] as $dir => $mtime) {
                    if (@filemtime($dir) !== $mtime) {
                        $fresh = false;
                        break;
                    }
                }
                if ($fresh) {
                    return $cached['components'];
                }
            }
        }

        $namespaces = [
            'App\\Livewire' => app_path('Livewire'),
            'App\\Http\\Livewire' => app_path('Http/Livewire'),
        ];

        $components = [];
        $directories = [];

        foreach ($namespaces as $baseNamespace => $dir) {
            if (! File::exists($dir)) {
                continue;
            }

            $directories[$dir] = filemtime($dir);
            foreach ((new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST)) as $item) {
                if ($item->isDir()) {
                    $directories[$item->getPathname()] = $item->getMTime();
                }
            }

            foreach (File::allFiles($dir) as $file) {
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
                $components[Str::kebab(str_replace('\\', '-', $short))] = $class;
            }
        }

        // Write to a temporary file first, so a concurrent request never reads half a file.
        $tmp = $manifest . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, '<?php return ' . var_export(['directories' => $directories, 'components' => $components], true) . ';' . PHP_EOL) !== false) {
            if (! @rename($tmp, $manifest)) {
                @unlink($tmp);
            }
        }

        return $components;
    }
}
