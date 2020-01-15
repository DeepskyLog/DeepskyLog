@extends('layout.master')

@section('title', _i('Message'))

@section('content')
    <div class="col-md-6">
        <h1>{{ $thread->subject }}</h1>

        {{  _i("Conversation between") }} {{ $thread->participantsString() }}
        @each('messenger.partials.messages', $thread->messages->reverse(), 'message')

        @include('messenger.partials.form-message')
    </div>
@endsection
