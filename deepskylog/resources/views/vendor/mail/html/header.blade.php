@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'DeepskyLog')
                {{-- Use asset() helper and the primary logo to ensure correct path in emails --}}
                <img src="{{ asset('images/logo2.png') }}" class="logo" alt="DeepskyLog Logo">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
