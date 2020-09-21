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


    <a class="btn btn-primary" role="button" href="/messages/create">
        <svg width="1.3em" height="1.3em" viewBox="0 0 16 16" class="bi bi-envelope-open icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M8.47 1.318a1 1 0 0 0-.94 0l-6 3.2A1 1 0 0 0 1 5.4v.818l5.724 3.465L8 8.917l1.276.766L15 6.218V5.4a1 1 0 0 0-.53-.882l-6-3.2zM15 7.388l-4.754 2.877L15 13.117v-5.73zm-.035 6.874L8 10.083l-6.965 4.18A1 1 0 0 0 2 15h12a1 1 0 0 0 .965-.738zM1 13.117l4.754-2.852L1 7.387v5.73zM7.059.435a2 2 0 0 1 1.882 0l6 3.2A2 2 0 0 1 16 5.4V14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V5.4a2 2 0 0 1 1.059-1.765l6-3.2z"/>
          </svg>
        {{ _i("Start new conversation") }}
    </a>

@endsection

@php
    $url = "http://cdn.datatables.net/plug-ins/1.10.19/i18n/" . \DeepskyLog\Languages\LanguagesFacade::lookup(
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
