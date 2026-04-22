<?php

namespace App\Providers;

use App\Models\ConnectedAccount;
use App\Models\DeepskyObject;
use App\Models\ObservingList;
use App\Models\Team;
use App\Models\User;
use App\Policies\ConnectedAccountPolicy;
use App\Policies\DeepskyObjectPolicy;
use App\Policies\ObservingListPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        ConnectedAccount::class => ConnectedAccountPolicy::class,
        User::class => UserPolicy::class,
        DeepskyObject::class => DeepskyObjectPolicy::class,
        ObservingList::class => ObservingListPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
