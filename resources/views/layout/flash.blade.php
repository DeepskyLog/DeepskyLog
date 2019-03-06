@if(Session::has('flash_message'))
    <div class="container">
        <div class="alert alert-success" role="alert">
            <em> {!! session('flash_message') !!}</em>
        </div>
    </div>
@endif
