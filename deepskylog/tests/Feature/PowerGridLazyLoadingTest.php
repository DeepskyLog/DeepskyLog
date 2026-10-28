<?php

use App\Livewire\LensTable;
use Livewire\EventBus;
use Livewire\Exceptions\MethodNotFoundException;

// PowerGrid 7 refuses every "__" method of its tables from the browser,
// also Livewire's __lazyLoad of the tables with #[Lazy] (App\Support\PowerGridLazyLoading).
function powerGridCallListeners(): array
{
    $listeners = \Livewire\invade(app(EventBus::class))->listeners['call'] ?? [];

    return array_values(array_filter($listeners, function ($listener) {
        $scope = (new ReflectionFunction($listener))->getClosureScopeClass()?->getName();

        return $scope === App\Support\PowerGridLazyLoading::class;
    }));
}

it('lets Livewire lazy load the PowerGrid tables', function () {
    $listeners = powerGridCallListeners();

    expect($listeners)->toHaveCount(1)
        ->and($listeners[0](new LensTable(), '__lazyLoad'))->toBeNull();
});

it('still refuses the other server-only methods of the PowerGrid tables', function () {
    powerGridCallListeners()[0](new LensTable(), '__construct');
})->throws(MethodNotFoundException::class);
