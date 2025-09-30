<?php

namespace App\Actions\Socialstream;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback as AuthenticatesOAuthCallbackContract;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse;
use JoelButcher\Socialstream\Contracts\OAuthRegisterResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkedResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthFailedResponse;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Events\OAuthLogin;
use JoelButcher\Socialstream\Events\NewOAuthRegistration;
use JoelButcher\Socialstream\Events\OAuthProviderLinked;
use JoelButcher\Socialstream\Events\OAuthProviderLinkFailed;
use JoelButcher\Socialstream\Events\OAuthFailed;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Concerns\ConfirmsFilament;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider as CreatesUserFromProviderContract;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Session;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\MessageBag;

class AuthenticateOAuthCallback implements AuthenticatesOAuthCallbackContract
{
    use ConfirmsFilament;
    use InteractsWithComposer;

    public function __construct(
        protected StatefulGuard $guard,
        protected CreatesUserFromProvider $createsUser,
        protected CreatesConnectedAccounts $createsConnectedAccounts,
        protected UpdatesConnectedAccounts $updatesConnectedAccounts
    ) {
        //
    }

    public function authenticate(string $provider, \Laravel\Socialite\Contracts\User $providerAccount): OAuthLoginResponse|OAuthRegisterResponse|RedirectResponse|OAuthProviderLinkedResponse|OAuthProviderLinkFailedResponse|OAuthFailedResponse
    {
        if ($user = auth()->user()) {
            cache()->put("socialstream.{$user->id}:$provider.provider", $providerAccount, ttl: new \DateInterval('PT10M'));

            return redirect()->route('oauth.callback.prompt', $provider);
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        if ($account) {
            return $this->login($account->user, $account, $provider, $providerAccount);
        }

        $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

        if ($user) {
            if (! Features::authenticatesExistingUnlinkedUsers()) {
                return $this->oauthFailed(
                    error: __('An account already exists with the same email address. Please log in to connect your :provider account.', ['provider' => Providers::name($provider)]),
                    provider: $provider,
                    providerAccount: $providerAccount,
                );
            }

            return $this->login(
                user: $user,
                account: $this->createsConnectedAccounts->create(
                    user: $user,
                    provider: $provider,
                    providerUser: $providerAccount,
                ),
                provider: $provider,
                providerAccount: $providerAccount
            );
        }

        if ($this->canRegister()) {
            return $this->register($provider, $providerAccount);
        }

        $error = route('login') && session('socialstream.previous_url') === route('login')
            ? __('Account not found, please register to create an account.')
            : __('Registration is disabled.');

        return $this->oauthFailed(error: $error, provider: $provider, providerAccount: $providerAccount);
    }

    protected function register(string $provider, \Laravel\Socialite\Contracts\User $providerAccount): OAuthRegisterResponse|RedirectResponse
    {
        $user = $this->createsUser->create($provider, $providerAccount);

        return tap(
            (new Pipeline(app()))->send(request())->through([
                function ($request, $next) use ($user) {
                    // Explicit guard login here to ensure session is written
                    $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

                    return $next($request);
                },
            ])->then(fn() => app(OAuthRegisterResponse::class)),
            fn() => event(new NewOAuthRegistration($user, $provider, $providerAccount))
        );
    }

    protected function login(Authenticatable $user, mixed $account, string $provider, \Laravel\Socialite\Contracts\User $providerAccount): OAuthLoginResponse|RedirectResponse
    {
        // Update connected account
        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        // Ensure explicit guard login to persist auth into session
        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        // Run the rest of the Fortify login pipeline (PrepareAuthenticatedSession etc.)
        $response = tap(
            $this->loginPipeline(request(), $user)->then(fn() => app(OAuthLoginResponse::class)),
            fn() => event(new OAuthLogin($user, $provider, $account, $providerAccount)),
        );

        return $response;
    }

    protected function loginPipeline(Request $request, Authenticatable $user): Pipeline
    {
        if (! class_exists(Fortify::class)) {
            return (new Pipeline(app()))->send($request)->through(array_filter([
                function ($request, $next) use ($user) {
                    app(StatefulGuard::class)->loginUsingId($user->getAuthIdentifier(), Socialstream::hasRememberSessionFeatures());

                    return $next($request);
                },
                function ($request, $next) {
                    if ($request->hasSession()) {
                        $request->session()->regenerate();
                    }

                    return $next($request);
                },
            ]));
        }

        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through($this->replaceFortifyAuthPipes(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            )));
        }

        if (is_array(config('fortify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through($this->replaceFortifyAuthPipes(array_filter(
                config('fortify.pipelines.login')
            )));
        }

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? \Laravel\Fortify\Actions\CanonicalizeUsername::class : null,
            \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::twoFactorAuthentication()) ? \Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class : null,
            \JoelButcher\Socialstream\Actions\AttemptToAuthenticate::class.':'.$user->getAuthIdentifier(),
            \Laravel\Fortify\Actions\PrepareAuthenticatedSession::class,
        ]));
    }

    private function oauthFailed(string $error, string $provider, \Laravel\Socialite\Contracts\User $providerAccount): OAuthFailedResponse
    {
        event(new OAuthFailed($provider, $providerAccount));

        session()->flash('errors', (new ViewErrorBag())->put(
            'default',
            new MessageBag(['socialstream' => $error])
        ));

        return app(OAuthFailedResponse::class);
    }

    private function canRegister(): bool
    {
        if ($this->usesFilament() && $this->canRegisterUsingFilament()) {
            return true;
        }

        if (class_exists(Fortify::class) && !\Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration())) {
            return false;
        }

        $previousRoute = session('socialstream.previous_url');

        if (route('register') && $previousRoute === route('register')) {
            return true;
        }

        if (route('login') && $previousRoute === route('login')) {
            return Features::hasCreateAccountOnFirstLoginFeatures();
        }

        return Features::hasCreateAccountOnFirstLoginFeatures() && Features::hasGlobalLoginFeatures();
    }

    private function replaceFortifyAuthPipes(mixed $pipes): array
    {
        return array_map(function ($pipe) {
            if ($pipe === \Laravel\Fortify\Actions\AttemptToAuthenticate::class) {
                return \JoelButcher\Socialstream\Actions\AttemptToAuthenticate::class;
            }

            if ($pipe === \Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class) {
                return \JoelButcher\Socialstream\Actions\RedirectIfTwoFactorAuthenticatable::class;
            }

            return $pipe;
        }, $pipes);
    }
}
