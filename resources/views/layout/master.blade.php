<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="revisit-after" content="1 day" />
        <meta name="author" content="DeepskyLog - VVS" />
        <meta name="keywords"
            content="VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, astronomy, Deepsky, deep-sky, waarnemingen, observations, kometen, comets, planeten, planets, moon, maan" />

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

        <link type="text/css" rel="stylesheet" href="{{ mix('css/app.css') }}">

        <title>@yield('title', 'DeepskyLog')</title>
        <livewire:styles />
        <livewire:scripts />

        <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
        <script src="{{ mix("js/trix.js") }}"></script>
        <script src="{{ mix("js/choices.js") }}"></script>
    </head>

    <body>
        <div class="d-flex" id="wrapper">

            <!-- Sidebar -->
            @include('layout.sidebar')

            <!-- /#sidebar-wrapper -->

            <!-- Page Content -->
            <div id="page-content-wrapper">

                @include('layout.header')

                <div class="container-fluid">
                    @include('layout.errors')
                    @include('layout.flash')
                    <br />
                    @yield('content')
                    <br />
                    @include('layout.footer')


                </div>
            </div>
            <!-- /#page-content-wrapper -->

        </div>
        <!-- /#wrapper -->

        <script src="{{ asset('/js/dark-mode-switch.js') }}"></script>

        <!-- App scripts -->
        @stack('scripts')

        <script>
            $("#menu-toggle").click(function(e) {
              e.preventDefault();
              $("#wrapper").toggleClass("toggled");
            });
            window.addEventListener("trix-file-accept", function(event) {
                event.preventDefault()
            });

            const choices = new Choices('.js-choice');
        </script>

        @include('cookieConsent::index')
    </body>

</html>
