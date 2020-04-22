@extends("layout.master")

@section('title', _i('Messages'))

@section('content')
    @include('messenger.partials.flash')

    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active nav-item">
            <a class="nav-link active" href="#new" data-toggle="tab">
                {{ _i("New messages") }}
            </a>
        </li>
        <li class="active nav-item">
            <a class="nav-link" href="#old" data-toggle="tab">
                {{ _i("Old messages") }}
            </a>
        </li>
    </ul>

    <div id="my-tab-content" class="tab-content">
        <!-- Personal tab -->
        <div class="tab-pane active" id="new">


    @if (count($newThreads) > 0)
        <table class="table table-sm table-striped table-hover" id="messages" width="100%">
            @include('messenger.partials.thread', ['threads' => $newThreads])
    @else
        @include('messenger.partials.no-threads')
    @endif

    </div>

    <div class="tab-pane" id="old">
        @if (count($oldThreads) > 0 && Auth::user()->name != "Administrator")
            <table class="table table-sm table-striped table-hover" id="messagesOld" width="100%">
            @include('messenger.partials.thread', ['threads' => $oldThreads])
        @else
            @include('messenger.partials.no-threads')
        @endif
    </div>
</div>


    <a class="btn btn-primary" role="button" href="/messages/create"><i class="far fa-envelope-open"></i> {{ _i("Start new conversation") }}</a>

@endsection

@php
    $url = "http://cdn.datatables.net/plug-ins/1.10.19/i18n/" . \PeterColes\Languages\LanguagesFacade::lookup(
        [\deepskylog\LaravelGettext\Facades\LaravelGettext::getLocaleLanguage()],
        'en'
    )->first()
    . ".json"
@endphp

@push('scripts')
    <script>
        var url = "<?php echo $url ?>"
        $(document).ready( function () {
            $('#messages, #messagesOld').DataTable( {
                "order": [[ 4, "desc" ]],
                "columnDefs": [
                    { "orderData":[ 5 ],   "targets": [ 4 ] },
                {
                    "targets": 'no-sort',
                    "orderable": false,
                } ],
                "language": {
                   "url": url
                },
            }
            );
        } );
    </script>
@endpush
