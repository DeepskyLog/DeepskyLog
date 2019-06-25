@component('mail::message')

{{  _i('You received the following DeepskyLog message from ') }}{{ $author }}:

---

# {{ $subject }}


{{ $message }}

---

{{ _i('The conversation is between ') }}{{ $participants }}

@component('mail::button', ['url' => \URL::to('/messages/'.$id)])
{{ _i('Answer message in DeepskyLog') }}
@endcomponent


{{  _i('Clear nights') }},<br>
{{ config('app.name') }}
@endcomponent
