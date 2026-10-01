<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    {{
                        __("Newly added sketch of the ") .
                            $week_month .
                            ": " .
                            $date
                    }}
                </h2>
            </div>
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        <div class="mt-3 max-w-xl pr-3">
                            @php
                                // $share is the complete post (with the link and the hashtags): X and
                                // WhatsApp get it as it is, the link is not added a second time.
                                $shareUrl = $observation->detailUrl() ?? url("/");
                                $shareSubject = $week_month === "month" ? __("DeepskyLog sketch of the month") : __("DeepskyLog sketch of the week");
                                $twitterHref = "https://twitter.com/intent/tweet?text=" . rawurlencode($share);
                                $facebookHref = "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($shareUrl);
                                $whatsappHref = "https://wa.me/?text=" . rawurlencode($share);
                                $mailtoHref = "mailto:?subject=" . rawurlencode($shareSubject) . "&body=" . rawurlencode($share);
                            @endphp
                            <a href="{{ $shareUrl }}">
                                <img width="400" src="{{ $drawing_src }}" />
                            </a>

                            <div
                                id="social-buttons"
                                class="text-center"
                                data-share
                                data-share-image="{{ $drawing_src }}"
                                data-share-title="{{ $shareSubject }}"
                                data-share-text="{{ $share }}"
                                data-share-url="{{ $shareUrl }}"
                            >
                                <a href="{{ $facebookHref }}" class="social-button hover" data-share-text="{{ $share }}" rel="follow" title="{{ __("Share on Facebook") }}"><span class="fab fa-facebook-square"></span></a>
                                <a href="{{ $twitterHref }}" class="social-button hover" rel="follow" title="{{ __("Share on X") }}"><span class="fab fa-square-x-twitter"></span></a>
                                <a href="{{ $shareUrl }}" class="social-button hover" id="clip" title="{{ __("Copy link") }}" rel="nofollow noopener noreferrer"><span class="fas fa-share"></span></a>
                                <a href="{{ $mailtoHref }}" class="social-button hover" rel="nofollow" title="{{ __("Share by email") }}"><span class="fas fa-envelope"></span></a>
                                <a href="{{ $whatsappHref }}" class="social-button hover" target="_blank" rel="nofollow" title="{{ __("Share on WhatsApp") }}"><span class="fab fa-square-whatsapp"></span></a>
                                <a href="{{ $shareUrl }}" class="social-button native-share hover" title="{{ __("Share…") }}" rel="nofollow"><span class="fas fa-share-nodes"></span></a>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
