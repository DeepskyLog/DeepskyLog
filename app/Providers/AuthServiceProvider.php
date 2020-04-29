<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Lens' => 'App\Policies\LensPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(Gate $gate)
    {
        $this->registerPolicies();

        Blade::if(
            'admin', function () {
                if (Auth::check()) {
                    $condition = auth()->user()->isAdmin();
                } else {
                    $condition = false;
                }

                return $condition;
            }
        );

        $gate::before(
            function ($user) {
                // ADMINISTRATOR CAN DO EVERYTHING
                if ($user->isAdmin()) {
                    return true;
                }
            }
        );
    }
}
