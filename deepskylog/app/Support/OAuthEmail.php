<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * Email addresses of social login users.
 *
 * A provider that gives no email gets a generated one,
 * {id}@{provider}.{app.domain}. Provider ids are public (GitHub's are in
 * every profile), so nobody may register or change to an address on those
 * domains: the provider user would otherwise find an account waiting for them.
 */
class OAuthEmail
{
    /**
     * The generated email of a provider user without one.
     */
    public static function generate(string $provider, string|int $id): string
    {
        return "{$id}@{$provider}.".config('app.domain');
    }

    /**
     * Whether the email is on the domain of the generated emails, a
     * subdomain of app.domain.
     */
    public static function isGenerated(?string $email): bool
    {
        $domain = Str::lower(Str::afterLast((string) $email, '@'));

        return Str::endsWith($domain, '.'.Str::lower(config('app.domain')));
    }

    /**
     * Whether the provider vouches that the user owns their email.
     *
     * Google says so in the email_verified claim. Socialite's GitHub driver
     * only returns the primary email when it is verified. Other providers
     * are not trusted.
     */
    public static function isVerifiedByProvider(string $provider, ProviderUser $user): bool
    {
        $email = $user->getEmail();

        if (! $email || static::isGenerated($email)) {
            return false;
        }

        $raw = method_exists($user, 'getRaw') ? $user->getRaw() : [];

        return match ($provider) {
            'google' => filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'github' => true,
            default => false,
        };
    }

    /**
     * A validation rule refusing the generated emails' domains, except for
     * the address the user already has.
     */
    public static function notGeneratedRule(?string $current = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($current) {
            if ($value !== $current && static::isGenerated((string) $value)) {
                $fail(__('This email address cannot be used.'));
            }
        };
    }
}
