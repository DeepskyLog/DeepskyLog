@extends("layout.master")

@section('title')
{{ _i("Sketch and observation forms") }}
@endsection

@section('content')
<h4>{{ _i("Sketch and observation forms") }}</h4>

<div id="formCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
    <!-- Indicators -->
    <ol class="carousel-indicators">
        <li data-target="#formCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#formCarousel" data-slide-to="1"></li>
        <li data-target="#formCarousel" data-slide-to="2"></li>
    </ol>
    <!-- Wrapper for slides -->
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="/images/FormsExample.png" alt="...">
            <div class="carousel-caption" style="background: gray;">
                <p>
                    {{ _i('Compact sketch form') }}
                    <br />
                    {{_i('A compact sketch form that folds in half so you can do two sketches on one piece of paper.') }}
                    <br />
                    {{ _i('This format allows you to sketch on top of a book like your PSA.') }}
                </p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="/images/FormsExample1.png" alt="...">
            <div class="carousel-caption" style="background: gray;">
                <p>
                    {{ _i('Big sketch form') }}
                    <br />
                    {{ _i('Utilizes the maximum amount of drawing space for your sketch. With a 17cm sketch circle and simplified details area.') }}
                </p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="/images/FormsExample2.png" alt="...">
            <div class="carousel-caption" style="background: gray;">
                <p>
                    {{ _i('Observation log form') }}
                    <br />
                    {{_i('If you want to jot down multiple brief observation notes on one piece of paper.')}}
                </p>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#formCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#formCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>

<h3>{{ _i('Compact sketch form') }}</h3>
<p>
    {{ _i('A compact sketch form that folds in half so you can do two sketches on one piece of paper.') }}
    <br />
    {{ _i('This format allows you to sketch on top of a book like your PSA.') }}
</p>
<a class="btn btn-success" href="/downloads/Sketch.pdf">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
        class="inline bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16">
        <path
            d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 6.854l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.293V5.5a.5.5 0 0 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708z" />
    </svg>
    {{ _i('Download') }}
</a>
<br /><br />
<h3>{{ _i('Big sketch form') }}</h3>
<p>
    {{ _i('Utilizes the maximum amount of drawing space for your sketch. With a 17cm sketch circle and simplified details area.') }}
</p>
<a class="btn btn-success" href="/downloads/Sketch big.pdf">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
        class="inline bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16">
        <path
            d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 6.854l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.293V5.5a.5.5 0 0 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708z" />
    </svg>
    {{ _i('Download') }}
</a>
<br /><br />
<h3>{{ _i('Observation log form') }}</h3>
<p>
    {{ _i('If you want to jot down multiple brief observation notes on one piece of paper.') }}
</p>
<a class="btn btn-success" href="/downloads/Observation log.pdf">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
        class="inline bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16">
        <path
            d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2zm2.354 6.854l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.293V5.5a.5.5 0 0 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708z" />
    </svg>
    {{ _i('Download') }}
</a>
<br /><br />
@endsection
