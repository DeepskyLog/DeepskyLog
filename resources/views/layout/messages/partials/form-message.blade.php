<h2>{{ _i("Add a new message") }}</h2>
<form action="{{ route('messages.update', $thread->id) }}" method="post">
    {{ method_field('put') }}
    {{ csrf_field() }}

    <!-- Message Form Input -->
    <div class="form-group">
        <textarea id="message" name="message" class="form-control">{{ old('message') }}</textarea>
    </div>

    @if($users->count() > 0)
    {{ _i("Add extra participants to this message") }}
    @foreach($users as $list)
    @php
    $array[$list['id']]=$list['name'];
    @endphp
    @endforeach

    <x-input.selectmultiple prettyname="modelprettyname" :options="$array" name=recipients[] />
    @endif

    <br /><br />
    <!-- Submit Form Input -->
    <div class="form-group">
        <button type="submit" class="btn btn-primary form-control">{{ _i("Submit") }}</button>
    </div>
</form>
