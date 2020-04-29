@extends("layout.master")

@section('content')
<h1>DeepskyLog</h1>

<a class="twitter-timeline"
    href="https://twitter.com/DeepskyLog?ref_src=twsrc%5Etfw"
    data-width="500"
    data-height="700"
    data-chrome="nofooter noborders"
    >
    Tweets by DeepskyLog
</a>
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
@endsection
