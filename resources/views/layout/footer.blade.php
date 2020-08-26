<nav class="navbar navbar-expand-lg navbar-light bg-light border-top border-bottom">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarFooter" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

        <div class="collapse navbar-collapse" id="navbarFooter">
        <span class="navbar-text">
            ©2004 - {{ now()->year }} - DeepskyLog
            <a href="https://github.com/DeepskyLog/DeepskyLog/wiki/What's-New-in-DeepskyLog">
                {{ config('app.dslversion') }}
            </a> - {{ _i('Database based on Eye&Telescope and Tycho2+ & USNO-UCAC3') }} -
            <a href='/privacy'>
                {{ _i('Privacy Policy') }}
            </a>
        </span>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="https://github.com/DeepskyLog/DeepskyLog/fork" rel="external">
                    <img width="36" height="36" src="/img/GitHub-Mark-32px.png">
                </a>&nbsp;&nbsp;&nbsp;
            </li>
            <li class="nav-item">
                <a href="https://github.com/openastronomylog/openastronomylog" rel="external">
                    <img width="36" height="36" src="{{ asset('img/oallogo.jpg') }}">
                </a>&nbsp;&nbsp;&nbsp;
            </li>
            <li class="nav-item">
                <a href="https://www.facebook.com/deepskylog" rel="external">
                    <img width="36" height="36" src="/img/f_logo_RGB-Blue_58.png">
                </a>&nbsp;&nbsp;&nbsp;
            </li>
            <li class="nav-item">
                <a href="https://www.instagram.com/deepskylog.be" rel="external">
                    <img width="36" height="36" src="/img/glyph-logo_May2016.png">
                </a>&nbsp;&nbsp;&nbsp;
            </li>
            <li class="nav-item">
                <a href="https://twitter.com/DeepskyLog" rel="external">
                    <img width="36" height="36" src="/img/Twitter_Logo_Blue.png">
                </a>&nbsp;&nbsp;&nbsp;
            </li>
            <li class="nav-item">
                <a href="https://www.youtube.com/channel/UC66H7w2Fl9q3krRy_tHRK5g" rel="external">
                    <img width="36" height="36" src="/img/youtube_social_circle_red.png">
                </a>
            </li>
        </ul>
    </div>
</nav>
