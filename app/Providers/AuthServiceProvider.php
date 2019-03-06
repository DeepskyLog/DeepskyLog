<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //'App\Lens' => 'App\Policies\LensPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(Gate $gate)
    {
        $this->registerPolicies();

        $gate::before(
            function ($user) {
                // TODO: ADMINISTRATOR CAN DO EVERYTHING
                return $user->isAdmin();

                // Or
                return $user->role() == 'admin';
            }
        );
    }
}
