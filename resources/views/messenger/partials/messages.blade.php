<div class="card">
    <div class="card-body">
        <img width="75" style="border-radius: 20%" class="float-right" src="/users/{{ $message->user->id }}/getImage">
        <h5 class="media-heading">{{ $message->user->name }}</h5>
        <p>{{ $message->body }}</p>
        <div class="text-muted">
            <small>{{ _i("Posted") }} {{ $message->created_at->diffForHumans() }}</small>
        </div>
    </div>
</div>
<br />
