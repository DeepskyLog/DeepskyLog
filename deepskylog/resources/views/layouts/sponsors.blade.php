<x-app-layout>


    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 bg-gray-900">
            <h2 class="font-semibold text-xl leading-tight">
                {{ __('DeepskyLog sponsors') }}
            </h2>
            <br />

            {{ 'DeepskyLog is a free web application, and we want to keep DeepskyLog ad-free forever.  There are however some costs involved in the development and maintenance of DeepskyLog.' }}
            <br />
            {{ 'Everybody who sponsors DeepskyLog will appear with his / her / the company name on this page.' }}
            <br />
            {{ 'You can get more information on sponsoring DeepskyLog by sending us a mail:' }}
            <br /><br />
            <a href="mailto:deepskylog@groups.io">
                <x-button type="button" secondary label="{{ __('Sponsor!') }}" wire:loading.attr="disabled" />
            </a>
            <br /><br />
            <h5>Main Sponsors</h5>
            <hr />
            <br />
            <a href="https://www.vvs.be/"><img src="/images/VVS.png"></a>
        </div>
    </div>

</x-app-layout>
