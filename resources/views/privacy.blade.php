@extends('layout.master')

@section('title', "DeepskyLog Privacy Policy")

@section('content')
<h1>
{{ _i('DeepskyLog Privacy Policy') }}
</h1>
{{ _i("The majority of information on this site can be accessed without providing any personal information.") }}
{{ _i("In case users want to record observations and get acces to a variety of useful tools, the user is asked to register and provide personal information including name, first name and email address.") }}

{{ _i("This information will be used only for user management and to keep you informed about our activities.") }}

{{ _i("The user has the right at any time, at no cost and upon request, to prohibit the use of his information for the purpose of direct communication.") }}

{{ _i("Your personal information is never passed on to third parties.") }}
<br /><br />

{{ _i("In case the registered user has not recorded any information in DeepskyLog within 24 months after registration, his account will be made obsolete and personal information deleted from the database.") }}
<br /><br />

@php
 echo (sprintf(
        _i(
            "In case of questions or concerns regarding your personal data, do not hesitate to contact us at %sdevelopers@deepskylog.be%s."
        ), "<a href='mailto:developers@deepskylog.be'>", "</a>"
    ));
@endphp
@endsection
