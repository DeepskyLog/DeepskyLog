@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'DeepskyLog')
                <img src="{{ config('app.url') }}/images/logo_small.jpg" class="logo" alt="DeepskyLog Logo">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
