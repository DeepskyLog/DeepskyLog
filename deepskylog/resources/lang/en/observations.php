<?php
return [
    'cluster_types' => [
        'A' => 'Rich, poorly concentrated, different magnitudes, loose',
        'B' => 'Poorly concentrated, different magnitudes, loose',
        'C' => 'Poor, poorly concentrated, different magnitudes, loose',
        'D' => 'Rich, concentrated, loose',
        'E' => 'Rich, poorly concentrated, same magnitudes, loose',
        'F' => 'Only same color or magnitudes, not loose',
        'G' => 'High magnification compact cluster, rich, weak',
        'H' => 'Very poor, not loose at all',
        'I' => 'High magnification compact cluster, poor, weak',
        'X' => 'No classification possible',
    ],

    'visibility' => [
        '1' => 'Very simple, prominent object',
        '2' => 'Object easily percepted with direct vision',
        '3' => 'Object perceptable with direct vision',
        '4' => 'Averted vision required to percept object',
        '5' => 'Object barely perceptable with averted vision',
        '6' => 'Perception of object is very questionable',
        '7' => 'Object definitely not seen',
    ],

    'visibility_oc' => [
        '1' => 'Very prominent and very beautiful cluster',
        '2' => 'Prominent and beautiful cluster',
        '3' => 'Conspicuously seen cluster',
        '4' => 'Cluster hardly attracts attention',
        '5' => 'Very unconspicuously, easily overlooked when slewing',
        '6' => 'Questionable sighting; star density similar to neighborhood',
        '7' => 'Virtually no stars at catalog position',
    ],

    'visibility_ds' => [
        '1' => 'Binary can be resolved',
        '2' => 'Binary appears as 8',
        '3' => 'Binary cannot be resolved',
    ],

    'component_colors' => [
        '1' => 'white',
        '2' => 'red',
        '3' => 'orange',
        '4' => 'yellow',
        '5' => 'green',
        '6' => 'blue',
    ],

    'seeing' => [
        '0' => '-----',
        '1' => 'Excellent',
        '2' => 'Good',
        '3' => 'Moderate',
        '4' => 'Poor',
        '5' => 'Bad',
    ],
    'transparency' => [
        '0' => '-----',
        '1' => 'Prime',
        '2' => 'Good',
        '3' => 'Typical',
        '4' => 'Poor',
        '5' => 'Awful',
    ],
];
