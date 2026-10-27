<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Logging in to the DeepskyLog GoTo app (project deepskylog-goto) with Google or GitHub.
 * The providers refuse to log in inside an app's web view, so the app opens
 * start() in the browser: there the observer logs in with the provider (or
 * already is logged in), and the page hands a one-time code to the app
 * through a dslgoto://login link. The app then opens redeem() in its web
 * view, which logs the observer in there.
 *
 * As in OAuth for apps (RFC 7636, PKCE), the app sends the SHA-256 of a secret
 * of its own (challenge) and redeems the code with the secret (verifier), so
 * another app that catches the dslgoto:// link cannot use the code.
 */
class AppLoginController extends Controller
{
    /** How long the code of a login is valid. */
    public const CODE_MINUTES = 5;

    public function start(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:'.implode(',', config('socialstream.providers', []))],
            'challenge' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{43}$/'],
        ]);

        // Socialstream returns to this page after the login
        if (! Auth::check()) {
            return redirect()->guest(route('oauth.redirect', $validated['provider']));
        }

        $code = Str::random(64);
        Cache::put(self::key($code), ['user' => Auth::id(), 'challenge' => $validated['challenge']], now()->addMinutes(self::CODE_MINUTES));

        return view('auth.app-login', ['link' => 'dslgoto://login?'.http_build_query(['code' => $code], '', '&', PHP_QUERY_RFC3986)]);
    }

    public function redeem(Request $request): RedirectResponse
    {
        $code = (string) $request->query('code', '');
        $verifier = (string) $request->query('verifier', '');
        $login = $code !== '' ? Cache::pull(self::key($code)) : null;

        $user = null;
        if (is_array($login) && $verifier !== '' && hash_equals($login['challenge'], self::challengeOf($verifier))) {
            $user = User::find($login['user']);
        }
        if ($user === null) {
            return redirect()->route('login')->withErrors(['app' => __('Logging in to the app failed: try again.')]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('observing-lists.eyepiece');
    }

    /** The challenge of a verifier: its SHA-256, base64url without padding. */
    public static function challengeOf(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    private static function key(string $code): string
    {
        return 'app_login:'.hash('sha256', $code);
    }
}
