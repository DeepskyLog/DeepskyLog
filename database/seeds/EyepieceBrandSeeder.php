<?php

use Illuminate\Database\Seeder;
use App\EyepieceBrand;

class EyepieceBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('eyepiece_brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        EyepieceBrand::create(
            [
                'brand' => 'Televue',
            ]
        );
        EyepieceBrand::create(
            [
                'brand' => 'Baader',
            ]
        );
        EyepieceBrand::create(
            [
                'brand' => 'Meade',
            ]
        );
        EyepieceBrand::create(
            [
                'brand' => 'University Optics',
            ]
        );
        EyepieceBrand::create(
            [
                'brand' => 'Pentax',
            ]
        );
        EyepieceBrand::create(
            [
                'brand' => 'Celestron',
            ]
        );
        EyepieceBrand::create(
            [
                'brand' => 'Vixen',
            ]
        );
    }
}
