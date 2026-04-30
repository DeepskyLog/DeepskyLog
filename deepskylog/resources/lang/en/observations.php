<?php
return [
    'cluster_types' => [
        'A' => 'Rich, poorly conc., diff. magnit., loose',
        'B' => 'Poorly conc., diff. magnit., loose',
        'C' => 'Poor, poorly conc., diff. magnit., loose',
        'D' => 'Rich, concentrated, loose',
        'E' => 'Rich, poorly conc., same magnit., loose',
        'F' => 'Only same color or magnit., not loose',
        'G' => 'High magnif. compact cl., rich, weak',
        'H' => 'Very poor, not loose at all',
        'I' => 'High magnif. compact cl., poor, weak',
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

    'seeing' => [
        '0' => '-----',
        '1' => 'Excellent',
        '2' => 'Good',
        '3' => 'Moderate',
        '4' => 'Poor',
        '5' => 'Bad',
    ],
];
