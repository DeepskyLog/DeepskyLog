<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    	<meta name="revisit-after" content="1 day" />
	    <meta name="author" content="DeepskyLog - VVS" />
	    <meta name="keywords" content="VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, astronomy, Deepsky, deep-sky, waarnemingen, observations, kometen, comets, planeten, planets, moon, maan" />

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

        <link type="text/css" rel="stylesheet" href="{{ mix('css/app.css') }}">

        <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>

    	<title>@yield('title', 'DeepskyLog')</title>
    </head>

    <body>
        @include('layout.header')
        @include('layout.subheader')

        <div class="container-fluid">
            <div class="row">
                @include('layout.sidebar')

                <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                    @include('layout.errors')
                    @include('layout.flash')
                    <br />
                    @yield('content')
                </main>
            </div>
        </div>

        <br />
        @include('layout.footer')

        <script>
            $(document).ready(function() {
                $(".selection").select2({
                });
            });

            $('#defaultInstrument').change(function() {
                this.form.submit();
            });

            $('#defaultLocation').change(function() {
                this.form.submit();
            });
        </script>
        <!-- App scripts -->
        @stack('scripts')

        @include('cookieConsent::index')
    </body>
</html>
