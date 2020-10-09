@extends("layout.master")

@section('title', _i('Messages'))

@section('content')
    @include('layout.messages.partials.flash')

    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active nav-item">
            <a class="nav-link active" href="#new" data-toggle="tab">
                {{ _i('New messages') }}
            </a>
        </li>
        <li class="active nav-item">
            <a class="nav-link" href="#old" data-toggle="tab">
                {{ _i('Old messages') }}
            </a>
        </li>
    </ul>

    <div id="my-tab-content" class="tab-content">
        <!-- Personal tab -->
        <div class="tab-pane active" id="new">


            @if (count($newThreads) > 0)
                <table class="table table-sm table-striped table-hover" id="messages" width="100%">
                    @include('layout.messages.partials.thread', ['threads' => $newThreads])
                @else
                    @include('layout.messages.partials.no-threads')
            @endif

        </div>

        <div class="tab-pane" id="old">
            @if (count($oldThreads) > 0 && Auth::user()->name != 'Administrator')
                <table class="table table-sm table-striped table-hover" id="messagesOld" width="100%">
                    @include('layout.messages.partials.thread', ['threads' => $oldThreads])
                @else
                    @include('layout.messages.partials.no-threads')
            @endif
        </div>
    </div>


    <a class="btn btn-primary" role="button" href="/messages/create">
        <svg width="1.3em" height="1.3em" viewBox="0 0 16 16" class="bi bi-envelope-open icon" fill="currentColor"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                d="M8.47 1.318a1 1 0 0 0-.94 0l-6 3.2A1 1 0 0 0 1 5.4v.818l5.724 3.465L8 8.917l1.276.766L15 6.218V5.4a1 1 0 0 0-.53-.882l-6-3.2zM15 7.388l-4.754 2.877L15 13.117v-5.73zm-.035 6.874L8 10.083l-6.965 4.18A1 1 0 0 0 2 15h12a1 1 0 0 0 .965-.738zM1 13.117l4.754-2.852L1 7.387v5.73zM7.059.435a2 2 0 0 1 1.882 0l6 3.2A2 2 0 0 1 16 5.4V14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V5.4a2 2 0 0 1 1.059-1.765l6-3.2z" />
        </svg>
        {{ _i('Start new conversation') }}
    </a>

    <a name="" id="" class="btn btn-primary" href="/messages/markAllRead" role="button">
        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check2-all" fill="currentColor"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                d="M12.354 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
            <path
                d="M6.25 8.043l-.896-.897a.5.5 0 1 0-.708.708l.897.896.707-.707zm1 2.414l.896.897a.5.5 0 0 0 .708 0l7-7a.5.5 0 0 0-.708-.708L8.5 10.293l-.543-.543-.707.707z" />
        </svg>
        {{ _i('Mark all messages read') }}
    </a>

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
        var url = "<?php echo $url; ?>"
        $(document).ready(function() {
            $('#messages, #messagesOld').DataTable({
                "order": [
                    [4, "desc"]
                ],
                "columnDefs": [{
                        "orderData": [5],
                        "targets": [4]
                    },
                    {
                        "targets": 'no-sort',
                        "orderable": false,
                    }
                ],
                "language": {
                    "url": url
                },
            });
        });

    </script>
@endpush
