<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Models\DeepskyObject;

function assert_equal($a, $b) {
    if ($a === $b) {
        echo "OK: $a\n";
    } else {
        echo "FAIL:\n  expected: $b\n  actual:   $a\n";
    }
}

echo "Static RA (hours):\n";
$out = DeepskyObject::formatRa(0.7122777778);
assert_equal($out, '00h42m44.2s');


echo "Static Dec positive:\n";
$out = DeepskyObject::formatDec(41.2684722222);
assert_equal($out, '41°16\'06.5"');

echo "Static Dec negative:\n";
$out = DeepskyObject::formatDec(-5.2009722222);
assert_equal($out, '-5°12\'03.5"');

echo "Instance methods:\n";
$o = new DeepskyObject();
$o->ra = 0.7122777778;
$o->decl = 41.2684722222;
assert_equal($o->formattedRa(), '00h42m44.2s');
assert_equal($o->formattedDec(), '41°16\'06.5"');

echo "Done.\n";
