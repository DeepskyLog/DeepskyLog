<?php

namespace App\Actions\Socialstream;

use App\Support\OAuthEmail;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use JoelButcher\Socialstream\Concerns\ConfirmsFilament;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback as AuthenticatesOAuthCallbackContract;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\OAuthFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkedResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthRegisterResponse;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Events\NewOAuthRegistration;
use JoelButcher\Socialstream\Events\OAuthFailed;
use JoelButcher\Socialstream\Events\OAuthLogin;
use JoelButcher\Socialstream\Events\OAuthProviderLinked;
use JoelButcher\Socialstream\Events\OAuthProviderLinkFailed;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

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

    public function authenticate(string $provider, ProviderUser $providerAccount): OAuthLoginResponse|OAuthRegisterResponse|RedirectResponse|OAuthFailedResponse
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
            // Linking logs the provider user into this account, so both sides
            // must have proven they own the email: otherwise whoever registered
            // the address first shares the account with its owner.
            if (! Features::authenticatesExistingUnlinkedUsers()
                || ! $user->hasVerifiedEmail()
                || ! OAuthEmail::isVerifiedByProvider($provider, $providerAccount)) {
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

    /**
     * Link the provider account to the logged-in user, after they confirmed it on the prompt.
     */
    public function link(Authenticatable $user, string $provider, ProviderUser $providerAccount): OAuthProviderLinkedResponse|OAuthProviderLinkFailedResponse
    {
        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        if ($account && $user->getAuthIdentifier() !== $account->user_id) {
            event(new OAuthProviderLinkFailed($user, $provider, $account, $providerAccount));

            $this->flashBanner(__('It looks like this :provider account is used by another user. Please log in.', ['provider' => Providers::name($provider)]), 'danger');

            return app(OAuthProviderLinkFailedResponse::class);
        }

        if (! $account) {
            $account = $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
        }

        event(new OAuthProviderLinked($user, $provider, $account, $providerAccount));

        $this->flashBanner(__('You have successfully linked your :provider account.', ['provider' => Providers::name($provider)]), 'success');

        return app(OAuthProviderLinkedResponse::class);
    }

    protected function register(string $provider, ProviderUser $providerAccount): OAuthRegisterResponse
    {
        $user = $this->createsUser->create($provider, $providerAccount);

        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        event(new NewOAuthRegistration($user, $provider, $providerAccount));

        return app(OAuthRegisterResponse::class);
    }

    /**
     * Log the user in, or, when they have two-factor authentication enabled, send them to the
     * challenge first: the provider only proves the first factor. Fortify's challenge
     * controller finishes the login from the login.id in the session.
     */
    protected function login(Authenticatable $user, mixed $account, string $provider, ProviderUser $providerAccount): OAuthLoginResponse|RedirectResponse
    {
        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        $request = request();

        if ($this->requiresTwoFactorChallenge($user)) {
            session()->put([
                'login.id' => $user->getAuthIdentifier(),
                'login.remember' => Socialstream::hasRememberSessionFeatures(),
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('two-factor.login');
        }

        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        event(new OAuthLogin($user, $provider, $account, $providerAccount));

        return app(OAuthLoginResponse::class);
    }

    /**
     * The same test as Fortify's RedirectIfTwoFactorAuthenticatable.
     */
    private function requiresTwoFactorChallenge(Authenticatable $user): bool
    {
        if (! FortifyFeatures::enabled(FortifyFeatures::twoFactorAuthentication())
            || ! in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))
            || ! $user->two_factor_secret) {
            return false;
        }

        return ! Fortify::confirmsTwoFactorAuthentication() || ! is_null($user->two_factor_confirmed_at);
    }

    private function oauthFailed(string $error, string $provider, ProviderUser $providerAccount): OAuthFailedResponse
    {
        event(new OAuthFailed($provider, $providerAccount));

        session()->flash('errors', (new ViewErrorBag())->put(
            'default',
            new MessageBag(['socialstream' => $error])
        ));

        return app(OAuthFailedResponse::class);
    }

    private function flashBanner(string $message, string $style): void
    {
        if (class_exists(Jetstream::class)) {
            Session::flash('flash.banner', $message);
            Session::flash('flash.bannerStyle', $style);

            return;
        }

        $style === 'success'
            ? Session::flash('status', $message)
            : Session::flash('errors', (new ViewErrorBag())->put('default', new MessageBag(['socialstream' => $message])));
    }

    private function canRegister(): bool
    {
        if ($this->usesFilament() && $this->canRegisterUsingFilament()) {
            return true;
        }

        if (class_exists(Fortify::class) && ! FortifyFeatures::enabled(FortifyFeatures::registration())) {
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
}
