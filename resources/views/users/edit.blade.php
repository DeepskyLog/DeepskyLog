@extends('layout.master')

@section('title', _i('Edit User'))

@section('content')

<div class='col-lg-4 col-lg-offset-4'>

    <h3><i class='fa fa-user-plus'></i> {{ _i('Edit') }} {{$user->name}}</h3>
    <hr>

    {{ Form::model($user, array('route' => array('users.update', $user->id), 'method' => 'PUT')) }}{{-- Form model binding to automatically populate our fields with user data --}}

    <div class="form-group">
        {{ Form::label('username', _i('Username')) }}
        {{ Form::text('username', null, ['class' => 'form-control', 'readonly' => 'true']) }}
    </div>

    <div class="form-group">
        {{ Form::label('name', _i('Name')) }}
        {{ Form::text('name', null, array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
        {{ Form::label('email', _i('Email')) }}
        {{ Form::email('email', null, array('class' => 'form-control')) }}
    </div>

    <div class="form-group">
        <label for="type">{{ _i("Role") }}</label>
        <div class="form">
            <select class="form-control selection" name="type">
                @if ($user->type == "admin")
                    <option>default</option>
                    <option selected="selected">admin</option>
                @else
                    <option selected="selected">default</option>
                    <option>admin</option>
                @endif
            </select>
        </div>
    </div>

    {{ Form::submit(_i('Adapt'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

</div>

@endsection
