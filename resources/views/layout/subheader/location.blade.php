<li class="nav-item">
    <span class="navbar-text">
        <form role="form" action="/users/{{ Auth::id() }}/settings" method="POST">
            @csrf
            @method('PATCH')

            <select class="form-control selection" name="stdlocation" id="defaultLocation">
                {!! App\Location::getLocationOptions() !!}
            </select>
        </form>
    </span>
</li>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
