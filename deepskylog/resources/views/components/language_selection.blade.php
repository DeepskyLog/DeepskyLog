@if (Auth::guest())
    @php
        $lang = Languages::lookup([config("app.locale")], config("app.locale"))[config("app.locale")];
    @endphp

    <div {{ $attributes->class(["mt-3 object-center"]) }}>
        <x-select
            x-on:selected="window.location.href = '/language/' + $event.detail.value;"
            placeholder="{{ $lang }}"
        >
            @foreach (config("app.available_locales") as $language => $key)
                <x-select.option
                    label="{{ Languages::lookup([$key], $key)[$key] }}"
                    value="{{ $key }}"
                />
            @endforeach
        </x-select>
    </div>
@endif
