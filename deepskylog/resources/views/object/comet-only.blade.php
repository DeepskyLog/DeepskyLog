{{-- Comet-specific UI wrapper — central place for comet blades
     This file consolidates comet UI and scripts that were previously embedded
     inside resources/views/object/show.blade.php.
-->

@includeWhen(strtolower(trim((string) ($session->source_type_raw ?? ''))) === 'comet',
    'object.partials.comet-details',
    [
        'session' => $session,
        'comet_magnitudes' => $comet_magnitudes ?? null,
        'ephemerides' => $ephemerides ?? null,
    ]
)
