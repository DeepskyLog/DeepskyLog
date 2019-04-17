@extends("layout.master")

@section('title')
    @if ($user == 'user')
        {{ _i("Lenses of %s", Auth::user()->name) }}
    @else
        {{ _i("All lenses") }}
    @endif
@endsection

@section('content')

<div id="lens">

        <div v-if="flasherror" class="alert-danger">
            {{  _i('Lens deleted: ') }} @{{ lensname }}
        </div>
        <div v-if="flash" class="alert-warning">
            <div v-if="active">
                {{  _i('Lens activated: ') }} @{{ lensname }}
            </div>
            <div v-else>
                {{  _i('Lens deactivated: ') }} @{{ lensname }}
            </div>
        </div>
    <br />

	<h4>
        @if ($user == 'user')
            {{ _i("Lenses of %s", Auth::user()->name) }}
        @else
            {{ _i("All lenses") }}
        @endif

    </h4>
	<hr />
    <a class="btn btn-success float-right" href="/lens/create">
        {{ _i("Add lens") }}
    </a>
    <br /><br />
    <table class="table table-sm table-striped table-hover" id="lens_table">
        <thead>
            <tr>
                <th>{{ _i("Name") }}</th>
                <th>{{ _i("Factor") }}</th>
                @if ($user == 'user')
                    <th>{{ _i("Active") }}</th>
                @endif
                <th>{{ _i("Delete") }}</th>
                <th>{{ _i("Observations") }}</th>
            </tr>
        </thead>
        <tbody>
            <!-- Only show the lenses for the correct user -->
            @foreach ($lenses as $lens)
                <tr>
                    <td>
                        <a href="/lens/{{  $lens->id }}/edit">
                            {{ $lens->name }}
                        </a>
                    </td>
                    <td>{{ $lens->factor }}</td>
                    @if ($user == 'user')
                    <td>
                        <lensactivation :selected="{{  $lens->active }}" :id="{{ $lens->id }}">
                        </lensactivation>
                    </td>
                    @endif
                    <td>
                        <!-- TODO: Only show if there are no observations with this lens -->
                        <lensdeletion name="{{  $lens->name }}" deleteid="{{  $lens->id }}">
                        </lensdeletion>
                    </td>
                    <td>
                        <!-- TODO: Show the correct number of observations with this lens, and make the correct link -->
                        <a href="#">
                            {{ $lens->id . ' ' . _n('observation', 'observations', $lens->id) }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

@endsection

@push('scripts')
<script>
// Set the correct language for the datatable
$.getScript('{{ URL::asset('js/datatables.js') }}', function()
{
    datatable('#lens_table', '{{ LaravelGettext::getLocale() }}', [
        // Sort columns naturally
       { type: 'natural' }
     ]);
});

// Remove the row from the datatable if the 'delete' icon is pressed.
$(document).ready(function() {
    $('#lens_table tbody').on( 'click', '#delete', function () {
        var table = $('#lens_table').DataTable();

        table.row( $(this).parents('tr') ).remove().draw();
    } );
});

// Activate select box and methods
Vue.component('lensactivation', {
    template: `
        <input type="checkbox" @change="activateLens" :checked="selected">
    `,
    props: {
        id: { },
        selected: { default: false },
    },
    methods:{
        activateLens() {
            // create a closure to access component in the callback below
            var self = this

            $.getJSON('/activateLensJson/' + this.id, function(data) {
                self.$parent.flash = true;
                self.$parent.lensname = data.name;
                self.$parent.active = data.active;
                self.$parent.flasherror = false;
            });
        }
    }
});

Vue.component('lensdeletion', {
    template: `
        <button id="delete" type="button" class="btn btn-sm btn-link" @click="deleteLens">
            <i class="far fa-trash-alt"></i>
        </button>
    `,
    props: {
        deleteid: { },
        name: { },
    },
    methods:{
        deleteLens() {
            // create a closure to access component in the callback below
            var self = this

            self.$parent.flasherror = true;
            self.$parent.flash = false;
            self.$parent.lensname = this.name;

            $.getJSON('/deleteLensJson/' + this.deleteid);
        }
    }
});

new Vue({
    el: '#lens',
    data: {
        flash: false,
        flasherror: false,
        lensname: '',
        active: ''
    }
})

</script>
@endpush
