
@if(sizeof(laraflash()->toArray()) > 0)
    <br />
    <div class="container">
        {!! laraflash()->render() !!}
    </div>
@endif
