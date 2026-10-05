@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'DeepskyLog')
                {{-- App\Listeners\EmbedMailLogo embeds the logo in the sent mail; the absolute URL is for
                     previews. The size keeps the logo in proportion: the default theme makes .logo 75×75px. --}}
                <img src="{{ url('images/logo-mail.png') }}" class="logo" alt="DeepskyLog Logo" width="260" height="60" style="width: 260px; height: 60px; max-height: 60px;">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
