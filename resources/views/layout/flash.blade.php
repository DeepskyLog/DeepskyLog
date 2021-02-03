@if(sizeof(laraflash()->toArray()) > 0)
@foreach (laraflash()->toArray() as $flash)
<p class="alert alert-{{ $flash['type'] }}">
    {!! $flash['content'] !!}
</p>
@endforeach
@endif
