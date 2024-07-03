@props(["observation_id" => null, "observer_name" => null, "observation_date" => null])
<a
    class="no-underline"
    href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ $observation_id }}"
>
    <img width="400" src="/images/cometdrawings/{{ $observation_id }}.jpg" />

    <div class="text-center">
        {{ $observer_name }} -
        {{ \App\Models\CometObservationsOld::find($observation_id)->object->name }}
        -
        {{ \Carbon\Carbon::create($observation_date)->translatedFormat("j M Y") }}
    </div>
    <div class="text-center">
        {!!
            ShareButtons::page(
                "https://www.deepskylog.org/comets/cometdrawings/" . $observation_id . ".jpg",
                __("Look at this sketch of ") . \App\Models\CometObservationsOld::find($observation_id)->object->name . __(" by ") . $observer_name . __(" on #deepskylog"),
                [
                    "title" => __("Share this sketch"),
                    "class" => "text-gray-500 hover:text-gray-700",
                    "rel" => "nofollow noopener noreferrer",
                ],
            )
                ->facebook(["class" => "hover", "rel" => "follow"])
                ->twitter(["class" => "hover", "rel" => "follow"])
                ->copylink()
                ->mailto(["class" => "hover", "rel" => "nofollow"])
                ->whatsapp()
                ->render()
        !!}
    </div>
</a>
