@extends('layout.master')

@section('title', _i('New message'))

@section('content')
@if (gettype($users) != 'string')
<h1>{{ _i("Create a new message") }}</h1>
@else
<h1>{{ _i("Create a new message for all users") }}</h1>
@endif

<form action="{{ route('messages.store') }}" method="post">
    {{ csrf_field() }}
    <div class="col-md-6">
        <!-- Subject Form Input -->
        <div class="form-group">
            <label class="control-label">{{ _i("Subject") }}</label>
            <input type="text" class="form-control" name="subject" placeholder="Subject" value="{{ old('subject') }}">
        </div>

        <!-- Message Form Input -->
        <div class="form-group">
            <label class="control-label">{{ _i("Message") }}</label>
            <textarea id="message" name="message" class="form-control">{{ old('message') }}</textarea>
        </div>

        @if (gettype($users) != 'string')
        {{ _i("Participants in the conversation") }}
        @if($users->count() > 0)
        @foreach($users as $list)
        @php
        $array[$list['id']]=$list['name'];
        @endphp
        @endforeach

        <x-input.selectmultiple prettyname="modelprettyname" :options="$array" name=recipients[] />

        @endif
        @else
        <input type="hidden" name="recipients[]" value="All">
        @endif

        <br /><br />
        <!-- Submit Form Input -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary form-control">{{ _i("Submit") }}</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')

@endpush
