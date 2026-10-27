{{-- The end of a login for the DeepskyLog GoTo app in the browser
     (AppLoginController::start): the dslgoto://login link hands the one-time
     code to the app. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex" />
    <title>{{ __('Back to the app') }} · {{ config('app.name', 'DeepskyLog') }}</title>
    <style>
        body { margin: 0; padding: 2rem 1rem; background: #000; color: #d1d5db; font: 16px/1.4 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; text-align: center; }
        a.button { display: inline-block; margin-top: 1rem; padding: 1rem 1.5rem; border-radius: .75rem; background: #b91c1c; color: #fff; font-size: 1.25rem; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <p>{{ __('You are logged in. Return to the DeepskyLog GoTo app to continue.') }}</p>
    <a class="button" href="{{ $link }}">{{ __('Back to the app') }}</a>
    <script>window.location.href = @json($link);</script>
</body>
</html>
