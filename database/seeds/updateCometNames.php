<?php

namespace Database\Seeders;

use App\Models\TargetName;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;

class updateCometNames extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $comets = \App\Models\Target::where('target_type', 'COMET')->get();

        foreach ($comets as $comet) {
            // If the comets starts with C/
            if (Str::startsWith($comet->target_name, 'C/')) {
                if (Str::startsWith($comet->target_name, 'C/2008 J2')) {
                    $number = '297P/';
                } else {
                    // Take the first 9 characters
                    $number = Str::substr($comet->target_name, 0, 9);
                }
            } elseif (Str::startsWith($comet->target_name, '29/P')) {
                $number = '29P/';
            } elseif (Str::startsWith($comet->target_name, 'P/2001 Q2')) {
                $number = '185P/';
            } elseif (Str::startsWith($comet->target_name, 'P/2006 T1 (Levy)')) {
                $number = '255P/';
            } elseif (Str::startsWith($comet->target_name, 'P/2009 UG31')) {
                $number = '279P/';
            } elseif (Str::startsWith($comet->target_name, 'P/2009 R2')) {
                $number = '226P/';
            } elseif (Str::startsWith($comet->target_name, 'P/2009 L2')) {
                $number = '325P/';
            } elseif (Str::startsWith($comet->target_name, 'Kohler')) {
                $number = 'C/1977 R1 (Kohler)';
            } elseif (Str::startsWith($comet->target_name, '73P/Schwassmann Wachmann Comp. C')) {
                $number = '73P/Schwassmann-Wachmann 3-C';
            } elseif (Str::startsWith($comet->target_name, '73P/Schwassmann Wachmann Comp. B')) {
                $number = '73P/Schwassmann-Wachmann 3-B';
            } elseif (Str::startsWith($comet->target_name, '73P/Schwassmann Wachmann Comp. G')) {
                $number = '73P/Schwassmann-Wachmann 3-G';
            } elseif (Str::startsWith($comet->target_name, 'D/1993')) {
                $number = 'D/1993 F2-A (Shoemaker-Levy 9)';
            } elseif (Str::startsWith($comet->target_name, 'P/')) {
                $number = Str::substr($comet->target_name, 0, 9);
            } else {
                // A periodic comet, starting with P/
                $number = Str::before($comet->target_name, 'P/') . 'P/';
            }
            // dump($comet->target_name);
            // Get the correct name from the CometOrbitalElements class
            $newName              = CometsOrbitalElements::where('name', 'like', $number . '%')->first()->name;
            $targetName           = TargetName::where('altname', $comet->target_name)->first();
            $targetName->catindex = $newName;
            $targetName->altname  = $newName;
            $targetName->save();
            dump('Changing ' . $comet->target_name . ' to ' . $newName);

            $comet->target_name = $newName;
            $comet->name        = $newName;
            $comet->save();
        }

        $comets = \App\Models\CometObjectOld::get();

        foreach ($comets as $comet) {
            // If the comets starts with C/
            if (Str::startsWith($comet->name, 'C/')) {
                if (Str::startsWith($comet->name, 'C/2008 J2')) {
                    $number = '297P/';
                } else {
                    // Take the first 9 characters
                    $number = Str::substr($comet->name, 0, 9);
                }
            } elseif (Str::startsWith($comet->name, '29/P')) {
                $number = '29P/';
            } elseif (Str::startsWith($comet->name, 'P/2001 Q2')) {
                $number = '185P/';
            } elseif (Str::startsWith($comet->name, 'P/2006 T1 (Levy)')) {
                $number = '255P/';
            } elseif (Str::startsWith($comet->name, 'P/2009 UG31')) {
                $number = '279P/';
            } elseif (Str::startsWith($comet->name, 'P/2009 R2')) {
                $number = '226P/';
            } elseif (Str::startsWith($comet->name, 'P/2009 L2')) {
                $number = '325P/';
            } elseif (Str::startsWith($comet->name, 'Kohler')) {
                $number = 'C/1977 R1 (Kohler)';
            } elseif (Str::startsWith($comet->name, 'D/1993')) {
                $number = 'D/1993 F2-A (Shoemaker-Levy 9)';
            } elseif (Str::startsWith($comet->name, 'P/')) {
                $number = Str::substr($comet->name, 0, 9);
            } else {
                if (Str::startsWith($comet->name, '73P/')) {
                    if (Str::startsWith($comet->name, '73P/Schwassmann Wachmann Comp. C')) {
                        $number = '73P/Schwassmann-Wachmann 3-C';
                    } elseif (Str::startsWith($comet->name, '73P/Schwassmann Wachmann Comp. B')) {
                        $number = '73P/Schwassmann-Wachmann 3-B';
                    } elseif (Str::startsWith($comet->name, '73P/Schwassmann Wachmann Comp. G')) {
                        $number = '73P/Schwassmann-Wachmann 3-G';
                    } else {
                        continue;
                    }
                } else {
                    // A periodic comet, starting with P/
                    $number = Str::before($comet->name, 'P/') . 'P/';
                }
            }
            // Get the correct name from the CometOrbitalElements class
            $newName              = CometsOrbitalElements::where('name', 'like', $number . '%')->first()->name;
            dump('Changing ' . $comet->name . ' to ' . $newName);

            $comet->name        = $newName;
            $comet->save();
        }
    }
}
