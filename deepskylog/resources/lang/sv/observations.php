<?php

return [
    'cluster_types' => [
        'A' => 'Rik, dåligt koncentrerad, olika magnituder, lös',
        'B' => 'Dåligt koncentrerad, olika magnituder, lös',
        'C' => 'Påverkat, dåligt koncentrerad, olika magnituder, lös',
        'D' => 'Rik, koncentrerad, lös',
        'E' => 'Rik, dåligt koncentrerad, samma magnituder, lös',
        'F' => 'Endast samma färg eller magnituder, inte lös',
        'G' => 'Hög förstoring kompakt kluster, rik, svag',
        'H' => 'Mycket dålig, inte lös alls',
        'I' => 'Hög förstoring kompakt kluster, dålig, svag',
        'X' => 'Ingen klassificering möjlig',
    ],

    'visibility' => [
        '1' => 'Mycket enkelt, framträdande objekt',
        '2' => 'Objekt lätt percepterat med direkt vision',
        '3' => 'Objekt percepterbart med direkt vision',
        '4' => 'Averted vision krävs för att perceptera objekt',
        '5' => 'Objekt knappt percepterbart med averted vision',
        '6' => 'Perception av objekt är mycket tveksam',
        '7' => 'Objekt definitivt inte sett',
    ],

    'visibility_oc' => [
        '1' => 'Mycket framträdande och mycket vackert kluster',
        '2' => 'Framträdande och vackert kluster',
        '3' => 'Tydligt sett kluster',
        '4' => 'Kluster lockar nästan inte uppmärksamhet',
        '5' => 'Mycket otydligt, lätt överhoppat vid sökning',
        '6' => 'Tveksam observation; stjärnledighet liknande närområdet',
        '7' => 'Nästan inga stjärnor på katalogpositionen',
    ],

    'visibility_ds' => [
        '1' => 'Binär kan lösas',
        '2' => 'Binär ser ut som 8',
        '3' => 'Binär kan inte lösas',
    ],

    'component_colors' => [
        '1' => 'vit',
        '2' => 'röd',
        '3' => 'orange',
        '4' => 'gul',
        '5' => 'grön',
        '6' => 'blå',
    ],

    'seeing' => [
        '0' => '-----',
        '1' => 'Utmärkt',
        '2' => 'Bra',
        '3' => 'Måttligt',
        '4' => 'Dålig',
        '5' => 'Sämre',
    ],
    'transparency' => [
        '0' => '-----',
        '1' => 'Utmärkt',
        '2' => 'Bra',
        '3' => 'Typisk',
        '4' => 'Dålig',
        '5' => 'Sämre',
    ],
];
