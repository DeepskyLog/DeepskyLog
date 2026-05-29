<?php

return [
    'cluster_types' => [
        'A' => 'Rico, poco concentrado, de diferentes magnitudes, suelto',
        'B' => 'Poco concentrado, de diferentes magnitudes, suelto',
        'C' => 'Pobre, poco concentrado, de diferentes magnitudes, suelto',
        'D' => 'Rico, concentrado, suelto',
        'E' => 'Rico, poco concentrado, mismas magnitudes, suelto',
        'F' => 'Solo mismo color o magnitudes, no suelto',
        'G' => 'Cluster compacto de alta magnificación, rico, débil',
        'H' => 'Muy pobre, no suelto en absoluto',
        'I' => 'Cluster compacto de alta magnificación, pobre, débil',
        'X' => 'No es posible clasificar',
    ],

    'visibility' => [
        '1' => 'Muy simple, objeto prominente',
        '2' => 'Objeto fácilmente perceptible con visión directa',
        '3' => 'Objeto perceptible con visión directa',
        '4' => 'Se requiere visión desviada para percibir el objeto',
        '5' => 'Objeto apenas perceptible con visión desviada',
        '6' => 'La percepción del objeto es muy cuestionable',
        '7' => 'Objeto definitivamente no visto',
    ],

    'visibility_oc' => [
        '1' => 'Cluster muy prominente y muy hermoso',
        '2' => 'Cluster prominente y hermoso',
        '3' => 'Cluster visto de manera conspicua',
        '4' => 'Cluster que apenas atrae la atención',
        '5' => 'Muy poco conspicuo, fácilmente pasado por alto al mover el telescopio',
        '6' => 'Avistamiento cuestionable; densidad de estrellas similar al vecindario',
        '7' => 'Prácticamente no hay estrellas en la posición del catálogo',
    ],

    'visibility_ds' => [
        '1' => 'Doble estrella puede ser resuelta',
        '2' => 'Doble estrella aparece como 8',
        '3' => 'Doble estrella no puede ser resuelta',
    ],

    'component_colors' => [
        '1' => 'blanco',
        '2' => 'rojo',
        '3' => 'naranja',
        '4' => 'amarillo',
        '5' => 'verde',
        '6' => 'azul',
    ],

    'seeing' => [
        '0' => '-----',
        '1' => 'Excelente',
        '2' => 'Bueno',
        '3' => 'Moderado',
        '4' => 'Malo',
        '5' => 'Horrible',
    ],
    'transparency' => [
        '0' => '-----',
        '1' => 'Excelente',
        '2' => 'Bueno',
        '3' => 'Típico',
        '4' => 'Malo',
        '5' => 'Horrible',
    ],
];
