<?php

namespace App\Support;

use Livewire\EventBus;
use PowerComponents\LivewirePowerGrid\Providers\PowerGridServiceProvider;
use ReflectionFunction;

/**
 * Lets the PowerGrid tables with Livewire's #[Lazy] load.
 *
 * PowerGrid 7 refuses every call from the browser to a method of a table
 * that starts with "__", except __dispatch, so the __lazyLoad that Livewire
 * sends to load a lazy component fails with a 500 and the table never shows.
 * Livewire checks that request itself (its checksum), so the listener of
 * PowerGrid's service provider is wrapped to let these two through, and keeps
 * refusing the other methods.
 */
class PowerGridLazyLoading
{
    private const LIVEWIRE_METHODS = ['__lazyLoad', '__lazyLoadIsland'];

    public static function allow(): void
    {
        if (! class_exists(PowerGridServiceProvider::class) || ! app()->bound(EventBus::class)) {
            return;
        }

        $bus = \Livewire\invade(app(EventBus::class));
        $listeners = $bus->listeners;

        foreach ($listeners['call'] ?? [] as $index => $listener) {
            if (! $listener instanceof \Closure
                || (new ReflectionFunction($listener))->getClosureScopeClass()?->getName() !== PowerGridServiceProvider::class) {
                continue;
            }

            $listeners['call'][$index] = function (mixed $component, string $method, ...$rest) use ($listener) {
                if (in_array($method, self::LIVEWIRE_METHODS, true)) {
                    return null;
                }

                return $listener($component, $method, ...$rest);
            };
        }

        $bus->listeners = $listeners;
    }
}
