<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

it('has all the expected columns after migrating up', function () {
    Artisan::call('migrate');

    expect(Schema::hasColumns('users', [
        'username',
        'country',
        'stdlocation',
        'stdtelescope',
        'language',
        'icqname',
        'observationlanguage',
        'standardAtlasCode',
        'fstOffset',
        'copyright',
        'overviewdsos',
        'lookupdsos',
        'detaildsos',
        'overviewstars',
        'lookupstars',
        'detailstars',
        'atlaspagefont',
        'photosize1',
        'overviewFoV',
        'photosize2',
        'lookupFoV',
        'detailFoV',
        'sendMail',
        'version',
        'showInches',
        'about',
    ]))->toBeTrue();
});
it('drops all the columns after migrating down', function () {
    Artisan::call('migrate');
    Artisan::call('migrate:rollback');

    expect(Schema::hasColumns('users', [
        'username',
        'country',
        'stdlocation',
        'stdtelescope',
        'language',
        'icqname',
        'observationlanguage',
        'standardAtlasCode',
        'fstOffset',
        'copyright',
        'overviewdsos',
        'lookupdsos',
        'detaildsos',
        'overviewstars',
        'lookupstars',
        'detailstars',
        'atlaspagefont',
        'photosize1',
        'overviewFoV',
        'photosize2',
        'lookupFoV',
        'detailFoV',
        'sendMail',
        'version',
        'showInches',
        'about',
    ]))->toBeFalse();
});
