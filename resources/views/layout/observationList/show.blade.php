@extends("layout.master")

@section('title')
{{ $observationList->name }}
@endsection

@section('content')
<h2>
    {{ $observationList->name }}
    @if ($media)
    <a href={{ $media->getUrl() }} data-lity>
        <img class="float-right" style="border-radius: 20%" src="{{ $media->getUrl('thumb') }}"
            alt="{{ $observationList->user->name }}">
    </a>
    @endif
</h2>

{{ _i('Observation list by ') }} <a
    href="/users/{{ $observationList->user->slug }}">{{ $observationList->user->name }}</a>
<br /><br />
<h4>{{ _i('Description') }}</h4>
@if ($observationList->description)
<div class="col-11">
    <div class="card">
        <div class="card-body trix-content">
            {!! $observationList->description !!}
        </div>
    </div>
</div>
@else
{{ _i('No description') }}
<br />
@endif
<br />
<h4>{{ _i('Tags') }}</h4>
@if ($observationList->tags()->count() > 0)
@foreach ($observationList->tags()->get() as $tag)
<h5 class="inline"><span class="badge badge-success">{{ $tag->name }}</span>&nbsp;</h5>
@endforeach
@else
{{ _i('No tags') }}
@endif
<br />

<livewire:observationlist.show :observationList="$observationList" />

@auth
@if (Auth::user()->id == $observationList->user_id || Auth::user()->isAdmin())
<br />
<a href="{{ route('observationList.edit', $observationList->slug) }}">
    <button type="button" class="btn btn-sm btn-primary">
        {{ _i('Edit') }} {{  $observationList->name }}
    </button>
</a>
<br />
@endif

@endauth
@endsection
