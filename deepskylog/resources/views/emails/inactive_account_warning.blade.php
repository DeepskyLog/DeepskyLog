<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ __('Your DeepskyLog account will be deleted on :date', ['date' => $deletionDate->translatedFormat('j F Y')]) }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111;">
    <div style="max-width: 680px; margin: 0 auto;">
        <p>{{ __('Hello') }} {{ html_entity_decode($user->name) }},</p>

        <p>{{ __('You registered on DeepskyLog on :registered with the username :username, but you have not recorded any information since: no observations, sessions, equipment, locations or observing lists.', ['registered' => $user->created_at->translatedFormat('j F Y'), 'username' => html_entity_decode($user->username)]) }}</p>

        <p>{{ __('As described in our privacy policy, accounts that have not recorded any information within 24 months after registration are deleted, together with their personal information. Your account will be deleted on :date.', ['date' => $deletionDate->translatedFormat('j F Y')]) }}</p>

        <p>{!! __('To keep your account, :login and record something, for example an observation, an instrument or a location.', ['login' => '<a href="'.e(route('login')).'">'.e(__('log in')).'</a>']) !!}</p>

        <p>{{ __('If you no longer want to use DeepskyLog, you do not have to do anything.') }}</p>

        <p style="color:#666; font-size:12px;">{!! __('In case of questions, contact us at :email.', ['email' => '<a href="mailto:deepskylog@groups.io">deepskylog@groups.io</a>']) !!}</p>
    </div>
</body>
</html>
