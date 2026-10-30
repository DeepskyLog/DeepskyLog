<?php

use App\Services\DreyerDescription;

it('decodes the abbreviations of a description', function (string $description, string $plain) {
    expect(DreyerDescription::plain($description))->toBe($plain);
})->with([
    'galaxy' => [
        'C 30 / H400 / Best NGC / Clark * / RASC / TAAS200 / B,pL,pmE163,smbM / H I 53 /UGC 12113',
        'Bright, fairly large, fairly much elongated in position angle 163°, suddenly much brighter in the middle',
    ],
    'two qualities in one word' => [
        'vB,pS,sbMvSN/ H IV 37/Cat\'s Eye Nebula',
        'Very bright, fairly small, suddenly brighter in the middle, very small nucleus',
    ],
    'remarkable' => [
        'Clark !!* / !! vB,vL,R,vsbM / Owl Nebula',
        'Very remarkable, very bright, very large, round, very suddenly brighter in the middle',
    ],
    'cluster' => [
        'Clark x / Cl,B,vRi,cC,st 9...13 / H VII 61',
        'Cluster, bright, very rich, considerably compressed, stars of magnitude 9 to 13',
    ],
    'stars' => [
        'B,S,R,*9M,*8 nf 100\'\'',
        'Bright, small, round, a star of magnitude 9 in the middle, a star of magnitude 8 to the north-east 100″ away',
    ],
    'place in a group' => [
        'F,L,E pf,vglbM,3rd of 3 / Leo group',
        'Faint, large, elongated east–west, very gradually slightly brighter in the middle, the third of 3',
    ],
    'preceding of two' => [
        'F,L,E,vgbM,p of 2',
        'Faint, large, elongated, very gradually brighter in the middle, the member to the west in a group of 2',
    ],
    'position angle with a space' => [
        'cB,L,mE 135,glbM',
        'Considerably bright, large, much elongated in position angle 135°, gradually slightly brighter in the middle',
    ],
    'an observer\'s telescope' => [
        '13\'\'-pB,vS,R,nBM at 220X / same as PK342+17.1',
        'In a 13-inch telescope: fairly bright, very small, round, not brighter in the middle at 220×',
    ],
    'binoculars' => [
        '10X50-vB,vL,irR, 18* / The Tuft in the tail of the dog',
        'In 10×50 binoculars: very bright, very large, irregularly round, 18 stars',
    ],
    'faint star' => [
        'cF,vS,vlE,vS* att,3rd of 3',
        'Considerably faint, very small, very slightly elongated, a very faint star attached, the third of 3',
    ],
    'and' => [
        'RASC / F,eL,neb&st in groups / ',
        'Faint, extremely large, nebula and stars in groups',
    ],
    'paired with' => [
        'pB,L,lE,bM / P w NGC 3501',
        'Fairly bright, large, slightly elongated, brighter in the middle; Paired with NGC 3501',
    ],
    'stars of SAC' => [
        'pB,pL,E50,2 st p / 16* mags 11..13 at 100X / 12...14',
        'Fairly bright, fairly large, elongated in position angle 50°, 2 stars to the west; 16 stars of magnitude 11 to 13 at 100×; Stars of magnitude 12 to 14',
    ],
    'unknown token kept' => [
        'Ring neb,B,pL,cE / Ring Nebula',
        'Ring neb, bright, fairly large, considerably elongated',
    ],
]);

it('leaves descriptions without abbreviations alone', function (?string $description) {
    expect(DreyerDescription::plain($description))->toBeNull();
})->with([
    'empty'         => [''],
    'null'          => [null],
    'catalog notes' => ['C 30 / H400 / Clark x / RASC / H I 53'],
    'english'       => ['Diam. 7\', elongated NE and SW, darkest part at SW end'],
    'double star'   => ['Mv A: 9.98 - B: 9.82'],
    'english words' => ['Round / Nearly edge-on / Large'],
]);

it('does not read words as abbreviations', function () {
    // Neb is a nebula, not "nucleus, extremely brighter"; Ring is not R + i + n + g.
    expect(DreyerDescription::segment('vF,vS Neb'))->toBe('Very faint, very small nebula')
        ->and(DreyerDescription::segment('Ring'))->toBeNull()
        ->and(DreyerDescription::segment('sc'))->toBe('Scattered');
});

it('writes the description in the language of the reader', function () {
    app()->setLocale('nl');

    expect(DreyerDescription::plain('F,pL,iR,bM,*10 f'))
        ->toBe('Zwak, vrij groot, onregelmatig rond, helderder in het midden, een ster van magnitude 10 in het oosten');

    app()->setLocale('fr');

    expect(DreyerDescription::plain('vF,pL,mE 45'))
        ->toBe('Très faible, assez grand, nettement allongé dans l’angle de position 45°');
});

it('has every phrase in every language', function () {
    $english = array_keys(require lang_path('en/dreyer.php'));

    foreach (['nl', 'fr', 'de', 'es', 'sv'] as $language) {
        $translated = require lang_path($language.'/dreyer.php');

        expect(array_keys($translated))->toEqualCanonicalizing($english, $language)
            ->and(array_filter($translated, fn ($t) => trim($t) === ''))->toBe([], $language);
    }
});
