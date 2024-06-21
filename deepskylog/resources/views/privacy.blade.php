<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("DeepskyLog Privacy Policy") }}
            </h2>
            <br />

            {{ __("The majority of information on this site can be accessed without providing any personal information.") }}
            {{ __("In case users want to record observations and get acces to a variety of useful tools, the user is asked to register and provide personal information including name, first name and email address.") }}

            {{ __("This information will be used only for user management and to keep you informed about our activities.") }}

            {{ __("The user has the right at any time, at no cost and upon request, to prohibit the use of his information for the purpose of direct communication.") }}

            {{ __("Your personal information is never passed on to third parties.") }}
            <br />
            <br />

            {{ __("In case the registered user has not recorded any information in DeepskyLog within 24 months after registration, his account will be made obsolete and personal information deleted from the database.") }}
            <br />
            <br />

            @php
                echo sprintf(
                    __("In case of questions or concerns regarding your personal data, do not hesitate to contact us at %sdevelopers@deepskylog.be%s."),
                    "<a href='mailto:developers@deepskylog.be'>",
                    "</a>",
                );
            @endphp
        </div>
    </div>
</x-app-layout>
