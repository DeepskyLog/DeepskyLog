<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $party->name }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111;">
    <div style="max-width: 680px; margin: 0 auto;">
        <p>{{ __('Hello') }},</p>

        <p>{{ __(':name invites you to the starparty :party.', ['name' => html_entity_decode($inviter?->name ?? 'DeepskyLog'), 'party' => $party->name]) }}</p>

        <p>
            <strong>{{ __('Nights') }}:</strong> {{ $party->dateRange() }}<br>
            <strong>{{ __('Location') }}:</strong> {{ $party->location_name }}@if ($party->country) ({{ $party->country }})@endif
            @if ($party->venue)
                <br>{{ $party->venue }}
            @endif
        </p>

        @if ($party->description)
            <div style="border-top:1px solid #ddd; padding-top:10px;">
                {!! \App\Support\Html::purify($party->displayDescription(), true) !!}
            </div>
        @endif

        <p><a href="{{ $url }}">{{ __('See the starparty on DeepskyLog') }}</a></p>

        <p>{{ __('DeepskyLog is a free website where amateur astronomers record their observations. Log in or register with the link above to tell the organiser whether you come.') }}</p>

        <p style="color:#666; font-size:12px;">{{ __('You get this mail because an observer of DeepskyLog invited this address. If you do not want to come, you can ignore it.') }}</p>
    </div>
</body>
</html>
