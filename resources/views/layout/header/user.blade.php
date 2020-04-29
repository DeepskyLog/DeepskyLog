
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @if (count(Auth::user()->getMedia('observer')) > 0)
            <img height="24px" style="border-radius: 20%" src="{{ Auth::user()->getMedia('observer')->first()->getUrl('thumb') }}">
        @endif
        {{ Auth::user()->name }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <a class="dropdown-item" href="/users/{{ Auth::user()->id }}">{{ _i('Details') }}</a>
        <a class="dropdown-item" href="/users/{{ Auth::user()->id }}/settings">{{ _i('Settings') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="{{ route('logout') }}"
            onclick="event.preventDefault();
            document.getElementById('logout-form').submit();">
            {{ _i('Log out') }}
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>
</li>
