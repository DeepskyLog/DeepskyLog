<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $subject ?? __('(no subject)') }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111;">
    <div style="max-width: 680px; margin: 0 auto;">
        <p>{{ __('Hello') }}{{ $recipientName ? ' '.$recipientName : '' }},</p>

        <p>{!! __('You have received a new message from :sender.', ['sender' => $sender]) !!}</p>

        <h3>{{ $subject ?? __('(no subject)') }}</h3>

        <div style="border-top:1px solid #ddd; padding-top:10px;">
            {!! $messageHtml !!}
        </div>

    <p style="color:#666; font-size:12px;">{{ __('This email was sent from DeepskyLog notifications.') }}</p>
    <p style="color:#666; font-size:12px;">{{ __('Please note: replying to this email will not send a reply to the sender. To reply, please use the DeepskyLog application message interface.') }}</p>
    <p style="color:#666; font-size:12px;">{!! __('You can disable these notification emails in your :link.', ['link' => '<a href="'.url('/user/profile').'">'.__('profile settings').'</a>']) !!}</p>
    </div>
</body>
</html>
