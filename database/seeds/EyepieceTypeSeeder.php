<?php

use App\EyepieceType;
use Illuminate\Database\Seeder;

class EyepieceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('eyepiece_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'Plössl',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'Panoptic',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'Nagler',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'Radian',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'Apollo',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'Ethos',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'DeLite',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Televue',
                'type' => 'Delos',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Baader',
                'type' => 'Hyperion',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Baader',
                'type' => 'Morpheus',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Meade',
                'type' => 'Super Plössl',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Meade',
                'type' => 'MWA',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'University Optics',
                'type' => 'Ortho',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'University Optics',
                'type' => 'König',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'University Optics',
                'type' => 'Wide Scan',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'University Optics',
                'type' => 'K',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'University Optics',
                'type' => 'ER',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'University Optics',
                'type' => 'UW',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Pentax',
                'type' => 'SMC XW',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Pentax',
                'type' => 'SMC XL',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Pentax',
                'type' => 'SMC XF',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Celestron',
                'type' => 'X-Cel LX',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Celestron',
                'type' => 'Omni',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Celestron',
                'type' => 'Luminos',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Celestron',
                'type' => 'Kellner',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Celestron',
                'type' => 'Plössl',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Celestron',
                'type' => 'Ortho',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Celestron',
                'type' => 'Erfle',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Vixen',
                'type' => 'LV',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Vixen',
                'type' => 'NPL',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Vixen',
                'type' => 'SLV',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Vixen',
                'type' => 'NLVW',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Vixen',
                'type' => 'NLV',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Vixen',
                'type' => 'HR',
            ]
        );
        EyepieceType::create(
            [
                'brand' => 'Vixen',
                'type' => 'SSW',
            ]
        );
    }
}
