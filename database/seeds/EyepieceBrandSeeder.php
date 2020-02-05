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
//        EyepieceBrand::create(
//            [
//                'name' => 'Celestron',
//            ]
//        );
    }
}
