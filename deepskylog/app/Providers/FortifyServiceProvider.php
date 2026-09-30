<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Support\LegacyPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            // Lower-cased: the email and username columns compare case-insensitively,
            // so "Jane" and "jane" are the same account and share a bucket.
            $login = Str::lower(trim((string) $request->email));

            return [
                Limit::perMinute(5)->by('login:'.$login.'|'.$request->ip()),
                // Guessing one account's password from many addresses.
                Limit::perHour(30)->by('login-account:'.$login),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::authenticateUsing(function (Request $request) {
            // where('username', null) would match every user without a username.
            if (! is_string($request->email) || trim($request->email) === '') {
                return null;
            }

            // Check for the correct user.  Use the mail address or the user id.
            $validator = Validator::make(['email' => $request->email], [
                'email' => 'required|email',
            ]);

            if ($validator->passes()) {
                $user = User::where('email', $request->email)->first();
            } else {
                $user = User::where('username', $request->email)->first();
            }

            if (! $user) {
                return null;
            }

            if (! LegacyPassword::isMd5($user->password) && Hash::check((string) $request->password, (string) $user->password)) {
                return $user;
            }

            // The MD5 password of the old DeepskyLog, plain or wrapped by users:rehash-md5-passwords.
            if (LegacyPassword::check($request->password, $user->password)) {
                // Update to the new, more secure password.
                $user->password = Hash::make($request->password);
                $user->save();

                return $user;
            }
        });
    }
}
