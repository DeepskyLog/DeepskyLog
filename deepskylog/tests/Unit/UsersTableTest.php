<?php

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_all_the_expected_columns_after_migrating_up()
    {
        Artisan::call('migrate');

        $this->assertTrue(Schema::hasColumns('users', [
            'username',
            'type',
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
        ]));
    }

    /** @test */
    public function it_drops_all_the_columns_after_migrating_down()
    {
        Artisan::call('migrate');
        Artisan::call('migrate:rollback');

        $this->assertFalse(Schema::hasColumns('users', [
            'username',
            'type',
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
        ]));
    }
}
