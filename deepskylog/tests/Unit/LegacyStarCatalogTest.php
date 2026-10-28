<?php

use App\Support\LegacyStarCatalog;

it('normalizes legacy star rows with full designation fields', function () {
    $normalized = LegacyStarCatalog::normalizeRow('stars8', [
        'type' => 'AA1STAR',
        'nameCon' => 'AND',
        'nameBayer' => 'alf',
        'nameBayer2' => '1',
        'nameFlamSteed' => '21',
        'name' => 'SOA36042',
        'RA2000mas' => 4648500,
        'DE2000mas' => 162825120,
        'RA2000' => 0.0860833,
        'DE2000' => 45.2292,
        'vMag' => 670,
        'spType' => 'A0V',
    ]);

    expect($normalized['source_table'])->toBe('stars8');
    expect($normalized['constellation_code'])->toBe('AND');
    expect($normalized['bayer'])->toBe('alf');
    expect($normalized['bayer_suffix'])->toBe('1');
    expect($normalized['flamsteed'])->toBe('21');
    expect($normalized['name'])->toBe('SOA36042');
    expect(abs($normalized['ra_hours'] - 0.0860833))->toBeLessThan(0.000001);
    expect(abs($normalized['ra_degrees'] - 1.2912495))->toBeLessThan(0.000001);
    expect(abs($normalized['decl_degrees'] - 45.2292))->toBeLessThan(0.000001);
    expect($normalized['vmag_int'])->toBe(670);
    expect(abs($normalized['vmag'] - 6.70))->toBeLessThan(0.000001);
    expect($normalized['spectral_type'])->toBe('A0V');
    expect($normalized['source_key'])->toHaveLength(40);
});

it('normalizes sparse deep catalog rows without designations', function () {
    $normalized = LegacyStarCatalog::normalizeRow('stars18', [
        'RA2000' => 12.6172,
        'DE2000' => -88.6004,
        'vMag' => 1800,
    ]);

    expect($normalized['source_table'])->toBe('stars18');
    expect($normalized['type'])->toBeNull();
    expect($normalized['name'])->toBeNull();
    expect($normalized['ra_mas'])->toBeNull();
    expect($normalized['dec_mas'])->toBeNull();
    expect(abs($normalized['ra_degrees'] - 189.258))->toBeLessThan(0.001);
    expect(abs($normalized['decl_degrees'] + 88.6004))->toBeLessThan(0.000001);
    expect($normalized['vmag_int'])->toBe(1800);
    expect(abs($normalized['vmag'] - 18.00))->toBeLessThan(0.000001);
});