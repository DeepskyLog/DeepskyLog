<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    	<meta name="revisit-after" content="1 day" />
	    <meta name="author" content="DeepskyLog - VVS" />
	    <meta name="keywords" content="VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, Deepsky, waarnemingen, kometen" />

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

        <link rel="stylesheet" href="css/app.css">
    	<title>@yield('title', 'DeepskyLog')</title>
    </head>

    @include('layout.header')

    <div>
        @yield('content')
    </div>
</html>
