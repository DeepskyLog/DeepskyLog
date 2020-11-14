<div class="card">
    <div class="card-body">
        <img width="75" style="border-radius: 20%" class="float-right" src="/users/{{ $message->user->slug }}/getImage">
        <h5 class="media-heading"><a href="/users/{{ $message->user->slug }}">{{ $message->user->name }}</a></h5>
        <p>{!! nl2br($message->body) !!}</p>
        <div class="text-muted">
            <small>{{ _i("Posted") }} {{ $message->created_at->diffForHumans() }}</small>
        </div>
    </div>
</div>
<br />
