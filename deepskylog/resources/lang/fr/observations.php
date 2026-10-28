<?php

return [
    'cluster_types' => [
        'A' => 'Riche, peu concentré, de différentes ampleurs, lâche',
        'B' => 'Peu concentré, de différentes ampleurs, lâche',
        'C' => 'Pauvre, peu concentré, de différentes ampleurs, lâche',
        'D' => 'Riche, concentré, lâche',
        'E' => 'Riche, peu concentré, mêmes ampleurs, lâche',
        'F' => 'Seulement même couleur ou ampleurs, pas lâche',
        'G' => 'Cluster compact à haute amplification, riche, faible',
        'H' => 'Très pauvre, pas lâche du tout',
        'I' => 'Cluster compact à haute amplification, pauvre, faible',
        'X' => 'Aucune classification possible',
    ],

    'visibility' => [
        '1' => 'Très simple, objet prominent',
        '2' => 'Objet facilement perçu avec la vision directe',
        '3' => 'Objet perceptible avec la vision directe',
        '4' => 'Vision déviée requise pour percevoir l\'objet',
        '5' => 'Objet à peine perceptible avec la vision déviée',
        '6' => 'La perception de l\'objet est très questionnable',
        '7' => 'Objet définitivement non vu',
    ],

    'visibility_oc' => [
        '1' => 'Cluster très prominent et très beau',
        '2' => 'Cluster prominent et beau',
        '3' => 'Cluster vu de manière conspicuelle',
        '4' => 'Cluster qui peine à attirer l\'attention',
        '5' => 'Très peu conspicu, facilement passé à côté lors du balayage',
        '6' => 'Avistissement questionnable; densité d\'étoiles similaire au voisinage',
        '7' => 'Virtuellement aucune étoile à la position du catalogue',
    ],

    'visibility_ds' => [
        '1' => 'Binaire peut être résolu',
        '2' => 'Binaire apparaît comme 8',
        '3' => 'Binaire ne peut pas être résolu',
    ],

    'component_colors' => [
        '1' => 'blanc',
        '2' => 'rouge',
        '3' => 'orange',
        '4' => 'jaune',
        '5' => 'vert',
        '6' => 'bleu',
    ],

    'seeing' => [
        '0' => '-----',
        '1' => 'Excellent',
        '2' => 'Bon',
        '3' => 'Moyen',
        '4' => 'Mauvais',
        '5' => 'Très mauvais',
    ],
    'transparency' => [
        '0' => '-----',
        '1' => 'Excellente',
        '2' => 'Bonne',
        '3' => 'Typique',
        '4' => 'Pauvre',
        '5' => 'Affreuse',
    ],
];
