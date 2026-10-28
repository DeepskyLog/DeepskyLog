<?php

return [
    'cluster_types' => [
        'A' => 'Reichhaltig, schlecht konzentriert, unterschiedliche Größenordnungen, locker',
        'B' => 'Schlecht konzentriert, unterschiedliche Größenordnungen, locker',
        'C' => 'Schlecht, schlecht konzentriert, unterschiedliche Größenordnungen, locker',
        'D' => 'Reichhaltig, konzentriert, locker',
        'E' => 'Reichhaltig, schlecht konzentriert, gleiche Größenordnungen, locker',
        'F' => 'Nur gleiche Farbe oder Größenordnungen, nicht locker',
        'G' => 'Hochvergrößernder kompakter Cluster, reichhaltig, schwach',
        'H' => 'Sehr schlecht, nicht locker überhaupt',
        'I' => 'Hochvergrößernder kompakter Cluster, schlecht, schwach',
        'X' => 'Keine Klassifizierung möglich',
    ],

    'visibility' => [
        '1' => 'Sehr einfach, auffälliges Objekt',
        '2' => 'Objekt leicht wahrnehmbar mit direktem Blick',
        '3' => 'Objekt wahrnehmbar mit direktem Blick',
        '4' => 'Ablenkender Blick erforderlich, um Objekt wahrzunehmen',
        '5' => 'Objekt kaum wahrnehmbar mit avertiertem Blick',
        '6' => 'Wahrnehmung des Objekts ist sehr fragwürdig',
        '7' => 'Objekt definitiv nicht gesehen',
    ],

    'visibility_oc' => [
        '1' => 'Sehr auffälliger und sehr schöner Cluster',
        '2' => 'Auffälliger und schöner Cluster',
        '3' => 'Auffällig gesehenes Cluster',
        '4' => 'Cluster zieht kaum Aufmerksamkeit auf sich',
        '5' => 'Sehr unmerklich, leicht übersehen beim Schwenken',
        '6' => 'Zweifelhafter Fund; Sternendichte ähnlich dem Nachbarbereich',
        '7' => 'Virtuell keine Sterne an der Katalogposition',
    ],

    'visibility_ds' => [
        '1' => 'Doppelstern kann aufgelöst werden',
        '2' => 'Doppelstern erscheint als 8',
        '3' => 'Doppelstern kann nicht aufgelöst werden',
    ],

    'component_colors' => [
        '1' => 'weiß',
        '2' => 'rot',
        '3' => 'orange',
        '4' => 'gelb',
        '5' => 'grün',
        '6' => 'blau',
    ],

    'seeing' => [
        '0' => '-----',
        '1' => 'Exzellent',
        '2' => 'Gut',
        '3' => 'Mäßig',
        '4' => 'Schlecht',
        '5' => 'Schrecklich',
    ],
    'transparency' => [
        '0' => '-----',
        '1' => 'Prime',
        '2' => 'Gut',
        '3' => 'Typisch',
        '4' => 'Schlecht',
        '5' => 'Schrecklich',
    ],
];
