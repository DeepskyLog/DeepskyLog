<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->string('icqname', 11)->nullable();
            $table->string('type', 8);
            $table->string('con', 5)->nullable();
            $table->float('ra')->unsigned()->nullable();
            $table->float('decl')->nullable();
            $table->float('mag')->nullable();
            $table->float('subr')->nullable();
            $table->float('diam1')->unsigned()->nullable();
            $table->float('diam2')->unsigned()->nullable();
            $table->smallInteger('pa')->unsigned()->nullable();
            $table->float('SBObj')->nullable();
            $table->string('datasource', 50)->nullable();
            $table->string('description', 1024)->nullable();

            $table->smallInteger('urano')->unsigned()->nullable();
            $table->smallInteger('urano_new')->unsigned()->nullable();
            $table->smallInteger('sky')->unsigned()->nullable();
            $table->smallInteger('millenium')->unsigned()->nullable();
            $table->smallInteger('taki')->unsigned()->nullable();
            $table->smallInteger('psa')->unsigned()->nullable();
            $table->smallInteger('torresB')->unsigned()->nullable();
            $table->smallInteger('torresBC')->unsigned()->nullable();
            $table->smallInteger('torresC')->unsigned()->nullable();
            $table->smallInteger('milleniumbase')->unsigned()->nullable();
            $table->smallInteger('DSLDL')->unsigned()->nullable();
            $table->smallInteger('DSLDP')->unsigned()->nullable();
            $table->smallInteger('DSLLL')->unsigned()->nullable();
            $table->smallInteger('DSLLP')->unsigned()->nullable();
            $table->smallInteger('DSLOL')->unsigned()->nullable();
            $table->smallInteger('DSLOP')->unsigned()->nullable();
            $table->smallInteger('DeepskyHunter')->unsigned()->nullable();
            $table->smallInteger('Interstellarum')->unsigned()->nullable();

            $table->timestamps();

            $table->foreign('type')
                ->references('id')->on('target_types');

            $table->foreign('con')
                ->references('id')->on('constellations');
        });

            // Insert the Sun
            DB::table('targets')->insert(
                [
                    'name' => 'Sun',
                    'type' => 'SUN'
                ]
            );

            // Insert the planets
            DB::table('targets')->insert(
                [
                    'name' => 'Mercury',
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Venus',
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Mars',
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Jupiter',
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Saturn',
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Uranus',
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Neptune',
                    'type' => 'PLANET'
                ]
            );

            // Insert the craters
            DB::table('targets')->insert(
                [
                    'name' => 'Abbe',
                    'type' => 'CRATER'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Abbe H',
                    'type' => 'CRATER'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Abbe K',
                    'type' => 'CRATER'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Abbe M',
                    'type' => 'CRATER'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => 'Abbot',
                    'type' => 'CRATER'
                ]
            );



/*            Abel                            34.5S  87.3E  122.0 Crater         F1895   F1895
            Abel A                          36.6S  86.0E   19.0 Crater                 NLF?
            Abel B                          36.7S  82.8E   41.0 Crater                 NLF?
            Abel C                          36.0S  81.0E   31.0 Crater                 NLF?
            Abel D                          37.7S  87.7E   30.0 Crater                 NLF?
            Abel E                          37.8S  86.5E   13.0 Crater                 NLF?
            Abel J                          35.5S  79.0E   11.0 Crater                 NLF?
            Abel K                          35.0S  77.2E    9.0 Crater                 NLF?
            Abel L                          34.4S  82.6E   67.0 Crater                 NLF?
            Abel M                          32.2S  83.6E   81.0 Crater                 NLF?
            Abenezra                        21.0S  11.9E   42.0 Crater         VL1645  R1651
            Abenezra A                      22.8S  10.5E   23.0 Crater                 NLF?
            Abenezra B                      20.8S  10.1E   14.0 Crater                 NLF?
            Abenezra C                      21.3S  11.1E   44.0 Crater                 NLF?
            Abenezra D                      21.7S   9.7E    8.0 Crater                 NLF?
            Abenezra E                      21.4S   9.4E   14.0 Crater                 NLF?
            Abenezra F                      21.5S  10.3E    7.0 Crater                 NLF?
            Abenezra G                      20.5S  11.0E    5.0 Crater                 NLF?
            Abenezra H                      21.1S  12.8E    4.0 Crater                 NLF?
            Abenezra J                      19.9S  10.7E    5.0 Crater                 NLF?
            Abenezra P                      19.9S   9.9E   44.0 Crater                 NLF?
            Abetti                          20.1N  27.8E    1.5 Crater                 IAU1976
            Abul W|%afa                      1.0N 116.6E   55.0 Crater                 IAU1970
            Abul W|%afa A                    1.4N 116.8E   16.0 Crater                 AW82
            Abul W|%afa Q                    0.2N 115.7E   30.0 Crater                 AW82
            Abulfeda                        13.8S  13.9E   65.0 Crater         VL1645  R1651
            Abulfeda A                      16.4S  10.8E   14.0 Crater                 NLF?
            Abulfeda B                      14.5S  16.4E   15.0 Crater                 NLF?
            Abulfeda BA                     14.6S  16.8E   13.0 Crater                 NLF?
            Abulfeda C                      12.8S  10.9E   17.0 Crater                 NLF?
            Abulfeda D                      13.2S   9.5E   20.0 Crater                 NLF?
            Abulfeda E                      16.7S  10.2E    6.0 Crater                 NLF?
            Abulfeda F                      16.2S  13.0E   13.0 Crater                 NLF?
            Abulfeda G                      13.1S   9.0E    7.0 Crater                 NLF?
            Abulfeda H                      13.8S   9.6E    5.0 Crater                 NLF?
            Abulfeda J                      15.5S  10.0E    5.0 Crater                 NLF?
            Abulfeda K                      14.9S  10.6E   10.0 Crater                 NLF?
            Abulfeda L                      14.1S  10.7E    5.0 Crater                 NLF?
            Abulfeda M                      16.2S  12.1E   10.0 Crater                 NLF?
            Abulfeda N                      15.1S  12.2E   14.0 Crater                 NLF?
            Abulfeda O                      15.4S  11.2E    7.0 Crater                 NLF?
            Abulfeda P                      15.5S  11.5E    5.0 Crater                 NLF?
            Abulfeda Q                      12.8S  12.3E    3.0 Crater                 NLF?
            Abulfeda R                      12.8S  13.0E    7.0 Crater                 NLF?
            Abulfeda S                      12.2S  13.3E    5.0 Crater                 NLF?
            Abulfeda T                      14.8S  13.8E    7.0 Crater                 NLF?
            Abulfeda U                      13.0S  13.8E    6.0 Crater                 NLF?
            Abulfeda W                      12.5S  13.9E    5.0 Crater                 NLF?
            Abulfeda X                      15.0S  14.0E    6.0 Crater                 NLF?
            Abulfeda Y                      12.8S  14.1E    5.0 Crater                 NLF?
            Abulfeda Z                      14.7S  15.2E    5.0 Crater                 NLF?
            Acosta                           5.6S  60.1E   13.0 Crater                 IAU1976
            Adams                           31.9S  68.2E   66.0 Crater                 IAU1970
            Adams B                         31.5S  65.6E   28.0 Crater                 NLF?
            Adams C                         32.3S  65.5E   10.0 Crater                 NLF?
            Adams D                         32.5S  71.6E   42.0 Crater                 NLF?
            Adams M                         34.8S  69.2E   24.0 Crater                 NLF?
            Adams P                         35.2S  71.0E   24.0 Crater                 NLF?
            Agatharchides                   19.8S  30.9W   48.0 Crater         M1834   M1834
            Agatharchides A                 23.2S  28.4W   16.0 Crater                 NLF?
            Agatharchides B                 21.5S  31.6W    7.0 Crater                 NLF?
            Agatharchides C                 22.0S  32.9W   12.0 Crater                 NLF?
            Agatharchides E                 20.7S  33.0W   15.0 Crater                 NLF?
            Agatharchides F                 20.3S  31.8W    6.0 Crater                 NLF?
            Agatharchides G                 20.1S  26.7W    6.0 Crater                 NLF?
            Agatharchides H                 20.4S  33.9W   15.0 Crater                 NLF?
            Agatharchides J                 21.6S  32.5W   13.0 Crater                 NLF?
            Agatharchides K                 21.0S  27.4W   11.0 Crater                 NLF?
            Agatharchides L                 21.1S  26.7W    8.0 Crater                 NLF?
            Agatharchides N                 21.1S  29.6W   22.0 Crater                 NLF?
            Agatharchides O                 19.2S  26.6W    5.0 Crater                 NLF?
            Agatharchides P                 20.2S  28.7W   66.0 Crater         H1647   NLF?
            Agatharchides R                 18.3S  30.7W    5.0 Crater                 NLF?
            Agatharchides S                 17.7S  30.5W    3.0 Crater                 NLF?
            Agatharchides T                 18.2S  27.7W    5.0 Crater                 NLF?
            Agrippa                          4.1N  10.5E   44.0 Crater         VL1645  R1651
            Agrippa B                        6.2N   9.4E    4.0 Crater                 NLF?
            Agrippa D                        3.8N   6.7E   20.0 Crater                 NLF?
            Agrippa E                        5.2N   8.5E    5.0 Crater                 NLF?
            Agrippa F                        4.4N  11.4E    6.0 Crater                 NLF?
            Agrippa G                        3.9N   6.2E   13.0 Crater                 NLF?
            Agrippa H                        4.8N  10.7E    6.0 Crater                 NLF?
            Agrippa S                        5.3N   8.9E   32.0 Crater                 NLF?
            Airy                            18.1S   5.7E   36.0 Crater         VL1645  M1834
            Airy A                          17.0S   7.7E   13.0 Crater                 NLF?
            Airy B                          17.6S   8.5E   29.0 Crater         VL1645  NLF?
            Airy C                          19.3S   4.9E   34.0 Crater                 NLF?
            Airy D                          18.2S   8.5E    7.0 Crater                 NLF?
            Airy E                          20.7S   7.6E   38.0 Crater                 NLF?
            Airy F                          18.2S   7.3E    5.0 Crater                 NLF?
            Airy G                          18.7S   7.0E   25.0 Crater                 NLF?
            Airy H                          18.7S   5.8E    9.0 Crater                 NLF?
            Airy J                          19.0S   6.1E    4.0 Crater                 NLF?
            Airy L                          20.4S   7.5E    6.0 Crater                 NLF?
            Airy M                          19.2S   7.6E    1.0 Crater                 NLF?
            Airy N                          17.8S   8.2E    8.0 Crater                 NLF?
            Airy O                          16.7S   8.3E    5.0 Crater                 NLF?
            Airy P                          15.8S   8.4E    7.0 Crater                 NLF?
            Airy R                          19.6S   8.8E    7.0 Crater                 NLF?
            Airy S                          17.2S   9.4E    5.0 Crater                 NLF?
            Airy T                          19.2S   9.4E   40.0 Crater                 NLF?
            Airy V                          17.5S   9.2E    5.0 Crater                 NLF?
            Airy X                          18.9S  10.2E    4.0 Crater                 NLF?
            Aitken                          16.8S 173.4E  135.0 Crater                 IAU1970
            Aitken A                        14.0S 173.7E   13.0 Crater                 AW82
            Aitken C                        14.0S 175.8E   74.0 Crater                 AW82
            Aitken G                        16.8S 174.2E    7.0 Crater                 AW82
            Aitken N                        17.7S 172.7E    7.0 Crater                 AW82
            Aitken Y                        12.0S 173.2E   35.0 Crater                 AW82
            Aitken Z                        15.1S 173.3E   33.0 Crater                 AW82
            Akis                            20.0N  31.8W    2.0 Crater                 IAU1976
            Al-Bakri                        14.3N  20.2E   12.0 Crater                 IAU1976
            Al-Biruni                       17.9N  92.5E   77.0 Crater                 IAU1970
            Al-Biruni C                     18.4N  93.0E    9.0 Crater                 AW82
            Al-Khwarizmi                     7.1N 106.4E   65.0 Crater                 IAU1973
            Al-Khwarizmi B                   9.0N 107.4E   62.0 Crater                 AW82
            Al-Khwarizmi G                   6.9N 107.1E   95.0 Crater         AW82    AW82
            Al-Khwarizmi H                   6.0N 109.2E   50.0 Crater         AW82    AW82
            Al-Khwarizmi J                   6.2N 107.6E   47.0 Crater         AW82    AW82
            Al-Khwarizmi K                   4.6N 107.6E   26.0 Crater         AW82    AW82
            Al-Khwarizmi L                   3.9N 107.4E   35.0 Crater         AW82    AW82
            Al-Khwarizmi M                   3.1N 107.0E   18.0 Crater         AW82    AW82
            Al-Khwarizmi T                   7.0N 104.5E   15.0 Crater         AW82    AW82
            Al-Marrakushi                   10.4S  55.8E    8.0 Crater                 IAU1976
            Alan                            10.9S   6.1W    2.0 Crater                 IAU1976
            Albategnius                     11.7S   4.3E  114.0 Crater         VL1645  R1651
            Albategnius A                    8.9S   3.2E    7.0 Crater                 NLF?
            Albategnius B                   10.0S   4.0E   20.0 Crater                 NLF?
            Albategnius C                   10.3S   3.7E    6.0 Crater                 NLF?
            Albategnius D                   11.3S   7.1E    9.0 Crater                 NLF?
            Albategnius E                   12.9S   6.4E   14.0 Crater                 NLF?
            Albategnius G                    9.4S   1.9E   15.0 Crater                 NLF?
            Albategnius H                    9.7S   5.2E   11.0 Crater                 NLF?
            Albategnius J                   11.1S   6.2E    7.0 Crater                 NLF?
            Albategnius K                    9.9S   2.0E   10.0 Crater                 NLF?
            Albategnius L                   12.1S   6.3E    8.0 Crater                 NLF?
            Albategnius M                    8.9S   4.2E    9.0 Crater                 NLF?
            Albategnius N                    9.8S   4.5E    9.0 Crater                 NLF?
            Albategnius O                   13.2S   4.2E    5.0 Crater                 NLF?
            Albategnius P                   12.9S   4.5E    5.0 Crater                 NLF?
            Albategnius S                   13.3S   6.1E    6.0 Crater                 NLF?
            Albategnius T                   12.6S   6.1E    9.0 Crater                 NLF?
            Alden                           23.6S 110.8E  104.0 Crater                 IAU1970
            Alden B                         20.5S 112.6E   17.0 Crater                 AW82
            Alden C                         22.5S 111.4E   50.0 Crater                 AW82
            Alden E                         23.2S 112.4E   28.0 Crater                 AW82
            Alden V                         22.5S 110.1E   19.0 Crater                 AW82
            Alder                           48.6S 177.4W   77.0 Crater                 IAU1979
            Alder E                         47.6S 172.3W   16.0 Crater                 AW82
            Aldrin                           1.4N  22.1E    3.0 Crater                 IAU1970
            Alekhin                         68.2S 131.3W   70.0 Crater                 IAU1970
            Alekhin E                       67.2S 124.1W   38.0 Crater                 AW82
            Alexander                       40.3N  13.5E   81.0 Crater         H1647   NLF
            Alexander A                     40.7N  14.9E    4.0 Crater                 NLF?
            Alexander B                     40.3N  15.2E    4.0 Crater                 NLF?
            Alexander C                     38.5N  14.9E    5.0 Crater                 NLF?
            Alexander K                     40.5N  19.3E    4.0 Crater                 NLF?
            Alfraganus                       5.4S  19.0E   20.0 Crater                 NLF
            Alfraganus A                     3.0S  20.3E   13.0 Crater                 NLF?
            Alfraganus C                     6.1S  18.1E   11.0 Crater                 NLF?
            Alfraganus D                     4.0S  20.1E    8.0 Crater                 NLF?
            Alfraganus E                     4.6S  19.0E    4.0 Crater                 NLF?
            Alfraganus F                     3.5S  20.8E    9.0 Crater                 NLF?
            Alfraganus G                     2.6S  21.2E    6.0 Crater                 NLF?
            Alfraganus H                     4.4S  19.1E   13.0 Crater                 NLF?
            Alfraganus K                     5.3S  19.5E    4.0 Crater                 NLF?
            Alfraganus M                     5.6S  19.6E    3.0 Crater                 NLF?
            Alhazen                         15.9N  71.8E   32.0 Crater         S1791   S1791
            Alhazen A                       16.2N  74.3E   14.0 Crater                 NLF?
            Alhazen D                       19.7N  75.2E   33.0 Crater                 NLF?
            Aliacensis                      30.6S   5.2E   79.0 Crater         VL1645  R1651
            Aliacensis A                    29.7S   7.4E   14.0 Crater                 NLF?
            Aliacensis B                    31.3S   3.2E   16.0 Crater                 NLF?
            Aliacensis C                    32.6S   5.4E    8.0 Crater                 NLF?
            Aliacensis D                    33.1S   6.9E   10.0 Crater                 NLF?
            Aliacensis E                    30.4S   2.3E    9.0 Crater                 NLF?
            Aliacensis F                    32.7S   3.9E    5.0 Crater                 NLF?
            Aliacensis G                    33.3S   4.7E    8.0 Crater                 NLF?
            Aliacensis H                    31.8S   6.1E    6.0 Crater                 NLF?
            Aliacensis K                    31.4S   6.2E    7.0 Crater                 NLF?
            Aliacensis W                    31.9S   5.3E   11.0 Crater                 NLF?
            Aliacensis X                    29.6S   6.9E    4.0 Crater                 NLF?
            Aliacensis Y                    30.1S   7.4E    5.0 Crater                 NLF?
            Aliacensis Z                    30.0S   4.6E    4.0 Crater                 NLF?
            Almanon                         16.8S  15.2E   49.0 Crater         VL1645  R1651
            Almanon A                       17.7S  15.3E   10.0 Crater                 NLF?
            Almanon B                       18.3S  15.3E   25.0 Crater                 NLF?
            Almanon C                       16.1S  16.0E   16.0 Crater                 NLF?
            Almanon D                       18.6S  15.6E    6.0 Crater                 NLF?
            Almanon E                       17.9S  13.7E    5.0 Crater                 NLF?
            Almanon F                       15.9S  14.3E    5.0 Crater                 NLF?
            Almanon G                       17.8S  14.6E    5.0 Crater                 NLF?
            Almanon H                       19.0S  15.3E    6.0 Crater                 NLF?
            Almanon K                       15.8S  15.4E    8.0 Crater                 NLF?
            Almanon L                       18.9S  16.6E    6.0 Crater                 NLF?
            Almanon P                       18.5S  17.0E    8.0 Crater                 NLF?
            Almanon Q                       18.1S  17.0E    5.0 Crater                 NLF?
            Almanon R                       18.2S  15.9E    4.0 Crater                 NLF?
            Aloha                           29.8N  53.9W    3.0 Crater                 IAU1976
            Alpes A                         51.4N   0.3W   11.0 Crater                 NLF?
            Alpes B                         45.8N   0.9W    5.0 Crater                 NLF?
            Alpetragius                     16.0S   4.5W   39.0 Crater         VL1645  R1651
            Alpetragius B                   15.1S   6.8W   10.0 Crater                 NLF?
            Alpetragius C                   13.7S   6.1W    2.0 Crater                 NLF?
            Alpetragius G                   18.2S   6.5W   12.0 Crater                 NLF?
            Alpetragius H                   18.0S   6.0W    5.0 Crater                 NLF?
            Alpetragius J                   18.0S   5.7W    4.0 Crater                 NLF?
            Alpetragius M                   16.5S   3.2W   24.0 Crater                 NLF?
            Alpetragius N                   16.7S   3.8W   11.0 Crater                 NLF?
            Alpetragius U                   17.7S   5.1W   14.0 Crater                 NLF?
            Alpetragius V                   18.1S   5.8W   17.0 Crater                 NLF?
            Alpetragius W                   17.9S   6.0W   27.0 Crater                 NLF?
            Alpetragius X                   15.6S   5.7W   32.0 Crater                 NLF?
            Alphonsus                       13.7S   3.2W  108.0 Crater         VL1645  R1651
            Alphonsus A                     14.8S   2.3W    4.0 Crater                 NLF?
            Alphonsus B                     13.2S   0.2W   24.0 Crater                 NLF?
            Alphonsus C                     14.4S   4.8W    4.0 Crater                 NLF?
            Alphonsus D                     15.1S   0.8W   23.0 Crater                 NLF?
            Alphonsus G                     12.3S   3.3W    4.0 Crater                 NLF?
            Alphonsus H                     15.6S   0.5W    8.0 Crater                 NLF?
            Alphonsus J                     15.1S   2.5W    8.0 Crater                 NLF?
            Alphonsus K                     12.5S   0.1W   20.0 Crater                 NLF?
            Alphonsus L                     12.0S   3.7W    4.0 Crater                 NLF?
            Alphonsus R                     14.4S   1.9W    3.0 Crater                 NLF?
            Alphonsus X                     15.0S   4.4W    5.0 Crater                 NLF?
            Alphonsus Y                     14.7S   1.8W    3.0 Crater                 NLF?
            Alter                           18.7N 107.5W   64.0 Crater                 IAU1970
            Alter K                         16.3N 106.0W   22.0 Crater                 AW82
            Alter W                         20.4N 109.2W   52.0 Crater                 AW82
            Ameghino                         3.3N  57.0E    9.0 Crater                 IAU1976
            Amici                            9.9S 172.1W   54.0 Crater                 IAU1970
            Amici M                         11.8S 171.9W  105.0 Crater                 AW82
            Amici N                         11.8S 172.5W   39.0 Crater                 AW82
            Amici P                         12.3S 174.1W   31.0 Crater                 AW82
            Amici Q                         12.0S 175.7W   47.0 Crater                 AW82
            Amici R                         11.4S 175.2W   34.0 Crater                 AW82
            Amici T                          9.7S 174.0W   43.0 Crater                 AW82
            Amici U                          8.7S 175.5W   96.0 Crater                 AW82
            Ammonius                         8.5S   0.8W    8.0 Crater                 IAU1976
            Amontons                         5.3S  46.8E    2.0 Crater                 IAU1976
            Amundsen                        84.3S  85.6E  101.0 Crater         W1926   IAU1964
            Amundsen C                      80.7S  83.2E   27.0 Crater                 RLA1963
            Anaxagoras                      73.4N  10.1W   50.0 Crater                 NLF
            Anaxagoras A                    72.2N   6.9W   18.0 Crater                 NLF?
            Anaxagoras B                    70.3N  11.4W    5.0 Crater                 NLF?
            Anaximander                     66.9N  51.3W   67.0 Crater                 NLF
            Anaximander A                   68.0N  50.2W   16.0 Crater                 NLF?
            Anaximander B                   67.8N  60.7W   78.0 Crater                 NLF?
            Anaximander D                   65.4N  50.1W   92.0 Crater                 NLF?
            Anaximander H                   65.2N  40.8W    9.0 Crater                 NLF?
            Anaximander R                   66.2N  54.9W    8.0 Crater                 NLF?
            Anaximander S                   68.3N  53.4W    7.0 Crater                 NLF?
            Anaximander T                   67.2N  52.0W    7.0 Crater                 NLF?
            Anaximander U                   64.1N  48.3W    8.0 Crater                 NLF?
            Anaximenes                      72.5N  44.5W   80.0 Crater                 NLF
            Anaximenes B                    68.8N  37.9W    9.0 Crater                 NLF?
            Anaximenes E                    66.5N  31.4W   10.0 Crater                 NLF?
            Anaximenes G                    73.8N  40.4W   56.0 Crater                 NLF?
            Anaximenes H                    74.6N  45.3W   43.0 Crater                 NLF?
            And|vel                         10.4S  12.4E   35.0 Crater         M1935   M1935
            And|vel A                       10.8S  11.3E   14.0 Crater                 NLF?
            And|vel C                        9.0S  11.2E    3.0 Crater                 NLF?
            And|vel D                       10.8S  11.7E    6.0 Crater                 NLF?
            And|vel E                       12.0S  12.2E    6.0 Crater                 NLF?
            And|vel F                        8.3S  11.1E    9.0 Crater                 NLF?
            And|vel G                       11.0S  12.4E    4.0 Crater                 NLF?
            And|vel H                        6.7S  11.3E    6.0 Crater                 NLF?
            And|vel J                        7.5S  11.4E    6.0 Crater                 NLF?
            And|vel K                        5.8S  11.6E    4.0 Crater                 NLF?
            And|vel M                        9.7S  11.1E   27.0 Crater                 NLF?
            And|vel N                       10.2S  11.4E    8.0 Crater                 NLF?
            And|vel P                       11.6S  12.3E   19.0 Crater                 NLF?
            And|vel S                       11.4S  12.7E    4.0 Crater                 NLF?
            And|vel T                       11.2S  13.3E    4.0 Crater                 NLF?
            And|vel W                       12.4S  12.3E   12.0 Crater                 NLF?
            Anders                          41.3S 142.9W   40.0 Crater                 IAU1970
            Anders D                        40.4S 140.5W   23.0 Crater                 AW82
            Anders G                        41.8S 141.9W   18.0 Crater                 AW82
            Anders X                        39.7S 143.8W   21.0 Crater                 AW82
            Anderson                        15.8N 171.1E  109.0 Crater                 IAU1970
            Anderson E                      16.9N 173.4E   28.0 Crater                 AW82
            Anderson F                      16.3N 173.6E   49.0 Crater                 AW82
            Anderson L                      14.6N 170.9E   14.0 Crater                 AW82
            Andersson                       49.7S  95.3W   13.0 Crater                 IAU85
            Andronov                        22.7S 146.1E   16.0 Crater                 IAU1976
            Ango                            20.5N  32.3W    1.0 Crater                 IAU1976
            Angstr|:om                      29.9N  41.6W    9.0 Crater         K1898   K1898
            Angstr|:om A                    30.9N  41.1W    6.0 Crater                 NLF?
            Angstr|:om B                    31.7N  44.1W    6.0 Crater                 NLF?
            Ann                             25.1N   0.1W    3.0 Crater                 IAU1976
            Annegrit                        29.4N  25.6W    1.0 Crater                 IAU1976
            Ansgarius                       12.7S  79.7E   94.0 Crater         M1834   M1834
            Ansgarius B                     11.9S  83.8E   29.0 Crater                 NLF?
            Ansgarius C                     14.8S  74.8E   14.0 Crater                 NLF?
            Ansgarius M                     11.3S  78.8E    7.0 Crater                 NLF?
            Ansgarius N                     11.9S  81.2E   10.0 Crater                 NLF?
            Ansgarius P                     13.0S  75.9E   10.0 Crater                 NLF?
            Antoniadi                       69.7S 172.0W  143.0 Crater                 IAU1970
            Anuchin                         49.0S 101.3E   57.0 Crater                 IAU1979
            Anuchin B                       46.7S 103.3E   24.0 Crater                 AW82
            Anuchin L                       50.2S 101.7E   15.0 Crater                 AW82
            Anuchin N                       51.6S  99.6E   33.0 Crater                 AW82
            Anuchin Q                       51.1S  98.3E   50.0 Crater                 AW82
            Anuchin V                       48.1S  99.6E   15.0 Crater                 AW82
            Anville                          1.9N  49.5E   10.0 Crater                 IAU1976
            Apianus                         26.9S   7.9E   63.0 Crater         VL1645  R1651
            Apianus A                       25.7S   6.6E   14.0 Crater                 NLF?
            Apianus B                       27.4S   9.0E   10.0 Crater                 NLF?
            Apianus C                       28.1S  10.5E   20.0 Crater                 NLF?
            Apianus D                       26.1S  10.7E   35.0 Crater                 NLF?
            Apianus E                       28.8S   8.2E    9.0 Crater                 NLF?
            Apianus F                       28.1S   6.4E    6.0 Crater                 NLF?
            Apianus G                       28.1S   7.7E    5.0 Crater                 NLF?
            Apianus H                       28.1S   8.7E    7.0 Crater                 NLF?
            Apianus J                       26.3S   8.6E    7.0 Crater                 NLF?
            Apianus K                       27.4S   9.3E    7.0 Crater                 NLF?
            Apianus L                       29.1S  10.9E    5.0 Crater                 NLF?
            Apianus M                       24.7S  10.3E    7.0 Crater                 NLF?
            Apianus N                       28.8S   9.9E    4.0 Crater                 NLF?
            Apianus P                       25.2S   9.2E   40.0 Crater                 NLF?
            Apianus R                       25.7S   8.9E   13.0 Crater                 NLF?
            Apianus S                       25.6S   8.5E    8.0 Crater                 NLF?
            Apianus T                       27.7S   9.5E   12.0 Crater                 NLF?
            Apianus U                       27.9S   9.0E   16.0 Crater                 NLF?
            Apianus V                       25.3S  10.5E    3.0 Crater                 NLF?
            Apianus W                       25.5S   7.4E    9.0 Crater                 NLF?
            Apianus X                       28.3S   7.1E    3.0 Crater                 NLF?
            Apollo                          36.1S 151.8W  537.0 Crater                 IAU1970
            Apollonius                       4.5N  61.1E   53.0 Crater         M1834   M1834
            Apollonius A                     4.8N  56.8E   24.0 Crater                 NLF?
            Apollonius B                     5.7N  57.6E   32.0 Crater                 NLF?
            Apollonius E                     4.4N  61.9E   16.0 Crater                 NLF?
            Apollonius F                     5.6N  60.0E   16.0 Crater                 NLF?
            Apollonius H                     3.4N  59.6E   20.0 Crater                 NLF?
            Apollonius J                     4.6N  57.5E   12.0 Crater                 NLF?
            Apollonius L                     6.5N  54.6E    9.0 Crater                 NLF?
            Apollonius M                     4.8N  61.9E   10.0 Crater                 NLF?
            Apollonius N                     4.8N  64.1E    9.0 Crater                 NLF?
            Apollonius S                     1.1N  62.6E   15.0 Crater                 NLF?
            Apollonius U                     4.9N  59.9E    7.0 Crater                 NLF?
            Apollonius V                     4.4N  58.2E   16.0 Crater                 NLF?
            Apollonius X                     7.0N  58.1E   31.0 Crater                 NLF?
            Apollonius Y                     4.9N  62.6E   10.0 Crater                 NLF?
            Appleton                        37.2N 158.3E   63.0 Crater                 IAU1970
            Appleton D                      38.0N 160.6E   37.0 Crater                 AW82
            Appleton M                      33.9N 158.3E   21.0 Crater                 AW82
            Appleton Q                      34.3N 155.3E   26.0 Crater                 AW82
            Appleton R                      36.2N 156.2E   39.0 Crater                 AW82
            Arago                            6.2N  21.4E   26.0 Crater         VL1645  M1834
            Arago B                          3.4N  20.8E    7.0 Crater                 NLF?
            Arago C                          3.9N  21.5E    3.0 Crater                 NLF?
            Arago D                          6.9N  22.4E    4.0 Crater                 NLF?
            Arago E                          8.5N  22.7E    6.0 Crater                 NLF?
            Aratus                          23.6N   4.5E   10.0 Crater                 NLF
            Aratus B                        24.2N   5.4E    7.0 Crater                 NLF?
            Aratus C                        24.1N   9.5E    4.0 Crater                 NLF?
            Aratus CA                       24.5N  11.2E    7.0 Crater                 NLF?
            Aratus D                        24.3N   8.6E    4.0 Crater                 NLF?
            Archimedes                      29.7N   4.0W   82.0 Crater         VL1645  R1651
            Archimedes C                    31.6N   1.5W    8.0 Crater                 NLF?
            Archimedes D                    32.2N   2.6W    5.0 Crater                 NLF?
            Archimedes E                    25.0N   7.2W    3.0 Crater                 NLF?
            Archimedes G                    29.1N   8.2W    3.0 Crater                 NLF?
            Archimedes H                    23.9N   7.0W    4.0 Crater                 NLF?
            Archimedes L                    25.0N   2.6W    4.0 Crater                 NLF?
            Archimedes M                    26.1N   3.2W    3.0 Crater                 NLF?
            Archimedes N                    24.1N   3.9W    3.0 Crater                 NLF?
            Archimedes P                    25.9N   2.5W    3.0 Crater                 NLF?
            Archimedes Q                    28.5N   2.4W    3.0 Crater                 NLF?
            Archimedes R                    26.0N   6.6W    4.0 Crater                 NLF?
            Archimedes S                    29.5N   2.7W    3.0 Crater                 NLF?
            Archimedes T                    30.3N   5.0W    3.0 Crater                 NLF?
            Archimedes U                    32.8N   1.9W    3.0 Crater                 NLF?
            Archimedes V                    32.9N   4.0W    3.0 Crater                 NLF?
            Archimedes W                    23.8N   6.2W    4.0 Crater                 NLF?
            Archimedes X                    31.0N   8.0W    2.0 Crater                 NLF?
            Archimedes Y                    29.9N   9.5W    2.0 Crater                 NLF?
            Archimedes Z                    26.8N   1.4W    2.0 Crater                 NLF?
            Archytas                        58.7N   5.0E   31.0 Crater         VL1645  NLF
            Archytas B                      61.3N   3.2E   36.0 Crater                 NLF?
            Archytas D                      63.5N  11.8E   43.0 Crater                 NLF?
            Archytas G                      55.6N   0.5E    7.0 Crater                 NLF?
            Archytas K                      62.6N   7.7E   15.0 Crater                 NLF?
            Archytas L                      56.2N   0.9E    5.0 Crater                 NLF?
            Archytas U                      62.8N   9.2E    8.0 Crater                 NLF?
            Archytas W                      61.2N   5.2E    6.0 Crater                 NLF?
            Argelander                      16.5S   5.8E   34.0 Crater         VL1645  S1878
            Argelander A                    16.5S   6.8E    9.0 Crater                 NLF?
            Argelander B                    15.5S   5.1E    6.0 Crater                 NLF?
            Argelander C                    16.3S   5.7E    4.0 Crater                 NLF?
            Argelander D                    17.6S   4.4E   11.0 Crater                 NLF?
            Argelander W                    16.7S   4.2E   19.0 Crater                 NLF?
            Ariadaeus                        4.6N  17.3E   11.0 Crater                 NLF
            Ariadaeus A                      4.6N  17.5E    8.0 Crater                 NLF?
            Ariadaeus B                      4.9N  15.0E    8.0 Crater                 NLF?
            Ariadaeus D                      4.9N  17.0E    4.0 Crater                 NLF?
            Ariadaeus E                      5.3N  17.7E   24.0 Crater                 NLF?
            Ariadaeus F                      4.4N  18.0E    3.0 Crater                 NLF?
            Aristarchus                     23.7N  47.4W   40.0 Crater         VL1645  R1651
            Aristarchus B                   26.3N  46.8W    7.0 Crater                 NLF?
            Aristarchus D                   23.7N  42.9W    5.0 Crater                 NLF?
            Aristarchus F                   21.7N  46.5W   18.0 Crater                 NLF?
            Aristarchus H                   22.6N  45.7W    4.0 Crater                 NLF?
            Aristarchus N                   22.8N  42.9W    3.0 Crater                 NLF?
            Aristarchus S                   19.3N  46.2W    4.0 Crater                 NLF?
            Aristarchus T                   19.6N  46.4W    4.0 Crater                 NLF?
            Aristarchus U                   19.7N  48.6W    4.0 Crater                 NLF?
            Aristarchus Z                   25.5N  48.4W    8.0 Crater                 NLF?
            Aristillus                      33.9N   1.2E   55.0 Crater         VL1645  R1651
            Aristillus A                    33.6N   4.5E    5.0 Crater                 NLF?
            Aristillus B                    34.8N   1.9W    8.0 Crater                 NLF?
            Aristoteles                     50.2N  17.4E   87.0 Crater         VL1645  R1651
            Aristoteles D                   47.5N  14.7E    6.0 Crater                 NLF?
            Aristoteles M                   53.5N  27.2E    7.0 Crater                 NLF?
            Aristoteles N                   52.9N  26.8E    5.0 Crater                 NLF?
            Armi|%nski                      16.4S 154.2E   26.0 Crater                 IAU1976
            Armi|%nski D                    15.9S 155.3E   19.0 Crater                 AW82
            Armi|%nski K                    17.1S 154.6E   22.0 Crater                 AW82 [Not in IAU2006?]
            Armstrong                        1.4N  25.0E    4.0 Crater                 IAU1970
            Arnold                          66.8N  35.9E   94.0 Crater         S1791   S1791
            Arnold A                        68.8N  39.8E   57.0 Crater                 NLF?
            Arnold E                        71.6N  38.3E   32.0 Crater                 NLF?
            Arnold F                        67.5N  35.2E   10.0 Crater                 NLF?
            Arnold G                        67.3N  31.4E   11.0 Crater                 NLF?
            Arnold H                        72.6N  45.3E   13.0 Crater                 NLF?
            Arnold J                        65.9N  33.7E    6.0 Crater                 NLF?
            Arnold K                        70.8N  42.8E   29.0 Crater                 NLF?
            Arnold L                        70.2N  36.1E   33.0 Crater                 NLF?
            Arnold M                        68.3N  43.6E    7.0 Crater                 NLF?
            Arnold N                        70.2N  41.9E   18.0 Crater                 NLF?
            Arrhenius                       55.6S  91.3W   40.0 Crater                 IAU1970
            Arrhenius J                     57.6S  88.3W   18.0 Crater                 AW82
            Artamonov                       25.5N 103.5E   60.0 Crater                 IAU1970
            Artem'ev                        10.8N 144.4W   67.0 Crater                 IAU1970
            Artem'ev G                      10.3N 142.8W   60.0 Crater                 AW82
            Artem'ev L                       8.3N 143.3W   30.0 Crater                 AW82
            Artemis                         25.0N  25.4W    2.0 Crater                 IAU1976
            Artsimovich                     27.6N  36.6W    8.0 Crater                 IAU1973
            Aryabhata                        6.2N  35.1E   22.0 Crater                 IAU1979
            Arzachel                        18.2S   1.9W   96.0 Crater         VL1645  R1651
            Arzachel A                      18.0S   1.5W   10.0 Crater                 NLF?
            Arzachel B                      17.0S   2.9W    8.0 Crater                 NLF?
            Arzachel C                      17.4S   3.7W    6.0 Crater                 NLF?
            Arzachel D                      20.2S   2.1W    8.0 Crater                 NLF?
            Arzachel H                      18.7S   2.0W    5.0 Crater                 NLF?
            Arzachel K                      18.3S   1.6W    4.0 Crater                 NLF?
            Arzachel L                      20.0S   0.1E    8.0 Crater                 NLF?
            Arzachel M                      20.6S   0.9W    3.0 Crater                 NLF?
            Arzachel N                      20.4S   2.2W    3.0 Crater                 NLF?
            Arzachel T                      17.7S   1.3W    3.0 Crater                 NLF?
            Arzachel Y                      18.2S   4.3W    4.0 Crater                 NLF?
            Asada                            7.3N  49.9E   12.0 Crater                 IAU1976
            Asclepi                         55.1S  25.4E   42.0 Crater         VL1645  S1878
            Asclepi A                       52.9S  23.0E   14.0 Crater                 NLF?
            Asclepi B                       54.1S  23.9E   19.0 Crater                 NLF?
            Asclepi C                       53.3S  23.4E   11.0 Crater                 NLF?
            Asclepi D                       53.5S  24.1E   18.0 Crater                 NLF?
            Asclepi E                       52.1S  24.1E    7.0 Crater                 NLF?
            Asclepi G                       53.3S  24.8E    5.0 Crater                 NLF?
            Asclepi H                       52.7S  25.1E   19.0 Crater                 NLF?
            Ashbrook                        81.4S 112.5W  156.0 Crater                 IAU94
            Aston                           32.9N  87.7W   43.0 Crater         RLA1963 IAU1964
            Aston K                         35.1N  87.8W   14.0 Crater                 RLA1963
            Aston L                         35.5N  86.5W   10.0 Crater                 RLA1963
            Atlas                           46.7N  44.4E   87.0 Crater         VL1645  R1651
            Atlas A                         45.3N  49.6E   22.0 Crater         VL1645  NLF?
            Atlas D                         50.4N  49.6E   25.0 Crater                 NLF?
            Atlas E                         48.6N  42.5E   58.0 Crater                 NLF?
            Atlas G                         50.7N  46.5E   23.0 Crater                 NLF?
            Atlas L                         51.3N  48.6E    6.0 Crater                 NLF?
            Atlas P                         49.6N  52.7E   27.0 Crater                 NLF?
            Atlas W                         44.4N  44.2E    4.0 Crater                 NLF?
            Atlas X                         45.1N  45.0E    5.0 Crater                 NLF?
            Atwood                           5.8S  57.7E   29.0 Crater                 IAU1976
            Autolycus                       30.7N   1.5E   39.0 Crater         VL1645  R1651
            Autolycus A                     30.9N   2.2E    4.0 Crater                 NLF?
            Autolycus K                     31.2N   5.4E    3.0 Crater                 NLF?
            Auwers                          15.1N  17.2E   20.0 Crater         K1898   K1898
            Auwers A                        13.8N  18.3E    8.0 Crater                 NLF?
            Auzout                          10.3N  64.1E   32.0 Crater         S1791   S1791
            Auzout C                         8.8N  65.3E   16.0 Crater                 NLF?
            Auzout D                         9.3N  62.3E   10.0 Crater                 NLF?
            Auzout E                         9.6N  60.6E   17.0 Crater                 NLF?
            Auzout L                         8.3N  61.3E    9.0 Crater                 NLF?
            Auzout R                         8.7N  60.1E    6.0 Crater                 NLF?
            Auzout U                         9.4N  61.0E    8.0 Crater                 NLF?
            Auzout V                         9.3N  61.4E    7.0 Crater                 NLF?
            Avery                            1.4S  81.4E    9.0 Crater                 IAU1976
            Avicenna                        39.7N  97.2W   74.0 Crater                 IAU1970
            Avicenna E                      40.0N  91.1W   25.0 Crater                 AW82
            Avicenna G                      39.0N  92.0W   26.0 Crater                 AW82
            Avicenna R                      38.9N 100.1W   21.0 Crater                 AW82
            Avogadro                        63.1N 164.9E  139.0 Crater                 IAU1970
            Avogadro D                      64.4N 169.5E   20.0 Crater                 NLF?
            Azophi                          22.1S  12.7E   47.0 Crater         VL1645  R1651
            Azophi A                        24.4S  11.2E   29.0 Crater                 NLF?
            Azophi B                        23.6S  10.6E   19.0 Crater                 NLF?
            Azophi C                        21.8S  13.1E    5.0 Crater                 NLF?
            Azophi D                        24.3S  13.4E    9.0 Crater                 NLF?
            Azophi E                        23.5S  13.8E    5.0 Crater                 NLF?
            Azophi F                        22.2S  13.9E    6.0 Crater                 NLF?
            Azophi G                        23.9S  12.3E   53.0 Crater                 NLF?
            Azophi H                        25.5S  11.8E   21.0 Crater                 NLF?
            Azophi J                        21.2S  13.1E    8.0 Crater                 NLF?
            Baade                           44.8S  81.8W   55.0 Crater         RLA1963 IAU1964
            Babakin                         20.8S 123.3E   20.0 Crater                 IAU1973
            Babbage                         59.7N  57.1W  143.0 Crater                 NLF
            Babbage A                       59.0N  55.1W   32.0 Crater                 NLF?
            Babbage B                       57.1N  59.7W    7.0 Crater                 NLF?
            Babbage C                       59.1N  57.3W   14.0 Crater                 NLF?
            Babbage D                       58.6N  61.0W   68.0 Crater                 NLF?
            Babbage E                       58.5N  61.4W    7.0 Crater                 NLF?
            Babbage U                       60.9N  51.3W    5.0 Crater                 NLF?
            Babbage X                       60.2N  49.9W    5.0 Crater                 NLF?
            Babcock                          4.2N  93.9E   99.0 Crater                 IAU1970
            Babcock H                        3.0N  96.5E   63.0 Crater                 AW82
            Babcock K                        1.2N  95.2E   10.0 Crater                 AW82
            Baby Ray                         9.1S  15.4E    0.0 Crater (A)             IAU1973
            Back                             1.1N  80.7E   35.0 Crater                 IAU1976
            Backlund                        16.0S 103.0E   75.0 Crater                 IAU1970
            Backlund E                      15.5S 105.3E   15.0 Crater                 AW82
            Backlund L                      18.2S 103.5E   56.0 Crater                 AW82
            Backlund N                      17.8S 102.8E   18.0 Crater                 AW82
            Backlund P                      18.9S 102.0E   27.0 Crater                 AW82
            Backlund R                      16.8S 101.5E   23.0 Crater                 AW82
            Backlund S                      16.8S 100.6E   21.0 Crater                 AW82
            Baco                            51.0S  19.1E   69.0 Crater         M1834   M1834
            Baco A                          52.8S  20.2E   39.0 Crater                 NLF?
            Baco B                          49.5S  16.6E   43.0 Crater                 NLF?
            Baco C                          50.8S  14.8E   14.0 Crater                 NLF?
            Baco D                          51.6S  16.4E    8.0 Crater                 NLF?
            Baco E                          52.9S  16.2E   28.0 Crater                 NLF?
            Baco F                          50.4S  17.7E    6.0 Crater                 NLF?
            Baco G                          54.4S  17.2E    9.0 Crater                 NLF?
            Baco H                          51.9S  18.9E    6.0 Crater                 NLF?
            Baco J                          54.7S  19.3E   19.0 Crater                 NLF?
            Baco K                          53.9S  17.6E   29.0 Crater                 NLF?
            Baco L                          49.5S  16.7E    7.0 Crater                 NLF?
            Baco M                          49.2S  18.0E    7.0 Crater                 NLF?
            Baco N                          50.8S  16.3E   23.0 Crater                 NLF?
            Baco O                          52.1S  19.9E    9.0 Crater                 NLF?
            Baco P                          50.8S  19.6E    5.0 Crater                 NLF?
            Baco Q                          52.3S  18.7E   20.0 Crater                 NLF?
            Baco R                          49.2S  21.0E   18.0 Crater                 NLF?
            Baco S                          49.4S  18.5E   18.0 Crater                 NLF?
            Baco T                          53.7S  19.8E    5.0 Crater                 NLF?
            Baco U                          52.4S  19.3E    6.0 Crater                 NLF?
            Baco W                          53.3S  21.1E    9.0 Crater                 NLF?
            Baco Z                          53.0S  15.0E    7.0 Crater                 NLF?
            Baillaud                        74.6N  37.5E   89.0 Crater         L1935   L1935
            Baillaud A                      75.7N  48.8E   56.0 Crater                 NLF?
            Baillaud B                      73.0N  33.3E   17.0 Crater                 NLF?
            Baillaud C                      75.0N  51.4E   11.0 Crater                 NLF?
            Baillaud D                      73.6N  49.7E   16.0 Crater                 NLF?
            Baillaud E                      74.3N  36.0E   14.0 Crater                 NLF?
            Baillaud F                      75.7N  53.7E   20.0 Crater                 NLF?
            Bailly                          66.5S  69.1W  287.0 Crater         S1791   S1791
            Bailly A                        69.3S  59.5W   38.0 Crater                 NLF?
            Bailly B                        68.8S  63.1W   65.0 Crater         R1651   NLF?
            Bailly C                        65.6S  69.6W   20.0 Crater                 NLF?
            Bailly D                        65.2S  72.2W   23.0 Crater                 NLF?
            Bailly E                        62.5S  65.7W   13.0 Crater                 NLF?
            Bailly F                        67.5S  69.2W   16.0 Crater                 NLF?
            Bailly G                        65.6S  59.1W   18.0 Crater                 NLF?
            Bailly H                        63.5S  62.1W   12.0 Crater                 NLF?
            Bailly K                        62.8S  76.5W   20.0 Crater                 NLF?
            Bailly L                        60.8S  70.9W   20.0 Crater                 NLF?
            Bailly M                        61.4S  68.4W   23.0 Crater                 NLF?
            Bailly N                        60.5S  63.6W   11.0 Crater                 NLF?
            Bailly O                        69.6S  56.7W   16.0 Crater                 NLF?
            Bailly P                        59.6S  60.6W   15.0 Crater                 NLF?
            Bailly R                        64.8S  80.0W   18.0 Crater                 NLF?
            Bailly T                        66.5S  72.8W   18.0 Crater                 NLF?
            Bailly U                        71.3S  75.8W   20.0 Crater                 NLF?
            Bailly V                        72.0S  85.0W   29.0 Crater                 NLF?
            Bailly Y                        61.3S  66.6W   12.0 Crater                 NLF?
            Bailly Z                        60.2S  65.6W   12.0 Crater                 NLF?
            Baily                           49.7N  30.4E   26.0 Crater         M1834   M1834
            Baily A                         48.6N  31.3E   16.0 Crater                 NLF?
            Baily B                         51.0N  35.1E    7.0 Crater                 NLF?
            Baily K                         51.5N  30.5E    3.0 Crater                 NLF?
            Balandin                        18.9S 152.6E   12.0 Crater                 IAU1976
            Balboa                          19.1N  83.2W   69.0 Crater         RLA1963 IAU1964
            Balboa A                        17.4N  81.9W   47.0 Crater                 RLA1963?
            Balboa B                        20.3N  82.3W   62.0 Crater                 RLA1963?
            Balboa C                        19.6N  79.1W   27.0 Crater                 RLA1963?
            Balboa D                        18.2N  79.7W   40.0 Crater                 RLA1963?
            Baldet                          53.3S 151.1W   55.0 Crater                 IAU1970
            Baldet J                        54.6S 149.5W   17.0 Crater                 AW82
            Ball                            35.9S   8.4W   41.0 Crater                 NLF
            Ball A                          34.7S   9.3W   29.0 Crater                 NLF?
            Ball B                          36.9S   9.1W   10.0 Crater                 NLF?
            Ball C                          37.7S   8.7W   31.0 Crater         R1651   NLF?
            Ball D                          35.6S  10.3W   21.0 Crater                 NLF?
            Ball E                          36.5S   8.1W    5.0 Crater                 NLF?
            Ball F                          36.9S   8.5W   12.0 Crater                 NLF?
            Ball G                          37.7S  10.1W   28.0 Crater                 NLF?
            Balmer                          20.3S  69.8E  138.0 Crater         RLA1963 IAU1964
            Balmer M                        20.7S  71.5E    5.0 Crater                 NLF?
            Balmer N                        19.9S  69.9E    8.0 Crater                 NLF?
            Balmer P                        20.4S  67.7E   13.0 Crater                 NLF?
            Balmer Q                        18.7S  70.5E    7.0 Crater                 NLF?
            Balmer R                        18.7S  69.1E    4.0 Crater                 NLF?
            Balmer S                        18.4S  67.6E    6.0 Crater                 NLF?
            Banachiewicz                     5.2N  80.1E   92.0 Crater         RLA1963 IAU1964
            Banachiewicz B                   5.3N  78.9E   24.0 Crater                 NLF?
            Banachiewicz C                   7.0N  75.4E   19.0 Crater                 NLF?
            Banachiewicz E                   7.5N  74.7E    7.0 Crater                 NLF?
            Bancroft                        28.0N   6.4W   13.0 Crater                 IAU1976
            Banting                         26.6N  16.4E    5.0 Crater                 IAU1973
            Barbier                         23.8S 157.9E   66.0 Crater                 IAU1970
            Barbier F                       23.8S 158.1E   14.0 Crater                 AW82
            Barbier D                       23.0S 160.2E   24.0 Crater                 AW82
            Barbier G                       24.4S 160.1E   17.0 Crater                 AW82
            Barbier H                       25.3S 160.5E   17.0 Crater                 AW82
            Barbier J                       26.0S 160.1E   43.0 Crater                 AW82
            Barbier K                       26.5S 159.4E    7.0 Crater                 AW82
            Barbier U                       22.8S 155.1E   38.0 Crater                 AW82
            Barbier V                       22.3S 154.6E   29.0 Crater                 AW82
            Barkla                          10.7S  67.2E   42.0 Crater                 IAU1979
            Barnard                         29.5S  85.6E  105.0 Crater         RLA1963 IAU1964
            Barnard A                       31.8S  84.5E   13.0 Crater                 RLA1963?
            Barnard D                       31.4S  89.3E   47.0 Crater                 RLA1963?
            Barocius                        44.9S  16.8E   82.0 Crater         R1651   R1651
            Barocius B                      44.0S  18.3E   39.0 Crater                 NLF?
            Barocius C                      43.1S  17.6E   33.0 Crater                 NLF?
            Barocius D                      46.0S  19.1E    8.0 Crater                 NLF?
            Barocius E                      47.1S  22.2E   26.0 Crater                 NLF?
            Barocius EC                     48.1S  22.5E    8.0 Crater                 NLF?
            Barocius F                      45.8S  21.6E   15.0 Crater                 NLF?
            Barocius G                      42.4S  21.0E   27.0 Crater                 NLF?
            Barocius H                      46.7S  21.6E   11.0 Crater                 NLF?
            Barocius J                      44.9S  21.5E   27.0 Crater                 NLF?
            Barocius K                      45.2S  19.6E   15.0 Crater                 NLF?
            Barocius L                      42.4S  18.9E   13.0 Crater                 NLF?
            Barocius M                      42.4S  19.5E   17.0 Crater                 NLF?
            Barocius N                      43.1S  19.8E   10.0 Crater                 NLF?
            Barocius O                      45.6S  21.9E    5.0 Crater                 NLF?
            Barocius R                      43.9S  21.6E   14.0 Crater                 NLF?
            Barocius S                      42.4S  21.8E    8.0 Crater                 NLF?
            Barocius W                      45.6S  16.2E   20.0 Crater                 NLF?
            Barringer                       28.0S 149.7W   68.0 Crater                 IAU1970
            Barringer C                     26.5S 148.8W   19.0 Crater                 AW82
            Barringer Z                     25.0S 150.3W   24.0 Crater                 AW82
            Barrow                          71.3N   7.7E   92.0 Crater         VL1645  R1651
            Barrow A                        70.5N   3.8E   28.0 Crater                 NLF?
            Barrow B                        70.1N  10.5E   16.0 Crater                 NLF?
            Barrow C                        73.1N  11.1E   29.0 Crater                 NLF?
            Barrow E                        68.9N   3.3E   18.0 Crater                 NLF?
            Barrow F                        69.1N   1.8E   19.0 Crater                 NLF?
            Barrow G                        70.1N   0.2E   30.0 Crater                 NLF?
            Barrow H                        69.2N   6.0E    5.0 Crater                 NLF?
            Barrow K                        69.2N  11.8E   46.0 Crater                 NLF?
            Barrow M                        67.6N   9.2E    6.0 Crater                 NLF?
            Bartels                         24.5N  89.8W   55.0 Crater                 IAU1970
            Bartels A                       25.7N  89.6W   17.0 Crater                 AW82
            Bawa                            25.3S 102.6E    1.0 Crater         X       IAU1976
            Bayer                           51.6S  35.0W   47.0 Crater                 NLF
            Bayer A                         51.3S  30.3W   18.0 Crater                 NLF?
            Bayer B                         48.8S  28.2W   18.0 Crater                 NLF?
            Bayer C                         49.7S  31.2W   22.0 Crater                 NLF?
            Bayer D                         47.9S  29.8W   20.0 Crater                 NLF?
            Bayer E                         51.7S  32.3W   29.0 Crater                 NLF?
            Bayer F                         53.0S  31.6W   20.0 Crater                 NLF?
            Bayer G                         51.7S  35.3W    7.0 Crater                 NLF?
            Bayer H                         53.5S  32.5W   27.0 Crater                 NLF?
            Bayer J                         52.2S  33.6W   18.0 Crater                 NLF?
            Bayer K                         50.2S  34.0W   16.0 Crater                 NLF?
            Bayer L                         47.5S  33.6W   14.0 Crater                 NLF?
            Bayer M                         50.6S  31.0W   10.0 Crater                 NLF?
            Bayer N                         48.3S  29.2W    9.0 Crater                 NLF?
            Bayer P                         51.6S  29.5W    4.0 Crater                 NLF?
            Bayer R                         52.5S  35.5W    9.0 Crater                 NLF?
            Bayer S                         52.3S  36.4W   13.0 Crater                 NLF?
            Bayer T                         49.2S  30.1W    8.0 Crater                 NLF?
            Bayer U                         48.4S  31.3W   10.0 Crater                 NLF?
            Bayer V                         47.5S  31.6W    9.0 Crater                 NLF?
            Bayer W                         48.0S  33.5W    9.0 Crater                 NLF?
            Bayer X                         53.4S  33.6W    8.0 Crater                 NLF?
            Bayer Y                         49.2S  35.7W   31.0 Crater                 NLF?
            Bayer Z                         49.0S  33.4W    7.0 Crater                 NLF?
            Beals                           37.3N  86.5E   48.0 Crater         N       IAU1982
            Beaumont                        18.0S  28.8E   53.0 Crater         M1834   M1834
            Beaumont A                      16.3S  27.7E   14.0 Crater                 NLF?
            Beaumont B                      18.6S  26.8E   16.0 Crater                 NLF?
            Beaumont C                      20.2S  28.0E    6.0 Crater                 NLF?
            Beaumont D                      17.0S  26.2E   11.0 Crater                 NLF?
            Beaumont E                      18.8S  27.5E   18.0 Crater                 NLF?
            Beaumont F                      18.3S  26.6E   10.0 Crater                 NLF?
            Beaumont G                      20.3S  27.1E    8.0 Crater                 NLF?
            Beaumont H                      17.2S  28.4E    6.0 Crater                 NLF?
            Beaumont J                      19.9S  26.5E    5.0 Crater                 NLF?
            Beaumont K                      17.5S  30.1E    6.0 Crater                 NLF?
            Beaumont L                      14.4S  30.0E    4.0 Crater                 NLF?
            Beaumont M                      19.4S  28.6E   10.0 Crater                 NLF?
            Beaumont N                      16.9S  27.7E    5.0 Crater                 NLF?
            Beaumont P                      19.9S  29.6E   17.0 Crater                 NLF?
            Beaumont R                      17.9S  30.7E    4.0 Crater                 NLF?
            Becquerel                       40.7N 129.7E   65.0 Crater                 IAU1970
            Becquerel E                     41.0N 131.5E   32.0 Crater                 AW82
            Becquerel F                     40.9N 132.9E   21.0 Crater                 AW82
            Becquerel W                     42.2N 126.9E   27.0 Crater                 AW82
            Becquerel X                     42.2N 128.1E   34.0 Crater                 AW82
            Be|vcv|%a|vr                     1.9S 125.2E   67.0 Crater                 IAU1970
            Be|vcv|%a|vr D                   1.5S 126.5E   15.0 Crater                 AW82
            Be|vcv|%a|vr E                   2.0S 127.8E   15.0 Crater                 AW82
            Be|vcv|%a|vr J                   3.6S 126.6E   45.0 Crater                 AW82
            Be|vcv|%a|vr Q                   2.9S 124.0E   28.0 Crater                 AW82
            Be|vcv|%a|vr S                   3.0S 121.1E   14.0 Crater                 AW82
            Be|vcv|%a|vr T                   1.8S 121.9E   27.0 Crater                 AW82
            Be|vcv|%a|vr X                   0.6S 124.2E   26.0 Crater                 AW82
            Beer                            27.1N   9.1W    9.0 Crater         S1878   S1878
            Beer A                          27.2N   8.6W    4.0 Crater                 NLF?
            Beer B                          25.7N   9.0W    2.0 Crater                 NLF?
            Beer E                          27.8N   7.8W    3.0 Crater                 NLF?
            Behaim                          16.5S  79.4E   55.0 Crater         M1834   M1834
            Behaim B                        16.1S  76.8E   24.0 Crater                 NLF?
            Behaim BA                       16.4S  76.0E   14.0 Crater                 NLF?
            Behaim C                        16.7S  77.8E   13.0 Crater                 NLF?
            Behaim N                        16.1S  73.5E    9.0 Crater                 NLF?
            Behaim S                        16.6S  81.4E   25.0 Crater                 NLF?
            Behaim T                        16.1S  81.3E   11.0 Crater                 NLF?
            Beijerinck                      13.5S 151.8E   70.0 Crater                 IAU1970
            Beijerinck C                    11.0S 153.7E   20.0 Crater                 AW82
            Beijerinck D                    12.8S 153.1E   14.0 Crater                 AW82
            Beijerinck H                    14.2S 153.3E   16.0 Crater                 AW82
            Beijerinck J                    14.8S 153.7E   40.0 Crater                 AW82
            Beijerinck R                    14.7S 149.2E   28.0 Crater                 AW82
            Beijerinck S                    14.2S 147.2E   27.0 Crater                 AW82
            Beijerinck U                    12.4S 149.0E   18.0 Crater                 AW82
            Beijerinck V                    12.7S 150.1E   42.0 Crater                 AW82
            Beketov                         16.3N  29.2E    8.0 Crater                 IAU1976
            B|%ela                          24.7N   2.3E   11.0 Crater         X       IAU1976
            Bel'kovich                      61.1N  90.2E  214.0 Crater         RLA1963 IAU1964
            Bel'kovich A                    58.7N  86.0E   58.0 Crater                 AW82
            Bel'kovich B                    58.9N  85.0E   13.0 Crater                 AW82
            Bel'kovich K                    63.8N  93.6E   47.0 Crater                 AW82
            Bell                            21.8N  96.4W   86.0 Crater                 IAU1970
            Bell E                          22.0N  95.8W   15.0 Crater                 AW82
            Bell J                          19.9N  94.0W   18.0 Crater                 AW82
            Bell K                          18.3N  95.1W   18.0 Crater                 AW82
            Bell L                          19.7N  95.8W   23.0 Crater                 AW82
            Bell N                          19.5N  96.9W   18.0 Crater                 AW82
            Bell Q                          20.7N  97.2W   23.0 Crater                 AW82
            Bell T                          21.9N  98.9W   52.0 Crater                 AW82
            Bell Y                          25.4N  96.7W   23.0 Crater                 AW82
            Bellinsgauzen                   60.6S 164.6W   63.0 Crater                 IAU1970
            Bellot                          12.4S  48.2E   17.0 Crater                 NLF
            Bellot A                        13.4S  47.7E    8.0 Crater                 NLF?
            Bellot B                        13.5S  47.8E    7.0 Crater                 NLF?
            Belopol'skiy                    17.2S 128.1W   59.0 Crater                 IAU1970
            Belyaev                         23.3N 143.5E   54.0 Crater                 IAU1970
            Belyaev Q                       20.6N 139.4E   50.0 Crater                 AW82
            Bench                            3.2S  23.4W    0.0 Crater (A)             IAU1973
            Benedict                         4.4N 141.5E   14.0 Crater                 IAU1976
            Bergman                          7.0N 137.5E   21.0 Crater                 IAU1976
            Bergstrand                      18.8S 176.3E   43.0 Crater                 IAU1970
            Bergstrand G                    20.0S 179.4E   34.0 Crater                 AW82
            Bergstrand J                    20.4S 178.2E   25.0 Crater                 AW82
            Bergstrand Q                    20.1S 175.1E   59.0 Crater                 AW82
            Berkner                         25.2N 105.2W   86.0 Crater                 IAU1970
            Berkner A                       27.6N 104.8W   22.0 Crater                 AW82
            Berkner B                       29.3N 104.1W   33.0 Crater                 AW82
            Berkner Y                       27.8N 106.2W   31.0 Crater                 AW82
            Berlage                         63.2S 162.8W   92.0 Crater                 IAU1970
            Berlage R                       64.0S 167.6W   25.0 Crater                 AW82
            Bernoulli                       35.0N  60.7E   47.0 Crater         S1791   S1791
            Bernoulli A                     36.4N  60.9E   22.0 Crater                 NLF?
            Bernoulli B                     36.9N  65.6E   22.0 Crater                 NLF?
            Bernoulli C                     35.3N  67.2E   19.0 Crater                 NLF?
            Bernoulli D                     35.7N  66.5E   12.0 Crater                 NLF?
            Bernoulli E                     35.3N  63.0E   26.0 Crater                 NLF?
            Bernoulli K                     36.7N  62.7E   20.0 Crater                 NLF?
            Berosus                         33.5N  69.9E   74.0 Crater         VL1645  NLF
            Berosus A                       33.1N  68.1E   12.0 Crater                 NLF?
            Berosus F                       34.0N  66.6E   22.0 Crater                 NLF?
            Berosus K                       32.1N  70.9E    6.0 Crater                 NLF?
            Berzelius                       36.6N  50.9E   50.0 Crater         M1834   M1834
            Berzelius A                     36.8N  48.9E    7.0 Crater                 NLF?
            Berzelius B                     32.6N  43.1E   23.0 Crater                 NLF?
            Berzelius F                     32.8N  46.0E   12.0 Crater                 NLF?
            Berzelius K                     35.5N  47.0E    7.0 Crater                 NLF?
            Berzelius T                     36.2N  48.0E    9.0 Crater                 NLF?
            Berzelius W                     38.2N  53.0E    6.0 Crater                 NLF?
            Bessarion                       14.9N  37.3W   10.0 Crater         VL1645  R1651
            Bessarion A                     17.1N  39.8W   13.0 Crater                 NLF?
            Bessarion B                     16.8N  41.7W   12.0 Crater                 NLF?
            Bessarion C                     16.0N  42.6W    9.0 Crater                 NLF?
            Bessarion D                     19.8N  41.7W    9.0 Crater                 NLF?
            Bessarion E                     15.4N  37.3W    8.0 Crater                 NLF?
            Bessarion G                     14.9N  40.3W    4.0 Crater                 NLF?
            Bessarion H                     15.3N  41.4W    5.0 Crater                 NLF?
            Bessarion V                     15.0N  35.0W    3.0 Crater                 NLF?
            Bessarion W                     16.7N  36.9W    3.0 Crater                 NLF?
            Bessel                          21.8N  17.9E   15.0 Crater         M1834   M1834
            Bessel D                        27.3N  19.9E    5.0 Crater                 NLF?
            Bessel F                        21.2N  13.8E    1.0 Crater                 NLF?
            Bessel G                        21.1N  14.7E    1.0 Crater                 NLF?
            Bessel H                        25.7N  20.0E    4.0 Crater                 NLF?
            Bettinus                        63.4S  44.8W   71.0 Crater         VL1645  R1651
            Bettinus A                      64.9S  48.8W   26.0 Crater                 NLF?
            Bettinus B                      63.6S  51.0W   24.0 Crater                 NLF?
            Bettinus C                      63.3S  37.7W   20.0 Crater                 NLF?
            Bettinus D                      65.0S  46.4W    9.0 Crater                 NLF?
            Bettinus E                      63.2S  42.1W    7.0 Crater                 NLF?
            Bettinus F                      62.9S  43.3W    6.0 Crater                 NLF?
            Bettinus G                      61.5S  44.3W    7.0 Crater                 NLF?
            Bettinus H                      64.6S  43.7W    8.0 Crater                 NLF?
            Bhabha                          55.1S 164.5W   64.0 Crater                 IAU1970
            Bianchini                       48.7N  34.3W   38.0 Crater         VL1645  S1791
            Bianchini D                     47.6N  35.8W    7.0 Crater                 NLF?
            Bianchini G                     46.7N  32.7W    4.0 Crater                 NLF?
            Bianchini H                     48.0N  32.7W    7.0 Crater                 NLF?
            Bianchini M                     48.4N  30.6W    4.0 Crater                 NLF?
            Bianchini N                     48.5N  31.0W    5.0 Crater                 NLF?
            Bianchini W                     48.5N  33.7W    9.0 Crater                 NLF?
            Biela                           54.9S  51.3E   76.0 Crater         M1834   M1834
            Biela A                         52.9S  53.3E   26.0 Crater                 NLF?
            Biela B                         56.5S  49.6E   43.0 Crater                 NLF?
            Biela C                         54.3S  53.5E   26.0 Crater                 NLF?
            Biela D                         55.8S  56.3E   14.0 Crater                 NLF?
            Biela E                         56.4S  56.3E    8.0 Crater                 NLF?
            Biela F                         56.3S  54.5E    9.0 Crater                 NLF?
            Biela G                         56.2S  53.9E   10.0 Crater                 NLF?
            Biela H                         57.9S  54.2E    8.0 Crater                 NLF?
            Biela J                         57.0S  52.9E   14.0 Crater                 NLF?
            Biela T                         53.8S  49.9E    7.0 Crater                 NLF?
            Biela U                         53.4S  49.0E   16.0 Crater                 NLF?
            Biela V                         53.6S  48.5E    6.0 Crater                 NLF?
            Biela W                         55.1S  49.6E   16.0 Crater                 NLF?
            Biela Y                         54.9S  58.0E   15.0 Crater                 NLF?
            Biela Z                         53.8S  57.0E   48.0 Crater                 NLF?
            Bilharz                          5.8S  56.3E   43.0 Crater                 IAU1976
            Billy                           13.8S  50.1W   45.0 Crater         R1651   R1651
            Billy A                         14.3S  46.3W    7.0 Crater                 NLF?
            Billy B                         12.2S  47.6W   25.0 Crater                 NLF?
            Billy C                         16.1S  49.0W    6.0 Crater                 NLF?
            Billy D                         14.9S  48.3W   11.0 Crater                 NLF?
            Billy E                         15.0S  49.6W    2.0 Crater                 NLF?
            Billy H                         15.6S  49.6W    3.0 Crater                 NLF?
            Billy K                         12.9S  48.7W    4.0 Crater                 NLF?
            Bingham                          8.1N 115.1E   33.0 Crater                 IAU1976
            Bingham H                        7.5N 116.2E   26.0 Crater                 AW82
            Biot                            22.6S  51.1E   12.0 Crater         M1834   M1834
            Biot A                          22.2S  48.9E   15.0 Crater                 NLF?
            Biot B                          20.4S  49.6E   28.0 Crater                 NLF?
            Biot C                          22.0S  51.1E    8.0 Crater                 NLF?
            Biot D                          24.3S  50.3E    9.0 Crater                 NLF?
            Biot E                          24.6S  50.9E    8.0 Crater                 NLF?
            Biot T                          21.4S  49.9E    5.0 Crater                 NLF?
            Birkeland                       30.2S 173.9E   82.0 Crater                 IAU1970
            Birkeland M                     32.0S 174.1E   23.0 Crater                 AW82
            Birkhoff                        58.7N 146.1W  345.0 Crater                 IAU1970
            Birkhoff K                      57.8N 144.3W   58.0 Crater                 AW82
            Birkhoff L                      56.6N 144.8W   37.0 Crater                 AW82
            Birkhoff M                      54.7N 144.8W   23.0 Crater                 AW82
            Birkhoff Q                      56.6N 150.8W   43.0 Crater                 AW82
            Birkhoff R                      57.5N 153.0W   27.0 Crater                 AW82
            Birkhoff X                      62.1N 149.7W   77.0 Crater                 AW82
            Birkhoff Y                      59.9N 146.6W   25.0 Crater                 AW82
            Birkhoff Z                      61.3N 145.3W   30.0 Crater                 AW82
            Birmingham                      65.1N  10.5W   92.0 Crater                 NLF
            Birmingham B                    63.6N  11.3W    8.0 Crater                 NLF?
            Birmingham G                    64.5N  10.2W    5.0 Crater                 NLF?
            Birmingham H                    64.4N  10.6W    7.0 Crater                 NLF?
            Birmingham K                    65.0N  13.1W    6.0 Crater                 NLF?
            Birt                            22.4S   8.5W   16.0 Crater         H1647   S1878
            Birt A                          22.5S   8.2W    7.0 Crater                 NLF?
            Birt B                          22.2S  10.2W    5.0 Crater                 NLF?
            Birt C                          23.7S   8.3W    2.0 Crater                 NLF?
            Birt D                          21.0S   9.8W    3.0 Crater                 NLF?
            Birt E                          20.7S   9.6W    5.0 Crater                 NLF?
            Birt F                          22.3S   9.1W    3.0 Crater                 NLF?
            Birt G                          23.1S   8.2W    2.0 Crater                 NLF?
            Birt H                          23.0S   9.1W    2.0 Crater                 NLF?
            Birt J                          23.0S   9.4W    2.0 Crater                 NLF?
            Birt K                          22.4S   9.7W    2.0 Crater                 NLF?
            Birt L                          21.6S   9.3W    3.0 Crater                 NLF?
            Bjerknes                        38.4S 113.0E   48.0 Crater                 IAU1970
            Bjerknes A                      36.0S 113.7E   18.0 Crater                 AW82
            Bjerknes B                      37.2S 113.8E   20.0 Crater                 AW82
            Bjerknes E                      38.0S 115.0E   54.0 Crater                 AW82
            Black                            9.2S  80.4E   18.0 Crater                 IAU1976
            Blackett                        37.5S 116.1W  141.0 Crater                 IAU1979
            Blackett N                      39.9S 116.2W   23.0 Crater                 AW82
            Blagg                            1.3N   1.5E    5.0 Crater         L1935   L1935
            Blancanus                       63.8S  21.4W  117.0 Crater         VL1645  R1651
            Blancanus A                     64.4S  21.6W    6.0 Crater                 NLF?
            Blancanus C                     66.5S  28.0W   46.0 Crater                 NLF?
            Blancanus D                     63.3S  16.5W   24.0 Crater                 NLF?
            Blancanus E                     66.6S  21.5W   37.0 Crater                 NLF?
            Blancanus F                     65.1S  27.4W    9.0 Crater                 NLF?
            Blancanus G                     63.3S  25.3W    9.0 Crater                 NLF?
            Blancanus H                     65.5S  23.5W    7.0 Crater                 NLF?
            Blancanus K                     60.6S  23.3W   11.0 Crater                 NLF?
            Blancanus N                     63.4S  25.8W   11.0 Crater                 NLF?
            Blancanus V                     64.0S  20.9W    7.0 Crater                 NLF?
            Blancanus W                     60.9S  20.2W    9.0 Crater                 NLF?
            Blanchard                       58.5S  94.4W   40.0 Crater         N       IAU1991
            Blanchinus                      25.4S   2.5E   61.0 Crater         VL1645  R1651
            Blanchinus B                    25.2S   1.6E    8.0 Crater                 NLF?
            Blanchinus D                    25.0S   4.2E    7.0 Crater                 NLF?
            Blanchinus K                    24.8S   5.1E    9.0 Crater                 NLF?
            Blanchinus M                    25.2S   2.6E    5.0 Crater                 NLF?
            Blazhko                         31.6N 148.0W   54.0 Crater                 IAU1970
            Blazhko D                       33.0N 145.2W   34.0 Crater                 AW82
            Blazkho F                       31.4N 146.7W   34.0 Crater                 AW82
            Blazkho L                       29.3N 147.6W   44.0 Crater                 AW82
            Blazkho R                       30.0N 149.8W   53.0 Crater                 AW82
            Bliss                           53.0N  13.5W   20.0 Crater                 IAU2000
            Block                            3.2S  23.4W    0.0 Crater (A)             IAU1973
            Bobillier                       19.6N  15.5E    6.0 Crater                 IAU1976
            Bobone                          26.9N 131.8W   31.0 Crater                 IAU1970
            Bode                             6.7N   2.4W   18.0 Crater         L1824   L1824
            Bode A                           9.0N   1.2W   12.0 Crater                 NLF?
            Bode B                           8.7N   3.1W   10.0 Crater                 NLF?
            Bode C                          12.2N   4.8W    7.0 Crater                 NLF?
            Bode D                           7.2N   3.3W    4.0 Crater                 NLF?
            Bode E                          12.4N   3.4W    7.0 Crater                 NLF?
            Bode G                           6.4N   3.5W    4.0 Crater                 NLF?
            Bode H                          12.2N   6.5W    4.0 Crater                 NLF?
            Bode K                           9.3N   2.3W    6.0 Crater                 NLF?
            Bode L                           5.6N   3.8W    5.0 Crater                 NLF?
            Bode N                          10.9N   3.9W    6.0 Crater                 NLF?
            Boethius                         5.6N  72.3E   10.0 Crater                 IAU1976
            Boguslawsky                     72.9S  43.2E   97.0 Crater         R1651   M1834
            Boguslawsky A                   74.4S  44.3E    6.0 Crater                 NLF?
            Boguslawsky B                   73.9S  61.0E   63.0 Crater                 NLF?
            Boguslawsky C                   70.9S  27.7E   36.0 Crater                 NLF?
            Boguslawsky D                   72.8S  47.3E   24.0 Crater                 NLF?
            Boguslawsky E                   74.2S  53.6E   14.0 Crater                 NLF?
            Boguslawsky F                   75.3S  52.5E   30.0 Crater                 NLF?
            Boguslawsky G                   71.5S  34.5E   21.0 Crater                 NLF?
            Boguslawsky H                   72.8S  29.1E   19.0 Crater                 NLF?
            Boguslawsky J                   72.2S  28.9E   36.0 Crater                 NLF?
            Boguslawsky K                   73.5S  50.9E   46.0 Crater                 NLF?
            Boguslawsky L                   70.6S  36.6E   22.0 Crater                 NLF?
            Boguslawsky M                   70.6S  35.2E    9.0 Crater                 NLF?
            Boguslawsky N                   74.0S  33.3E   28.0 Crater                 NLF?
            Bohnenberger                    16.2S  40.0E   33.0 Crater         M1834   M1834
            Bohnenberger A                  17.8S  40.1E   30.0 Crater                 NLF?
            Bohnenberger C                  18.5S  41.1E   16.0 Crater                 NLF?
            Bohnenberger D                  18.3S  42.6E   14.0 Crater                 NLF?
            Bohnenberger E                  17.4S  42.1E   13.0 Crater                 NLF?
            Bohnenberger F                  14.7S  39.6E   10.0 Crater                 NLF?
            Bohnenberger G                  17.2S  40.1E   12.0 Crater                 NLF?
            Bohnenberger J                  14.8S  40.3E    5.0 Crater                 NLF?
            Bohnenberger N                  17.9S  41.9E    6.0 Crater                 NLF?
            Bohnenberger P                  19.1S  41.4E   11.0 Crater                 NLF?
            Bohnenberger W                  18.2S  41.1E   10.0 Crater                 NLF?
            Bohr                            12.4N  86.6W   71.0 Crater         RLA1963 IAU1964
            Bok                             20.2S 171.6W   45.0 Crater                 IAU1979
            Bok C                           19.1S 170.2W   27.0 Crater                 AW82
            Boltzmann                       74.9S  90.7W   76.0 Crater         RLA1963 IAU1964
            Bolyai                          33.6S 125.9E  135.0 Crater                 IAU1970
            Bolyai D                        32.5S 128.0E   34.0 Crater                 AW82
            Bolyai K                        36.3S 126.8E   29.0 Crater                 AW82
            Bolyai L                        36.3S 126.2E   73.0 Crater                 AW82
            Bolyai Q                        36.1S 122.5E   28.0 Crater                 AW82
            Bolyai W                        32.2S 123.9E   50.0 Crater                 AW82
            Bombelli                         5.3N  56.2E   10.0 Crater                 IAU1976
            Bondarenko                      17.8S 136.3E   30.0 Crater         N       IAU1991
            Bonpland                         8.3S  17.4W   60.0 Crater         M1834   M1834
            Bonpland C                      10.2S  17.4W    4.0 Crater                 NLF?
            Bonpland D                      10.1S  18.2W    6.0 Crater                 NLF?
            Bonpland F                       7.3S  19.3W    4.0 Crater                 NLF?
            Bonpland G                      11.6S  18.8W    4.0 Crater                 NLF?
            Bonpland H                      11.4S  19.9W    4.0 Crater                 NLF?
            Bonpland J                      11.4S  20.4W    3.0 Crater                 NLF?
            Bonpland L                       7.5S  21.2W    3.0 Crater                 NLF?
            Bonpland N                       9.4S  21.4W    3.0 Crater                 NLF?
            Bonpland P                      10.9S  21.5W    1.0 Crater                 NLF?
            Bonpland R                      10.7S  18.6W    3.0 Crater                 NLF?
            Boole                           63.7N  87.4W   63.0 Crater         RLA1963 IAU1964
            Boole A                         63.6N  80.6W   56.0 Crater                 RLA1963?
            Boole B                         63.6N  77.3W    9.0 Crater                 RLA1963?
            Boole C                         65.4N  82.5W   18.0 Crater                 RLA1963?
            Boole D                         64.0N  83.5W   10.0 Crater                 RLA1963?
            Boole E                         62.9N  84.6W   16.0 Crater                 RLA1963?
            Boole F                         64.2N  79.4W   34.0 Crater                 RLA1963?
            Boole G                         64.8N  90.9W   41.0 Crater                 RLA1963?
            Boole H                         61.6N  88.9W   75.0 Crater                 RLA1963?
            Boole R                         64.4N  78.0W   13.0 Crater                 RLA1963?
            Borda                           25.1S  46.6E   44.0 Crater         M1834   M1834
            Borda A                         26.8S  51.0E   19.0 Crater                 NLF?
            Borda D                         24.5S  46.3E    6.0 Crater                 NLF?
            Borda E                         24.0S  45.5E   12.0 Crater                 NLF?
            Borda F                         26.3S  47.5E   11.0 Crater                 NLF?
            Borda G                         26.2S  45.4E    6.0 Crater                 NLF?
            Borda H                         26.7S  46.7E   10.0 Crater                 NLF?
            Borda J                         26.9S  47.0E   17.0 Crater                 NLF?
            Borda K                         27.5S  47.2E   12.0 Crater                 NLF?
            Borda L                         27.0S  47.7E   12.0 Crater                 NLF?
            Borda M                         25.4S  43.9E   15.0 Crater                 NLF?
            Borda R                         27.4S  50.5E   17.0 Crater                 NLF?
            Borel                           22.3N  26.4E    4.0 Crater                 IAU1976
            Boris                           30.6N  33.5W    1.0 Crater         X       IAU1979
            Borman                          38.8S 147.7W   50.0 Crater                 IAU1970
            Borman V                        37.4S 150.6W   28.0 Crater                 AW82
            Born                             6.0S  66.8E   14.0 Crater                 IAU1979
            Boscovich                        9.8N  11.1E   46.0 Crater         VL1645  S1791
            Boscovich A                      9.5N  12.6E    6.0 Crater                 NLF?
            Boscovich B                      9.8N   9.2E    5.0 Crater                 NLF?
            Boscovich C                      8.5N  12.0E    3.0 Crater                 NLF?
            Boscovich D                      9.0N  12.2E    5.0 Crater                 NLF?
            Boscovich E                      9.0N  12.7E   21.0 Crater                 NLF?
            Boscovich F                     10.6N  11.4E    5.0 Crater                 NLF?
            Boscovich P                     11.5N  10.3E   67.0 Crater                 NLF?
            Bose                            53.5S 170.0W   91.0 Crater                 IAU1970
            Bose A                          49.3S 166.5W   28.0 Crater                 AW82
            Bose D                          52.7S 166.1W   20.0 Crater                 AW82
            Bose U                          52.8S 174.6W   38.0 Crater                 AW82
            Boss                            45.8N  89.2E   47.0 Crater         RLA1963 IAU1964
            Boss A                          52.3N  80.3E   27.0 Crater                 RLA1963?
            Boss B                          52.0N  77.1E   12.0 Crater                 RLA1963?
            Boss C                          52.2N  76.4E   21.0 Crater                 RLA1963?
            Boss D                          44.9N  87.4E   15.0 Crater                 RLA1963?
            Boss F                          54.5N  86.8E   36.0 Crater                 RLA1963?
            Boss K                          49.6N  80.7E   19.0 Crater                 RLA1963?
            Boss L                          50.9N  82.3E   40.0 Crater                 RLA1963?
            Boss M                          52.0N  83.8E   13.0 Crater                 RLA1963?
            Boss N                          52.2N  85.2E   16.0 Crater                 RLA1963?
            Bouguer                         52.3N  35.8W   22.0 Crater         M1834   M1834
            Bouguer A                       52.5N  33.8W    8.0 Crater                 NLF?
            Bouguer B                       53.3N  33.0W    7.0 Crater                 NLF?
            Boussingault                    70.2S  54.6E  142.0 Crater         M1834   M1834
            Boussingault A                  69.9S  54.0E   72.0 Crater                 NLF?
            Boussingault B                  65.6S  46.9E   54.0 Crater                 NLF?
            Boussingault C                  65.1S  48.2E   24.0 Crater                 NLF?
            Boussingault D                  63.5S  44.9E    9.0 Crater                 NLF?
            Boussingault E                  67.2S  46.8E   98.0 Crater                 NLF?
            Boussingault F                  68.8S  39.4E   16.0 Crater                 NLF?
            Boussingault G                  71.4S  51.8E    5.0 Crater                 NLF?
            Boussingault K                  68.9S  50.9E   29.0 Crater                 NLF?
            Boussingault N                  71.5S  62.1E   15.0 Crater                 NLF?
            Boussingault P                  67.1S  45.1E   13.0 Crater                 NLF?
            Boussingault R                  64.3S  48.6E   12.0 Crater                 NLF?
            Boussingault S                  64.1S  46.9E   16.0 Crater                 NLF?
            Boussingault T                  63.0S  43.2E   20.0 Crater                 NLF?
            Bouvard B                       41.7S  79.7W   25.0 Crater                 NLF?
            Bouvard C                       37.1S  77.3W   16.0 Crater                 NLF?
            Bouvard D                       42.8S  80.5W   26.0 Crater                 NLF?
            Bouvard E                       41.9S  77.4W   14.0 Crater                 NLF?
            Bouvard F                       42.5S  76.4W   11.0 Crater                 NLF?
            Bouvard G                       42.1S  74.6W   21.0 Crater                 NLF?
            Bouvard M                       40.6S  77.4W   69.0 Crater                 NLF?
            Bouvard N                       38.6S  76.5W   66.0 Crater                 NLF?
            Bouvard P                       39.0S  75.0W   13.0 Crater                 NLF?
            Bouvard R                       35.5S  84.2W    8.0 Crater                 NLF?
            Bouvard S                       35.6S  80.5W   12.0 Crater                 NLF?
            Bowditch                        25.0S 103.1E   40.0 Crater                 IAU1976
            Bowditch M                      26.7S 103.3E   16.0 Crater                 AW82
            Bowditch N                      26.6S 102.8E   16.0 Crater                 AW82
            Bowen                           17.6N   9.1E    8.0 Crater                 IAU1973
            Bowen-Apollo                    20.3N  30.9E    0.0 Crater (A)             IAU1973
            Boyle                           53.1S 178.1E   57.0 Crater                 IAU1970
            Boyle A                         50.8S 178.3E   21.0 Crater                 AW82
            Boyle Z                         51.3S 177.7E   52.0 Crater                 AW82
            Brackett                        17.9N  23.6E    8.0 Crater                 IAU1973
            Bradley H                       22.9N   0.3W    5.0 Crater                 NLF?
            Bradley K                       23.3N   0.7W    4.0 Crater                 NLF?
            Bragg                           42.5N 102.9W   84.0 Crater                 IAU1970
            Bragg H                         41.7N 101.0W   40.0 Crater                 AW82
            Bragg M                         39.1N 102.5W   45.0 Crater                 AW82
            Bragg P                         40.0N 104.4W   30.0 Crater                 AW82
            Brashear                        73.8S 170.7W   55.0 Crater                 IAU1970
            Brashear P                      76.8S 175.7W   71.0 Crater                 AW82
            Brayley                         20.9N  36.9W   14.0 Crater         VL1645  NLF
            Brayley B                       20.8N  34.3W   10.0 Crater         VL1645  NLF?
            Brayley C                       21.4N  39.4W    9.0 Crater                 NLF?
            Brayley D                       20.1N  32.8W    6.0 Crater                 NLF?
            Brayley E                       21.2N  39.7W    5.0 Crater                 NLF?
            Brayley F                       21.1N  34.0W    5.0 Crater                 NLF?
            Brayley G                       24.2N  36.5W    5.0 Crater                 NLF?
            Brayley K                       21.2N  41.7W    3.0 Crater                 NLF?
            Brayley L                       20.9N  42.6W    4.0 Crater                 NLF?
            Brayley S                       25.0N  36.7W    3.0 Crater                 NLF?
            Bredikhin                       17.3N 158.2W   59.0 Crater                 IAU1970
            Bredikhin B                     19.0N 157.4W   18.0 Crater                 AW82
            Breislak                        48.2S  18.3E   49.0 Crater         VL1645  S1878
            Breislak A                      47.0S  17.4E    7.0 Crater                 NLF?
            Breislak B                      47.6S  18.1E    7.0 Crater                 NLF?
            Breislak C                      48.9S  18.8E    6.0 Crater                 NLF?
            Breislak D                      48.0S  18.7E    5.0 Crater                 NLF?
            Breislak E                      47.7S  19.1E    8.0 Crater                 NLF?
            Breislak F                      48.4S  19.4E    7.0 Crater                 NLF?
            Breislak G                      46.9S  19.1E   16.0 Crater                 NLF?
            Brenner                         39.0S  39.3E   97.0 Crater         R1651   F1936
            Brenner A                       40.4S  40.0E   32.0 Crater                 NLF?
            Brenner B                       37.4S  41.8E   10.0 Crater                 NLF?
            Brenner C                       36.5S  41.9E    7.0 Crater                 NLF?
            Brenner D                       36.2S  38.7E    8.0 Crater                 NLF?
            Brenner E                       38.9S  40.5E   14.0 Crater                 NLF?
            Brenner F                       40.6S  37.0E   14.0 Crater                 NLF?
            Brenner H                       36.9S  38.7E    8.0 Crater                 NLF?
            Brenner J                       37.7S  36.6E    8.0 Crater                 NLF?
            Brenner K                       38.0S  37.3E    7.0 Crater                 NLF?
            Brenner L                       38.1S  36.6E    5.0 Crater                 NLF?
            Brenner M                       38.8S  36.9E    7.0 Crater                 NLF?
            Brenner N                       39.0S  36.7E    7.0 Crater                 NLF?
            Brenner P                       38.8S  35.3E    7.0 Crater                 NLF?
            Brenner Q                       39.2S  35.9E    8.0 Crater                 NLF?
            Brenner R                       40.7S  38.3E   10.0 Crater                 NLF?
            Brenner S                       38.4S  36.2E    6.0 Crater                 NLF?
            Brewster                        23.3N  34.7E   10.0 Crater                 IAU1976
            Brianchon                       75.0N  86.2W  134.0 Crater         RLA1963 IAU1964
            Brianchon A                     76.7N  86.3W   50.0 Crater                 RLA1963?
            Brianchon B                     72.2N  89.1W   31.0 Crater                 RLA1963?
            Brianchon T                     75.8N  99.8W   30.0 Crater                 RLA1963?
            Bridge                          26.0N   3.6E    1.0 Crater (A)             IAU1973
            Bridgman                        43.5N 137.1E   80.0 Crater                 IAU1970
            Bridgman C                      46.7N 140.2E   35.0 Crater                 AW82
            Bridgman E                      44.1N 141.7E   29.0 Crater                 AW82
            Bridgman F                      44.0N 141.2E   51.0 Crater                 AW82
            Briggs                          26.5N  69.1W   37.0 Crater         S1791   S1791
            Briggs A                        27.1N  73.7W   23.0 Crater                 NLF?
            Briggs B                        28.1N  70.9W   25.0 Crater                 NLF?
            Briggs C                        25.0N  66.9W    6.0 Crater                 NLF?
            Brisbane                        49.1S  68.5E   44.0 Crater         S1878   S1878
            Brisbane E                      50.0S  71.2E   56.0 Crater                 NLF?
            Brisbane H                      50.3S  64.9E   43.0 Crater                 NLF?
            Brisbane X                      50.4S  67.4E   20.0 Crater                 NLF?
            Brisbane Y                      51.4S  69.8E   17.0 Crater                 NLF?
            Brisbane Z                      52.8S  72.4E   64.0 Crater                 NLF?
            Bronk                           26.1N 134.5W   64.0 Crater                 IAU1979
            Bront|:e                        20.2N  30.7E    0.0 Crater (A)             IAU1973
            Brouwer                         36.2S 126.0W  158.0 Crater                 IAU1970
            Brouwer C                       33.4S 122.1W   26.0 Crater                 AW82
            Brouwer H                       35.9S 124.4W   19.0 Crater                 AW82
            Brouwer P                       38.6S 126.5W   29.0 Crater                 AW82
            Brown                           46.4S  17.9W   34.0 Crater         IC1935  IC1935
            Brown A                         48.1S  17.4W   16.0 Crater                 NLF?
            Brown B                         44.7S  16.1W   12.0 Crater                 NLF?
            Brown C                         47.6S  17.0W   13.0 Crater                 NLF?
            Brown D                         46.0S  16.1W   20.0 Crater                 NLF?
            Brown E                         46.8S  17.6W   22.0 Crater                 NLF?
            Brown F                         46.9S  18.3W    6.0 Crater                 NLF?
            Brown G                         45.5S  16.8W    5.0 Crater                 NLF?
            Brown K                         46.6S  15.6W   16.0 Crater                 NLF?
            Bruce                            1.1N   0.4E    6.0 Crater         K1898   K1898
            Brunner                          9.9S  90.9E   53.0 Crater                 IAU1970
            Brunner L                       12.4S  91.3E   34.0 Crater                 AW82
            Brunner N                       11.4S  90.7E   18.0 Crater                 AW82
            Brunner P                       12.5S  90.1E   19.0 Crater                 AW82
            Buch                            38.8S  17.7E   53.0 Crater         M1834   M1834
            Buch A                          41.0S  17.6E   19.0 Crater                 NLF?
            Buch B                          37.8S  17.0E    6.0 Crater                 NLF?
            Buch C                          37.3S  17.2E   28.0 Crater                 NLF?
            Buch D                          39.6S  16.5E    7.0 Crater                 NLF?
            Buch E                          39.0S  16.5E    6.0 Crater                 NLF?
            Buffon                          40.4S 133.4W  106.0 Crater                 IAU1970
            Buffon D                        40.2S 131.7W   20.0 Crater                 AW82
            Buffon H                        42.3S 128.5W   26.0 Crater                 AW82
            Buffon K                        46.3S 128.0W   18.0 Crater                 AW82
            Buffon V                        39.2S 137.1W   38.0 Crater                 AW82
            Buisson                          1.4S 112.5E   56.0 Crater                 IAU1970
            Buisson V                        0.6S 110.8E   22.0 Crater                 AW82
            Buisson X                        1.6N 111.6E   21.0 Crater                 AW82
            Buisson Y                        1.4N 112.6E   36.0 Crater                 AW82
            Buisson Z                        0.0S 112.5E   98.0 Crater                 AW82
            Bullialdus                      20.7S  22.2W   60.0 Crater         VL1645  R1651
            Bullialdus A                    22.1S  21.5W   26.0 Crater                 NLF?
            Bullialdus B                    23.4S  21.9W   21.0 Crater                 NLF?
            Bullialdus E                    21.7S  23.9W    4.0 Crater                 NLF?
            Bullialdus F                    22.5S  24.8W    6.0 Crater                 NLF?
            Bullialdus G                    23.2S  23.6W    4.0 Crater                 NLF?
            Bullialdus H                    22.7S  19.3W    5.0 Crater                 NLF?
            Bullialdus K                    21.8S  25.6W   12.0 Crater                 NLF?
            Bullialdus L                    20.2S  24.4W    4.0 Crater                 NLF?
            Bullialdus R                    20.1S  19.8W   17.0 Crater                 NLF?
            Bullialdus Y                    18.5S  19.1W    4.0 Crater                 NLF?
            Bunsen                          41.4N  85.3W   52.0 Crater         RLA1963 IAU1964
            Bunsen A                        43.2N  88.9W   39.0 Crater                 RLA1963?
            Bunsen B                        44.2N  88.2W   20.0 Crater                 RLA1963?
            Bunsen C                        44.2N  90.0W   18.0 Crater                 RLA1963?
            Bunsen D                        40.9N  86.9W   14.0 Crater                 RLA1963?
            Burckhardt                      31.1N  56.5E   56.0 Crater         VL1645  M1834
            Burckhardt A                    30.5N  58.8E   28.0 Crater                 NLF?
            Burckhardt B                    29.9N  60.1E   11.0 Crater                 NLF?
            Burckhardt C                    31.6N  59.0E    6.0 Crater                 NLF?
            Burckhardt E                    30.6N  55.7E   39.0 Crater                 NLF?
            Burckhardt F                    31.4N  57.2E   43.0 Crater                 NLF?
            Burckhardt G                    32.1N  57.5E    7.0 Crater                 NLF?
            B|:urg                          45.0N  28.2E   39.0 Crater         VL1645  M1834
            B|:urg A                        46.8N  33.1E   12.0 Crater                 NLF?
            B|:urg B                        42.6N  23.5E    6.0 Crater                 NLF?
            Burnham                         13.9S   7.3E   24.0 Crater         K1898   K1898
            Burnham A                       14.8S   7.1E    7.0 Crater                 NLF?
            Burnham B                       15.3S   7.3E    4.0 Crater                 NLF?
            Burnham F                       14.3S   6.9E    9.0 Crater                 NLF?
            Burnham K                       13.6S   7.4E    3.0 Crater                 NLF?
            Burnham L                       14.2S   7.6E    4.0 Crater                 NLF?
            Burnham M                       14.1S   9.0E    9.0 Crater                 NLF?
            Burnham T                       14.7S   9.6E    4.0 Crater                 NLF?
            B|:usching                      38.0S  20.0E   52.0 Crater         M1834   M1834
            B|:usching A                    38.3S  20.4E    6.0 Crater                 NLF?
            B|:usching B                    39.0S  22.8E   17.0 Crater                 NLF?
            B|:usching C                    37.2S  19.6E    7.0 Crater                 NLF?
            B|:usching D                    38.6S  22.0E   33.0 Crater                 NLF?
            B|:usching E                    36.6S  18.4E   15.0 Crater                 NLF?
            B|:usching F                    39.0S  21.0E    6.0 Crater                 NLF?
            B|:usching G                    39.5S  21.6E    8.0 Crater                 NLF?
            B|:usching H                    37.4S  21.1E    5.0 Crater                 NLF?
            B|:usching J                    39.5S  22.2E    7.0 Crater                 NLF?
            B|:usching K                    37.9S  18.7E    5.0 Crater                 NLF?
            Butlerov                        12.5N 108.7W   40.0 Crater                 IAU1970
            Buys-Ballot                     20.8N 174.5E   55.0 Crater                 IAU1970
            Buys-Ballot H                   19.4N 179.5E   22.0 Crater                 AW82
            Buys-Ballot Q                   19.5N 172.7E   58.0 Crater                 AW82
            Buys-Ballot Y                   22.9N 174.0E   31.0 Crater                 AW82
            Buys-Ballot Z                   22.5N 174.5E   58.0 Crater                 AW82
            Byrd                            85.3N   9.8E   93.0 Crater         RLA1963 IAU1964
            Byrd C                          84.7N  26.8E   52.0 Crater                 NLF?
            Byrd D                          85.4N  32.7E   24.0 Crater                 NLF?
            Byrgius                         24.7S  65.3W   87.0 Crater         H1647   R1651
            Byrgius A                       24.5S  63.7W   19.0 Crater                 NLF?
            Byrgius B                       23.9S  60.8W   23.0 Crater                 NLF?
            Byrgius D                       24.1S  67.1W   27.0 Crater                 NLF?
            Byrgius E                       23.5S  66.2W   18.0 Crater                 NLF?
            Byrgius H                       23.7S  62.4W   27.0 Crater                 NLF?
            Byrgius K                       23.0S  61.8W   14.0 Crater                 NLF?
            Byrgius N                       22.3S  63.1W   20.0 Crater                 NLF?
            Byrgius P                       22.6S  64.1W   19.0 Crater                 NLF?
            Byrgius R                       26.5S  60.7W    7.0 Crater                 NLF?
            Byrgius S                       26.2S  61.4W   43.0 Crater                 NLF?
            Byrgius T                       25.1S  61.5W    5.0 Crater                 NLF?
            Byrgius U                       25.8S  67.2W   13.0 Crater                 NLF?
            Byrgius V                       26.0S  67.8W    9.0 Crater                 NLF?
            Byrgius W                       26.1S  68.5W   14.0 Crater                 NLF?
            Byrgius X                       25.7S  65.4W    6.0 Crater                 NLF?
            C. Herschel                     34.5N  31.2W   13.0 Crater                 NLF
            C. Herschel C                   37.2N  32.5W    7.0 Crater                 NLF?
            C. Herschel E                   34.2N  34.7W    5.0 Crater                 NLF?
            C. Herschel U                   36.2N  31.5W    3.0 Crater                 NLF?
            C. Herschel V                   36.4N  33.5W    4.0 Crater                 NLF?
            C. Mayer                        63.2N  17.3E   38.0 Crater         VL1645  S1791
            C. Mayer B                      60.2N  15.6E   36.0 Crater                 NLF?
            C. Mayer D                      62.1N  18.6E   66.0 Crater                 NLF?
            C. Mayer E                      61.1N  16.0E   12.0 Crater                 NLF?
            C. Mayer F                      62.0N  19.5E    7.0 Crater                 NLF?
            C. Mayer H                      64.1N  14.7E   43.0 Crater                 NLF?
            Cabannes                        60.9S 169.6W   80.0 Crater                 IAU1970
            Cabannes J                      62.2S 167.2W   34.0 Crater                 AW82
            Cabannes M                      64.2S 170.2W   48.0 Crater                 AW82
            Cabannes Q                      63.3S 174.5W   49.0 Crater                 AW82
            Cabeus                          84.9S  35.5W   98.0 Crater                 NLF
            Cabeus A                        82.2S  39.1W   48.0 Crater                 NLF?
            Cabeus B                        82.4S  53.0W   61.0 Crater                 NLF?
            Cailleux                        60.8S 153.3E   50.0 Crater         N       IAU1997
            Cajal                           12.6N  31.1E    9.0 Crater                 IAU1973
            Cajori                          47.4S 168.8E   70.0 Crater                 IAU1970
            Cajori K                        49.1S 169.8E   32.0 Crater                 AW82
            Calippus                        38.9N  10.7E   32.0 Crater                 NLF
            Calippus A                      37.0N   7.9E   16.0 Crater                 NLF?
            Calippus B                      36.0N  10.0E    7.0 Crater                 NLF?
            Calippus C                      39.6N   9.1E   40.0 Crater                 NLF?
            Calippus D                      36.3N  11.3E    4.0 Crater                 NLF?
            Calippus E                      38.9N  11.9E    5.0 Crater                 NLF?
            Calippus F                      40.5N  10.0E    6.0 Crater                 NLF?
            Calippus G                      41.3N  11.5E    4.0 Crater                 NLF?
            Camelot                         20.2N  30.7E    1.0 Crater (A)             IAU1973
            Cameron                          6.2N  45.9E   10.0 Crater                 IAU1973
            Campanus                        28.0S  27.8W   48.0 Crater         VL1645   R1651
            Campanus A                      26.0S  28.6W   11.0 Crater                 NLF?
            Campanus B                      29.2S  29.2W    6.0 Crater                 NLF?
            Campanus G                      28.6S  31.3W   10.0 Crater                 NLF?
            Campanus K                      26.6S  28.3W    5.0 Crater                 NLF?
            Campanus X                      27.8S  27.3W    4.0 Crater                 NLF?
            Campanus Y                      27.8S  28.2W    4.0 Crater                 NLF?
            Campbell                        45.3N 151.4E  219.0 Crater                 IAU1970
            Campbell A                      52.2N 155.2E   20.0 Crater                 AW82
            Campbell E                      46.4N 158.6E   15.0 Crater                 AW82
            Campbell N                      43.2N 152.3E   23.0 Crater                 AW82
            Campbell X                      47.7N 149.4E   24.0 Crater                 AW82
            Campbell Z                      48.8N 152.9E   28.0 Crater                 AW82
            Cannizzaro                      55.6N  99.6W   56.0 Crater                 IAU1970
            Cannon                          19.9N  81.4E   56.0 Crater         RLA1963 IAU1964
            Cannon B                        17.5N  80.0E   31.0 Crater                 RLA1963?
            Cannon E                        19.2N  79.1E   22.0 Crater                 RLA1963?
            Cantor                          38.2N 118.6E   81.0 Crater                 IAU1970
            Cantor C                        39.5N 120.3E   21.0 Crater                 AW82
            Cantor T                        37.9N 113.4E   23.0 Crater                 AW82
            Capella                          7.5S  35.0E   49.0 Crater         VL1645  R1651
            Capella A                        7.6S  37.2E   13.0 Crater                 NLF?
            Capella B                        9.4S  36.8E   10.0 Crater                 NLF?
            Capella C                        5.7S  36.3E   11.0 Crater                 NLF?
            Capella D                        6.7S  37.6E    8.0 Crater                 NLF?
            Capella E                        7.5S  37.7E   16.0 Crater                 NLF?
            Capella F                        9.2S  35.4E   14.0 Crater                 NLF?
            Capella G                        6.8S  36.9E   12.0 Crater                 NLF?
            Capella H                        8.1S  37.4E    9.0 Crater                 NLF?
            Capella J                        9.4S  36.0E    9.0 Crater                 NLF?
            Capella M                        4.4S  37.0E   12.0 Crater                 NLF?
            Capella R                        6.0S  35.2E    7.0 Crater                 NLF?
            Capella T                        6.9S  34.2E    6.0 Crater                 NLF?
            Capuanus                        34.1S  26.7W   59.0 Crater         R1651   NLF
            Capuanus A                      34.7S  25.6W   13.0 Crater                 NLF?
            Capuanus B                      34.3S  27.7W   11.0 Crater                 NLF?
            Capuanus C                      34.9S  25.3W   10.0 Crater                 NLF?
            Capuanus D                      36.4S  26.2W   22.0 Crater                 NLF?
            Capuanus E                      37.5S  27.1W   29.0 Crater                 NLF?
            Capuanus F                      36.9S  26.6W    8.0 Crater                 NLF?
            Capuanus H                      39.4S  27.2W    4.0 Crater                 NLF?
            Capuanus K                      37.9S  26.5W    9.0 Crater                 NLF?
            Capuanus L                      38.3S  26.3W   11.0 Crater                 NLF?
            Capuanus M                      37.5S  25.6W    7.0 Crater                 NLF?
            Capuanus P                      35.3S  28.3W   78.0 Crater                 NLF?
            Cardanus                        13.2N  72.5W   49.0 Crater         R1651   R1651
            Cardanus B                      11.4N  73.8W   13.0 Crater                 NLF?
            Cardanus C                      11.3N  76.2W   14.0 Crater                 NLF?
            Cardanus E                      12.7N  70.7W    6.0 Crater                 NLF?
            Cardanus G                      11.5N  74.9W    8.0 Crater                 NLF?
            Cardanus K                      14.2N  76.8W    8.0 Crater                 NLF?
            Cardanus M                      14.9N  77.1W    9.0 Crater                 NLF?
            Cardanus R                      12.3N  73.4W   21.0 Crater                 NLF?
            Carlini                         33.7N  24.1W   10.0 Crater         M1834   M1834
            Carlini A                       35.4N  26.6W    7.0 Crater                 NLF?
            Carlini C                       35.0N  22.9W    4.0 Crater                 NLF?
            Carlini D                       33.0N  16.0W    9.0 Crater                 NLF?
            Carlini E                       31.6N  20.5W    1.0 Crater                 NLF?
            Carlini G                       32.6N  25.0W    4.0 Crater                 NLF?
            Carlini H                       32.4N  24.4W    4.0 Crater                 NLF?
            Carlini K                       31.1N  23.7W    4.0 Crater                 NLF?
            Carlini L                       31.3N  24.8W    3.0 Crater                 NLF?
            Carlini S                       37.9N  27.2W    4.0 Crater                 NLF?
            Carlos                          24.9N   2.3E    4.0 Crater         X       IAU1976
            Carmichael                      19.6N  40.4E   20.0 Crater         VL1645  IAU1973
            Carnot                          52.3N 143.5W  126.0 Crater                 IAU1970
            Carnot F                        52.5N 138.9W   35.0 Crater                 AW82
            Carol                            8.5N 122.3E    8.0 Crater         X       IAU1979
            Carpenter                       69.4N  50.9W   59.0 Crater         R1651   NLF
            Carpenter T                     70.2N  58.3W    9.0 Crater                 NLF?
            Carpenter U                     70.6N  57.0W   26.0 Crater                 NLF?
            Carpenter V                     71.8N  54.1W    6.0 Crater                 NLF?
            Carpenter W                     72.3N  59.8W   10.0 Crater                 NLF?
            Carpenter Y                     71.9N  62.7W    9.0 Crater                 NLF?
            Carrel                          10.7N  26.7E   15.0 Crater                 IAU1979
            Carrillo                         2.2S  80.9E   16.0 Crater                 IAU1979
            Carrington                      44.0N  62.1E   30.0 Crater         S1878   S1878
            Cartan                           4.2N  59.3E   15.0 Crater                 IAU1976
            Carver                          43.0S 126.9E   59.0 Crater                 IAU1970
            Carver L                        45.2S 127.8E   33.0 Crater                 AW82
            Carver M                        45.0S 126.8E   76.0 Crater                 AW82
            Casatus                         72.8S  29.5W  108.0 Crater         VL1645  R1651
            Casatus A                       73.0S  38.9W   56.0 Crater                 NLF?
            Casatus C                       72.2S  30.2W   17.0 Crater                 NLF?
            Casatus D                       77.2S  44.3W   36.0 Crater                 NLF?
            Casatus E                       79.1S  53.2W   41.0 Crater                 NLF?
            Casatus H                       72.0S  21.3W   35.0 Crater                 NLF?
            Casatus J                       74.3S  32.8W   22.0 Crater                 NLF?
            Casatus K                       75.0S  41.4W   36.0 Crater                 NLF?
            Cassegrain                      52.4S 113.5E   55.0 Crater                 IAU1970
            Cassegrain B                    49.0S 114.0E   39.0 Crater                 AW82
            Cassegrain H                    53.1S 115.6E   28.0 Crater                 AW82
            Cassegrain K                    55.0S 113.5E   17.0 Crater                 AW82
            Cassini                         40.2N   4.6E   56.0 Crater         S1791   S1791
            Cassini A                       40.5N   4.8E   15.0 Crater                 NLF?
            Cassini B                       39.9N   3.9E    9.0 Crater                 NLF?
            Cassini C                       41.7N   7.8E   14.0 Crater                 NLF?
            Cassini E                       42.9N   7.3E   10.0 Crater                 NLF?
            Cassini F                       40.9N   7.3E    7.0 Crater                 NLF?
            Cassini G                       44.7N   5.5E    5.0 Crater                 NLF?
            Cassini K                       45.2N   4.1E    4.0 Crater                 NLF?
            Cassini L                       44.0N   4.5E    6.0 Crater                 NLF?
            Cassini M                       41.3N   3.7E    8.0 Crater                 NLF?
            Cassini P                       44.7N   1.9E    4.0 Crater                 NLF?
            Cassini W                       42.3N   4.3E    6.0 Crater                 NLF?
            Cassini X                       43.9N   7.9E    4.0 Crater                 NLF?
            Cassini Y                       41.9N   2.2E    3.0 Crater                 NLF?
            Cassini Z                       43.4N   2.3E    4.0 Crater                 NLF?
            Catal|%an                       45.7S  87.3W   25.0 Crater                 IAU1970
            Catal|%an A                     45.7S  89.2W   21.0 Crater                 AW82
            Catal|%an B                     45.6S  88.4W   14.0 Crater                 AW82
            Catal|%an U                     45.1S  90.6W   20.0 Crater                 AW82
            Catharina                       18.1S  23.4E  104.0 Crater         VL1645  R1651
            Catharina A                     20.2S  22.3E   14.0 Crater                 NLF?
            Catharina B                     17.0S  24.3E   24.0 Crater                 NLF?
            Catharina C                     20.3S  24.4E   28.0 Crater                 NLF?
            Catharina D                     16.8S  21.4E    9.0 Crater                 NLF?
            Catharina E                     17.1S  21.3E    7.0 Crater                 NLF?
            Catharina F                     19.5S  23.1E    7.0 Crater                 NLF?
            Catharina G                     17.4S  24.9E   17.0 Crater                 NLF?
            Catharina H                     19.2S  25.4E    6.0 Crater                 NLF?
            Catharina J                     19.4S  22.2E    6.0 Crater                 NLF?
            Catharina K                     20.0S  23.9E    7.0 Crater                 NLF?
            Catharina L                     21.0S  24.3E    5.0 Crater                 NLF?
            Catharina M                     19.2S  20.7E    6.0 Crater                 NLF?
            Catharina P                     17.2S  23.3E   46.0 Crater                 NLF?
            Catharina S                     18.8S  23.3E   16.0 Crater                 NLF?
            Cauchy                           9.6N  38.6E   12.0 Crater         H1647   N1876
            Cauchy A                        12.1N  37.9E    8.0 Crater                 NLF?
            Cauchy B                         9.8N  35.8E    6.0 Crater                 NLF?
            Cauchy C                         8.2N  38.9E    4.0 Crater                 NLF?
            Cauchy D                        10.0N  40.3E    9.0 Crater                 NLF?
            Cauchy E                         8.9N  38.6E    4.0 Crater                 NLF?
            Cauchy F                         9.6N  36.8E    4.0 Crater                 NLF?
            Cauchy M                         7.6N  35.1E    5.0 Crater                 NLF?
            Cauchy U                         8.8N  42.3E    5.0 Crater                 NLF?
            Cauchy V                         9.0N  41.5E    5.0 Crater                 NLF?
            Cauchy W                        10.6N  41.6E    4.0 Crater                 NLF?
            Cavalerius                       5.1N  66.8W   57.0 Crater         R1651   R1651
            Cavalerius A                     4.5N  69.5W   14.0 Crater                 NLF?
            Cavalerius B                     6.0N  71.0W   39.0 Crater                 NLF?
            Cavalerius C                     5.8N  69.2W    8.0 Crater                 NLF?
            Cavalerius D                     8.6N  68.3W   52.0 Crater                 NLF?
            Cavalerius E                     7.7N  69.9W    9.0 Crater                 NLF?
            Cavalerius F                     8.1N  65.3W    7.0 Crater                 NLF?
            Cavalerius K                    10.3N  69.2W   10.0 Crater                 NLF?
            Cavalerius L                    10.4N  70.2W   10.0 Crater                 NLF?
            Cavalerius M                    10.3N  71.5W   12.0 Crater                 NLF?
            Cavalerius U                    10.1N  67.4W    7.0 Crater                 NLF?
            Cavalerius W                     6.9N  67.3W    7.0 Crater                 NLF?
            Cavalerius X                     9.2N  66.6W    4.0 Crater                 NLF?
            Cavalerius Y                    10.7N  69.8W    7.0 Crater                 NLF?
            Cavalerius Z                    11.0N  69.5W    4.0 Crater                 NLF?
            Cavendish                       24.5S  53.7W   56.0 Crater         H1647   M1834
            Cavendish A                     24.0S  52.7W   10.0 Crater                 NLF?
            Cavendish B                     23.2S  55.1W   10.0 Crater                 NLF?
            Cavendish E                     25.4S  54.2W   24.0 Crater                 NLF?
            Cavendish F                     26.1S  54.0W   18.0 Crater                 NLF?
            Cavendish L                     21.7S  53.6W    5.0 Crater                 NLF?
            Cavendish M                     22.0S  53.8W    6.0 Crater                 NLF?
            Cavendish N                     22.1S  54.3W    4.0 Crater                 NLF?
            Cavendish P                     24.2S  51.6W    4.0 Crater                 NLF?
            Cavendish S                     23.8S  52.4W    5.0 Crater                 NLF?
            Cavendish T                     24.8S  55.2W    4.0 Crater                 NLF?
            Caventou                        29.8N  29.4W    3.0 Crater                 IAU1976
            Cayley                           4.0N  15.1E   14.0 Crater                 NLF
            Celsius                         34.1S  20.1E   36.0 Crater         S1878   S1878
            Celsius A                       33.0S  20.5E   14.0 Crater                 NLF?
            Celsius B                       34.6S  19.7E    6.0 Crater                 NLF?
            Celsius D                       34.7S  19.1E   19.0 Crater                 NLF?
            Celsius E                       32.9S  20.1E   11.0 Crater                 NLF?
            Celsius H                       33.8S  20.1E    6.0 Crater                 NLF?
            Censorinus                       0.4S  32.7E    3.0 Crater         R1651   R1651
            Censorinus A                     0.4S  33.0E    7.0 Crater                 NLF?
            Censorinus B                     2.0S  31.4E    8.0 Crater                 NLF?
            Censorinus C                     3.0S  34.1E   28.0 Crater         R1651   NLF?
            Censorinus D                     1.9S  35.8E   10.0 Crater                 NLF?
            Censorinus E                     3.6S  34.8E   12.0 Crater                 NLF?
            Censorinus H                     1.8S  33.7E   10.0 Crater                 NLF?
            Censorinus J                     1.0S  31.3E    5.0 Crater                 NLF?
            Censorinus K                     1.0S  28.8E    4.0 Crater                 NLF?
            Censorinus L                     2.5S  31.2E    4.0 Crater                 NLF?
            Censorinus N                     1.9S  36.5E   36.0 Crater         VL1645  NLF?
            Censorinus S                     3.8S  36.1E   17.0 Crater                 NLF?
            Censorinus T                     3.2S  31.1E    5.0 Crater                 NLF?
            Censorinus U                     1.5S  34.4E    3.0 Crater                 NLF?
            Censorinus V                     0.6S  35.4E    4.0 Crater                 NLF?
            Censorinus W                     1.0S  37.5E    9.0 Crater                 NLF?
            Censorinus X                     0.5S  37.2E   18.0 Crater                 NLF?
            Censorinus Z                     3.7S  36.8E   12.0 Crater                 NLF?
            Cepheus                         40.8N  45.8E   39.0 Crater         VL1645  NLF
            Cepheus A                       41.0N  46.5E   13.0 Crater                 NLF?
            Chacornac                       29.8N  31.7E   51.0 Crater                 NLF
            Chacornac A                     29.8N  31.5E    5.0 Crater                 NLF?
            Chacornac B                     29.0N  31.9E    6.0 Crater                 NLF?
            Chacornac C                     30.8N  32.6E    4.0 Crater                 NLF?
            Chacornac D                     30.6N  33.6E   26.0 Crater                 NLF?
            Chacornac E                     29.4N  33.7E   22.0 Crater                 NLF?
            Chacornac F                     29.2N  32.9E   26.0 Crater                 NLF?
            Chadwick                        52.7S 101.3W   30.0 Crater         N       IAU1985
            Chaffee                         38.8S 153.9W   49.0 Crater                 IAU1970
            Chaffee F                       38.8S 152.5W   35.0 Crater                 AW82
            Chaffee S                       39.5S 156.6W   19.0 Crater                 AW82
            Chaffee W                       38.2S 155.3W   25.0 Crater                 AW82
            Challis                         79.5N   9.2E   55.0 Crater                 NLF
            Challis A                       77.2N   2.3E   32.0 Crater                 NLF?
            Chalonge                        21.2S 117.3W   30.0 Crater         N       IAU1985
            Chamberlin                      58.9S  95.7E   58.0 Crater                 IAU1970
            Chamberlin D                    57.5S 102.1E   21.0 Crater                 AW82
            Chamberlin H                    59.8S  99.9E   27.0 Crater                 AW82
            Chamberlin R                    60.0S  92.3E   38.0 Crater                 AW82
            Champollion                     37.4N 175.2E   58.0 Crater                 IAU1970
            Champollion A                   41.1N 177.1E   27.0 Crater                 AW82
            Champollion F                   37.3N 177.8E   21.0 Crater                 AW82
            Champollion K                   36.5N 176.4E   22.0 Crater                 AW82
            Champollion Y                   40.8N 174.7E   22.0 Crater                 AW82
            Chandler                        43.8N 171.5E   85.0 Crater                 IAU1970
            Chandler G                      43.3N 175.8E   33.0 Crater                 AW82
            Chandler P                      41.7N 170.3E   67.0 Crater                 AW82
            Chandler Q                      41.2N 169.2E   16.0 Crater                 AW82
            Chandler U                      45.5N 166.7E   14.0 Crater                 AW82
            Chang Heng                      19.0N 112.2E   43.0 Crater                 IAU1970
            Chang Heng C                    20.4N 114.0E   25.0 Crater                 AW82
            Chang-Ngo                       12.7S   2.1W    3.0 Crater         X       IAU1976
            Chant                           40.0S 109.2W   33.0 Crater                 IAU1970
            Chaplygin                        6.2S 150.3E  137.0 Crater                 IAU1970
            Chaplygin K                      7.7S 151.2E   19.0 Crater                 AW82
            Chaplygin Q                      7.7S 147.8E   12.0 Crater                 AW82
            Chaplygin Y                      2.8S 149.7E   29.0 Crater                 AW82
            Chapman                         50.4N 100.7W   71.0 Crater                 IAU1970
            Chapman D                       51.4N  96.8W   39.0 Crater                 AW82
            Chapman M                       49.0N 100.7W   38.0 Crater                 AW82
            Chapman V                       51.0N 103.8W   21.0 Crater                 AW82
            Chappe                          61.2S  91.5W   59.0 Crater         N       IAU1994
            Chappell                        54.7N 177.0W   80.0 Crater                 IAU1970
            Chappell E                      55.8N 171.5W   59.0 Crater                 AW82
            Chappell T                      54.8N 178.9E   28.0 Crater                 AW82
            Charles                         29.9N  26.4W    1.0 Crater         X       IAU1976
            Charlier                        36.6N 131.5W   99.0 Crater                 IAU1970
            Charlier Z                      39.7N 131.6W   46.0 Crater                 AW82
            Chaucer                          3.7N 140.0W   45.0 Crater                 IAU1970
            Chaucer B                        6.5N 137.4W   27.0 Crater                 AW82
            Chaucer P                        1.8N 141.3W   13.0 Crater                 AW82
            Chauvenet                       11.5S 137.0E   81.0 Crater                 IAU1970
            Chauvenet C                     10.4S 138.0E   48.0 Crater                 AW82
            Chauvenet D                     10.6S 139.7E   14.0 Crater                 AW82
            Chauvenet E                     11.4S 140.7E   27.0 Crater                 AW82
            Chauvenet G                     12.7S 141.0E   26.0 Crater                 AW82
            Chauvenet J                     13.9S 139.3E   77.0 Crater                 AW82
            Chauvenet L                     13.3S 137.5E   10.0 Crater                 AW82
            Chauvenet P                     14.5S 135.8E   12.0 Crater                 AW82
            Chauvenet Q                     13.3S 135.4E   42.0 Crater                 AW82
            Chauvenet S                     12.3S 134.4E   38.0 Crater                 AW82
            Chauvenet U                     11.0S 135.2E   11.0 Crater                 AW82
            Chawla                          42.8S 147.5W   15.0 Crater                 IAU2006?
            Chebyshev                       33.7S 133.1W  178.0 Crater                 IAU1970
            Chebyshev C                     30.3S 127.2W   27.0 Crater                 AW82
            Chebyshev N                     37.7S 134.4W   24.0 Crater                 AW82
            Chebyshev U                     33.3S 137.0W   36.0 Crater                 AW82
            Chebyshev V                     33.5S 133.6W   23.0 Crater                 AW82
            Chernyshev                      47.3N 174.2E   58.0 Crater                 IAU1970
            Chernyshev B                    48.5N 175.7E   20.0 Crater                 AW82
            Chevallier                      44.9N  51.2E   52.0 Crater                 NLF
            Chevallier B                    45.2N  51.9E   13.0 Crater                 NLF?
            Chevallier F                    46.1N  56.5E    9.0 Crater                 NLF?
            Chevallier K                    43.5N  50.9E    6.0 Crater                 NLF?
            Chevallier M                    46.0N  51.2E   16.0 Crater                 NLF?
            Ching-Te                        20.0N  30.0E    4.0 Crater         X       IAU1976
            Chladni                          4.0N   1.1E   13.0 Crater         S1878   S1878
            Chr|%etien                      45.9S 162.9E   88.0 Crater                 IAU1970
            Chr|%etien A                    43.9S 163.6E   13.0 Crater                 AW82
            Chr|%etien C                    44.5S 165.3E   63.0 Crater                 AW82
            Chr|%etien S                    46.5S 160.5E   40.0 Crater                 AW82
            Chr|%etien W                    44.3S 160.8E   34.0 Crater                 AW82
            Cichus                          33.3S  21.1W   40.0 Crater         VL1645  NLF
            Cichus A                        34.8S  21.4W   21.0 Crater                 NLF?
            Cichus B                        33.2S  19.3W   14.0 Crater                 NLF?
            Cichus C                        33.5S  21.8W   11.0 Crater                 NLF?
            Cichus F                        35.7S  22.4W    8.0 Crater                 NLF?
            Cichus G                        35.5S  23.5W   23.0 Crater                 NLF?
            Cichus H                        32.8S  22.4W    7.0 Crater                 NLF?
            Cichus J                        32.0S  21.3W   13.0 Crater                 NLF?
            Cichus K                        36.6S  19.9W    6.0 Crater                 NLF?
            Cichus N                        30.5S  21.7W    8.0 Crater                 NLF?
            Cinco                            9.1S  15.5E    0.0 Crater (A)             IAU1973
            Clairaut                        47.7S  13.9E   75.0 Crater         VL1645  M1834
            Clairaut A                      48.9S  14.8E   36.0 Crater                 NLF?
            Clairaut B                      48.3S  12.6E   43.0 Crater                 NLF?
            Clairaut C                      48.1S  13.5E   17.0 Crater                 NLF?
            Clairaut D                      47.3S  14.2E   12.0 Crater                 NLF?
            Clairaut E                      46.4S  12.6E   29.0 Crater                 NLF?
            Clairaut F                      46.0S  14.5E   23.0 Crater                 NLF?
            Clairaut G                      47.2S  11.7E    6.0 Crater                 NLF?
            Clairaut H                      48.9S  12.1E    9.0 Crater                 NLF?
            Clairaut J                      45.7S  12.7E   14.0 Crater                 NLF?
            Clairaut K                      49.7S  14.0E   12.0 Crater                 NLF?
            Clairaut M                      46.1S  13.8E    5.0 Crater                 NLF?
            Clairaut P                      49.0S  11.8E    9.0 Crater                 NLF?
            Clairaut R                      48.0S  15.9E   15.0 Crater                 NLF?
            Clairaut S                      47.5S  16.3E   22.0 Crater                 NLF?
            Clark                           38.4S 118.9E   49.0 Crater                 IAU1970
            Clark F                         38.4S 122.5E   27.0 Crater                 AW82
            Clausius                        36.9S  43.8W   24.0 Crater         N1876   N1876
            Clausius A                      36.3S  43.9W    7.0 Crater                 NLF?
            Clausius B                      36.0S  40.1W   23.0 Crater                 NLF?
            Clausius BA                     35.7S  40.1W   17.0 Crater                 NLF?
            Clausius C                      35.4S  38.9W   15.0 Crater                 NLF?
            Clausius D                      38.2S  44.6W   18.0 Crater                 NLF?
            Clausius E                      36.4S  45.5W    6.0 Crater                 NLF?
            Clausius F                      36.5S  38.1W   26.0 Crater                 NLF?
            Clausius G                      37.1S  41.0W    6.0 Crater                 NLF?
            Clausius H                      37.8S  39.6W    7.0 Crater                 NLF?
            Clausius J                      37.2S  42.7W    4.0 Crater                 NLF?
            Clavius                         58.8S  14.1W  245.0 Crater         VL1645  R1651
            Clavius C                       57.7S  14.2W   21.0 Crater                 NLF?
            Clavius D                       58.8S  12.4W   28.0 Crater                 NLF?
            Clavius E                       51.5S  12.6W   16.0 Crater                 NLF?
            Clavius F                       55.4S  21.9W    7.0 Crater                 NLF?
            Clavius G                       52.0S  13.9W   17.0 Crater                 NLF?
            Clavius H                       51.9S  15.8W   34.0 Crater                 NLF?
            Clavius J                       58.1S  18.1W   12.0 Crater                 NLF?
            Clavius K                       60.4S  19.8W   20.0 Crater                 NLF?
            Clavius L                       58.7S  21.2W   24.0 Crater                 NLF?
            Clavius M                       54.8S  11.9W   44.0 Crater                 NLF?
            Clavius N                       57.5S  16.5W   13.0 Crater                 NLF?
            Clavius O                       56.8S  16.4W    4.0 Crater                 NLF?
            Clavius P                       57.0S   7.7W   10.0 Crater                 NLF?
            Clavius R                       53.1S  15.4W    7.0 Crater                 NLF?
            Clavius T                       60.4S  14.9W    9.0 Crater                 NLF?
            Clavius W                       55.8S  16.0W    6.0 Crater                 NLF?
            Clavius X                       60.0S  17.6W    7.0 Crater                 NLF?
            Clavius Y                       57.8S  16.0W    7.0 Crater                 NLF?
            Cleomedes                       27.7N  56.0E  125.0 Crater         VL1645  R1651
            Cleomedes A                     28.9N  55.0E   12.0 Crater                 NLF?
            Cleomedes B                     27.2N  55.9E   11.0 Crater                 NLF?
            Cleomedes C                     25.7N  54.9E   14.0 Crater                 NLF?
            Cleomedes D                     29.3N  61.9E   25.0 Crater                 NLF?
            Cleomedes E                     28.6N  54.4E   21.0 Crater                 NLF?
            Cleomedes F                     22.6N  56.9E   12.0 Crater                 NLF?
            Cleomedes G                     24.0N  57.3E   20.0 Crater                 NLF?
            Cleomedes H                     22.4N  57.6E    6.0 Crater                 NLF?
            Cleomedes J                     26.9N  56.8E   10.0 Crater                 NLF?
            Cleomedes L                     23.8N  54.4E    7.0 Crater                 NLF?
            Cleomedes M                     24.2N  51.6E    6.0 Crater                 NLF?
            Cleomedes N                     24.8N  52.5E    6.0 Crater                 NLF?
            Cleomedes P                     24.8N  56.4E    9.0 Crater                 NLF?
            Cleomedes Q                     24.9N  56.9E    4.0 Crater                 NLF?
            Cleomedes R                     29.5N  60.2E   15.0 Crater                 NLF?
            Cleomedes S                     29.5N  59.0E    8.0 Crater                 NLF?
            Cleomedes T                     25.8N  57.7E   11.0 Crater                 NLF?
            Cleostratus                     60.4N  77.0W   62.0 Crater         R1651   R1651
            Cleostratus A                   62.7N  77.3W   35.0 Crater                 NLF?
            Cleostratus E                   60.9N  79.6W   21.0 Crater                 NLF?
            Cleostratus F                   61.5N  80.4W   50.0 Crater                 NLF?
            Cleostratus H                   61.2N  81.9W   13.0 Crater                 NLF?
            Cleostratus J                   61.3N  83.8W   20.0 Crater                 NLF?
            Cleostratus K                   62.0N  81.1W   17.0 Crater                 NLF?
            Cleostratus L                   62.2N  79.3W   11.0 Crater                 NLF?
            Cleostratus M                   61.5N  74.9W    9.0 Crater                 NLF?
            Cleostratus N                   60.6N  73.1W    4.0 Crater                 NLF?
            Cleostratus P                   59.6N  72.9W    7.0 Crater                 NLF?
            Cleostratus R                   58.9N  72.9W    6.0 Crater                 NLF?
            Clerke                          21.7N  29.8E    6.0 Crater                 IAU1973
            Coblentz                        37.9S 126.1E   33.0 Crater                 IAU1970
            Cochise                         20.2N  30.8E    1.0 Crater (A)             IAU1973
            Cockcroft                       31.3N 162.6W   93.0 Crater                 IAU1970
            Cockcroft N                     29.1N 163.7W   56.0 Crater                 AW82
            Collins                          1.3N  23.7E    2.0 Crater                 IAU1970
            Colombo                         15.1S  45.8E   76.0 Crater         H1647   M1834
            Colombo A                       14.1S  44.4E   42.0 Crater                 NLF?
            Colombo B                       16.4S  45.2E   16.0 Crater                 NLF?
            Colombo E                       15.8S  42.4E   17.0 Crater                 NLF?
            Colombo G                       13.9S  43.9E   10.0 Crater                 NLF?
            Colombo H                       17.4S  44.1E   14.0 Crater                 NLF?
            Colombo J                       14.3S  43.5E    7.0 Crater                 NLF?
            Colombo K                       15.8S  46.4E    5.0 Crater                 NLF?
            Colombo M                       14.6S  47.8E   17.0 Crater                 NLF?
            Colombo P                       15.1S  48.0E    5.0 Crater                 NLF?
            Colombo T                       18.9S  45.4E   10.0 Crater                 NLF?
            Compton                         55.3N 103.8E  162.0 Crater                 IAU1970
            Compton E                       55.4N 113.4E   19.0 Crater                 AW82
            Compton R                       52.6N  91.5E   37.0 Crater                 AW82
            Compton W                       58.6N  97.2E   16.0 Crater                 AW82
            Comrie                          23.3N 112.7W   59.0 Crater                 IAU1970
            Comrie K                        22.1N 112.3W   73.0 Crater                 AW82
            Comrie T                        23.1N 115.3W   43.0 Crater                 AW82
            Comrie V                        24.6N 115.9W   29.0 Crater                 AW82
            Comstock                        21.8N 121.5W   72.0 Crater                 IAU1970
            Comstock A                      24.8N 121.2W   21.0 Crater                 AW82
            Comstock P                      20.1N 122.7W   26.0 Crater                 AW82
            Condon                           1.9N  60.4E   34.0 Crater                 IAU1976
            Condorcet                       12.1N  69.6E   74.0 Crater         S1791   S1791
            Condorcet A                     11.5N  67.3E   14.0 Crater                 NLF?
            Condorcet D                      9.8N  68.5E   22.0 Crater                 NLF?
            Condorcet E                     11.3N  68.1E    6.0 Crater                 NLF?
            Condorcet F                      8.2N  73.1E   37.0 Crater                 NLF?
            Condorcet G                     10.7N  68.0E    7.0 Crater                 NLF?
            Condorcet H                     12.4N  65.0E   23.0 Crater                 NLF?
            Condorcet J                     13.1N  65.0E   16.0 Crater                 NLF?
            Condorcet L                     10.1N  73.7E   12.0 Crater                 NLF?
            Condorcet M                      9.0N  73.1E    9.0 Crater                 NLF?
            Condorcet N                      9.0N  72.9E    4.0 Crater                 NLF?
            Condorcet P                      8.7N  70.4E   46.0 Crater                 NLF?
            Condorcet Q                     11.4N  73.3E   31.0 Crater                 NLF?
            Condorcet R                     11.7N  74.8E   15.0 Crater                 NLF?
            Condorcet S                     10.6N  75.6E    9.0 Crater                 NLF?
            Condorcet T                     11.8N  65.8E   15.0 Crater                 NLF?
            Condorcet TA                    12.2N  65.7E   14.0 Crater                 NLF?
            Condorcet U                     10.0N  75.4E    9.0 Crater                 NLF?
            Condorcet W                     13.9N  66.9E   33.0 Crater                 NLF?
            Condorcet X                     10.1N  69.9E    8.0 Crater                 NLF?
            Condorcet Y                     12.8N  68.9E   13.0 Crater                 NLF?
            Cone                             3.7S  17.4W    0.0 Crater (A)             IAU1973
            Congreve                         0.2S 167.3W   57.0 Crater                 IAU1970
            Congreve G                       0.9S 163.7W   17.0 Crater                 AW82
            Congreve H                       1.2S 165.2W   37.0 Crater                 AW82
            Congreve L                       3.6S 166.3W   30.0 Crater                 AW82
            Congreve N                       3.4S 168.2W   31.0 Crater                 AW82
            Congreve Q                       1.4S 169.6W   59.0 Crater                 AW82
            Congreve U                       0.6S 170.7W   59.0 Crater                 AW82
            Conon                           21.6N   2.0E   21.0 Crater                 NLF
            Conon A                         19.7N   4.5E    7.0 Crater                 NLF?
            Conon W                         18.7N   3.0E    4.0 Crater                 NLF?
            Conon Y                         22.3N   1.9E    4.0 Crater                 NLF?
            Cook                            17.5S  48.9E   46.0 Crater         M1834   M1834
            Cook A                          17.8S  49.2E    6.0 Crater                 NLF?
            Cook B                          17.3S  51.7E    9.0 Crater                 NLF?
            Cook C                          18.2S  51.3E    5.0 Crater                 NLF?
            Cook D                          20.1S  53.4E    4.0 Crater                 NLF?
            Cook E                          18.4S  55.1E    5.0 Crater                 NLF?
            Cook F                          17.6S  55.4E    7.0 Crater                 NLF?
            Cook G                          18.9S  48.7E    9.0 Crater                 NLF?
            Cooper                          52.9N 175.6E   36.0 Crater                 IAU1970
            Cooper G                        52.6N 178.5E   20.0 Crater                 AW82
            Cooper K                        51.1N 178.1E   30.0 Crater                 AW82
            Copernicus                       9.7N  20.1W   93.0 Crater         VL1645  R1651
            Copernicus A                     9.5N  18.9W    3.0 Crater                 NLF?
            Copernicus B                     7.5N  22.4W    7.0 Crater                 NLF?
            Copernicus C                     7.1N  15.4W    6.0 Crater                 NLF?
            Copernicus D                    12.2N  24.7W    5.0 Crater                 NLF?
            Copernicus E                     6.4N  22.7W    4.0 Crater                 NLF?
            Copernicus F                     5.9N  22.2W    4.0 Crater                 NLF?
            Copernicus G                     5.9N  21.5W    4.0 Crater                 NLF?
            Copernicus H                     6.9N  18.3W    5.0 Crater                 NLF?
            Copernicus J                    10.1N  23.9W    6.0 Crater                 NLF?
            Copernicus L                    13.5N  17.0W    4.0 Crater                 NLF?
            Copernicus N                     6.9N  23.3W    7.0 Crater                 NLF?
            Copernicus P                    10.1N  16.0W    5.0 Crater                 NLF?
            Copernicus R                     8.1N  16.8W    3.0 Crater                 NLF?
            Cori                            50.6S 151.9W   65.0 Crater                 IAU1979
            Cori G                          50.9S 147.0W   20.0 Crater                 AW82
            Coriolis                         0.1N 171.8E   78.0 Crater                 IAU1970
            Coriolis C                       1.9N 173.3E   19.0 Crater                 AW82
            Coriolis G                       0.0N 174.7E   17.0 Crater                 AW82
            Coriolis H                       0.5S 174.2E   12.0 Crater                 AW82
            Coriolis L                       1.9S 172.7E   32.0 Crater                 AW82
            Coriolis M                       1.4S 171.7E   31.0 Crater                 AW82
            Coriolis S                       0.1N 169.7E   17.0 Crater                 AW82
            Coriolis W                       3.1N 168.0E   37.0 Crater                 AW82
            Coriolis Y                       3.6N 171.2E   31.0 Crater                 AW82
            Coriolis Z                       4.2N 171.5E   53.0 Crater                 AW82
            Couder                           4.8S  92.4W   21.0 Crater         N       IAU1985
            Coulomb                         54.7N 114.6W   89.0 Crater                 IAU1970
            Coulomb C                       57.4N 110.8W   34.0 Crater                 AW82
            Coulomb J                       53.1N 111.6W   35.0 Crater                 AW82
            Coulomb N                       50.6N 115.8W   32.0 Crater                 AW82
            Coulomb P                       50.5N 117.4W   38.0 Crater                 AW82
            Coulomb V                       55.6N 118.1W   36.0 Crater                 AW82
            Coulomb W                       56.5N 120.4W   34.0 Crater                 AW82
            Courtney                        25.1N  30.8W    1.0 Crater         X       IAU1976
            Cremona                         67.5N  90.6W   85.0 Crater         RLA1963 IAU1964
            Cremona A                       69.6N  91.0W   47.0 Crater                 RLA1963?
            Cremona B                       67.9N  92.4W   20.0 Crater                 RLA1963?
            Cremona C                       67.2N  92.2W   15.0 Crater                 RLA1963?
            Cremona L                       66.1N  90.0W   23.0 Crater                 RLA1963?
            Crescent                         2.9S  23.4W    1.0 Crater (A)             IAU1973
            Crile                           14.2N  46.0E    9.0 Crater                 IAU1976
            Crocco                          47.5S 150.2E   75.0 Crater                 IAU1970
            Crocco E                        46.8S 152.0E   17.0 Crater                 AW82
            Crocco G                        47.8S 152.3E   42.0 Crater                 AW82
            Crocco R                        48.3S 147.5E   57.0 Crater                 AW82
            Crommelin                       68.1S 146.9W   94.0 Crater                 IAU1970
            Crommelin C                     66.4S 144.8W   44.0 Crater                 AW82
            Crommelin W                     66.0S 152.7W   24.0 Crater                 AW82
            Crommelin X                     66.3S 150.0W   26.0 Crater                 AW82
            Crookes                         10.3S 164.5W   49.0 Crater                 IAU1970
            Crookes D                        9.6S 162.8W   41.0 Crater                 AW82
            Crookes P                       11.7S 165.8W   21.0 Crater                 AW82
            Crookes X                        6.6S 166.2W   24.0 Crater                 AW82
            Crozier                         13.5S  50.8E   22.0 Crater                 NLF
            Crozier B                       12.5S  52.4E    8.0 Crater                 NLF?
            Crozier D                       13.4S  51.7E   21.0 Crater                 NLF?
            Crozier E                       12.7S  52.0E    6.0 Crater                 NLF?
            Crozier F                       12.8S  51.0E    5.0 Crater                 NLF?
            Crozier G                       12.1S  50.1E    4.0 Crater                 NLF?
            Crozier H                       14.0S  49.4E   11.0 Crater                 NLF?
            Crozier L                       10.0S  51.4E    6.0 Crater                 NLF?
            Crozier M                        8.9S  53.4E    6.0 Crater                 NLF?
            Cr|:uger                        16.7S  66.8W   45.0 Crater         VL1645  R1651
            Cr|:uger A                      16.0S  62.7W   27.0 Crater                 NLF?
            Cr|:uger B                      17.2S  71.6W   13.0 Crater                 NLF?
            Cr|:uger C                      16.8S  61.9W   12.0 Crater                 NLF?
            Cr|:uger D                      15.3S  64.5W   12.0 Crater                 NLF?
            Cr|:uger E                      17.5S  65.2W   16.0 Crater                 NLF?
            Cr|:uger F                      14.2S  64.4W    9.0 Crater                 NLF?
            Cr|:uger G                      17.9S  68.0W    8.0 Crater                 NLF?
            Cr|:uger H                      18.0S  65.2W    7.0 Crater                 NLF?
            Ctesibius                        0.8N 118.7E   36.0 Crater                 IAU1976
            Curie                           22.9S  91.0E  151.0 Crater         BML1960 IAU1961
            Curie C                         21.1S  94.1E   47.0 Crater                 AW82
            Curie E                         22.4S  96.2E   43.0 Crater                 AW82
            Curie G                         23.6S  94.8E   53.0 Crater                 AW82
            Curie K                         23.7S  92.7E   12.0 Crater                 AW82
            Curie L                         26.3S  92.8E   21.0 Crater                 AW82
            Curie M                         28.4S  92.5E   34.0 Crater                 AW82
            Curie P                         28.4S  90.1E   26.0 Crater                 AW82
            Curie V                         22.0S  90.4E   21.0 Crater                 AW82
            Curie Z                         20.5S  92.2E   25.0 Crater                 AW82
            Curtis                          14.6N  56.6E    2.0 Crater                 IAU1973
            Curtius                         67.2S   4.4E   95.0 Crater         R1651   R1651
            Curtius A                       68.5S   2.7E   12.0 Crater                 NLF?
            Curtius B                       63.7S   4.7E   41.0 Crater         VL1645  NLF?
            Curtius C                       69.2S   4.4E   10.0 Crater                 NLF?
            Curtius D                       64.8S   8.1E   61.0 Crater                 NLF?
            Curtius E                       67.2S   8.2E   15.0 Crater                 NLF?
            Curtius F                       66.5S   2.7E    6.0 Crater                 NLF?
            Curtius G                       65.9S   3.1E    6.0 Crater                 NLF?
            Curtius H                       69.4S   8.2E   10.0 Crater                 NLF?
            Curtius K                       69.1S   9.8E    6.0 Crater                 NLF?
            Curtius L                       68.2S   9.4E    7.0 Crater                 NLF?
            Curtius M                       65.5S   8.6E    5.0 Crater                 NLF?
            Cusanus                         72.0N  70.8E   63.0 Crater         S1878   S1878
            Cusanus A                       70.6N  64.0E   16.0 Crater                 NLF?
            Cusanus B                       70.1N  64.5E   21.0 Crater                 NLF?
            Cusanus C                       70.4N  60.8E   25.0 Crater                 NLF?
            Cusanus E                       72.0N  72.3E   10.0 Crater                 NLF?
            Cusanus F                       70.7N  73.3E   10.0 Crater                 NLF?
            Cusanus G                       69.9N  76.9E   10.0 Crater                 NLF?
            Cusanus H                       69.4N  59.4E    8.0 Crater                 NLF?
            Cuvier                          50.3S   9.9E   75.0 Crater         R1651  M1834
            Cuvier A                        52.4S  12.0E   18.0 Crater                 NLF?
            Cuvier B                        51.7S  13.8E   17.0 Crater                 NLF?
            Cuvier C                        49.9S  11.7E    9.0 Crater                 NLF?
            Cuvier D                        51.3S   7.8E   17.0 Crater                 NLF?
            Cuvier E                        52.3S  12.9E   19.0 Crater                 NLF?
            Cuvier F                        52.2S  11.2E   16.0 Crater                 NLF?
            Cuvier G                        50.8S   7.5E    8.0 Crater                 NLF?
            Cuvier H                        48.6S   8.5E   10.0 Crater                 NLF?
            Cuvier J                        49.3S   8.8E    6.0 Crater                 NLF?
            Cuvier K                        52.2S  10.0E    8.0 Crater                 NLF?
            Cuvier L                        48.9S   9.8E   13.0 Crater                 NLF?
            Cuvier M                        53.3S  10.9E    6.0 Crater                 NLF?
            Cuvier N                        53.4S  12.1E    4.0 Crater                 NLF?
            Cuvier O                        51.6S  12.1E   10.0 Crater                 NLF?
            Cuvier P                        50.0S  12.7E   11.0 Crater                 NLF?
            Cuvier Q                        51.6S  10.6E   13.0 Crater                 NLF?
            Cuvier R                        51.0S  13.1E    7.0 Crater                 NLF?
            Cyrano                          20.5S 157.7E   80.0 Crater                 IAU1970
            Cyrano A                        18.1S 158.6E   26.0 Crater                 AW82
            Cyrano B                        18.3S 159.7E   23.0 Crater                 AW82
            Cyrano D                        19.6S 162.2E   24.0 Crater                 AW82
            Cyrano E                        20.1S 161.2E   21.0 Crater                 AW82
            Cyrano P                        21.6S 156.8E   19.0 Crater                 AW82
            Cyrillus                        13.2S  24.0E   98.0 Crater         VL1645  R1651
            Cyrillus A                      13.8S  23.1E   17.0 Crater                 NLF?
            Cyrillus C                      12.3S  21.5E   12.0 Crater                 NLF?
            Cyrillus E                      15.8S  25.3E   11.0 Crater                 NLF?
            Cyrillus F                      15.3S  25.5E   44.0 Crater                 NLF?
            Cyrillus G                      15.6S  26.6E    8.0 Crater                 NLF?
            Cysatus                         66.2S   6.1W   48.0 Crater         R1651   R1651
            Cysatus A                       64.2S   0.8W   14.0 Crater                 NLF?
            Cysatus B                       65.7S   1.8W    8.0 Crater                 NLF?
            Cysatus C                       63.8S   0.6E   27.0 Crater                 NLF?
            Cysatus D                       65.0S   6.0W    5.0 Crater                 NLF?
            Cysatus E                       66.7S   1.3W   48.0 Crater                 NLF?
            Cysatus F                       63.9S   3.5W    5.0 Crater                 NLF?
            Cysatus G                       65.8S   0.3W    6.0 Crater                 NLF?
            Cysatus H                       66.8S   0.0E    8.0 Crater                 NLF?
            Cysatus J                       63.0S   0.8E   10.0 Crater                 NLF?
            D. Brown                        42.0S 147.2W   16.0 Crater                 IAU2006?
            d'Alembert                      50.8N 163.9E  248.0 Crater         S1791   S1791
            d'Alembert E                    52.8N 168.2E   22.0 Crater                 AW82
            d'Alembert G                    50.9N 167.5E   18.0 Crater                 AW82
            d'Alembert J                    47.5N 170.4E   20.0 Crater                 AW82
            d'Alembert Z                    55.4N 165.6E   44.0 Crater                 AW82
            d'Arrest                         2.3N  14.7E   30.0 Crater         S1878   S1878
            d'Arrest A                       1.9N  13.7E    4.0 Crater                 NLF?
            d'Arrest B                       1.0N  13.6E    5.0 Crater                 NLF?
            d'Arrest M                       1.9N  13.6E   23.0 Crater                 NLF?
            d'Arrest R                       0.5N  15.6E   19.0 Crater                 NLF?
            d'Arsonval                      10.3S 124.6E   28.0 Crater                 IAU1976
            d'Arsonval A                     8.4S 125.0E   17.0 Crater                 AW82
            da Vinci                         9.1N  45.0E   37.0 Crater         PE1935  PE1935
            da Vinci A                       9.7N  44.2E   18.0 Crater                 NLF?
            Daedalus                         5.9S 179.4E   93.0 Crater                 IAU1970
            Daedalus B                       4.1S 179.8W   23.0 Crater                 AW82
            Daedalus C                       4.1S 178.9W   68.0 Crater                 AW82
            Daedalus G                       6.6S 177.4W   33.0 Crater                 AW82
            Daedalus K                       8.3S 178.5W   24.0 Crater                 AW82
            Daedalus M                       8.1S 179.5E   13.0 Crater                 AW82
            Daedalus R                       7.7S 175.2E   41.0 Crater                 AW82
            Daedalus S                       6.8S 172.9E   20.0 Crater                 AW82
            Daedalus U                       4.2S 174.9E   30.0 Crater                 AW82
            Daedalus W                       3.5S 177.5E   70.0 Crater                 AW82
            Dag                             18.7N   5.3E    0.5 Crater         X       IAU1976
            Daguerre                        11.9S  33.6E   46.0 Crater         S1878   S1878
            Daguerre K                      12.2S  35.8E    5.0 Crater                 NLF?
            Daguerre U                      15.1S  35.7E    4.0 Crater                 NLF?
            Daguerre X                      14.0S  34.5E    4.0 Crater                 NLF?
            Daguerre Y                      13.9S  35.4E    3.0 Crater                 NLF?
            Daguerre Z                      14.9S  34.7E    4.0 Crater                 NLF?
            Dale                             9.6S  82.9E   22.0 Crater                 IAU1976
            Dalton                          17.1N  84.3W   60.0 Crater         RLA1963 IAU1964
            Daly                             5.7N  59.6E   17.0 Crater                 IAU1973
            Damoiseau                        4.8S  61.1W   36.0 Crater         M1834   M1834
            Damoiseau A                      6.3S  62.4W   47.0 Crater                 NLF?
            Damoiseau B                      8.6S  61.6W   23.0 Crater                 NLF?
            Damoiseau BA                     8.3S  59.0W    9.0 Crater                 NLF?
            Damoiseau C                      9.1S  62.5W   15.0 Crater                 NLF?
            Damoiseau D                      6.4S  63.3W   17.0 Crater                 NLF?
            Damoiseau E                      5.2S  58.3W   14.0 Crater                 NLF?
            Damoiseau F                      7.9S  62.1W   11.0 Crater                 NLF?
            Damoiseau G                      2.5S  55.6W    4.0 Crater                 NLF?
            Damoiseau H                      3.8S  59.8W   45.0 Crater                 NLF?
            Damoiseau J                      4.1S  62.0W    7.0 Crater                 NLF?
            Damoiseau K                      4.6S  60.4W   23.0 Crater                 NLF?
            Damoiseau L                      4.5S  59.3W   14.0 Crater                 NLF?
            Damoiseau M                      5.1S  61.3W   54.0 Crater                 NLF?
            Daniell                         35.3N  31.1E   29.0 Crater                 NLF
            Daniell D                       37.0N  25.8E    6.0 Crater                 NLF?
            Daniell W                       35.9N  31.5E    3.0 Crater                 NLF?
            Daniell X                       36.6N  31.8E    5.0 Crater                 NLF?
            Danjon                          11.4S 124.0E   71.0 Crater                 IAU1970
            Danjon J                        12.8S 125.6E   23.0 Crater                 AW82
            Danjon K                        13.8S 125.1E   17.0 Crater                 AW82
            Danjon M                        13.9S 124.1E   12.0 Crater                 AW82
            Danjon X                        10.0S 122.8E   65.0 Crater                 AW82
            Dante                           25.5N 180.0E   54.0 Crater                 IAU1970
            Dante C                         28.3N 177.1W   54.0 Crater                 AW82
            Dante E                         26.7N 177.0W   43.0 Crater                 AW82
            Dante G                         24.9N 178.6W   24.0 Crater                 AW82
            Dante P                         23.6N 179.4E   27.0 Crater                 AW82
            Dante S                         24.9N 177.3E   17.0 Crater                 AW82
            Dante T                         25.8N 176.6E   20.0 Crater                 AW82
            Dante Y                         27.1N 179.5E   27.0 Crater                 AW82
            Darney                          14.5S  23.5W   15.0 Crater         VL1645  L1935
            Darney B                        14.8S  26.4W    4.0 Crater                 NLF?
            Darney C                        14.1S  26.0W   13.0 Crater                 NLF?
            Darney D                        14.5S  27.0W    6.0 Crater                 NLF?
            Darney E                        12.4S  25.4W    4.0 Crater                 NLF?
            Darney F                        13.3S  26.4W    4.0 Crater                 NLF?
            Darney J                        14.3S  21.4W    7.0 Crater                 NLF?
            Darwin                          20.2S  69.5W  120.0 Crater         S1878   S1878
            Darwin A                        21.8S  73.0W   24.0 Crater                 NLF?
            Darwin B                        19.9S  72.2W   56.0 Crater                 NLF?
            Darwin C                        20.5S  71.0W   16.0 Crater                 NLF?
            Darwin F                        21.0S  71.0W   18.0 Crater                 NLF?
            Darwin G                        21.5S  70.7W   17.0 Crater                 NLF?
            Darwin H                        21.0S  68.8W   30.0 Crater                 NLF?
            Das                             26.6S 136.8W   38.0 Crater                 IAU1970
            Das G                           26.9S 135.2W   32.0 Crater                 AW82
            Daubr|%ee                       15.7N  14.7E   14.0 Crater                 IAU1973
            Davisson                        37.5S 174.6W   87.0 Crater                 IAU1970
            Davy                            11.8S   8.1W   34.0 Crater         M1834   M1834
            Davy A                          12.2S   7.7W   15.0 Crater                 NLF?
            Davy B                          10.8S   8.9W    7.0 Crater                 NLF?
            Davy C                          11.2S   7.0W    3.0 Crater                 NLF?
            Davy G                          10.4S   5.1W   16.0 Crater                 NLF?
            Davy K                          10.2S   9.5W    3.0 Crater                 NLF?
            Davy U                          12.9S   7.1W    3.0 Crater                 NLF?
            Davy Y                          11.0S   7.1W   70.0 Crater                 NLF?
            Dawes                           17.2N  26.4E   18.0 Crater         R1651   NLF
            Dawson                          67.4S 134.7W   45.0 Crater                 IAU1970
            Dawson D                        66.6S 131.7W   39.0 Crater                 AW82
            Dawson V                        66.6S 137.0W   58.0 Crater                 AW82
            De Forest                       77.3S 162.1W   57.0 Crater                 IAU1970
            De Forest N                     79.5S 164.7W   41.0 Crater                 AW82
            De Forest P                     80.0S 176.0W   18.0 Crater                 AW82
            de Gasparis                     25.9S  50.7W   30.0 Crater         S1878   S1878
            de Gasparis A                   26.7S  51.3W   37.0 Crater                 NLF?
            de Gasparis B                   27.0S  52.5W   12.0 Crater                 NLF?
            de Gasparis C                   26.3S  51.7W    6.0 Crater                 NLF?
            de Gasparis D                   25.7S  50.1W    4.0 Crater                 NLF?
            de Gasparis E                   26.4S  49.4W    7.0 Crater                 NLF?
            de Gasparis F                   26.3S  49.3W    8.0 Crater                 NLF?
            de Gasparis G                   27.0S  49.3W    6.0 Crater                 NLF?
            de Gerlache                     88.5S  87.1W   32.4 Crater         N       IAU2000
            de la Rue                       59.1N  52.3E  134.0 Crater                 NLF?
            de la Rue D                     56.8N  46.2E   17.0 Crater                 NLF?
            de la Rue E                     56.8N  49.7E   32.0 Crater                 NLF?
            de la Rue J                     59.0N  52.8E   14.0 Crater                 NLF?
            de la Rue P                     60.5N  61.4E   10.0 Crater                 NLF?
            de la Rue Q                     61.5N  60.5E   10.0 Crater                 NLF?
            de la Rue R                     62.1N  61.1E    9.0 Crater                 NLF?
            de la Rue S                     62.9N  61.6E   12.0 Crater                 NLF?
            de la Rue W                     55.7N  46.9E   18.0 Crater                 NLF?
            de Moraes                       49.5N 143.2E   53.0 Crater                 IAU1979
            de Moraes S                     48.9N 140.7E   45.0 Crater                 AW82
            de Moraes T                     49.3N 139.5E   46.0 Crater                 AW82
            de Morgan                        3.3N  14.9E   10.0 Crater                 NLF
            de Roy                          55.3S  99.1W   43.0 Crater                 IAU1970
            de Roy N                        59.7S 103.1W   26.0 Crater                 AW82
            de Roy P                        58.4S 102.4W   35.0 Crater                 AW82
            de Roy Q                        58.1S 103.6W   22.0 Crater                 AW82
            de Sitter                       80.1N  39.6E   64.0 Crater         RLA1963 IAU1964
            de Sitter A                     80.2N  26.6E   36.0 Crater                 RLA1963?
            de Sitter F                     80.2N  51.0E   22.0 Crater                 RLA1963?
            de Sitter G                     78.9N  42.7E    9.0 Crater                 RLA1963?
            de Sitter L                     78.8N  34.5E   69.0 Crater                 RLA1963?
            de Sitter M                     81.3N  38.5E   81.0 Crater                 RLA1963?
            de Sitter U                     77.8N  46.5E   37.0 Crater                 RLA1963?
            de Sitter V                     79.1N  56.9E   17.0 Crater                 RLA1963?
            de Sitter W                     79.4N  54.1E   40.0 Crater                 RLA1963?
            de Sitter X                     80.3N  55.4E    9.0 Crater                 RLA1963?
            de Vico                         19.7S  60.2W   20.0 Crater         N1876   N1876
            de Vico A                       18.8S  63.5W   32.0 Crater                 NLF?
            de Vico AA                      18.8S  63.1W   12.0 Crater                 NLF?
            de Vico B                       17.8S  58.7W    9.0 Crater                 NLF?
            de Vico C                       20.6S  62.3W   12.0 Crater                 NLF?
            de Vico D                       21.1S  61.9W   12.0 Crater                 NLF?
            de Vico E                       21.1S  61.3W   13.0 Crater                 NLF?
            de Vico F                       19.1S  62.6W   12.0 Crater                 NLF?
            de Vico G                       19.0S  58.9W    8.0 Crater                 NLF?
            de Vico H                       19.9S  59.1W    8.0 Crater                 NLF?
            de Vico K                       20.1S  58.3W    8.0 Crater                 NLF?
            de Vico L                       19.9S  57.7W    5.0 Crater                 NLF?
            de Vico M                       21.1S  59.4W    8.0 Crater                 NLF?
            de Vico N                       19.8S  61.9W    6.0 Crater                 NLF?
            de Vico P                       20.4S  60.8W   30.0 Crater                 NLF?
            de Vico R                       19.4S  61.9W   13.0 Crater                 NLF?
            de Vico S                       19.5S  63.4W   10.0 Crater                 NLF?
            de Vico T                       18.7S  61.8W   41.0 Crater                 NLF?
            de Vico X                       20.5S  60.1W    6.0 Crater                 NLF?
            de Vico Y                       20.4S  60.3W    6.0 Crater                 NLF?
            de Vries                        19.9S 176.7W   59.0 Crater                 IAU1970
            de Vries D                      18.9S 174.3W   19.0 Crater                 AW82
            de Vries N                      21.5S 177.3W   30.0 Crater                 AW82
            de Vries R                      20.7S 178.4W   14.0 Crater                 AW82
            Debes                           29.5N  51.7E   30.0 Crater         M1935   M1935
            Debes A                         28.8N  51.5E   33.0 Crater                 NLF?
            Debes B                         29.0N  50.6E   19.0 Crater                 NLF?
            Debus                           10.5S  99.6E   20.0 Crater         N       IAU2000
            Debye                           49.6N 176.2W  142.0 Crater                 IAU1970
            Debye E                         50.4N 171.0W   41.0 Crater                 AW82
            Debye J                         48.4N 172.6W   30.0 Crater                 AW82
            Debye Q                         47.5N 179.2W   26.0 Crater                 AW82
            Dechen                          46.1N  68.2W   12.0 Crater         S1878   S1878
            Dechen A                        46.0N  65.7W    5.0 Crater                 NLF?
            Dechen B                        44.2N  64.3W    6.0 Crater                 NLF?
            Dechen C                        45.9N  69.9W    6.0 Crater                 NLF?
            Dechen D                        46.2N  60.5W    5.0 Crater                 NLF?
            Delambre                         1.9S  17.5E   51.0 Crater         VL1645  L1824
            Delambre B                       1.7S  19.6E   10.0 Crater                 NLF?
            Delambre D                       1.1S  17.6E    5.0 Crater                 NLF?
            Delambre F                       1.0S  19.3E    5.0 Crater                 NLF?
            Delambre H                       1.0S  16.4E   16.0 Crater                 NLF?
            Delambre J                       0.3S  16.8E   12.0 Crater                 NLF?
            Delaunay                        22.2S   2.5E   46.0 Crater                 NLF
            Delaunay A                      21.9S   2.0E    6.0 Crater                 NLF?
            Delia                           10.9S   6.1W    2.0 Crater         X       IAU1976
            Delisle                         29.9N  34.6W   25.0 Crater         VL1645  S1791
            Delisle K                       29.0N  38.4W    3.0 Crater                 NLF?
            Dellinger                        6.8S 140.6E   81.0 Crater                 IAU1970
            Dellinger B                      5.5S 141.1E   53.0 Crater                 AW82
            Dellinger U                      6.3S 136.8E   16.0 Crater                 AW82
            Delmotte                        27.1N  60.2E   32.0 Crater         L1935   L1935
            Delporte                        16.0S 121.6E   45.0 Crater                 IAU1970
            Deluc                           55.0S   2.8W   46.0 Crater         M1834   M1834
            Deluc A                         54.1S   0.4W   56.0 Crater                 NLF?
            Deluc B                         52.0S   0.5E   38.0 Crater                 NLF?
            Deluc C                         51.4S   0.9E   28.0 Crater                 NLF?
            Deluc D                         56.4S   2.4W   27.0 Crater                 NLF?
            Deluc E                         60.3S   4.3W   12.0 Crater                 NLF?
            Deluc F                         60.0S   3.1W   38.0 Crater                 NLF?
            Deluc G                         61.6S   0.7E   27.0 Crater                 NLF?
            Deluc H                         54.2S   2.1W   26.0 Crater                 NLF?
            Deluc J                         53.3S   4.1W   33.0 Crater                 NLF?
            Deluc L                         60.8S   6.2W    8.0 Crater                 NLF?
            Deluc M                         54.9S   6.2W   19.0 Crater                 NLF?
            Deluc N                         60.6S   0.5E   10.0 Crater                 NLF?
            Deluc O                         62.7S   4.4W    7.0 Crater                 NLF?
            Deluc P                         58.9S   4.8W    7.0 Crater                 NLF?
            Deluc Q                         59.0S   3.5W    5.0 Crater                 NLF?
            Deluc R                         55.4S   0.6E   22.0 Crater                 NLF?
            Deluc S                         61.9S   0.2E    6.0 Crater                 NLF?
            Deluc T                         55.8S   3.1W   10.0 Crater                 NLF?
            Deluc U                         59.0S   2.9W    5.0 Crater                 NLF?
            Deluc V                         61.8S   1.7E    9.0 Crater                 NLF?
            Deluc W                         61.6S   1.8W    6.0 Crater                 NLF?
            Dembowski                        2.9N   7.2E   26.0 Crater         K1898   K1898
            Dembowski A                      3.0N   6.5E    6.0 Crater                 NLF?
            Dembowski B                      2.5N   6.2E    7.0 Crater                 NLF?
            Dembowski C                      2.1N   7.4E   16.0 Crater                 NLF?
            Democritus                      62.3N  35.0E   39.0 Crater         VL1645  R1651
            Democritus A                    61.6N  32.4E   11.0 Crater                 NLF?
            Democritus B                    60.1N  28.6E   12.0 Crater                 NLF?
            Democritus D                    62.9N  31.2E    8.0 Crater                 NLF?
            Democritus K                    63.1N  40.7E    7.0 Crater                 NLF?
            Democritus L                    63.4N  39.7E   18.0 Crater                 NLF?
            Democritus M                    63.6N  37.1E    5.0 Crater                 NLF?
            Democritus N                    63.6N  34.3E   16.0 Crater                 NLF?
            Demonax                         77.9S  60.8E  128.0 Crater         S1878   S1878
            Demonax A                       79.1S  64.3E   16.0 Crater                 NLF?
            Demonax B                       81.5S  73.9E   19.0 Crater                 NLF?
            Demonax C                       80.1S  54.9E   10.0 Crater                 NLF?
            Demonax E                       78.3S  43.4E   40.0 Crater                 NLF?
            Denning                         16.4S 142.6E   44.0 Crater                 IAU1970
            Denning B                       15.2S 143.5E   32.0 Crater                 AW82
            Denning C                       14.5S 145.3E   17.0 Crater                 AW82
            Denning D                       16.1S 144.1E   14.0 Crater                 AW82
            Denning L                       18.8S 143.2E   21.0 Crater                 AW82
            Denning R                       17.2S 141.2E   72.0 Crater                 AW82
            Denning U                       16.0S 138.6E   30.0 Crater                 AW82
            Denning V                       15.5S 139.7E   26.0 Crater                 AW82
            Denning Y                       14.0S 142.3E   52.0 Crater                 AW82
            Denning Z                       12.9S 142.5E   14.0 Crater                 AW82
            Desargues                       70.2N  73.3W   85.0 Crater         RLA1963 IAU1964
            Desargues A                     71.4N  75.3W   30.0 Crater                 RLA1963?
            Desargues B                     70.7N  65.0W   50.0 Crater                 RLA1963?
            Desargues C                     69.7N  78.4W   12.0 Crater                 RLA1963?
            Desargues D                     69.3N  69.6W   11.0 Crater                 RLA1963?
            Desargues E                     70.2N  67.4W   31.0 Crater                 RLA1963?
            Desargues K                     68.5N  67.2W   10.0 Crater                 RLA1963?
            Desargues L                     69.6N  82.2W   13.0 Crater                 RLA1963?
            Desargues M                     68.4N  73.9W   30.0 Crater                 RLA1963?
            Descartes                       11.7S  15.7E   48.0 Crater         M1834   M1834
            Descartes A                     12.1S  15.2E   16.0 Crater                 NLF?
            Descartes C                     11.0S  16.3E    4.0 Crater                 NLF?
            Deseilligny                     21.1N  20.6E    6.0 Crater         L1935   L1935
            Deslandres                      33.1S   4.8W  256.0 Crater                 IAU1948
            Deutsch                         24.1N 110.5E   66.0 Crater                 IAU1970
            Deutsch F                       24.1N 112.0E   31.0 Crater                 AW82
            Deutsch L                       22.4N 110.8E   36.0 Crater                 AW82
            Dewar                            2.7S 165.5E   50.0 Crater                 IAU1970
            Dewar E                          2.3S 167.8E   15.0 Crater                 AW82
            Dewar F                          2.8S 167.5E   14.0 Crater                 AW82
            Dewar S                          3.1S 163.9E   23.0 Crater                 AW82
            Diana                           14.3N  35.7E    2.0 Crater         X       IAU1979
            Diderot                         20.4S 121.5E   20.0 Crater                 IAU1979
            Dionysius                        2.8N  17.3E   18.0 Crater         VL1645  NLF
            Dionysius A                      1.7N  17.6E    3.0 Crater                 NLF?
            Dionysius B                      3.0N  15.8E    4.0 Crater                 NLF?
            Diophantus                      27.6N  34.3W   17.0 Crater         VL1645  M1834
            Diophantus B                    29.1N  32.5W    6.0 Crater                 NLF?
            Diophantus C                    27.3N  34.7W    5.0 Crater                 NLF?
            Diophantus D                    26.9N  36.3W    4.0 Crater                 NLF?
            Dirichlet                       11.1N 151.4W   47.0 Crater                 IAU1970
            Dirichlet E                     12.2N 147.8W   26.0 Crater                 AW82
            Dobrovol'skiy                   12.8S 129.7E   38.0 Crater                 IAU1973
            Dobrovol'skiy D                 12.2S 130.6E   49.0 Crater                 AW82
            Dobrovol'skiy M                 14.6S 129.6E   31.0 Crater                 AW82
            Dobrovol'skiy R                 14.0S 127.7E   24.0 Crater                 AW82
            Doerfel                         69.1S 107.9W   68.0 Crater         S1791   S1791
            Doerfel R                       71.3S 119.4W   32.0 Crater                 AW82
            Doerfel S                       69.9S 119.6W   32.0 Crater                 AW82
            Doerfel U                       68.8S 117.2W   34.0 Crater                 AW82
            Doerfel Y                       67.8S 108.8W   68.0 Crater                 AW82
            Dollond                         10.4S  14.4E   11.0 Crater         L1824   L1824
            Dollond B                        7.7S  13.8E   37.0 Crater                 NLF?
            Dollond D                        8.2S  12.5E    9.0 Crater                 NLF?
            Dollond E                       10.2S  15.7E    6.0 Crater                 NLF?
            Dollond L                        8.7S  12.5E    5.0 Crater                 NLF?
            Dollond M                       10.1S  16.9E    6.0 Crater                 NLF?
            Dollond T                        9.4S  15.0E    3.0 Crater                 NLF?
            Dollond U                        7.3S  16.0E    3.0 Crater                 NLF?
            Dollond V                        7.9S  15.5E    6.0 Crater                 NLF?
            Dollond W                        6.7S  14.6E   11.0 Crater                 NLF?
            Dollond Y                        8.4S  13.2E   14.0 Crater                 NLF?
            Donati                          20.7S   5.2E   36.0 Crater                 NLF
            Donati A                        19.6S   4.5E    9.0 Crater                 NLF?
            Donati B                        20.4S   5.7E   11.0 Crater                 NLF?
            Donati C                        19.9S   3.4E    8.0 Crater                 NLF?
            Donati D                        22.1S   5.8E    5.0 Crater                 NLF?
            Donati K                        21.1S   6.8E   13.0 Crater                 NLF?
            Donna                            7.2N  38.3E    2.0 Crater         X       IAU1979
            Donner                          31.4S  98.0E   58.0 Crater                 IAU1970
            Donner N                        33.2S  97.1E   19.0 Crater                 AW82
            Donner P                        33.5S  96.3E   39.0 Crater                 AW82
            Donner Q                        34.3S  95.6E   15.0 Crater                 AW82
            Donner R                        34.4S  92.3E   15.0 Crater                 AW82
            Donner S                        32.1S  92.9E   23.0 Crater                 AW82
            Donner T                        31.1S  94.8E   46.0 Crater                 AW82
            Donner V                        30.5S  95.7E   19.0 Crater                 AW82
            Donner Z                        29.7S  97.8E   13.0 Crater                 AW82
            Doppelmayer                     28.5S  41.4W   63.0 Crater         S1791   S1791
            Doppelmayer A                   29.8S  43.1W   10.0 Crater                 NLF?
            Doppelmayer B                   30.5S  45.4W   11.0 Crater                 NLF?
            Doppelmayer C                   30.3S  44.1W    7.0 Crater                 NLF?
            Doppelmayer D                   31.8S  45.8W    9.0 Crater                 NLF?
            Doppelmayer G                   28.9S  44.9W   15.0 Crater                 NLF?
            Doppelmayer H                   28.8S  43.2W   10.0 Crater                 NLF?
            Doppelmayer J                   24.5S  41.1W    6.0 Crater                 NLF?
            Doppelmayer K                   24.0S  40.7W    5.0 Crater                 NLF?
            Doppelmayer L                   23.6S  40.5W    4.0 Crater                 NLF?
            Doppelmayer M                   29.5S  43.9W   15.0 Crater                 NLF?
            Doppelmayer N                   29.2S  44.6W    5.0 Crater                 NLF?
            Doppelmayer P                   29.1S  42.7W    8.0 Crater                 NLF?
            Doppelmayer R                   29.2S  43.2W    4.0 Crater                 NLF?
            Doppelmayer S                   28.1S  43.6W    4.0 Crater                 NLF?
            Doppelmayer T                   25.9S  43.2W    3.0 Crater                 NLF?
            Doppelmayer V                   29.8S  45.6W    8.0 Crater                 NLF?
            Doppelmayer W                   33.6S  45.6W    8.0 Crater                 NLF?
            Doppelmayer Y                   33.1S  46.1W   10.0 Crater                 NLF?
            Doppelmayer Z                   33.0S  46.4W   10.0 Crater                 NLF?
            Doppler                         12.6S 159.6W  110.0 Crater                 IAU1970
            Doppler B                       11.8S 159.4W   37.0 Crater                 AW82
            Doppler M                       15.4S 160.0W   25.0 Crater                 AW82
            Doppler N                       16.9S 160.5W   17.0 Crater                 AW82
            Doppler W                       11.0S 161.7W   15.0 Crater                 AW82
            Doppler X                       10.3S 161.3W   18.0 Crater                 AW82
            Doublet                          3.7S  17.5W    0.0 Crater (A)             IAU1973
            Douglass                        35.9N 122.4W   49.0 Crater                 IAU1970
            Douglass C                      36.7N 121.0W   28.0 Crater                 AW82
            Douglass X                      38.4N 123.8W   23.0 Crater                 AW82
            Dove                            46.7S  31.5E   30.0 Crater         S1878   S1878
            Dove A                          46.9S  33.5E   13.0 Crater                 NLF?
            Dove B                          47.1S  33.1E   19.0 Crater                 NLF?
            Dove C                          47.0S  30.8E   19.0 Crater                 NLF?
            Dove Z                          44.5S  29.2E    8.0 Crater                 NLF?
            Draper                          17.6N  21.7W    8.0 Crater         K1898   K1898
            Draper A                        17.9N  23.4W    4.0 Crater                 NLF?
            Draper C                        17.1N  21.5W    8.0 Crater                 NLF?
            Drebbel                         40.9S  49.0W   30.0 Crater         M1834   M1834
            Drebbel A                       38.9S  51.0W    7.0 Crater                 NLF?
            Drebbel B                       37.8S  47.3W   18.0 Crater                 NLF?
            Drebbel C                       40.4S  42.9W   30.0 Crater                 NLF?
            Drebbel D                       37.9S  49.3W   10.0 Crater                 NLF?
            Drebbel E                       38.1S  51.3W   65.0 Crater         VL1645  NLF?
            Drebbel F                       42.7S  44.6W   15.0 Crater                 NLF?
            Drebbel G                       43.9S  45.2W   17.0 Crater                 NLF?
            Drebbel H                       41.7S  45.3W   10.0 Crater                 NLF?
            Drebbel J                       40.6S  52.3W   13.0 Crater                 NLF?
            Drebbel K                       40.0S  49.5W   37.0 Crater                 NLF?
            Drebbel L                       40.3S  50.8W    9.0 Crater                 NLF?
            Drebbel M                       41.2S  41.4W    8.0 Crater                 NLF?
            Drebbel N                       41.3S  52.4W    9.0 Crater                 NLF?
            Drebbel P                       39.7S  51.8W    4.0 Crater                 NLF?
            Dreyer                          10.0N  96.9E   61.0 Crater                 IAU1970
            Dreyer C                        11.2N  98.2E   37.0 Crater                 AW82
            Dreyer D                        10.8N  99.8E   27.0 Crater                 AW82
            Dreyer J                         8.8N  98.2E   29.0 Crater                 AW82
            Dreyer K                         9.0N  97.4E   23.0 Crater                 AW82
            Dreyer R                         8.5N  94.0E   18.0 Crater                 AW82
            Dreyer W                        11.8N  95.7E   30.0 Crater                 AW82
            Drude                           38.5S  91.8W   24.0 Crater                 IAU1970
            Dryden                          33.0S 155.2W   51.0 Crater                 IAU1970
            Dryden S                        33.8S 158.8W   30.0 Crater                 AW82
            Dryden T                        32.8S 158.6W   35.0 Crater                 AW82
            Dryden W                        31.0S 158.5W   30.0 Crater                 AW82
            Drygalski                       79.3S  84.9W  149.0 Crater         F1936   F1936
            Drygalski P                     81.0S  99.9W   30.0 Crater                 AW82
            Drygalski V                     78.5S  93.4W   21.0 Crater                 AW82
            Dubyago                          4.4N  70.0E   51.0 Crater         RLA1963 IAU1964
            Dubyago B                        2.8N  70.2E   36.0 Crater                 NLF?
            Dubyago D                        1.4N  71.2E   14.0 Crater                 NLF?
            Dubyago E                        1.3N  69.0E   12.0 Crater                 NLF?
            Dubyago F                        1.8N  69.4E    9.0 Crater                 NLF?
            Dubyago G                        1.8N  69.0E    9.0 Crater                 NLF?
            Dubyago H                        2.3N  69.2E    8.0 Crater                 NLF?
            Dubyago J                        2.9N  69.6E   11.0 Crater                 NLF?
            Dubyago K                        1.5N  68.2E    9.0 Crater                 NLF?
            Dubyago L                        1.9N  68.1E    7.0 Crater                 NLF?
            Dubyago M                        2.5N  68.1E   12.0 Crater                 NLF?
            Dubyago N                        1.4N  67.0E    7.0 Crater                 NLF?
            Dubyago R                        2.5N  66.3E    8.0 Crater                 NLF?
            Dubyago T                        4.8N  72.3E    9.0 Crater                 NLF?
            Dubyago V                        5.9N  70.0E   12.0 Crater                 NLF?
            Dubyago W                        6.5N  69.9E    9.0 Crater                 NLF?
            Dubyago X                        6.5N  73.0E    8.0 Crater                 NLF?
            Dubyago Y                        4.2N  68.2E    7.0 Crater                 NLF?
            Dubyago Z                        3.8N  70.9E    9.0 Crater                 NLF?
            Dufay                            5.5N 169.5E   39.0 Crater                 IAU1970
            Dufay A                          9.5N 170.5E   15.0 Crater                 AW82
            Dufay B                          8.5N 171.0E   20.0 Crater                 AW82
            Dufay D                          6.3N 170.5E   32.0 Crater                 AW82
            Dufay X                          7.2N 168.5E   42.0 Crater                 AW82
            Dufay Y                          8.3N 168.4E   16.0 Crater                 AW82
            Dugan                           64.2N 103.3E   50.0 Crater                 IAU1970
            Dugan J                         61.6N 108.0E   13.0 Crater                 AW82
            Dugan X                         67.8N  98.5E   14.0 Crater                 AW82
            Dune                            26.0N   3.7E    0.0 Crater (A)             IAU1973
            Dun|%er                         44.8N 179.5E   62.0 Crater                 IAU1970
            Dun|%er A                       47.7N 179.7E   38.0 Crater                 AW82
            Dunthorne                       30.1S  31.6W   15.0 Crater         K1898   K1898
            Dunthorne A                     28.8S  32.6W    6.0 Crater                 NLF?
            Dunthorne B                     31.4S  31.6W    7.0 Crater                 NLF?
            Dunthorne C                     29.4S  32.5W    7.0 Crater                 NLF?
            Dunthorne D                     30.0S  34.0W    6.0 Crater                 NLF?
            Dyson                           61.3N 121.2W   63.0 Crater                 IAU1970
            Dyson B                         63.6N 117.6W   45.0 Crater                 AW82
            Dyson H                         59.5N 113.7W   21.0 Crater                 AW82
            Dyson M                         58.4N 120.9W   34.0 Crater                 AW82
            Dyson Q                         59.8N 125.7W   89.0 Crater                 AW82
            Dyson X                         62.6N 122.5W   28.0 Crater                 AW82
            Dziewulski                      21.2N  98.9E   63.0 Crater                 IAU1970
            Dziewulski Q                    20.5N  98.2E   32.0 Crater                 AW82
            Earthlight                      26.1N   3.7E    0.0 Crater (A)             IAU1973
            Eckert                          17.3N  58.3E    2.0 Crater                 IAU1973
            Eddington                       21.3N  72.2W  118.0 Crater         RLA1963 IAU1964
            Eddington P                     21.0N  71.0W   12.0 Crater                 NLF?
            Edison                          25.0N  99.1E   62.0 Crater         BML1960 IAU1961
            Edison T                        24.7N  97.1E   48.0 Crater                 AW82
            Edith                           25.8S 102.3E    8.0 Crater         X       IAU1976
            Egede                           48.7N  10.6E   37.0 Crater         M1834   M1834
            Egede A                         51.6N  10.5E   13.0 Crater         H1647   NLF?
            Egede B                         50.5N   8.9E    8.0 Crater                 NLF?
            Egede C                         50.1N  13.0E    5.0 Crater                 NLF?
            Egede E                         49.6N  10.4E    4.0 Crater                 NLF?
            Egede F                         51.9N  12.5E    4.0 Crater                 NLF?
            Egede G                         51.9N   6.9E    7.0 Crater                 NLF?
            Egede M                         49.5N  12.4E    4.0 Crater                 NLF?
            Egede N                         49.7N  11.1E    4.0 Crater                 NLF?
            Egede P                         47.8N  10.5E    4.0 Crater                 NLF?
            Ehrlich                         40.9N 172.4W   30.0 Crater                 IAU1970
            Ehrlich J                       40.2N 170.7W   25.0 Crater                 AW82
            Ehrlich N                       39.0N 173.1W   19.0 Crater                 AW82
            Ehrlich W                       42.7N 174.0W   26.0 Crater                 AW82
            Ehrlich Z                       42.2N 172.4W   28.0 Crater                 AW82
            Eichstadt                       22.6S  78.3W   49.0 Crater         R1651   R1651
            Eichstadt C                     21.7S  76.7W   15.0 Crater                 NLF?
            Eichstadt D                     23.5S  76.0W    7.0 Crater                 NLF?
            Eichstadt E                     23.9S  78.3W   18.0 Crater                 NLF?
            Eichstadt G                     22.4S  80.7W   11.0 Crater                 NLF?
            Eichstadt H                     19.0S  79.9W   11.0 Crater                 NLF?
            Eichstadt K                     18.2S  83.2W   13.0 Crater                 NLF?
            Eijkman                         63.1S 141.5W   54.0 Crater                 IAU1970
            Eijkman D                       62.3S 136.9W   25.0 Crater                 AW82
            Eimmart                         24.0N  64.8E   46.0 Crater         S1791   S1791
            Eimmart A                       24.0N  65.7E    7.0 Crater                 NLF?
            Eimmart B                       21.4N  66.5E   11.0 Crater                 NLF?
            Eimmart C                       22.4N  61.2E   24.0 Crater                 NLF?
            Eimmart D                       23.0N  69.1E   11.0 Crater                 NLF?
            Eimmart F                       23.3N  61.9E    8.0 Crater                 NLF?
            Eimmart G                       25.5N  64.8E   14.0 Crater                 NLF?
            Eimmart H                       22.1N  64.4E   16.0 Crater                 NLF?
            Eimmart K                       20.2N  67.6E   13.0 Crater                 NLF?
            Einstein                        16.3N  88.7W  198.0 Crater         RLA1963 IAU1964
            Einstein A                      16.7N  88.2W   51.0 Crater                 AW82
            Einstein R                      13.9N  91.8W   20.0 Crater                 AW82
            Einstein S                      15.1N  91.5W   20.0 Crater                 AW82
            Einthoven                        4.9S 109.6E   69.0 Crater                 IAU1970
            Einthoven G                      5.3S 111.8E   34.0 Crater                 AW82
            Einthoven K                      7.9S 111.2E   21.0 Crater                 AW82
            Einthoven L                      8.0S 110.7E   16.0 Crater                 AW82
            Einthoven M                      7.5S 109.6E   52.0 Crater                 AW82
            Einthoven P                      6.8S 108.5E   18.0 Crater                 AW82
            Einthoven R                      5.9S 107.0E   13.0 Crater                 AW82
            Einthoven X                      3.6S 108.7E   45.0 Crater                 AW82
            Elbow                           26.0N   3.6E    0.0 Crater (A)             IAU1973
            Elger                           35.3S  29.8W   21.0 Crater         K1898   K1898
            Elger A                         37.3S  31.2W    8.0 Crater                 NLF?
            Elger B                         37.1S  32.0W    8.0 Crater                 NLF?
            Ellerman                        25.3S 120.1W   47.0 Crater                 IAU1970
            Ellison                         55.1N 107.5W   36.0 Crater                 IAU1970
            Ellison P                       52.8N 109.6W   32.0 Crater                 AW82
            Elmer                           10.1S  84.1E   16.0 Crater                 IAU1976
            Elvey                            8.8N 100.5W   74.0 Crater                 IAU1970
            Elvey G                          7.8N  97.9W   14.0 Crater                 AW82
            Elvey K                          6.0N  98.8W   22.0 Crater                 AW82
            Emden                           63.3N 177.3W  111.0 Crater                 IAU1970
            Emden D                         64.4N 171.1W   47.0 Crater                 AW82
            Emden F                         63.5N 171.1W   20.0 Crater                 AW82
            Emden M                         61.3N 177.0W   25.0 Crater                 AW82
            Emden U                         64.7N 177.9E   38.0 Crater                 AW82
            Emden V                         65.8N 177.5E   35.0 Crater                 AW82
            Emden W                         66.4N 178.3E   25.0 Crater                 AW82
            Emory                           20.1N  30.8E    1.0 Crater (A)             IAU1973
            Encke                            4.6N  36.6W   28.0 Crater         M1834   M1834
            Encke B                          2.4N  36.8W   12.0 Crater                 NLF?
            Encke C                          0.7N  36.4W    9.0 Crater                 NLF?
            Encke E                          0.3N  40.1W    9.0 Crater                 NLF?
            Encke G                          4.8N  38.8W    7.0 Crater                 NLF?
            Encke H                          4.0N  37.3W    4.0 Crater                 NLF?
            Encke J                          5.2N  39.5W    5.0 Crater                 NLF?
            Encke K                          1.4N  37.2W    4.0 Crater                 NLF?
            Encke M                          4.5N  35.1W    3.0 Crater                 NLF?
            Encke N                          4.6N  37.1W    4.0 Crater                 NLF?
            Encke T                          3.4N  38.0W   91.0 Crater                 NLF?
            Encke X                          0.9N  40.3W    3.0 Crater                 NLF?
            Encke Y                          5.9N  36.4W    3.0 Crater                 NLF?
            End                              8.9S  15.6E    0.0 Crater (A)             IAU1973
            Endymion                        53.9N  57.0E  123.0 Crater         VL1645  VL1645
            Endymion A                      54.7N  62.8E   30.0 Crater                 NLF?
            Endymion B                      59.8N  67.2E   59.0 Crater                 NLF?
            Endymion C                      58.4N  60.8E   32.0 Crater         VL1645  NLF?
            Endymion D                      52.4N  62.4E   20.0 Crater                 NLF?
            Endymion E                      53.6N  66.2E   18.0 Crater                 NLF?
            Endymion F                      56.9N  65.1E   12.0 Crater                 NLF?
            Endymion G                      56.4N  55.6E   15.0 Crater                 NLF?
            Endymion H                      51.1N  56.3E   14.0 Crater                 NLF?
            Endymion J                      53.5N  50.7E   67.0 Crater                 NLF?
            Endymion K                      51.3N  52.3E    7.0 Crater                 NLF?
            Endymion L                      55.4N  71.0E    9.0 Crater                 NLF?
            Endymion M                      52.7N  70.9E    9.0 Crater                 NLF?
            Endymion N                      52.4N  69.6E    9.0 Crater                 NLF?
            Endymion W                      52.7N  69.2E   10.0 Crater                 NLF?
            Endymion X                      52.9N  50.1E    6.0 Crater                 NLF?
            Endymion Y                      55.8N  58.0E    8.0 Crater                 NLF?
            Engel'gardt                     5.7N 159.0W   43.0 Crater                  IAU1970
            Engel'gardt B                   8.3N 157.7W  136.0 Crater                  AW82
            Engel'gardt C     1             0.1N 156.9W   49.0 Crater                  AW82
            Engel'gardt J                   2.7N 155.4W   19.0 Crater                  AW82
            Engel'gardt K                   2.4N 157.8W   18.0 Crater                  AW82
            Engel'gardt N                   4.4N 159.3W   28.0 Crater                  AW82
            Engel'gardt R                   4.4N 162.0W   15.0 Crater                  AW82
            E|:otv|:os                      35.5S 133.8E   99.0 Crater                 IAU1970
            Eotvos B                        33.0S 134.8E   22.0 Crater                 AW82
            Eotvos D                        34.4S 136.1E   16.0 Crater                 AW82
            Eotvos E                        34.5S 138.1E   23.0 Crater                 AW82
            Eotvos F                        35.8S 136.2E   21.0 Crater                 AW82
            Eotvos T                        35.3S 130.8E   15.0 Crater                 AW82
            Epigenes                        67.5N   4.6W   55.0 Crater         R1651   R1651
            Epigenes A                      66.9N   0.3W   18.0 Crater                 NLF?
            Epigenes B                      68.3N   3.1W   11.0 Crater                 NLF?
            Epigenes D                      68.3N   0.3E   10.0 Crater                 NLF?
            Epigenes F                      67.1N   8.1W    5.0 Crater                 NLF?
            Epigenes G                      68.9N   7.0W    5.0 Crater                 NLF?
            Epigenes H                      69.4N   6.4W    7.0 Crater                 NLF?
            Epigenes P                      65.4N   5.4W   33.0 Crater                 NLF?
            Epimenides                      40.9S  30.2W   27.0 Crater                 NLF
            Epimenides A                    43.2S  30.1W   15.0 Crater                 NLF?
            Epimenides B                    41.6S  28.8W   10.0 Crater                 NLF?
            Epimenides C                    42.3S  27.5W    4.0 Crater                 NLF?
            Epimenides S                    41.6S  29.3W   26.0 Crater                 NLF?
            Eratosthenes                    14.5N  11.3W   58.0 Crater         VL1645  R1651
            Eratosthenes A                  18.4N   8.3W    6.0 Crater                 NLF?
            Eratosthenes B                  18.7N   8.7W    5.0 Crater                 NLF?
            Eratosthenes C                  16.9N  12.4W    5.0 Crater                 NLF?
            Eratosthenes D                  17.5N  10.9W    4.0 Crater                 NLF?
            Eratosthenes E                  18.0N  10.9W    4.0 Crater                 NLF?
            Eratosthenes F                  17.7N   9.9W    4.0 Crater                 NLF?
            Eratosthenes H                  13.3N  12.2W    3.0 Crater                 NLF?
            Eratosthenes K                  12.9N   9.2W    5.0 Crater                 NLF?
            Eratosthenes M                  14.0N  13.6W    4.0 Crater                 NLF?
            Eratosthenes Z                  13.8N  14.1W    1.0 Crater                 NLF?
            Erro                             5.7N  98.5E   61.0 Crater                 IAU1970
            Erro D                           6.8N 100.5E   30.0 Crater                 AW82
            Erro J                           4.6N  99.4E   15.0 Crater                 AW82
            Erro K                           3.8N  99.6E   17.0 Crater                 AW82
            Erro T                           5.6N  96.9E   16.0 Crater                 AW82
            Erro V                           6.3N  97.8E   18.0 Crater                 AW82
            Esclangon                       21.5N  42.1E   15.0 Crater                 IAU1976
            Esnault-Pelterie                47.7N 141.4W   79.0 Crater                 IAU1970
            Espin                           28.1N 109.1E   75.0 Crater                 IAU1970
            Espin E                         28.3N 111.3E   35.0 Crater                 AW82
            Euclides                         7.4S  29.5W   11.0 Crater         M1834   M1834
            Euclides C                      13.2S  30.0W   10.0 Crater                 NLF?
            Euclides D                       9.4S  25.7W    6.0 Crater                 NLF?
            Euclides E                       6.3S  25.1W    4.0 Crater                 NLF?
            Euclides F                       6.3S  33.7W    5.0 Crater                 NLF?
            Euclides J                       6.4S  28.5W    4.0 Crater                 NLF?
            Euclides K                       4.2S  24.7W    6.0 Crater                 NLF?
            Euclides M                      10.4S  28.2W    6.0 Crater                 NLF?
            Euclides P                       4.5S  27.6W   66.0 Crater                 NLF?
            Euctemon                        76.4N  31.3E   62.0 Crater         VL1645  R1651
            Euctemon C                      76.2N  38.9E   20.0 Crater                 NLF?
            Euctemon D                      77.1N  39.2E   20.0 Crater                 NLF?
            Euctemon H                      76.3N  26.6E   16.0 Crater                 NLF?
            Euctemon K                      75.9N  28.4E    7.0 Crater                 NLF?
            Euctemon N                      75.5N  33.1E    8.0 Crater                 NLF?
            Eudoxus                         44.3N  16.3E   67.0 Crater         VL1645  R1651
            Eudoxus A                       45.8N  20.0E   14.0 Crater                 NLF?
            Eudoxus B                       45.6N  17.4E    8.0 Crater                 NLF?
            Eudoxus D                       43.3N  13.2E   10.0 Crater                 NLF?
            Eudoxus E                       44.3N  21.1E    6.0 Crater                 NLF?
            Eudoxus G                       45.4N  18.8E    7.0 Crater                 NLF?
            Eudoxus J                       40.8N  20.2E    4.0 Crater                 NLF?
            Eudoxus U                       43.9N  20.3E    4.0 Crater                 NLF?
            Eudoxus V                       43.1N  18.9E    4.0 Crater                 NLF?
            Euler                           23.3N  29.2W   27.0 Crater         VL1645  S1791
            Euler E                         24.7N  34.0W    6.0 Crater                 NLF?
            Euler F                         21.2N  27.9W    6.0 Crater                 NLF?
            Euler G                         20.7N  27.4W    4.0 Crater                 NLF?
            Euler H                         25.3N  28.6W    4.0 Crater                 NLF?
            Euler J                         22.3N  31.5W    4.0 Crater                 NLF?
            Euler L                         21.4N  28.9W    4.0 Crater                 NLF?
            Evans                            9.5S 133.5W   67.0 Crater                 IAU1970
            Evans Q                         11.2S 136.4W  137.0 Crater                 AW82
            Evdokimov                       34.8N 153.0W   50.0 Crater                 IAU1970
            Evdokimov G                     33.9N 150.5W   48.0 Crater                 AW82
            Evdokimov N                     31.7N 153.7W   27.0 Crater                 AW82
            Evershed                        35.7N 159.5W   66.0 Crater                 IAU1970
            Evershed C                      38.1N 156.7W   48.0 Crater                 AW82
            Evershed D                      38.8N 156.0W   49.0 Crater                 AW82
            Evershed E                      35.9N 158.3W   73.0 Crater                 AW82
            Evershed R                      35.1N 161.2W   31.0 Crater                 AW82
            Evershed S                      34.9N 162.6W   45.0 Crater                 AW82
            Ewen                             7.7N 121.4E    3.0 Crater         X       IAU1979
            Fabbroni                        18.7N  29.2E   10.0 Crater                 IAU1976
            Fabricius                       42.9S  42.0E   78.0 Crater         VL1645  R1651
            Fabricius A                     44.6S  44.0E   45.0 Crater                 NLF?
            Fabricius B                     43.6S  44.9E   17.0 Crater                 NLF?
            Fabricius J                     45.8S  45.2E   16.0 Crater                 NLF?
            Fabry                           42.9N 100.7E  184.0 Crater                 IAU1970
            Fabry H                         41.9N 105.2E   37.0 Crater                 AW82
            Fabry X                         49.0N  96.7E   28.0 Crater                 AW82
            Fahrenheit                      13.1N  61.7E    6.0 Crater                 IAU1976
            Fairouz                         26.1S 102.9E    3.0 Crater         X       IAU1976
            Falcon                          20.4N  30.3E    0.0 Crater (A)             IAU1973
            Faraday                         42.4S   8.7E   69.0 Crater         S1878   S1878
            Faraday A                       41.5S   9.7E   21.0 Crater                 NLF?
            Faraday C                       43.3S   8.1E   30.0 Crater                 NLF?
            Faraday D                       43.7S   9.6E   14.0 Crater                 NLF?
            Faraday G                       45.8S  10.1E   31.0 Crater                 NLF?
            Faraday H                       45.0S  10.3E   12.0 Crater                 NLF?
            Faraday K                       42.6S  10.3E    7.0 Crater                 NLF?
            Faustini                        87.3S  77.0E   39.0 Crater         N       IAU1994
            Fauth                            6.3N  20.1W   12.0 Crater         M1935   M1935
            Fauth A                          6.0N  20.1W   10.0 Crater                 NLF?
            Fauth B                          5.8N  19.3W    3.0 Crater                 NLF?
            Fauth C                          5.2N  18.8W    4.0 Crater                 NLF?
            Fauth D                          6.0N  18.4W    5.0 Crater                 NLF?
            Fauth E                          5.4N  20.7W    4.0 Crater                 NLF?
            Fauth F                          5.5N  17.4W    4.0 Crater                 NLF?
            Fauth G                          5.3N  16.2W    3.0 Crater                 NLF?
            Fauth H                          4.8N  16.2W    4.0 Crater                 NLF?
            Faye                            21.4S   3.9E   36.0 Crater                 NLF
            Faye A                          21.2S   3.1E    4.0 Crater                 NLF?
            Faye B                          22.6S   4.5E    4.0 Crater                 NLF?
            Fechner                         59.0S 124.9E   63.0 Crater                 IAU1970
            Fechner T                       59.1S 122.9E   14.0 Crater                 AW82
            Fedorov                         28.2N  37.0W    6.0 Crater                 IAU1979
            Felix                           25.1N  25.4W    1.0 Crater         X       IAU1979
            F|%enyi                         44.9S 105.1W   38.0 Crater                 IAU1970
            F|%enyi A                       43.6S 104.4W   19.0 Crater                 AW82
            F|%enyi Y                       43.6S 105.5W   21.0 Crater                 AW82
            Feoktistov                      30.9N 140.7E   23.0 Crater                 IAU1970
            Feoktistov X                    33.1N 139.5E   23.0 Crater                 AW82
            Fermat                          22.6S  19.8E   38.0 Crater         M1834   M1834
            Fermat A                        21.8S  19.6E   17.0 Crater                 NLF?
            Fermat B                        23.0S  21.1E   11.0 Crater                 NLF?
            Fermat C                        21.0S  18.5E   14.0 Crater                 NLF?
            Fermat D                        20.1S  18.0E   13.0 Crater                 NLF?
            Fermat E                        19.9S  19.9E    7.0 Crater                 NLF?
            Fermat F                        22.1S  20.2E    5.0 Crater                 NLF?
            Fermat G                        19.4S  20.0E    7.0 Crater                 NLF?
            Fermat H                        23.1S  20.7E    5.0 Crater                 NLF?
            Fermat P                        23.6S  19.3E   37.0 Crater                 NLF?
            Fermi                           19.3S 122.6E  183.0 Crater         RLA1963 IAU1964
            Fernelius                       38.1S   4.9E   65.0 Crater                 NLF
            Fernelius A                     38.3S   3.5E   30.0 Crater                 NLF?
            Fernelius B                     37.4S   4.1E   10.0 Crater                 NLF?
            Fernelius C                     38.9S   4.4E    7.0 Crater                 NLF?
            Fernelius D                     38.2S   6.2E    7.0 Crater                 NLF?
            Fernelius E                     38.3S   6.6E    6.0 Crater                 NLF?
            Fersman                         18.7N 126.0W  151.0 Crater                 IAU1970
            Fesenkov                        23.2S 135.1E   35.0 Crater                 IAU1973
            Fesenkov F                      23.3S 137.3E   16.0 Crater                 AW82
            Fesenkov S                      23.5S 133.8E   17.0 Crater                 AW82
            Feuill|%ee                      27.4N   9.4W    9.0 Crater         S1878   S1878
            Finsch                          23.6N  21.3E    4.0 Crater                 IAU1976
            Finsen                          42.0S 177.9W   72.0 Crater                 IAU1979
            Finsen C                        40.6S 175.8W   26.0 Crater                 AW82
            Finsen G                        43.0S 175.3W   33.0 Crater                 AW82
            Firmicus                         7.3N  63.4E   56.0 Crater         VL1645  R1651
            Firmicus A                       6.4N  65.1E    8.0 Crater                 NLF?
            Firmicus B                       7.3N  65.8E   14.0 Crater                 NLF?
            Firmicus C                       7.7N  66.5E   13.0 Crater                 NLF?
            Firmicus D                       5.9N  64.4E   11.0 Crater                 NLF?
            Firmicus E                       8.0N  63.6E    9.0 Crater                 NLF?
            Firmicus F                       6.5N  61.8E    9.0 Crater                 NLF?
            Firmicus G                       6.9N  61.9E    9.0 Crater                 NLF?
            Firmicus H                       7.5N  60.3E    7.0 Crater                 NLF?
            Firmicus M                       4.1N  67.2E   42.0 Crater                 NLF?
            Firsov                           4.5N 112.2E   51.0 Crater                 IAU1970
            Firsov K                         3.1N 113.0E   58.0 Crater                 AW82
            Firsov P                         2.9N 111.1E   15.0 Crater                 AW82
            Firsov Q                         2.3N 110.0E   25.0 Crater                 AW82
            Firsov S                         3.6N 109.8E   96.0 Crater                 AW82
            Firsov T                         4.1N 108.6E   26.0 Crater                 AW82
            Firsov V                         5.1N 110.7E   44.0 Crater                 AW82
            Fischer                          8.0N 142.4E   30.0 Crater                 IAU1976
            Fitzgerald                      27.5N 171.7W  110.0 Crater                 IAU1970
            Fitzgerald B                    29.1N 170.9W   26.0 Crater                 AW82
            Fitzgerald W                    28.7N 173.8W   51.0 Crater                 AW82
            Fitzgerald Y                    31.0N 172.7W   34.0 Crater                 AW82
            Fizeau                          58.6S 133.9W  111.0 Crater                 IAU1970
            Fizeau C                        56.1S 128.5W   22.0 Crater                 AW82
            Fizeau F                        58.2S 124.5W   19.0 Crater                 AW82
            Fizeau G                        59.2S 124.5W   54.0 Crater                 AW82
            Fizeau Q                        59.8S 136.3W   28.0 Crater                 AW82
            Fizeau S                        58.7S 139.9W   62.0 Crater                 AW82
            Flag                             9.0S  15.5E    0.0 Crater (A)             IAU1973
            Flammarion                       3.4S   3.7W   74.0 Crater                 NLF
            Flammarion A                     1.9S   2.5W    4.0 Crater                 NLF?
            Flammarion B                     4.0S   4.5W    6.0 Crater                 NLF?
            Flammarion C                     2.0S   3.7W    5.0 Crater                 NLF?
            Flammarion D                     3.0S   4.8W    5.0 Crater                 NLF?
            Flammarion T                     2.9S   2.1W   34.0 Crater                 NLF?
            Flammarion U                     3.0S   1.4W   10.0 Crater                 NLF?
            Flammarion W                     2.1S   2.4W    7.0 Crater                 NLF?
            Flammarion X                     2.9S   3.0W    3.0 Crater                 NLF?
            Flammarion Y                     3.7S   3.2W    3.0 Crater                 NLF?
            Flammarion Z                     2.2S   1.4W    4.0 Crater                 NLF?
            Flamsteed                        4.5S  44.3W   20.0 Crater         M1834   M1834
            Flamsteed A                      7.9S  42.9W   11.0 Crater                 NLF?
            Flamsteed B                      5.9S  43.7W   10.0 Crater                 NLF?
            Flamsteed C                      5.5S  46.3W    9.0 Crater                 NLF?
            Flamsteed D                      3.2S  44.9W    6.0 Crater                 NLF?
            Flamsteed E                      3.7S  46.1W    2.0 Crater                 NLF?
            Flamsteed F                      4.7S  41.1W    5.0 Crater                 NLF?
            Flamsteed G                      4.8S  50.9W   46.0 Crater                 NLF?
            Flamsteed H                      5.9S  51.7W    4.0 Crater                 NLF?
            Flamsteed J                      6.6S  49.3W    5.0 Crater                 NLF?
            Flamsteed K                      3.1S  43.7W    4.0 Crater                 NLF?
            Flamsteed L                      3.4S  40.9W    4.0 Crater                 NLF?
            Flamsteed M                      2.4S  40.6W    4.0 Crater                 NLF?
            Flamsteed P                      3.2S  44.1W  112.0 Crater                 NLF?
            Flamsteed S                      3.4S  52.2W    4.0 Crater                 NLF?
            Flamsteed T                      3.1S  51.6W   24.0 Crater                 NLF?
            Flamsteed U                      3.6S  50.2W    4.0 Crater                 NLF?
            Flamsteed X                      2.3S  47.3W    3.0 Crater                 NLF?
            Flamsteed Z                      1.3S  47.8W    3.0 Crater                 NLF?
            Flank                            3.7S  17.4W    0.0 Crater (A)             IAU1973
            Fleming                         15.0N 109.6E  106.0 Crater                 IAU1970
            Fleming D                       17.0N 114.0E   25.0 Crater                 AW82
            Fleming N                       12.7N 108.8E   24.0 Crater                 AW82
            Fleming W                       18.0N 106.2E   50.0 Crater                 AW82
            Fleming Y                       18.2N 108.2E   30.0 Crater                 AW82
            Florensky                       25.3N 131.5E   71.0 Crater         N       IAU1985
            Focas                           33.7S  93.8W   22.0 Crater                 IAU1970
            Focas U                         32.7S  98.5W   10.0 Crater                 AW82
            Fontana                         16.1S  56.6W   31.0 Crater                 NLF
            Fontana A                       15.7S  56.1W   13.0 Crater                 NLF?
            Fontana B                       15.5S  56.3W   11.0 Crater                 NLF?
            Fontana C                       12.8S  57.1W   14.0 Crater                 NLF?
            Fontana D                       17.0S  57.3W   11.0 Crater                 NLF?
            Fontana E                       17.6S  57.9W   13.0 Crater                 NLF?
            Fontana F                       16.2S  59.9W    7.0 Crater                 NLF?
            Fontana G                       16.0S  59.2W   15.0 Crater                 NLF?
            Fontana H                       14.0S  57.9W    9.0 Crater                 NLF?
            Fontana K                       13.2S  57.3W    7.0 Crater                 NLF?
            Fontana M                       17.2S  57.5W    6.0 Crater                 NLF?
            Fontana W                       17.2S  58.3W    6.0 Crater                 NLF?
            Fontana Y                       16.7S  58.3W    5.0 Crater                 NLF?
            Fontenelle                      63.4N  18.9W   38.0 Crater         S1791   S1791
            Fontenelle A                    67.5N  16.1W   21.0 Crater                 NLF?
            Fontenelle B                    61.9N  23.0W   14.0 Crater                 NLF?
            Fontenelle C                    64.4N  27.2W   13.0 Crater                 NLF?
            Fontenelle D                    62.5N  23.4W   17.0 Crater                 NLF?
            Fontenelle F                    64.4N  28.2W   11.0 Crater                 NLF?
            Fontenelle G                    59.5N  18.3W    4.0 Crater                 NLF?
            Fontenelle H                    64.1N  20.1W    6.0 Crater                 NLF?
            Fontenelle K                    69.6N  15.6W    7.0 Crater                 NLF?
            Fontenelle L                    66.5N  16.6W    6.0 Crater                 NLF?
            Fontenelle M                    63.1N  28.8W    9.0 Crater                 NLF?
            Fontenelle N                    64.0N  29.7W    8.0 Crater                 NLF?
            Fontenelle P                    64.1N  17.2W    6.0 Crater                 NLF?
            Fontenelle R                    64.3N  18.8W    6.0 Crater                 NLF?
            Fontenelle S                    65.3N  26.7W    7.0 Crater                 NLF?
            Fontenelle T                    66.3N  25.7W    7.0 Crater                 NLF?
            Fontenelle X                    60.5N  27.8W    7.0 Crater                 NLF?
            Foster                          23.7N 141.5W   33.0 Crater                 IAU1970
            Foster H                        23.2N 139.6W   25.0 Crater                 AW82
            Foster L                        21.0N 140.5W   32.0 Crater                 AW82
            Foster P                        20.2N 143.5W   36.0 Crater                 AW82
            Foster S                        22.9N 143.7W   36.0 Crater                 AW82
            Foucault                        50.4N  39.7W   23.0 Crater                 NLF
            Fourier                         30.3S  53.0W   51.0 Crater         M1834   M1834
            Fourier A                       30.2S  49.5W   32.0 Crater                 NLF?
            Fourier B                       30.5S  52.0W   11.0 Crater                 NLF?
            Fourier C                       28.5S  51.9W   14.0 Crater                 NLF?
            Fourier D                       31.5S  50.4W   21.0 Crater                 NLF?
            Fourier E                       28.7S  50.1W   14.0 Crater                 NLF?
            Fourier F                       28.8S  52.7W   14.0 Crater                 NLF?
            Fourier G                       29.4S  51.7W   11.0 Crater                 NLF?
            Fourier K                       30.0S  54.2W   10.0 Crater                 NLF?
            Fourier L                       30.2S  52.6W    5.0 Crater                 NLF?
            Fourier M                       30.4S  53.1W    4.0 Crater                 NLF?
            Fourier N                       33.5S  56.4W   10.0 Crater                 NLF?
            Fourier P                       31.0S  54.9W    9.0 Crater                 NLF?
            Fourier R                       34.2S  51.2W    9.0 Crater                 NLF?
            Fowler                          42.3N 145.0W  146.0 Crater                 IAU1970
            Fowler A                        46.3N 145.0W   52.0 Crater                 AW82
            Fowler C                        45.0N 141.9W   32.0 Crater                 AW82
            Fowler N                        40.1N 146.1W   39.0 Crater                 AW82
            Fowler R                        42.2N 150.1W   18.0 Crater                 AW82
            Fowler W                        46.0N 150.2W   31.0 Crater                 AW82
            Fox                              0.5N  98.2E   24.0 Crater                 IAU1973
            Fox A                            1.5N  98.3E   13.0 Crater                 AW82
            Fra Mauro                        6.1S  17.0W  101.0 Crater         M1834   M1834
            Fra Mauro A                      5.4S  20.9W    9.0 Crater                 NLF?
            Fra Mauro B                      4.0S  21.7W    7.0 Crater                 NLF?
            Fra Mauro C                      5.4S  21.6W    7.0 Crater                 NLF?
            Fra Mauro D                      4.8S  17.6W    5.0 Crater                 NLF?
            Fra Mauro E                      6.0S  16.8W    4.0 Crater                 NLF?
            Fra Mauro F                      6.7S  16.9W    3.0 Crater                 NLF?
            Fra Mauro G                      2.2S  16.3W    6.0 Crater                 NLF?
            Fra Mauro H                      4.1S  15.5W    6.0 Crater                 NLF?
            Fra Mauro J                      2.6S  18.6W    3.0 Crater                 NLF?
            Fra Mauro K                      2.5S  16.7W    6.0 Crater                 NLF?
            Fra Mauro N                      5.3S  17.4W    3.0 Crater                 NLF?
            Fra Mauro P                      5.4S  16.5W    3.0 Crater                 NLF?
            Fra Mauro R                      2.2S  15.6W    3.0 Crater                 NLF?
            Fra Mauro T                      2.1S  19.3W    3.0 Crater                 NLF?
            Fra Mauro W                      1.3S  16.8W    4.0 Crater                 NLF?
            Fra Mauro X                      4.5S  17.3W   20.0 Crater                 NLF?
            Fra Mauro Y                      4.1S  16.7W    4.0 Crater                 NLF?
            Fra Mauro Z                      3.8S  14.6W    5.0 Crater                 NLF?
            Fracastorius                    21.5S  33.2E  112.0 Crater         VL1645  R1651
            Fracastorius A                  24.4S  36.5E   18.0 Crater                 NLF?
            Fracastorius B                  22.5S  37.2E   27.0 Crater                 NLF?
            Fracastorius C                  24.6S  34.6E   16.0 Crater                 NLF?
            Fracastorius D                  21.8S  30.9E   28.0 Crater                 NLF?
            Fracastorius E                  20.2S  31.0E   13.0 Crater                 NLF?
            Fracastorius G                  21.2S  38.3E   16.0 Crater                 NLF?
            Fracastorius H                  20.7S  30.6E   21.0 Crater                 NLF?
            Fracastorius J                  20.8S  37.4E   12.0 Crater                 NLF?
            Fracastorius K                  25.4S  34.7E   17.0 Crater                 NLF?
            Fracastorius L                  20.6S  33.2E    5.0 Crater                 NLF?
            Fracastorius M                  21.7S  32.9E    4.0 Crater                 NLF?
            Fracastorius N                  23.2S  34.0E   10.0 Crater                 NLF?
            Fracastorius P                  25.5S  33.3E    8.0 Crater                 NLF?
            Fracastorius Q                  25.1S  33.2E    8.0 Crater                 NLF?
            Fracastorius R                  23.8S  33.7E    5.0 Crater                 NLF?
            Fracastorius S                  19.0S  31.9E    5.0 Crater                 NLF?
            Fracastorius T                  19.8S  37.4E   14.0 Crater                 NLF?
            Fracastorius W                  22.6S  35.7E    7.0 Crater                 NLF?
            Fracastorius X                  23.0S  31.1E    7.0 Crater                 NLF?
            Fracastorius Y                  23.0S  32.0E   12.0 Crater                 NLF?
            Fracastorius Z                  24.8S  33.6E    9.0 Crater                 NLF?
            Franck                          22.6N  35.5E   12.0 Crater                 IAU1973
            Franklin                        38.8N  47.7E   56.0 Crater         VL1645  M1834
            Franklin C                      35.7N  44.3E   15.0 Crater                 NLF?
            Franklin F                      37.5N  47.7E   38.0 Crater                 NLF?
            Franklin G                      40.1N  48.1E    7.0 Crater                 NLF?
            Franklin H                      37.1N  43.7E    6.0 Crater                 NLF?
            Franklin K                      39.1N  51.4E   20.0 Crater                 NLF?
            Franklin W                      37.8N  43.7E    6.0 Crater                 NLF?
            Franz                           16.6N  40.2E   25.0 Crater         K1898   K1898
            Fraunhofer                      39.5S  59.1E   56.0 Crater         M1834   M1834
            Fraunhofer A                    39.6S  61.7E   29.0 Crater                 NLF?
            Fraunhofer B                    41.8S  67.3E   36.0 Crater                 NLF?
            Fraunhofer C                    42.9S  64.7E   38.0 Crater                 NLF?
            Fraunhofer D                    43.1S  69.0E   17.0 Crater                 NLF?
            Fraunhofer E                    43.4S  61.7E   42.0 Crater                 NLF?
            Fraunhofer F                    41.7S  59.8E   16.0 Crater                 NLF?
            Fraunhofer G                    38.5S  58.3E   11.0 Crater                 NLF?
            Fraunhofer H                    40.8S  61.7E   43.0 Crater                 NLF?
            Fraunhofer J                    42.4S  63.6E   63.0 Crater                 NLF?
            Fraunhofer K                    42.5S  69.3E   16.0 Crater                 NLF?
            Fraunhofer L                    42.1S  68.8E    8.0 Crater                 NLF?
            Fraunhofer M                    40.9S  65.6E   21.0 Crater                 NLF?
            Fraunhofer N                    40.9S  64.4E   12.0 Crater                 NLF?
            Fraunhofer R                    43.5S  68.7E   11.0 Crater                 NLF?
            Fraunhofer S                    43.1S  69.9E   13.0 Crater                 NLF?
            Fraunhofer T                    37.9S  55.7E    8.0 Crater                 NLF?
            Fraunhofer U                    40.2S  65.1E   24.0 Crater                 NLF?
            Fraunhofer V                    39.0S  58.0E   24.0 Crater                 NLF?
            Fraunhofer W                    39.4S  62.8E   18.0 Crater                 NLF?
            Fraunhofer X                    39.7S  60.6E    6.0 Crater                 NLF?
            Fraunhofer Y                    40.2S  63.0E   13.0 Crater                 NLF?
            Fraunhofer Z                    39.9S  63.9E   14.0 Crater                 NLF?
            Fredholm                        18.4N  46.5E   14.0 Crater                 IAU1976
            Freud                           25.8N  52.3W    2.0 Crater                 IAU1973
            Freundlich                      25.0N 171.0E   85.0 Crater                 IAU1970
            Freundlich G                    24.5N 173.5E   25.0 Crater                 AW82
            Freundlich Q                    22.7N 167.4E   17.0 Crater                 AW82
            Freundlich R                    23.8N 167.8E   20.0 Crater                 AW82
            Fridman                         12.6S 126.0W  102.0 Crater                 IAU1970
            Fridman C                       10.5S 124.5W   36.0 Crater                 AW82
            Froelich                        80.3N 109.7W   58.0 Crater                 IAU1970
            Froelich M                      77.6N 109.3W   29.0 Crater                 AW82
            Frost                           37.7N 118.4W   75.0 Crater                 IAU1970
            Frost N                         35.0N 119.2W   43.0 Crater                 AW82
            Fryxell                         21.3S 101.4W   18.0 Crater         N       IAU1985
            Furnerius                       36.0S  60.6E  135.0 Crater         VL1645  R1651
            Furnerius A                     33.5S  59.0E   12.0 Crater                 NLF?
            Furnerius B                     35.5S  59.9E   22.0 Crater                 NLF?
            Furnerius C                     33.7S  57.8E   22.0 Crater                 NLF?
            Furnerius D                     37.0S  55.9E   16.0 Crater                 NLF?
            Furnerius E                     34.8S  57.1E   22.0 Crater                 NLF?
            Furnerius F                     36.2S  64.0E   43.0 Crater                 NLF?
            Furnerius G                     38.2S  65.4E   34.0 Crater                 NLF?
            Furnerius H                     37.6S  69.5E   44.0 Crater                 NLF?
            Furnerius J                     34.8S  64.2E   24.0 Crater                 NLF?
            Furnerius K                     38.1S  68.1E   36.0 Crater                 NLF?
            Furnerius L                     38.6S  69.9E   13.0 Crater                 NLF?
            Furnerius N                     33.6S  61.1E    9.0 Crater                 NLF?
            Furnerius P                     38.0S  61.8E   18.0 Crater                 NLF?
            Furnerius Q                     39.5S  67.3E   30.0 Crater                 NLF?
            Furnerius R                     39.9S  69.1E   17.0 Crater                 NLF?
            Furnerius S                     39.1S  68.0E   15.0 Crater                 NLF?
            Furnerius T                     37.8S  63.1E   10.0 Crater                 NLF?
            Furnerius U                     35.7S  68.2E   20.0 Crater                 NLF?
            Furnerius V                     35.7S  65.6E   58.0 Crater                 NLF?
            Furnerius W                     37.1S  71.1E   32.0 Crater                 NLF?
            Furnerius X                     33.9S  63.6E    8.0 Crater                 NLF?
            Furnerius Y                     34.3S  65.2E   12.0 Crater                 NLF?
            Furnerius Z                     33.5S  63.0E    8.0 Crater                 NLF?
            G. Bond                         32.4N  36.2E   20.0 Crater                 NLF
            G. Bond A                       31.6N  36.8E    9.0 Crater                 NLF?
            G. Bond B                       29.9N  34.7E   33.0 Crater                 NLF?
            G. Bond C                       28.2N  34.8E   46.0 Crater                 NLF?
            G. Bond G                       32.8N  37.3E   31.0 Crater                 NLF?
            G. Bond K                       32.1N  38.3E   14.0 Crater                 NLF?
            Gadomski                        36.4N 147.3W   65.0 Crater                 IAU1970
            Gadomski A                      38.6N 145.9W   32.0 Crater                 AW82
            Gadomski X                      37.8N 148.3W   35.0 Crater                 AW82
            Gagarin                         20.2S 149.2E  265.0 Crater                 IAU1970
            Gagarin G                       20.5S 150.5E   14.0 Crater                 AW82
            Gagarin M                       23.5S 149.2E   19.0 Crater                 AW82
            Gagarin T                       19.4S 144.5E   24.0 Crater                 AW82
            Gagarin Z                       15.4S 149.4E   29.0 Crater                 AW82
            Galen                           21.9N   5.0E   10.0 Crater                 IAU1973
            Galilaei                        10.5N  62.7W   15.0 Crater                 NLF
            Galilaei A                      11.7N  62.9W   11.0 Crater                 NLF?
            Galilaei B                      11.4N  67.6W   15.0 Crater                 NLF?
            Galilaei D                       8.7N  62.7W    1.0 Crater                 NLF?
            Galilaei E                      14.0N  61.8W    7.0 Crater                 NLF?
            Galilaei F                      12.3N  66.2W    3.0 Crater                 NLF?
            Galilaei G                      12.7N  67.1W    1.0 Crater                 NLF?
            Galilaei H                      11.5N  68.7W    7.0 Crater                 NLF?
            Galilaei J                      13.0N  61.9W    4.0 Crater                 NLF?
            Galilaei K                      13.0N  62.7W    3.0 Crater                 NLF?
            Galilaei L                      13.2N  58.5W    3.0 Crater                 NLF?
            Galilaei M                      13.3N  56.8W    3.0 Crater                 NLF?
            Galilaei S                      15.4N  64.7W    2.0 Crater                 NLF?
            Galilaei T                      16.2N  61.4W    2.0 Crater                 NLF?
            Galilaei V                      17.1N  60.3W    3.0 Crater                 NLF?
            Galilaei W                      17.8N  60.5W    4.0 Crater                 NLF?
            Galle                           55.9N  22.3E   21.0 Crater         S1878   S1878
            Galle A                         53.9N  22.3E    6.0 Crater                 NLF?
            Galle B                         55.4N  17.4E    7.0 Crater                 NLF?
            Galle C                         57.8N  24.5E   11.0 Crater                 NLF?
            Galois                          14.2S 151.9W  222.0 Crater                 IAU1970
            Galois A                        14.0S 152.5W   54.0 Crater                 AW82
            Galois B                        11.3S 151.8W   20.0 Crater                 AW82
            Galois C                        12.4S 150.5W   22.0 Crater                 AW82
            Galois F                        13.9S 146.4W   13.0 Crater                 AW82
            Galois H                        15.2S 150.9W   19.0 Crater                 AW82
            Galois L                        15.5S 152.0W   51.0 Crater                 AW82
            Galois M                        16.1S 152.4W   18.0 Crater                 AW82
            Galois Q                        15.2S 154.7W  132.0 Crater                 AW82
            Galois S                        14.5S 154.9W   18.0 Crater                 AW82
            Galois U                        13.2S 154.7W   35.0 Crater                 AW82
            Galvani                         49.6N  84.6W   80.0 Crater         S1878   S1878
            Galvani B                       49.5N  89.0W   15.0 Crater                 NLF?
            Galvani D                       47.8N  88.3W   13.0 Crater                 NLF?
            Gambart                          1.0N  15.2W   25.0 Crater         M1834   M1834
            Gambart A                        1.0N  18.7W   12.0 Crater                 NLF?
            Gambart B                        2.2N  11.5W   11.0 Crater                 NLF?
            Gambart C                        3.3N  11.8W   12.0 Crater         R1651   NLF?
            Gambart D                        3.4N  17.7W    6.0 Crater                 NLF?
            Gambart E                        1.0N  17.2W    4.0 Crater                 NLF?
            Gambart F                        0.1N  16.9W    5.0 Crater                 NLF?
            Gambart G                        1.9N  12.0W    6.0 Crater                 NLF?
            Gambart H                        3.2N  10.6W    4.0 Crater                 NLF?
            Gambart J                        0.7S  18.2W    7.0 Crater                 NLF?
            Gambart K                        3.9N  14.2W    4.0 Crater                 NLF?
            Gambart L                        3.3N  15.3W    4.0 Crater                 NLF?
            Gambart M                        5.4N  11.7W    4.0 Crater                 NLF?
            Gambart N                        0.5S  14.9W    5.0 Crater                 NLF?
            Gambart R                        0.6S  20.8W    4.0 Crater                 NLF?
            Gambart S                        0.1S  13.2W    3.0 Crater                 NLF?
            Gamow                           65.3N 145.3E  129.0 Crater                 IAU1970
            Gamow A                         67.3N 148.8E   31.0 Crater                 AW82
            Gamow B                         66.4N 149.5E   26.0 Crater                 AW82
            Gamow U                         66.7N 137.0E   39.0 Crater                 AW82
            Gamow V                         66.3N 139.7E   49.0 Crater                 AW82
            Gamow Y                         67.8N 143.9E   27.0 Crater                 AW82
            Ganskiy                          9.7S  97.0E   43.0 Crater                 IAU1970
            Ganskiy F                        9.6S  99.0E    9.0 Crater                 AW82
            Ganskiy K                       12.5S  98.4E   13.0 Crater                 AW82
            Ganskiy M                       12.0S  97.1E   14.0 Crater                 AW82
            Ganskiy S                       10.2S  94.7E   22.0 Crater                 AW82
            Ganswindt                       79.6S 110.3E   74.0 Crater                 IAU1970
            Garavito                        47.5S 156.7E   74.0 Crater                 IAU1970
            Garavito C                      45.0S 159.0E   25.0 Crater                 AW82
            Garavito D                      46.6S 158.8E   33.0 Crater                 AW82
            Garavito Q                      49.6S 153.6E   42.0 Crater                 AW82
            Garavito Y                      45.5S 155.6E   52.0 Crater                 AW82
            Gardner                         17.7N  33.8E   18.0 Crater                 IAU1976
            G|:artner                       59.1N  34.6E  115.0 Crater         S1791   S1791
            G|:artner A                     60.7N  37.8E   14.0 Crater                 NLF?
            G|:artner C                     59.4N  31.0E    8.0 Crater                 NLF?
            G|:artner D                     58.5N  33.9E    8.0 Crater                 NLF?
            G|:artner E                     61.5N  43.8E    7.0 Crater                 NLF?
            G|:artner F                     57.5N  30.1E   14.0 Crater                 NLF?
            G|:artner G                     59.5N  39.8E   33.0 Crater                 NLF?
            G|:artner M                     55.5N  37.0E   11.0 Crater                 NLF?
            Gassendi                        17.6S  40.1W  101.0 Crater         VL1645  R1651
            Gassendi A                      15.5S  39.7W   33.0 Crater         VL1645  NLF?
            Gassendi B                      14.7S  40.6W   26.0 Crater                 NLF?
            Gassendi E                      18.4S  43.5W    8.0 Crater                 NLF?
            Gassendi F                      15.0S  45.0W    8.0 Crater                 NLF?
            Gassendi G                      16.8S  44.6W    8.0 Crater                 NLF?
            Gassendi J                      21.6S  37.0W    9.0 Crater                 NLF?
            Gassendi K                      18.7S  43.6W    6.0 Crater                 NLF?
            Gassendi L                      20.4S  41.8W    6.0 Crater                 NLF?
            Gassendi M                      18.6S  39.0W    3.0 Crater                 NLF?
            Gassendi N                      18.0S  39.2W    3.0 Crater                 NLF?
            Gassendi O                      21.9S  35.1W   11.0 Crater                 NLF?
            Gassendi P                      17.2S  40.6W    2.0 Crater                 NLF?
            Gassendi R                      21.9S  37.7W    3.0 Crater                 NLF?
            Gassendi T                      19.0S  35.4W   10.0 Crater                 NLF?
            Gassendi W                      17.6S  43.7W    6.0 Crater                 NLF?
            Gassendi Y                      20.8S  38.3W    5.0 Crater                 NLF?
            Gaston                          30.9N  34.0W    2.0 Crater         X       IAU1979
            Gator                            9.0S  15.6E    1.0 Crater (A)             IAU1973
            Gaudibert                       10.9S  37.8E   34.0 Crater         K1898   K1898
            Gaudibert A                     12.2S  37.9E   21.0 Crater                 NLF?
            Gaudibert B                     12.3S  38.5E   21.0 Crater                 NLF?
            Gaudibert C                     11.5S  37.8E    9.0 Crater                 NLF?
            Gaudibert D                     10.5S  36.3E    5.0 Crater                 NLF?
            Gaudibert H                     13.8S  36.7E   11.0 Crater                 NLF?
            Gaudibert J                     11.1S  39.1E   10.0 Crater                 NLF?
            Gauricus                        33.8S  12.6W   79.0 Crater         VL1645  R1651
            Gauricus A                      35.6S  13.4W   38.0 Crater                 NLF?
            Gauricus B                      35.3S  12.2W   23.0 Crater                 NLF?
            Gauricus C                      35.2S  10.7W   11.0 Crater                 NLF?
            Gauricus D                      35.1S  11.4W   13.0 Crater                 NLF?
            Gauricus E                      32.5S  11.8W    7.0 Crater                 NLF?
            Gauricus F                      33.0S  12.6W   12.0 Crater                 NLF?
            Gauricus G                      33.9S  11.0W   18.0 Crater                 NLF?
            Gauricus H                      38.1S  13.3W    8.0 Crater                 NLF?
            Gauricus J                      32.3S  11.9W   10.0 Crater                 NLF?
            Gauricus K                      33.3S  13.9W    5.0 Crater                 NLF?
            Gauricus L                      34.0S  13.8W    4.0 Crater                 NLF?
            Gauricus M                      34.4S  13.6W    6.0 Crater                 NLF?
            Gauricus N                      32.4S  12.7W    7.0 Crater                 NLF?
            Gauricus P                      35.1S  12.4W    6.0 Crater                 NLF?
            Gauricus R                      34.8S  13.3W    6.0 Crater                 NLF?
            Gauricus S                      33.9S  10.1W   15.0 Crater                 NLF?
            Gauss                           35.7N  79.0E  177.0 Crater         M1834   M1834
            Gauss A                         36.5N  82.7E   18.0 Crater                 NLF?
            Gauss B                         35.9N  81.2E   37.0 Crater                 NLF?
            Gauss C                         39.7N  72.1E   29.0 Crater                 NLF?
            Gauss D                         39.3N  73.8E   24.0 Crater                 NLF?
            Gauss E                         35.3N  77.6E    8.0 Crater                 NLF?
            Gauss F                         34.8N  78.3E   20.0 Crater                 NLF?
            Gauss G                         34.2N  78.6E   18.0 Crater                 NLF?
            Gauss H                         33.2N  77.1E   11.0 Crater                 NLF?
            Gauss J                         40.6N  72.6E   14.0 Crater                 NLF?
            Gauss W                         34.5N  80.2E   18.0 Crater                 NLF?
            Gavrilov                        17.4N 130.9E   60.0 Crater                 IAU1970
            Gavrilov A                      19.6N 131.9E   26.0 Crater                 AW82
            Gavrilov K                      15.0N 132.5E   38.0 Crater                 AW82
            Gay-Lussac                      13.9N  20.8W   26.0 Crater         M1834   M1834
            Gay-Lussac A                    13.2N  20.4W   15.0 Crater                 NLF?
            Gay-Lussac B                    16.2N  21.1W    3.0 Crater                 NLF?
            Gay-Lussac C                    15.4N  22.5W    5.0 Crater                 NLF?
            Gay-Lussac D                    14.6N  21.0W    5.0 Crater                 NLF?
            Gay-Lussac F                    14.0N  19.6W    5.0 Crater                 NLF?
            Gay-Lussac G                    13.8N  18.9W    5.0 Crater                 NLF?
            Gay-Lussac H                    13.4N  23.2W    5.0 Crater                 NLF?
            Gay-Lussac J                    11.7N  21.7W    3.0 Crater                 NLF?
            Gay-Lussac N                    12.6N  20.9W    2.0 Crater                 NLF?
            Geber                           19.4S  13.9E   44.0 Crater         VL1645  R1651
            Geber A                         21.8S  14.7E   14.0 Crater                 NLF?
            Geber B                         19.0S  13.0E   19.0 Crater                 NLF?
            Geber C                         22.1S  14.9E   11.0 Crater                 NLF?
            Geber D                         19.3S  11.9E    5.0 Crater                 NLF?
            Geber E                         20.5S  12.9E    6.0 Crater                 NLF?
            Geber F                         19.9S  13.2E    5.0 Crater                 NLF?
            Geber H                         17.9S  12.5E    4.0 Crater                 NLF?
            Geber J                         20.0S  15.9E    4.0 Crater                 NLF?
            Geber K                         17.5S  10.6E    5.0 Crater                 NLF?
            Geiger                          14.6S 158.5E   34.0 Crater                 IAU1970
            Geiger K                        16.0S 159.9E   11.0 Crater                 AW82
            Geiger L                        16.3S 159.3E    6.0 Crater                 AW82
            Geiger R                        15.7S 156.5E   40.0 Crater                 AW82
            Geiger Y                        12.7S 158.1E   29.0 Crater                 AW82
            Geissler                         2.6S  76.5E   16.0 Crater                 IAU1976
            Geminus                         34.5N  56.7E   85.0 Crater         VL1645  R1651
            Geminus A                       31.5N  51.8E   15.0 Crater                 NLF?
            Geminus B                       34.2N  52.3E   10.0 Crater                 NLF?
            Geminus C                       33.9N  58.7E   16.0 Crater                 NLF?
            Geminus D                       30.6N  47.4E   16.0 Crater                 NLF?
            Geminus E                       33.5N  48.5E   67.0 Crater                 NLF?
            Geminus F                       32.1N  51.1E   22.0 Crater                 NLF?
            Geminus G                       30.8N  48.6E   14.0 Crater                 NLF?
            Geminus H                       31.6N  48.9E   15.0 Crater                 NLF?
            Geminus M                       31.9N  48.5E   11.0 Crater                 NLF?
            Geminus N                       31.4N  47.7E   24.0 Crater                 NLF?
            Geminus W                       34.3N  47.4E    6.0 Crater                 NLF?
            Geminus Z                       30.7N  46.7E   26.0 Crater                 NLF?
            Gemma Frisius                   34.2S  13.3E   87.0 Crater         VL1645  R1651
            Gemma Frisius A                 35.8S  15.2E   68.0 Crater                 NLF?
            Gemma Frisius B                 35.5S  17.1E   41.0 Crater                 NLF?
            Gemma Frisius C                 35.6S  18.8E   35.0 Crater                 NLF?
            Gemma Frisius D                 34.3S  10.9E   28.0 Crater                 NLF?
            Gemma Frisius E                 37.2S  12.8E   19.0 Crater                 NLF?
            Gemma Frisius F                 35.8S  10.3E    9.0 Crater                 NLF?
            Gemma Frisius G                 33.2S  11.4E   37.0 Crater                 NLF?
            Gemma Frisius H                 32.4S  12.2E   28.0 Crater                 NLF?
            Gemma Frisius J                 35.1S  18.1E   12.0 Crater                 NLF?
            Gemma Frisius K                 37.4S  11.0E   10.0 Crater                 NLF?
            Gemma Frisius L                 34.8S  11.8E    6.0 Crater                 NLF?
            Gemma Frisius M                 34.3S  12.5E    5.0 Crater                 NLF?
            Gemma Frisius O                 32.5S  12.9E    6.0 Crater                 NLF?
            Gemma Frisius P                 31.8S  12.8E    4.0 Crater                 NLF?
            Gemma Frisius Q                 35.8S  14.8E    9.0 Crater                 NLF?
            Gemma Frisius R                 37.1S  15.3E    5.0 Crater                 NLF?
            Gemma Frisius S                 35.2S  15.1E    6.0 Crater                 NLF?
            Gemma Frisius T                 34.9S  16.4E    8.0 Crater                 NLF?
            Gemma Frisius U                 34.5S  16.8E    8.0 Crater                 NLF?
            Gemma Frisius W                 36.9S  13.3E   15.0 Crater                 NLF?
            Gemma Frisius X                 34.7S  15.8E   15.0 Crater                 NLF?
            Gemma Frisius Y                 37.4S  13.5E   28.0 Crater                 NLF?
            Gemma Frisius Z                 35.1S   9.6E   10.0 Crater                 NLF?
            Gerard                          44.5N  80.0W   90.0 Crater         M1834   M1834
            Gerard A                        45.1N  82.3W   17.0 Crater                 NLF?
            Gerard B                        46.4N  88.3W   14.0 Crater                 NLF?
            Gerard C                        45.9N  79.2W   27.0 Crater                 NLF?
            Gerard D                        46.2N  79.9W    5.0 Crater                 NLF?
            Gerard E                        44.5N  81.0W    5.0 Crater                 NLF?
            Gerard F                        43.8N  82.3W    5.0 Crater                 NLF?
            Gerard G                        45.7N  88.3W   22.0 Crater                 NLF?
            Gerard H                        44.5N  87.0W   13.0 Crater                 NLF?
            Gerard J                        46.9N  88.7W   10.0 Crater                 NLF?
            Gerard K                        44.0N  77.2W    7.0 Crater                 NLF?
            Gerard L                        43.2N  76.4W    4.0 Crater                 NLF?
            Gerard Q Inner                  46.6N  83.1W    7.0 Crater                 NLF?
            Gerard Q Outer                  46.7N  84.1W   18.0 Crater                 NLF?
            Gerasimovich                    22.9S 122.6W   86.0 Crater                 IAU1970
            Gerasimovich D                  22.3S 121.6W   26.0 Crater                 AW82
            Gerasimovich R                  24.1S 125.9W   55.0 Crater                 AW82
            Gernsback                       36.5S  99.7E   48.0 Crater                 IAU1970
            Gernsback H                     38.2S 103.5E   43.0 Crater                 AW82
            Gernsback J                     37.7S 101.9E   18.0 Crater                 AW82
            Gibbs                           18.4S  84.3E   76.0 Crater         RLA1963 IAU1964
            Gibbs D                         13.1S  85.9E   13.0 Crater                 RLA1963?
            Gilbert                          3.2S  76.0E  112.0 Crater         RLA1963 IAU1964
            Gilbert J                        4.3S  72.7E   38.0 Crater                 NLF?
            Gilbert K                        5.5S  73.2E   38.0 Crater                 NLF?
            Gilbert P                        0.9S  75.6E   18.0 Crater                 NLF?
            Gilbert S                        1.9S  75.6E   19.0 Crater                 NLF?
            Gilbert V                        1.5S  79.9E   15.0 Crater                 NLF?
            Gilbert W                        1.1S  78.9E   19.0 Crater                 NLF?
            Gill                            63.9S  75.9E   66.0 Crater         RLA1963 IAU1964
            Gill A                          63.6S  72.9E   13.0 Crater                 NLF?
            Gill B                          61.7S  69.9E   31.0 Crater                 NLF?
            Gill C                          62.2S  67.4E   30.0 Crater                 NLF?
            Gill D                          63.4S  79.8E   15.0 Crater                 NLF?
            Gill E                          63.3S  70.4E   13.0 Crater                 NLF?
            Gill F                          63.8S  65.1E   23.0 Crater                 NLF?
            Gill G                          63.5S  68.2E   32.0 Crater                 NLF?
            Gill H                          63.9S  70.2E    8.0 Crater                 NLF?
            Ginzel                          14.3N  97.4E   55.0 Crater                 IAU1970
            Ginzel G                        13.7N 100.2E   42.0 Crater                 AW82
            Ginzel H                        12.7N 100.1E   50.0 Crater                 AW82
            Ginzel L                        13.1N  97.8E   28.0 Crater                 AW82
            Gioja                           83.3N   2.0E   41.0 Crater         M1834   M1834
            Giordano Bruno                  35.9N 102.8E   22.0 Crater         BML1960 IAU1961
            Glaisher                        13.2N  49.5E   15.0 Crater                 NLF
            Glaisher A                      12.9N  50.7E   19.0 Crater                 NLF?
            Glaisher B                      12.6N  50.1E   18.0 Crater                 NLF?
            Glaisher E                      12.7N  49.2E   21.0 Crater                 NLF?
            Glaisher F                      13.7N  50.0E    7.0 Crater                 NLF?
            Glaisher G                      12.4N  49.5E   20.0 Crater                 NLF?
            Glaisher H                      13.8N  49.6E    5.0 Crater                 NLF?
            Glaisher L                      13.4N  48.8E    7.0 Crater                 NLF?
            Glaisher M                      13.1N  48.6E    5.0 Crater                 NLF?
            Glaisher N                      13.1N  47.5E    7.0 Crater                 NLF?
            Glaisher V                      11.1N  49.9E   12.0 Crater                 NLF?
            Glaisher W                      12.4N  47.6E   46.0 Crater                 NLF?
            Glauber                         11.5N 142.6E   15.0 Crater                 IAU1976
            Glazenap                         1.6S 137.6E   43.0 Crater                 IAU1970
            Glazenap E                       1.4S 139.0E   14.0 Crater                 AW82
            Glazenap F                       1.5S 139.7E   11.0 Crater                 AW82
            Glazenap P                       5.0S 136.0E   57.0 Crater                 AW82
            Glazenap S                       2.0S 134.3E   28.0 Crater                 AW82
            Glazenap V                       0.6S 136.0E   22.0 Crater                 AW82
            Glushko                          8.4N  77.6W   43.0 Crater         N       IAU1994
            Goclenius                       10.0S  45.0E   72.0 Crater         R1651   R1651
            Goclenius B                      9.2S  44.4E    7.0 Crater                 NLF?
            Goclenius U                      9.3S  50.1E   22.0 Crater                 NLF?
            Goddard                         14.8N  89.0E   89.0 Crater         RLA1963 IAU1964
            Goddard A                       17.0N  89.6E   12.0 Crater                 RLA1963?
            Goddard B                       16.0N  86.8E   12.0 Crater                 RLA1963?
            Goddard C                       16.5N  85.1E   49.0 Crater                 RLA1963?
            Godin                            1.8N  10.2E   34.0 Crater         VL1645  S1791
            Godin A                          2.7N   9.7E    9.0 Crater                 NLF?
            Godin B                          0.7N   9.8E   12.0 Crater                 NLF?
            Godin C                          1.5N   8.4E    4.0 Crater                 NLF?
            Godin D                          1.0N   8.3E    5.0 Crater                 NLF?
            Godin E                          1.7N  12.4E    4.0 Crater                 NLF?
            Godin G                          1.9N  11.0E    7.0 Crater                 NLF?
            Goldschmidt                     73.2N   3.8W  113.0 Crater         R1651   NLF
            Goldschmidt A                   72.5N   2.5W    7.0 Crater                 NLF?
            Goldschmidt B                   70.6N   6.7W   10.0 Crater                 NLF?
            Goldschmidt C                   71.1N   6.0W    7.0 Crater                 NLF?
            Goldschmidt D                   75.3N   7.7W   14.0 Crater                 NLF?
            Golgi                           27.8N  60.0W    5.0 Crater                 IAU1976
            Golitsyn                        25.1S 105.0W   36.0 Crater                 IAU1970
            Golitsyn J                      27.6S 103.0W   20.0 Crater                 AW82
            Golovin                         39.9N 161.1E   37.0 Crater                 IAU1970
            Golovin C                       40.8N 163.1E   16.0 Crater                 AW82
            Goodacre                        32.7S  14.1E   46.0 Crater         W1926   W1926
            Goodacre B                      31.8S  13.7E    9.0 Crater                 NLF?
            Goodacre C                      32.3S  14.2E    5.0 Crater                 NLF?
            Goodacre D                      33.4S  15.0E    8.0 Crater                 NLF?
            Goodacre E                      32.9S  15.5E    6.0 Crater                 NLF?
            Goodacre F                      31.9S  14.6E    5.0 Crater                 NLF?
            Goodacre G                      33.3S  13.9E   16.0 Crater                 NLF?
            Goodacre H                      32.8S  16.1E    4.0 Crater                 NLF?
            Goodacre K                      30.9S  13.5E   11.0 Crater                 NLF?
            Goodacre P                      34.0S  16.7E   22.0 Crater                 NLF?
            Gould                           19.2S  17.2W   34.0 Crater         K1898   K1898
            Gould A                         19.2S  17.0W    3.0 Crater                 NLF?
            Gould B                         20.5S  18.4W    3.0 Crater                 NLF?
            Gould M                         17.7S  17.2W   41.0 Crater                 NLF?
            Gould N                         18.4S  17.6W   17.0 Crater                 NLF?
            Gould P                         18.8S  16.6W    8.0 Crater                 NLF?
            Gould U                         18.2S  14.9W    2.0 Crater                 NLF?
            Gould X                         20.9S  16.9W    3.0 Crater                 NLF?
            Gould Y                         20.6S  15.8W    3.0 Crater                 NLF?
            Gould Z                         19.5S  15.1W    2.0 Crater                 NLF?
            Grace                           14.2N  35.9E    1.0 Crater         X       IAU1979
            Grachev                          3.7S 108.2W   35.0 Crater                 IAU1970
            Graff                           42.4S  88.6W   36.0 Crater                 IAU1970
            Graff A                         41.2S  86.1W   21.0 Crater                 AW82
            Graff U                         42.1S  90.7W   20.0 Crater                 AW82
            Grave                           17.1S 150.3E   40.0 Crater                 IAU1976
            Greaves                         13.2N  52.7E   13.0 Crater                 IAU1976
            Green                            4.1N 132.9E   65.0 Crater                 IAU1970
            Green M                          0.9N 132.9E   37.0 Crater                 AW82
            Green P                          1.0N 131.8E   21.0 Crater                 AW82
            Green Q                          2.8N 131.7E   16.0 Crater                 AW82
            Green R                          3.4N 131.0E   33.0 Crater                 AW82
            Gregory                          2.2N 127.2E   67.0 Crater                 IAU1970
            Gregory K                        0.4S 128.5E   26.0 Crater                 AW82
            Gregory Q                        0.6N 125.7E   68.0 Crater                 AW82
            Grigg                           12.9N 129.4W   36.0 Crater                 IAU1970
            Grigg P                         11.4N 131.1W   32.0 Crater                 AW82
            Grimaldi                         5.5S  68.3W  172.0 Crater         H1647   R1651
            Grimaldi A                       5.4S  71.2W   15.0 Crater                 NLF?
            Grimaldi B                       2.9S  69.2W   22.0 Crater                 NLF?
            Grimaldi C                       2.6S  61.5W   10.0 Crater                 NLF?
            Grimaldi D                       3.7S  65.5W   22.0 Crater                 NLF?
            Grimaldi E                       3.7S  64.4W   13.0 Crater                 NLF?
            Grimaldi F                       4.0S  62.7W   29.0 Crater                 NLF?
            Grimaldi G                       7.4S  64.9W   13.0 Crater                 NLF?
            Grimaldi H                       4.9S  71.4W    9.0 Crater                 NLF?
            Grimaldi J                       2.9S  70.6W   16.0 Crater                 NLF?
            Grimaldi L                       8.5S  66.7W   19.0 Crater                 NLF?
            Grimaldi M                       8.0S  67.0W   18.0 Crater                 NLF?
            Grimaldi N                       7.6S  66.6W    8.0 Crater                 NLF?
            Grimaldi P                       8.0S  68.3W   10.0 Crater                 NLF?
            Grimaldi Q                       4.8S  64.8W   21.0 Crater                 NLF?
            Grimaldi R                       8.5S  71.2W    9.0 Crater                 NLF?
            Grimaldi S                       6.4S  65.0W   11.0 Crater                 NLF?
            Grimaldi T                       7.7S  70.9W   12.0 Crater                 NLF?
            Grimaldi X                       5.8S  72.3W    9.0 Crater                 NLF?
            Grissom                         47.0S 147.4W   58.0 Crater                 IAU1970
            Grissom K                       49.5S 145.7W   26.0 Crater                 AW82
            Grissom M                       49.1S 147.7W   38.0 Crater                 AW82
            Grotrian                        66.5S 128.3E   37.0 Crater                 IAU1970
            Grotrian X                      64.5S 125.5E   20.0 Crater                 AW82
            Grove                           40.3N  32.9E   28.0 Crater         VL1645  NLF
            Grove Y                         37.4N  31.7E    3.0 Crater                 NLF?
            Gruemberger                     66.9S  10.0W   93.0 Crater         R1651   R1651
            Gruemberger A                   67.2S  11.8W   20.0 Crater                 NLF?
            Gruemberger B                   64.6S   9.0W   31.0 Crater                 NLF?
            Gruemberger C                   65.9S  15.3W   13.0 Crater                 NLF?
            Gruemberger D                   68.1S  14.4W    5.0 Crater                 NLF?
            Gruemberger E                   63.6S   7.1W    9.0 Crater                 NLF?
            Gruemberger F                   62.9S   6.3W    7.0 Crater                 NLF?
            Gruithuisen                     32.9N  39.7W   15.0 Crater         N1876   N1876
            Gruithuisen B                   35.6N  38.8W    9.0 Crater                 NLF?
            Gruithuisen E                   37.3N  44.3W    8.0 Crater                 NLF?
            Gruithuisen F                   36.3N  37.9W    4.0 Crater                 NLF?
            Gruithuisen G                   36.6N  43.9W    6.0 Crater                 NLF?
            Gruithuisen H                   33.3N  38.4W    6.0 Crater                 NLF?
            Gruithuisen K                   35.3N  42.7W    6.0 Crater                 NLF?
            Gruithuisen M                   36.9N  43.2W    7.0 Crater                 NLF?
            Gruithuisen P                   37.1N  40.5W   11.0 Crater                 NLF?
            Gruithuisen R                   37.1N  45.3W    7.0 Crater                 NLF?
            Gruithuisen S                   37.5N  45.6W    7.0 Crater                 NLF?
            Guericke                        11.5S  14.1W   63.0 Crater         H1647   M1834
            Guericke A                      11.1S  17.3W    5.0 Crater                 NLF?
            Guericke B                      14.5S  15.3W   16.0 Crater                 NLF?
            Guericke D                      12.0S  14.6W    8.0 Crater                 NLF?
            Guericke E                      10.0S  12.0W    4.0 Crater                 NLF?
            Guericke F                      12.2S  15.3W   21.0 Crater                 NLF?
            Guericke G                      14.0S  15.0W    5.0 Crater                 NLF?
            Guericke H                      12.4S  14.2W    6.0 Crater                 NLF?
            Guericke J                      10.6S  13.4W    8.0 Crater                 NLF?
            Guericke K                      15.1S  13.3W    3.0 Crater                 NLF?
            Guericke M                      12.9S  12.5W    2.0 Crater                 NLF?
            Guericke N                      12.5S   9.9W    3.0 Crater                 NLF?
            Guericke P                      15.0S  14.6W    3.0 Crater                 NLF?
            Guericke S                      10.3S  13.3W   11.0 Crater                 NLF?
            Guillaume                       45.4N 173.4W   57.0 Crater                 IAU1979
            Guillaume B                     47.3N 172.6W   26.0 Crater                 AW82
            Guillaume D                     46.6N 170.5W   26.0 Crater                 AW82
            Guillaume F                     45.4N 169.4W   33.0 Crater                 AW82
            Guillaume J                     43.7N 170.6W   17.0 Crater                 AW82
            Gullstrand                      45.2N 129.3W   43.0 Crater                 IAU1970
            Gullstrand C                    46.8N 126.6W   15.0 Crater                 AW82
            Gum                             40.4S  88.6E   54.0 Crater                 IAU1970
            Gum S                           39.8S  85.0E   33.0 Crater                 AW82
            Gutenberg                        8.6S  41.2E   74.0 Crater         M1834   M1834
            Gutenberg A                      9.0S  39.9E   15.0 Crater                 NLF?
            Gutenberg B                      9.1S  38.3E   15.0 Crater                 NLF?
            Gutenberg C                     10.0S  41.1E   45.0 Crater                 NLF?
            Gutenberg D                     10.9S  42.8E   20.0 Crater                 NLF?
            Gutenberg E                      8.2S  42.4E   28.0 Crater                 NLF?
            Gutenberg F                     10.2S  42.6E    8.0 Crater                 NLF?
            Gutenberg G                      6.0S  40.0E   32.0 Crater                 NLF?
            Gutenberg H                      6.7S  39.0E    5.0 Crater                 NLF?
            Gutenberg K                      7.2S  40.8E    6.0 Crater                 NLF?
            Guthnick                        47.7S  93.9W   36.0 Crater                 IAU1970
            Guyot                           11.4N 117.5E   92.0 Crater                 IAU1970
            Guyot J                          8.3N 119.6E   14.0 Crater                 AW82
            Guyot K                          8.3N 118.7E   14.0 Crater                 AW82
            Guyot W                         14.0N 115.5E   21.0 Crater                 AW82
            Gyld|%en                         5.3S   0.3E   47.0 Crater         K1898   K1898
            Gyld|%en C                       5.8S   1.0E    6.0 Crater                 NLF?
            Gyld|%en K                       5.5S   0.6E    5.0 Crater                 NLF?
            H. G. Wells                     40.7N 122.8E  114.0 Crater                 IAU1970
            H. G. Wells X                   43.3N 121.3E   25.0 Crater                 AW82
            Hadley C                        25.5N   2.8E    6.0 Crater                 NLF?
            Hagecius                        59.8S  46.6E   76.0 Crater                 NLF
            Hagecius A                      58.2S  47.2E   61.0 Crater                 NLF?
            Hagecius B                      60.4S  48.9E   34.0 Crater                 NLF?
            Hagecius C                      60.7S  47.5E   24.0 Crater                 NLF?
            Hagecius D                      57.1S  47.0E   17.0 Crater                 NLF?
            Hagecius E                      63.3S  49.1E   44.0 Crater                 NLF?
            Hagecius F                      62.3S  44.8E   36.0 Crater                 NLF?
            Hagecius G                      61.8S  47.6E   30.0 Crater                 NLF?
            Hagecius H                      60.4S  50.7E   13.0 Crater                 NLF?
            Hagecius J                      62.6S  57.8E   14.0 Crater                 NLF?
            Hagecius K                      61.2S  52.0E   31.0 Crater                 NLF?
            Hagecius L                      61.5S  55.7E    8.0 Crater                 NLF?
            Hagecius M                      60.0S  52.0E   10.0 Crater                 NLF?
            Hagecius N                      60.2S  53.1E   16.0 Crater                 NLF?
            Hagecius P                      59.8S  53.2E    7.0 Crater                 NLF?
            Hagecius Q                      59.2S  53.0E   20.0 Crater                 NLF?
            Hagecius R                      58.7S  52.7E   15.0 Crater                 NLF?
            Hagecius S                      59.0S  54.6E   10.0 Crater                 NLF?
            Hagecius T                      60.6S  57.4E   14.0 Crater                 NLF?
            Hagecius V                      61.9S  58.3E   14.0 Crater                 NLF?
            Hagen                           48.3S 135.1E   55.0 Crater                 IAU1970
            Hagen C                         48.0S 135.5E   22.0 Crater                 AW82
            Hagen J                         49.0S 137.2E   47.0 Crater                 AW82
            Hagen P                         52.1S 133.7E   26.0 Crater                 AW82
            Hagen Q                         50.0S 132.7E   20.0 Crater                 AW82
            Hagen S                         48.3S 133.2E   23.0 Crater                 AW82
            Hagen V                         47.1S 132.2E   12.0 Crater                 AW82
            Hahn                            31.3N  73.6E   84.0 Crater         VL1645  M1834
            Hahn A                          29.7N  69.7E   17.0 Crater                 NLF?
            Hahn B                          31.4N  77.0E   15.0 Crater                 NLF?
            Hahn D                          27.5N  68.6E   15.0 Crater                 NLF?
            Hahn E                          27.7N  70.0E   15.0 Crater                 NLF?
            Hahn F                          32.2N  73.0E   23.0 Crater                 NLF?
            Haidinger                       39.2S  25.0W   22.0 Crater         S1878   S1878
            Haidinger A                     38.6S  24.6W    9.0 Crater                 NLF?
            Haidinger B                     39.2S  24.4W   11.0 Crater                 NLF?
            Haidinger C                     39.0S  22.1W   19.0 Crater                 NLF?
            Haidinger F                     38.7S  23.1W    5.0 Crater                 NLF?
            Haidinger G                     39.6S  22.6W   11.0 Crater                 NLF?
            Haidinger J                     37.9S  24.4W   15.0 Crater                 NLF?
            Haidinger M                     37.4S  22.0W   23.0 Crater                 NLF?
            Haidinger N                     39.4S  26.1W    6.0 Crater                 NLF?
            Haidinger P                     38.5S  25.6W    4.0 Crater                 NLF?
            Hainzel                         41.3S  33.5W   70.0 Crater         VL1645  NLF
            Hainzel A                       40.3S  33.9W   53.0 Crater                 NLF?
            Hainzel B                       38.0S  33.4W   15.0 Crater                 NLF?
            Hainzel C                       41.1S  32.8W   38.0 Crater                 NLF?
            Hainzel G                       37.5S  33.0W    5.0 Crater                 NLF?
            Hainzel H                       37.0S  33.1W   11.0 Crater                 NLF?
            Hainzel J                       37.8S  37.8W   13.0 Crater                 NLF?
            Hainzel K                       37.5S  32.3W   14.0 Crater                 NLF?
            Hainzel L                       38.1S  34.9W   16.0 Crater                 NLF?
            Hainzel N                       42.6S  40.2W   24.0 Crater                 NLF?
            Hainzel O                       38.6S  38.6W   14.0 Crater                 NLF?
            Hainzel R                       38.7S  36.4W   19.0 Crater                 NLF?
            Hainzel S                       41.1S  37.7W    8.0 Crater                 NLF?
            Hainzel T                       40.2S  37.2W    8.0 Crater                 NLF?
            Hainzel V                       41.3S  38.7W   20.0 Crater                 NLF?
            Hainzel W                       40.6S  38.7W   31.0 Crater                 NLF?
            Hainzel X                       36.7S  36.8W    5.0 Crater                 NLF?
            Hainzel Y                       40.8S  39.9W   22.0 Crater                 NLF?
            Hainzel Z                       37.7S  35.4W    5.0 Crater                 NLF?
            Haldane                          1.7S  84.1E   37.0 Crater                 IAU1973
            Hale                            74.2S  90.8E   83.0 Crater         RLA1963 IAU1964
            Hale Q                          76.5S  83.1E   24.0 Crater                 AW82
            Halfway                          9.0S  15.5E    0.0 Crater (A)             IAU1973
            Halo                             3.2S  23.4W    0.0 Crater (A)             IAU1973
            Hall                            33.7N  37.0E   35.0 Crater                 NLF
            Hall C                          34.7N  35.8E    6.0 Crater                 NLF?
            Hall J                          35.4N  36.9E    8.0 Crater                 NLF?
            Hall K                          35.5N  34.2E    8.0 Crater                 NLF?
            Hall X                          35.7N  37.8E    4.0 Crater                 NLF?
            Hall Y                          36.4N  36.9E    4.0 Crater                 NLF?
            Halley                           8.0S   5.7E   36.0 Crater         VL1645  NLF
            Halley B                         8.5S   4.5E    6.0 Crater                 NLF?
            Halley C                         9.9S   6.6E    5.0 Crater                 NLF?
            Halley G                         9.1S   5.6E    5.0 Crater                 NLF?
            Halley K                         8.6S   5.9E    5.0 Crater                 NLF?
            Hamilton                        42.8S  84.7E   57.0 Crater         RLA1963 IAU1964
            Hamilton B                      42.6S  82.1E   32.0 Crater                 RLA1963?
            Hanno                           56.3S  71.2E   56.0 Crater         M1834   M1834
            Hanno A                         53.4S  63.2E   38.0 Crater                 NLF?
            Hanno B                         52.6S  68.6E   36.0 Crater                 NLF?
            Hanno C                         55.9S  68.9E   22.0 Crater                 NLF?
            Hanno D                         59.1S  78.3E   18.0 Crater                 NLF?
            Hanno E                         59.3S  73.0E   18.0 Crater                 NLF?
            Hanno F                         52.3S  68.2E    9.0 Crater                 NLF?
            Hanno G                         58.0S  70.6E   16.0 Crater                 NLF?
            Hanno H                         57.6S  74.4E   57.0 Crater                 NLF?
            Hanno K                         53.5S  76.9E   25.0 Crater                 NLF?
            Hanno W                         54.6S  60.1E   10.0 Crater                 NLF?
            Hanno X                         55.3S  67.7E   13.0 Crater                 NLF?
            Hanno Y                         55.3S  66.0E    8.0 Crater                 NLF?
            Hanno Z                         55.1S  65.1E   10.0 Crater                 NLF?
            Hansen                          14.0N  72.5E   39.0 Crater         M1834   M1834
            Hansen A                        13.3N  74.3E   13.0 Crater                 NLF?
            Hansen B                        14.3N  79.9E   80.0 Crater                 NLF?
            Hansteen                        11.5S  52.0W   44.0 Crater                 NLF
            Hansteen A                      12.7S  52.2W    6.0 Crater                 NLF?
            Hansteen B                      12.7S  52.4W    6.0 Crater                 NLF?
            Hansteen E                      10.5S  50.5W   28.0 Crater                 NLF?
            Hansteen K                      13.9S  53.2W    3.0 Crater                 NLF?
            Hansteen L                      13.5S  52.9W    3.0 Crater                 NLF?
            Harden                           5.5N 143.5E   15.0 Crater                 IAU1976
            Harding                         43.5N  71.7W   22.0 Crater         M1834   M1834
            Harding A                       40.4N  75.5W   14.0 Crater                 NLF?
            Harding B                       41.9N  76.3W   17.0 Crater                 NLF?
            Harding C                       42.4N  74.7W    8.0 Crater                 NLF?
            Harding D                       42.9N  67.7W    7.0 Crater                 NLF?
            Harding H                       40.8N  64.4W    6.0 Crater                 NLF?
            Haret                           59.0S 176.5W   29.0 Crater                 IAU1970
            Haret C                         57.2S 172.8W   30.0 Crater                 AW82
            Haret Y                         55.7S 175.5W   27.0 Crater                 AW82
            Hargreaves                       2.2S  64.0E   16.0 Crater                 IAU1979
            Harkhebi                        39.6N  98.3E  237.0 Crater                 IAU1979
            Harkhebi H                      39.3N  99.8E   30.0 Crater                 AW82
            Harkhebi J                      37.4N 103.4E   40.0 Crater                 AW82
            Harkhebi K                      35.7N 100.8E   27.0 Crater                 AW82
            Harkhebi T                      40.1N  95.7E   16.0 Crater                 AW82
            Harkhebi U                      40.8N  97.0E   18.0 Crater                 AW82
            Harkhebi W                      43.5N  95.7E   17.0 Crater                 AW82
            Harlan                          38.5S  79.5E   65.0 Crater         N       IAU2000
            Harold                          10.9S   6.0W    2.0 Crater         X       IAU1976
            Harpalus                        52.6N  43.4W   39.0 Crater         VL1645  R1651
            Harpalus B                      56.2N  43.7W    8.0 Crater                 NLF?
            Harpalus C                      55.5N  45.1W   10.0 Crater                 NLF?
            Harpalus E                      52.7N  50.8W    7.0 Crater                 NLF?
            Harpalus G                      53.6N  52.3W   11.0 Crater                 NLF?
            Harpalus H                      53.8N  53.2W    8.0 Crater                 NLF?
            Harpalus S                      51.4N  49.9W    5.0 Crater                 NLF?
            Harpalus T                      50.0N  49.4W    4.0 Crater                 NLF?
            Harriot                         33.1N 114.3E   56.0 Crater                 IAU1970
            Harriot A                       35.6N 114.9E   63.0 Crater                 AW82
            Harriot B                       33.4N 114.5E   37.0 Crater                 AW82
            Harriot W                       35.0N 111.7E   39.0 Crater                 AW82
            Harriot X                       35.0N 113.0E   24.0 Crater                 AW82
            Hartmann                         3.2N 135.3E   61.0 Crater                 IAU1970
            Hartmann K                       1.8N 136.0E   13.0 Crater                 AW82
            Hartwig                          6.1S  80.5W   79.0 Crater         RLA1963 IAU1964
            Hartwig A                        5.7S  79.8W   10.0 Crater                 NLF?
            Hartwig B                        8.3S  77.4W   11.0 Crater                 NLF?
            Harvey                          19.5N 146.5W   60.0 Crater                 IAU1970
            Hase                            29.4S  62.5E   83.0 Crater         S1791   S1791
            Hase A                          29.0S  62.9E   14.0 Crater                 NLF?
            Hase B                          31.6S  60.3E   17.0 Crater                 NLF?
            Hase D                          31.0S  63.3E   56.0 Crater                 NLF?
            Hatanaka                        29.7N 121.5W   26.0 Crater                 IAU1970
            Hatanaka Q                      26.1N 124.2W   20.0 Crater                 AW82
            Hausen                          65.0S  88.1W  167.0 Crater         S1791   S1791
            Hayford                         12.7N 176.4W   27.0 Crater                 IAU1970
            Hayford E                       13.5N 172.1W   21.0 Crater                 AW82
            Hayford K                        9.6N 174.2W   26.0 Crater                 AW82
            Hayford L                        8.2N 175.9W   16.0 Crater                 AW82
            Hayford P                       11.1N 177.6W   21.0 Crater                 AW82
            Hayford T                       13.3N 179.5E   31.0 Crater                 AW82
            Hayford U                       14.0N 179.9E   21.0 Crater                 AW82
            Hayn                            64.7N  85.2E   87.0 Crater         RLA1963 IAU1964
            Hayn A                          62.9N  70.5E   54.0 Crater                 RLA1963?
            Hayn B                          65.2N  64.1E   25.0 Crater                 RLA1963?
            Hayn C                          65.0N  88.0E   13.0 Crater                 RLA1963?
            Hayn D                          65.5N  62.0E   20.0 Crater                 RLA1963?
            Hayn E                          67.1N  66.4E   42.0 Crater                 RLA1963?
            Hayn F                          68.0N  84.0E   59.0 Crater                 RLA1963?
            Hayn G                          67.2N  85.6E   21.0 Crater                 RLA1963?
            Hayn H                          63.4N  68.5E   14.0 Crater                 RLA1963?
            Hayn J                          66.7N  64.2E   39.0 Crater                 RLA1963?
            Hayn L                          64.4N  68.0E   27.0 Crater                 RLA1963?
            Hayn M                          62.9N  66.5E    7.0 Crater                 RLA1963?
            Hayn S                          68.0N  66.1E   10.0 Crater                 RLA1963?
            Hayn T                          68.4N  74.4E    7.0 Crater                 RLA1963?
            Head                             3.0S  23.4W    0.0 Crater (A)             IAU1973
            Healy                           32.8N 110.5W   38.0 Crater                 IAU1970
            Healy J                         30.2N 108.8W   42.0 Crater                 AW82
            Healy N                         30.9N 110.8W   42.0 Crater                 AW82
            Heaviside                       10.4S 167.1E  165.0 Crater                 IAU1970
            Heaviside B                      5.5S 169.3E   23.0 Crater                 AW82
            Heaviside C                      5.7S 171.1E   28.0 Crater                 AW82
            Heaviside D                      6.7S 171.8E   18.0 Crater                 AW82
            Heaviside E                     10.2S 169.2E   12.0 Crater                 AW82
            Heaviside F                     10.8S 172.8E   14.0 Crater                 AW82
            Heaviside K                     13.3S 168.5E  110.0 Crater                 AW82
            Heaviside N                     11.8S 166.6E   18.0 Crater                 AW82
            Heaviside Z                      8.8S 166.8E   12.0 Crater                 AW82
            Hecataeus                       21.8S  79.4E  167.0 Crater         M1834   M1834
            Hecataeus A                     22.0S  81.6E   11.0 Crater                 NLF?
            Hecataeus B                     19.5S  75.6E   69.0 Crater                 NLF?
            Hecataeus C                     19.0S  73.2E   22.0 Crater                 NLF?
            Hecataeus E                     18.5S  72.8E   13.0 Crater                 NLF?
            Hecataeus J                     22.6S  80.8E   11.0 Crater                 NLF?
            Hecataeus K                     19.1S  79.8E   76.0 Crater                 NLF?
            Hecataeus L                     19.1S  79.0E   21.0 Crater                 NLF?
            Hecataeus M                     20.9S  84.1E   18.0 Crater                 NLF?
            Hecataeus N                     21.0S  80.8E   10.0 Crater                 NLF?
            H|%ederv|%ari                   81.8S  84.0E   69.0 Crater         N       IAU1994
            Hedin                            2.0N  76.5W  150.0 Crater         RLA1963 IAU1964
            Hedin A                          5.5N  78.1W   60.0 Crater                 RLA1963?
            Hedin B                          4.4N  83.7W   20.0 Crater                 RLA1963?
            Hedin C                          4.4N  84.6W   10.0 Crater                 RLA1963?
            Hedin F                          4.0N  74.4W   19.0 Crater                 RLA1963?
            Hedin G                          3.8N  73.4W   14.0 Crater                 RLA1963?
            Hedin H                          3.0N  72.2W   11.0 Crater                 RLA1963?
            Hedin K                          2.9N  73.0W   11.0 Crater                 RLA1963?
            Hedin L                          5.1N  71.3W   10.0 Crater                 RLA1963?
            Hedin N                          4.9N  71.7W   24.0 Crater                 RLA1963?
            Hedin R                          5.3N  75.9W    7.0 Crater                 RLA1963?
            Hedin S                          5.7N  75.1W    8.0 Crater                 RLA1963?
            Hedin T                          4.2N  72.8W    7.0 Crater                 RLA1963?
            Hedin V                          5.2N  73.7W    9.0 Crater                 RLA1963?
            Hedin Z                          1.9N  78.9W   10.0 Crater                 RLA1963?
            Heinrich                        24.8N  15.3W    6.0 Crater                 IAU1979
            Heinsius                        39.5S  17.7W   64.0 Crater         S1791   S1791
            Heinsius A                      39.7S  17.6W   20.0 Crater                 NLF?
            Heinsius B                      40.0S  18.6W   23.0 Crater                 NLF?
            Heinsius C                      40.6S  17.9W   23.0 Crater                 NLF?
            Heinsius D                      38.8S  20.7W    7.0 Crater                 NLF?
            Heinsius E                      37.8S  19.5W   17.0 Crater                 NLF?
            Heinsius F                      40.5S  19.7W    7.0 Crater                 NLF?
            Heinsius G                      38.3S  14.5W   11.0 Crater                 NLF?
            Heinsius H                      37.4S  18.5W    8.0 Crater                 NLF?
            Heinsius J                      39.3S  20.4W    8.0 Crater                 NLF?
            Heinsius K                      38.5S  18.5W    5.0 Crater                 NLF?
            Heinsius L                      41.2S  18.4W    8.0 Crater                 NLF?
            Heinsius M                      40.9S  15.3W   14.0 Crater                 NLF?
            Heinsius N                      37.3S  14.7W    7.0 Crater                 NLF?
            Heinsius O                      38.8S  14.8W    5.0 Crater                 NLF?
            Heinsius P                      39.4S  13.8W   40.0 Crater                 NLF?
            Heinsius Q                      39.9S  14.5W   35.0 Crater                 NLF?
            Heinsius R                      40.2S  20.7W    5.0 Crater                 NLF?
            Heinsius S                      39.6S  16.9W    7.0 Crater                 NLF?
            Heinsius T                      39.7S  16.5W    7.0 Crater                 NLF?
            Heis                            32.4N  31.9W   14.0 Crater         S1878   S1878
            Heis A                          32.7N  31.9W    6.0 Crater                 NLF?
            Heis D                          31.7N  31.1W    8.0 Crater                 NLF?
            Helberg                         22.5N 102.2W   62.0 Crater                 IAU1970
            Helberg C                       23.4N 100.6W   70.0 Crater                 AW82
            Helberg H                       21.8N 101.2W   29.0 Crater                 AW82
            Helicon                         40.4N  23.1W   24.0 Crater         R1651   R1651
            Helicon B                       38.0N  21.3W    6.0 Crater                 NLF?
            Helicon C                       40.1N  26.2W    1.0 Crater                 NLF?
            Helicon E                       40.5N  24.1W    3.0 Crater                 NLF?
            Helicon G                       41.7N  24.9W    2.0 Crater                 NLF?
            Hell                            32.4S   7.8W   33.0 Crater         VL1645  S1791
            Hell A                          33.9S   8.4W   22.0 Crater                 NLF?
            Hell B                          30.0S   5.8W   22.0 Crater                 NLF?
            Hell C                          34.0S   6.4W   14.0 Crater                 NLF?
            Hell E                          34.5S   6.1W   10.0 Crater                 NLF?
            Hell H                          31.7S   3.8W    5.0 Crater                 NLF?
            Hell J                          29.7S   6.9W    6.0 Crater                 NLF?
            Hell K                          34.0S   5.3W    5.0 Crater                 NLF?
            Hell L                          30.6S   4.7W    6.0 Crater                 NLF?
            Hell M                          30.3S   4.7W   10.0 Crater                 NLF?
            Hell N                          30.0S   5.0W    4.0 Crater                 NLF?
            Hell P                          32.5S   5.7W    4.0 Crater                 NLF?
            Hell Q                          33.0S   4.4W    4.0 Crater                 NLF?
            Hell R                          32.7S   6.5W    3.0 Crater                 NLF?
            Hell S                          33.4S   6.2W    4.0 Crater                 NLF?
            Hell T                          33.7S   7.0W    5.0 Crater                 NLF?
            Hell U                          33.4S   9.1W    5.0 Crater                 NLF?
            Hell V                          32.8S   8.8W    7.0 Crater                 NLF?
            Hell W                          32.5S   8.6W    7.0 Crater                 NLF?
            Hell X                          32.0S   9.1W    4.0 Crater                 NLF?
            Helmert                          7.6S  87.6E   26.0 Crater                 IAU1973
            Helmholtz                       68.1S  64.1E   94.0 Crater         S1878   S1878
            Helmholtz A                     64.4S  51.5E   16.0 Crater                 NLF?
            Helmholtz B                     67.8S  68.4E   10.0 Crater                 NLF?
            Helmholtz D                     66.3S  54.3E   46.0 Crater                 NLF?
            Helmholtz F                     64.3S  60.1E   53.0 Crater                 NLF?
            Helmholtz H                     64.5S  65.2E   18.0 Crater                 NLF?
            Helmholtz J                     64.8S  67.8E   22.0 Crater                 NLF?
            Helmholtz M                     65.2S  51.1E   21.0 Crater                 NLF?
            Helmholtz N                     64.8S  50.1E   13.0 Crater                 NLF?
            Helmholtz R                     63.5S  54.7E   12.0 Crater                 NLF?
            Helmholtz S                     64.3S  56.6E   31.0 Crater                 NLF?
            Helmholtz T                     65.7S  59.7E   31.0 Crater                 NLF?
            Henderson                        4.8N 152.1E   47.0 Crater                 IAU1970
            Henderson B                      7.6N 153.2E   18.0 Crater                 AW82
            Henderson F                      4.7N 155.7E   14.0 Crater                 AW82
            Henderson G                      3.6N 155.8E   46.0 Crater                 AW82
            Henderson Q                      3.4N 151.0E   17.0 Crater                 AW82
            Hendrix                         46.6S 159.2W   18.0 Crater                 IAU1970
            Hendrix M                       48.4S 158.9W   21.0 Crater                 AW82
            Henry                           24.0S  56.8W   41.0 Crater                 IAU1970
            Henry A                         24.5S  57.1W    8.0 Crater                 NLF?
            Henry B                         24.3S  56.3W    5.0 Crater                 NLF?
            Henry D                         24.9S  59.1W    7.0 Crater                 NLF?
            Henry J                         22.8S  55.4W    5.0 Crater                 NLF?
            Henry K                         23.2S  55.5W    6.0 Crater                 NLF?
            Henry L                         25.5S  57.4W    6.0 Crater                 NLF?
            Henry M                         25.8S  57.6W   13.0 Crater                 NLF?
            Henry N                         26.1S  58.3W    9.0 Crater                 NLF?
            Henry P                         25.8S  59.0W    6.0 Crater                 NLF?
            Henry Fr|`eres                  23.5S  58.9W   42.0 Crater                 IAU1961
            Henry Fr|`eres C                24.6S  59.7W   37.0 Crater                 RLA1963?
            Henry Fr|`eres E                24.6S  60.0W    4.0 Crater                 RLA1963?
            Henry Fr|`eres G                22.8S  58.0W    4.0 Crater                 RLA1963?
            Henry Fr|`eres H                22.3S  56.6W    6.0 Crater                 RLA1963?
            Henry Fr|`eres R                21.5S  57.8W    7.0 Crater                 RLA1963?
            Henry Fr|`eres S                20.5S  56.4W    6.0 Crater                 RLA1963?
            Henyey                          13.5N 151.6W   63.0 Crater                 IAU1970
            Henyey U                        14.2N 153.0W   45.0 Crater                 AW82
            Henyey V                        14.7N 153.9W   26.0 Crater                 AW82
            Heraclides A                    40.9N  34.2W    6.0 Crater                 NLF?
            Heraclides E                    42.9N  32.7W    4.0 Crater                 NLF?
            Heraclides F                    38.5N  33.7W    3.0 Crater                 NLF?
            Heraclitus                      49.2S   6.2E   90.0 Crater         S1878   S1878
            Heraclitus A                    49.3S   4.7E    6.0 Crater                 NLF?
            Heraclitus C                    48.8S   6.3E    7.0 Crater                 NLF?
            Heraclitus D                    50.4S   5.2E   52.0 Crater                 NLF?
            Heraclitus E                    49.7S   6.7E    7.0 Crater                 NLF?
            Heraclitus K                    49.5S   3.5E   17.0 Crater                 NLF?
            Hercules                        46.7N  39.1E   69.0 Crater         VL1645  R1651
            Hercules B                      47.8N  36.6E    9.0 Crater                 NLF?
            Hercules C                      42.7N  35.3E    9.0 Crater                 NLF?
            Hercules D                      44.8N  39.7E    8.0 Crater                 NLF?
            Hercules E                      45.7N  38.5E    9.0 Crater                 NLF?
            Hercules F                      50.3N  41.7E   14.0 Crater                 NLF?
            Hercules G                      46.4N  39.2E   14.0 Crater                 NLF?
            Hercules H                      51.2N  40.9E    7.0 Crater                 NLF?
            Hercules J                      44.1N  36.4E    8.0 Crater                 NLF?
            Hercules K                      44.2N  36.9E    7.0 Crater                 NLF?
            Herigonius                      13.3S  33.9W   15.0 Crater                 NLF
            Herigonius E                    13.8S  35.6W    7.0 Crater                 NLF?
            Herigonius F                    15.5S  35.0W    5.0 Crater                 NLF?
            Herigonius G                    15.3S  32.4W    3.0 Crater                 NLF?
            Herigonius H                    17.0S  33.2W    4.0 Crater                 NLF?
            Herigonius K                    12.8S  36.4W    3.0 Crater                 NLF?
            Hermann                          0.9S  57.0W   15.0 Crater         S1791   S1791
            Hermann A                        0.4N  58.2W    4.0 Crater                 NLF?
            Hermann B                        0.3S  57.1W    5.0 Crater                 NLF?
            Hermann C                        0.2S  60.6W    3.0 Crater                 NLF?
            Hermann D                        2.3S  54.0W    3.0 Crater                 NLF?
            Hermann E                        0.1N  52.0W    4.0 Crater                 NLF?
            Hermann F                        1.3N  55.4W    5.0 Crater                 NLF?
            Hermann H                        0.9N  61.8W    4.0 Crater                 NLF?
            Hermann J                        2.6N  57.4W    4.0 Crater                 NLF?
            Hermann K                        2.4N  58.3W    3.0 Crater                 NLF?
            Hermann L                        2.4N  59.1W    3.0 Crater                 NLF?
            Hermann R                        0.6N  55.6W    3.0 Crater                 NLF?
            Hermann S                        1.0N  55.5W    4.0 Crater                 NLF?
            Hermite                         86.0N  89.9W  104.0 Crater         RLA1963 IAU1964
            Hermite A                       87.8N  47.1W   20.0 Crater                 NLF?
            Heron                            0.7N 119.8E   24.0 Crater                 IAU1976
            Heron H                          0.2N 120.7E   20.0 Crater                 AW82
            Heron Y                          1.4N 119.7E   15.0 Crater                 AW82
            Herodotus                       23.2N  49.7W   34.0 Crater         M1834   M1834
            Herodotus A                     21.5N  52.0W   10.0 Crater                 NLF?
            Herodotus B                     22.6N  55.4W    6.0 Crater                 NLF?
            Herodotus C                     21.9N  55.0W    5.0 Crater                 NLF?
            Herodotus E                     29.5N  51.8W   48.0 Crater                 NLF?
            Herodotus G                     24.7N  50.2W    4.0 Crater                 NLF?
            Herodotus H                     26.8N  50.0W    6.0 Crater                 NLF?
            Herodotus K                     24.5N  51.9W    5.0 Crater                 NLF?
            Herodotus L                     26.1N  53.2W    4.0 Crater                 NLF?
            Herodotus N                     23.7N  50.0W    4.0 Crater                 NLF?
            Herodotus R                     27.3N  53.9W    4.0 Crater                 NLF?
            Herodotus S                     27.7N  53.4W    4.0 Crater                 NLF?
            Herodotus T                     27.9N  53.8W    5.0 Crater                 NLF?
            Herschel                         5.7S   2.1W   40.0 Crater         VL1645  L1824
            Herschel C                       5.0S   3.2W   10.0 Crater                 NLF?
            Herschel D                       5.3S   4.0W   20.0 Crater                 NLF?
            Herschel F                       5.8S   4.4W    7.0 Crater                 NLF?
            Herschel G                       6.5S   2.4W   14.0 Crater                 NLF?
            Herschel H                       6.3S   3.4W    5.0 Crater                 NLF?
            Herschel J                       6.4S   4.3W    5.0 Crater                 NLF?
            Herschel N                       5.2S   1.1W   15.0 Crater                 NLF?
            Herschel X                       5.3S   2.7W    3.0 Crater                 NLF?
            Hertz                           13.4N 104.5E   90.0 Crater         BML1960 IAU1961
            Hertzsprung                      2.6N 129.2W  591.0 Crater                 IAU1970
            Hertzsprung D                    3.3N 125.4W   45.0 Crater                 AW82
            Hertzsprung H                    1.3S 124.4W   21.0 Crater                 AW82
            Hertzsprung K                    0.5S 127.6W   27.0 Crater                 AW82
            Hertzsprung L                    0.4N 127.8W   33.0 Crater                 AW82
            Hertzsprung M                    7.5S 128.9W   36.0 Crater                 AW82
            Hertzsprung P                    0.0N 129.3W   22.0 Crater                 AW82
            Hertzsprung R                    0.1S 131.8W   33.0 Crater                 AW82
            Hertzsprung S                    0.6N 132.5W   47.0 Crater                 AW82
            Hertzsprung V                    5.2N 133.3W   39.0 Crater                 AW82
            Hertzsprung X                    3.8N 129.1W   24.0 Crater                 AW82
            Hertzsprung Y                    8.8N 131.2W   23.0 Crater                 AW82
            Hesiodus                        29.4S  16.3W   42.0 Crater         M1834   M1834
            Hesiodus A                      30.1S  17.0W   15.0 Crater                 NLF?
            Hesiodus B                      27.1S  17.5W   10.0 Crater                 NLF?
            Hesiodus D                      29.3S  16.4W    5.0 Crater                 NLF?
            Hesiodus E                      27.8S  15.3W    3.0 Crater                 NLF?
            Hesiodus X                      27.3S  16.2W   24.0 Crater                 NLF?
            Hesiodus Y                      28.3S  17.2W   17.0 Crater                 NLF?
            Hesiodus Z                      28.7S  19.4W    4.0 Crater                 NLF?
            Hess                            54.3S 174.6E   88.0 Crater                 IAU1970
            Hess M                          55.9S 173.7E   27.0 Crater                 AW82
            Hess W                          52.6S 171.4E   28.0 Crater                 AW82
            Hess Z                          52.0S 174.0E   73.0 Crater                 AW82
            Hess-Apollo                     20.1N  30.7E    1.0 Crater (A)             IAU1973
            Hevelius                         2.2N  67.6W  115.0 Crater         H1647   R1651
            Hevelius A                       2.9N  68.1W   14.0 Crater                 NLF?
            Hevelius B                       1.4N  68.8W   14.0 Crater                 NLF?
            Hevelius D                       3.1N  60.8W    8.0 Crater                 NLF?
            Hevelius E                       2.9N  65.7W    9.0 Crater                 NLF?
            Hevelius J                       0.7N  69.7W   14.0 Crater                 NLF?
            Hevelius K                       1.5N  70.0W    6.0 Crater                 NLF?
            Hevelius L                       2.0N  70.3W    7.0 Crater                 NLF?
            Heymans                         75.3N 144.1W   50.0 Crater                 IAU1970
            Heymans D                       76.8N 132.3W   25.0 Crater                 AW82
            Heymans F                       75.0N 133.6W   50.0 Crater                 AW82
            Heymans T                       75.2N 155.4W   31.0 Crater                 AW82
            Heyrovsky                       39.6S  95.3W   16.0 Crater         N       IAU1985
            Hilbert                         17.9S 108.2E  151.0 Crater                 IAU1970
            Hilbert A                       15.9S 108.7E   11.0 Crater                 AW82
            Hilbert E                       16.5S 111.8E   49.0 Crater                 AW82
            Hilbert G                       19.0S 114.0E   50.0 Crater                 AW82
            Hilbert H                       18.2S 109.6E   14.0 Crater                 AW82
            Hilbert L                       21.2S 108.9E   32.0 Crater                 AW82
            Hilbert S                       18.1S 105.8E   12.0 Crater                 AW82
            Hilbert W                       17.1S 107.6E   20.0 Crater                 AW82
            Hilbert Y                       15.6S 107.5E   28.0 Crater                 AW82
            Hill                            20.9N  40.8E   16.0 Crater                 IAU1973
            Hind                             7.9S   7.4E   29.0 Crater         VL1645  NLF
            Hind C                           8.7S   7.4E    7.0 Crater                 NLF?
            Hippalus                        24.8S  30.2W   57.0 Crater         M1834   M1834
            Hippalus A                      23.8S  32.8W    8.0 Crater                 NLF?
            Hippalus B                      25.1S  30.1W    5.0 Crater                 NLF?
            Hippalus C                      24.1S  30.5W    4.0 Crater                 NLF?
            Hippalus D                      23.6S  31.9W   24.0 Crater                 NLF?
            Hipparchus                       5.1S   5.2E  138.0 Crater         H1647   R1651
            Hipparchus B                     6.9S   1.7E    5.0 Crater                 NLF?
            Hipparchus C                     7.3S   8.2E   17.0 Crater                 NLF?
            Hipparchus D                     4.5S   2.1E    5.0 Crater                 NLF?
            Hipparchus E                     4.2S   2.3E    5.0 Crater                 NLF?
            Hipparchus F                     4.2S   2.5E    9.0 Crater                 NLF?
            Hipparchus G                     5.0S   7.4E   15.0 Crater                 NLF?
            Hipparchus H                     5.4S   2.3E    5.0 Crater                 NLF?
            Hipparchus J                     7.6S   3.2E   14.0 Crater                 NLF?
            Hipparchus K                     6.9S   2.2E   12.0 Crater                 NLF?
            Hipparchus L                     6.8S   9.0E   13.0 Crater                 NLF?
            Hipparchus N                     4.8S   5.0E    6.0 Crater                 NLF?
            Hipparchus P                     4.7S   2.8E    5.0 Crater                 NLF?
            Hipparchus Q                     8.5S   2.9E    8.0 Crater                 NLF?
            Hipparchus T                     7.1S   3.6E    8.0 Crater                 NLF?
            Hipparchus U                     6.7S   3.6E    8.0 Crater                 NLF?
            Hipparchus W                     5.0S   7.8E    5.0 Crater                 NLF?
            Hipparchus X                     5.7S   4.9E   17.0 Crater                 NLF?
            Hipparchus Z                     8.5S   9.1E    6.0 Crater                 NLF?
            Hippocrates                     70.7N 145.9W   60.0 Crater                 IAU1970
            Hippocrates Q                   69.0N 148.0W   35.0 Crater                 AW82
            Hirayama                         6.1S  93.5E  132.0 Crater                 IAU1970
            Hirayama C                       4.2S  95.4E   23.0 Crater                 AW82
            Hirayama F                       5.8S  97.2E   35.0 Crater                 AW82
            Hirayama G                       6.4S  96.8E   18.0 Crater                 AW82
            Hirayama K                       8.3S  94.9E   39.0 Crater                 AW82
            Hirayama L                       9.4S  94.4E   24.0 Crater                 AW82
            Hirayama M                       9.2S  93.5E   29.0 Crater                 AW82
            Hirayama N                       7.2S  93.6E   17.0 Crater                 AW82
            Hirayama Q                       8.0S  91.3E   40.0 Crater                 AW82
            Hirayama S                       6.5S  92.3E   29.0 Crater                 AW82
            Hirayama T                       6.4S  91.5E   18.0 Crater                 AW82
            Hirayama Y                       4.5S  93.2E   50.0 Crater                 AW82
            Hoffmeister                     15.2N 136.9E   45.0 Crater                 IAU1970
            Hoffmeister D                   16.9N 140.3E   21.0 Crater                 AW82
            Hoffmeister F                   14.7N 141.0E   19.0 Crater                 AW82
            Hoffmeister N                   13.7N 136.4E   42.0 Crater                 AW82
            Hoffmeister Z                   17.8N 136.7E   29.0 Crater                 AW82
            Hogg                            33.6N 121.9E   38.0 Crater                 IAU1970
            Hogg E                          34.1N 124.9E   21.0 Crater                 AW82
            Hogg K                          31.1N 123.5E   19.0 Crater                 AW82
            Hogg P                          32.5N 121.4E   26.0 Crater                 AW82
            Hogg T                          33.9N 119.0E   27.0 Crater                 AW82
            Hohmann                         17.9S  94.1W   16.0 Crater                 IAU1970
            Hohmann Q                       21.8S  98.1W   15.0 Crater                 AW82
            Holden                          19.1S  62.5E   47.0 Crater         K1898   K1898
            Holden R                        20.7S  61.1E   18.0 Crater                 NLF?
            Holden S                        20.4S  61.5E   15.0 Crater                 NLF?
            Holden T                        19.0S  64.2E    9.0 Crater                 NLF?
            Holden V                        18.4S  62.1E   10.0 Crater                 NLF?
            Holden W                        19.0S  60.1E   12.0 Crater                 NLF?
            Holetschek                      27.6S 150.9E   38.0 Crater                 IAU1970
            Holetschek N                    30.2S 150.1E   19.0 Crater                 AW82
            Holetschek P                    30.0S 149.5E   16.0 Crater                 AW82
            Holetschek R                    29.0S 147.5E   69.0 Crater                 AW82
            Holetschek Z                    26.3S 150.9E   30.0 Crater                 AW82
            Hommel                          54.7S  33.8E  126.0 Crater         VL1645  NLF
            Hommel A                        53.7S  34.3E   51.0 Crater                 NLF?
            Hommel B                        55.3S  37.0E   33.0 Crater                 NLF?
            Hommel C                        54.8S  29.6E   53.0 Crater                 NLF?
            Hommel D                        55.8S  32.5E   28.0 Crater                 NLF?
            Hommel E                        59.0S  31.0E   14.0 Crater                 NLF?
            Hommel F                        58.4S  32.0E   21.0 Crater                 NLF?
            Hommel G                        58.1S  27.4E   30.0 Crater                 NLF?
            Hommel H                        52.6S  30.9E   43.0 Crater                 NLF?
            Hommel HA                       52.0S  30.5E    8.0 Crater                 NLF?
            Hommel J                        53.5S  27.9E   18.0 Crater                 NLF?
            Hommel K                        55.5S  27.0E   16.0 Crater                 NLF?
            Hommel L                        56.1S  27.9E   18.0 Crater                 NLF?
            Hommel M                        59.8S  27.5E    7.0 Crater                 NLF?
            Hommel N                        59.3S  28.8E   14.0 Crater                 NLF?
            Hommel O                        58.5S  28.2E    6.0 Crater                 NLF?
            Hommel P                        56.9S  31.7E   34.0 Crater                 NLF?
            Hommel Q                        56.1S  38.4E   29.0 Crater                 NLF?
            Hommel R                        52.6S  32.6E   11.0 Crater                 NLF?
            Hommel S                        56.6S  36.2E   22.0 Crater                 NLF?
            Hommel T                        57.6S  26.3E   22.0 Crater                 NLF?
            Hommel V                        53.5S  33.5E   13.0 Crater                 NLF?
            Hommel X                        60.9S  32.2E    6.0 Crater                 NLF?
            Hommel Y                        60.4S  30.8E    4.0 Crater                 NLF?
            Hommel Z                        59.8S  30.4E    4.0 Crater                 NLF?
            Hooke                           41.2N  54.9E   36.0 Crater         S1791   S1791
            Hooke D                         40.7N  55.8E   19.0 Crater                 NLF?
            Hopmann                         50.8S 160.3E   88.0 Crater                 IAU1979
            Horatio                         20.2N  30.7E    0.0 Crater (A)             IAU1973
            Hornsby                         23.8N  12.5E    3.0 Crater                 IAU1973
            Horrebow                        58.7N  40.8W   24.0 Crater         S1791   S1791
            Horrebow A                      59.2N  40.4W   25.0 Crater                 NLF?
            Horrebow B                      58.7N  42.7W   13.0 Crater                 NLF?
            Horrebow C                      56.9N  36.0W    5.0 Crater                 NLF?
            Horrebow D                      57.9N  38.7W    5.0 Crater                 NLF?
            Horrebow G                      59.7N  41.7W    8.0 Crater                 NLF?
            Horrocks                         4.0S   5.9E   30.0 Crater         VL1645  NLF
            Horrocks M                       4.0S   7.6E    5.0 Crater                 NLF?
            Horrocks U                       3.2S   4.8E    4.0 Crater                 NLF?
            Hortensius                       6.5N  28.0W   14.0 Crater         VL1645  R1651
            Hortensius A                     4.4N  30.7W   10.0 Crater                 NLF?
            Hortensius B                     5.3N  29.5W    6.0 Crater                 NLF?
            Hortensius C                     6.0N  26.7W    7.0 Crater                 NLF?
            Hortensius D                     5.4N  32.3W    6.0 Crater                 NLF?
            Hortensius E                     5.2N  25.4W   15.0 Crater                 NLF?
            Hortensius F                     7.1N  25.6W    6.0 Crater                 NLF?
            Hortensius G                     8.1N  26.1W    4.0 Crater                 NLF?
            Hortensius H                     5.9N  31.1W    6.0 Crater                 NLF?
            Houtermans                       9.4S  87.2E   29.0 Crater                 IAU1973
            Houzeau                         17.1S 123.5W   71.0 Crater                 IAU1970
            Houzeau P                       19.7S 125.0W   25.0 Crater                 AW82
            Houzeau Q                       19.0S 124.7W   18.0 Crater                 AW82
            Hubble                          22.1N  86.9E   80.0 Crater         RLA1963 IAU1964
            Hubble C                        19.6N  85.3E   50.0 Crater                 RLA1963?
            Huggins                         41.1S   1.4W   65.0 Crater                 NLF
            Huggins A                       40.6S   2.2W   11.0 Crater                 NLF?
            Humason                         30.7N  56.6W    4.0 Crater                 IAU1973
            Humboldt                        27.0S  80.9E  189.0 Crater         M1834   M1834
            Humboldt B                      30.9S  83.7E   21.0 Crater                 NLF?
            Humboldt N                      26.0S  80.5E   14.0 Crater                 NLF?
            Hume                             4.7S  90.4E   23.0 Crater                 IAU1976
            Hume A                           3.8S  90.6E   25.0 Crater                 AW82
            Hume Z                           3.6S  90.4E   14.0 Crater                 AW82
            Husband                         40.8S 147.9W   29.0 Crater                 IAU2006
            Hutton                          37.3N 168.7E   50.0 Crater                 IAU1970
            Hutton P                        35.7N 167.4E   42.0 Crater                 AW82
            Hutton W                        39.1N 166.7E   23.0 Crater                 AW82
            Huxley                          20.2N   4.5W    4.0 Crater                 IAU1973
            Huygens A                       19.7N   1.9W    6.0 Crater                 NLF?
            Hyginus                          7.8N   6.3E    9.0 Crater                 NLF
            Hyginus A                        6.3N   5.7E    8.0 Crater                 NLF?
            Hyginus B                        7.6N   5.1E    6.0 Crater                 NLF?
            Hyginus C                        7.7N   8.3E    5.0 Crater                 NLF?
            Hyginus D                       11.4N   4.3E    5.0 Crater                 NLF?
            Hyginus E                        8.7N   8.5E    4.0 Crater                 NLF?
            Hyginus F                        8.0N   8.6E    4.0 Crater                 NLF?
            Hyginus G                       11.0N   6.0E    4.0 Crater                 NLF?
            Hyginus H                        6.0N   7.0E    4.0 Crater                 NLF?
            Hyginus N                       10.5N   7.4E   11.0 Crater                 NLF?
            Hyginus S                        6.4N   8.0E   29.0 Crater                 NLF?
            Hyginus W                        9.7N   7.7E   22.0 Crater                 NLF?
            Hyginus Z                        8.0N   9.5E   28.0 Crater                 NLF?
            Hypatia                          4.3S  22.6E   40.0 Crater         R1651   R1651
            Hypatia A                        4.9S  22.2E   16.0 Crater                 NLF?
            Hypatia B                        4.6S  21.3E    5.0 Crater                 NLF?
            Hypatia C                        0.9S  20.8E   15.0 Crater                 NLF?
            Hypatia D                        3.1S  22.7E    6.0 Crater                 NLF?
            Hypatia E                        0.3S  20.4E    6.0 Crater                 NLF?
            Hypatia F                        4.1S  21.5E    8.0 Crater                 NLF?
            Hypatia G                        2.7S  23.0E    5.0 Crater                 NLF?
            Hypatia H                        4.5S  24.1E    5.0 Crater                 NLF?
            Hypatia M                        5.3S  23.4E   28.0 Crater                 NLF?
            Hypatia R                        1.9S  21.2E    4.0 Crater                 NLF?
            Ian                             25.7N   0.4W    1.0 Crater         X       IAU1976
            Ibn Battuta                      6.9S  50.4E   11.0 Crater                 IAU1976
            Ibn Firnas                       6.8N 122.3E   89.0 Crater                 IAU1976
            Ibn Firnas E                     7.5N 125.5E   42.0 Crater                 AW82
            Ibn Firnas L                     5.9N 123.0E   21.0 Crater                 AW82
            Ibn Yunus                       14.1N  91.1E   58.0 Crater                 IAU1970
            Ibn-Rushd                       11.7S  21.7E   32.0 Crater                 IAU1976
            Icarus                           5.3S 173.2W   96.0 Crater                 IAU1970
            Icarus D                         4.3S 171.2W   68.0 Crater                 AW82
            Icarus E                         5.2S 168.8W   21.0 Crater                 AW82
            Icarus H                         7.8S 169.4W   32.0 Crater                 AW82
            Icarus J                         7.3S 170.9W   32.0 Crater                 AW82
            Icarus Q                         7.8S 176.2W   41.0 Crater                 AW82
            Icarus V                         3.9S 176.0W   36.0 Crater                 AW82
            Icarus X                         2.2S 175.5W   43.0 Crater                 AW82
            Idel'son                        81.5S 110.9E   60.0 Crater                 IAU1970
            Idel'son L                      84.2S 115.8E   28.0 Crater                 AW82
            Ideler                          49.2S  22.3E   38.0 Crater         VL1645  S1878
            Ideler A                        50.1S  22.0E   11.0 Crater                 NLF?
            Ideler B                        50.6S  22.3E   11.0 Crater                 NLF?
            Ideler C                        51.2S  23.2E    7.0 Crater                 NLF?
            Ideler L                        49.2S  23.6E   36.0 Crater                 NLF?
            Ideler M                        48.8S  25.6E   20.0 Crater                 NLF?
            Il'in                           17.8S  97.5W   13.0 Crater         N       IAU1985
            Ina                             18.6N   5.3E    3.0 Crater         X       IAU1979
            Index                           26.1N   3.7E    0.0 Crater (A)             IAU1973
            Ingalls                         26.4N 153.1W   37.0 Crater                 IAU1970
            Ingalls G                       25.8N 150.4W   55.0 Crater                 AW82
            Ingalls M                       24.0N 153.0W   27.0 Crater                 AW82
            Ingalls U                       27.3N 156.2W   28.0 Crater                 AW82
            Ingalls V                       27.4N 155.3W   27.0 Crater                 AW82
            Ingalls Y                       29.7N 154.1W   23.0 Crater                 AW82
            Ingalls Z                       30.3N 153.3W   25.0 Crater                 AW82
            Inghirami                       47.5S  68.8W   91.0 Crater         M1834   M1834
            Inghirami A                     44.9S  65.3W   34.0 Crater                 NLF?
            Inghirami C                     44.1S  74.5W   18.0 Crater                 NLF?
            Inghirami F                     49.8S  71.4W   23.0 Crater                 NLF?
            Inghirami G                     51.1S  74.1W   29.0 Crater                 NLF?
            Inghirami H                     50.2S  72.7W   18.0 Crater                 NLF?
            Inghirami K                     49.6S  73.9W   23.0 Crater                 NLF?
            Inghirami L                     46.0S  61.0W   13.0 Crater                 NLF?
            Inghirami M                     45.6S  60.3W   14.0 Crater                 NLF?
            Inghirami N                     48.9S  66.8W   14.0 Crater                 NLF?
            Inghirami Q                     48.0S  72.9W   42.0 Crater                 NLF?
            Inghirami S                     49.3S  68.4W   11.0 Crater                 NLF?
            Inghirami T                     49.8S  67.8W    9.0 Crater                 NLF?
            Inghirami W                     44.4S  67.4W   68.0 Crater                 NLF?
            Innes                           27.8N 119.2E   42.0 Crater                 IAU1970
            Innes G                         26.7N 122.3E   22.0 Crater                 AW82
            Innes S                         27.6N 117.3E   33.0 Crater                 AW82
            Innes Z                         29.8N 119.2E   33.0 Crater                 AW82
            Ioffe                           14.4S 129.2W   86.0 Crater                 IAU1970
            Isabel                          28.2N  34.1W    1.0 Crater         X       IAU1979
            Isaev                           17.5S 147.5E   90.0 Crater                 IAU1976
            Isaev N                         18.7S 147.3E   24.0 Crater                 AW82
            Isidorus                         8.0S  33.5E   42.0 Crater         VL1645  R1651
            Isidorus A                       8.0S  33.2E   10.0 Crater                 NLF?
            Isidorus B                       4.5S  33.0E   30.0 Crater                 NLF?
            Isidorus C                       4.8S  31.7E    9.0 Crater                 NLF?
            Isidorus D                       4.2S  34.1E   15.0 Crater                 NLF?
            Isidorus E                       5.3S  32.6E   15.0 Crater                 NLF?
            Isidorus F                       8.7S  34.2E   20.0 Crater                 NLF?
            Isidorus G                       6.4S  31.6E    7.0 Crater                 NLF?
            Isidorus H                       3.9S  32.6E    7.0 Crater                 NLF?
            Isidorus K                       8.9S  33.3E    7.0 Crater                 NLF?
            Isidorus U                       7.9S  31.5E    6.0 Crater                 NLF?
            Isidorus V                       8.9S  30.8E    4.0 Crater                 NLF?
            Isidorus W                       9.4S  32.3E    4.0 Crater                 NLF?
            Isis                            18.9N  27.5E    1.0 Crater         X       IAU1976
            Ivan                            26.9N  43.3W    4.0 Crater         X       IAU1976
            Izsak                           23.3S 117.1E   30.0 Crater                 IAU1970
            Izsak T                         23.2S 114.8E   14.0 Crater                 AW82
            J. Herschel                     62.0N  42.0W  165.0 Crater                 NLF
            J. Herschel B                   59.9N  38.8W    7.0 Crater                 NLF?
            J. Herschel C                   62.3N  39.9W   12.0 Crater                 NLF?
            J. Herschel D                   60.4N  38.0W   10.0 Crater                 NLF?
            J. Herschel F                   58.8N  35.4W   19.0 Crater                 NLF?
            J. Herschel K                   62.9N  39.3W    8.0 Crater                 NLF?
            J. Herschel L                   61.0N  40.0W    7.0 Crater                 NLF?
            J. Herschel M                   57.3N  32.9W    9.0 Crater                 NLF?
            J. Herschel N                   60.0N  32.8W    7.0 Crater                 NLF?
            J. Herschel P                   63.5N  32.8W    6.0 Crater                 NLF?
            J. Herschel R                   62.5N  30.6W    9.0 Crater                 NLF?
            Jackson                         22.4N 163.1W   71.0 Crater                 IAU1970
            Jackson Q                       21.1N 164.7W   13.0 Crater                 AW82
            Jackson X                       25.2N 164.3W   17.0 Crater                 AW82
            Jacobi                          56.7S  11.4E   68.0 Crater         VL1645  M1834
            Jacobi A                        58.5S  16.0E   28.0 Crater                 NLF?
            Jacobi B                        54.4S  13.9E   14.0 Crater                 NLF?
            Jacobi C                        59.8S  10.6E   35.0 Crater                 NLF?
            Jacobi D                        60.8S  10.6E   21.0 Crater                 NLF?
            Jacobi E                        58.5S  11.8E   24.0 Crater                 NLF?
            Jacobi F                        58.5S   9.6E   42.0 Crater                 NLF?
            Jacobi G                        58.4S  13.9E   42.0 Crater                 NLF?
            Jacobi H                        58.5S  10.6E    9.0 Crater                 NLF?
            Jacobi J                        58.0S  10.3E   19.0 Crater                 NLF?
            Jacobi K                        56.7S  10.8E    9.0 Crater                 NLF?
            Jacobi L                        55.4S  15.4E    9.0 Crater                 NLF?
            Jacobi M                        57.8S  12.1E   10.0 Crater                 NLF?
            Jacobi N                        56.3S  11.8E    8.0 Crater                 NLF?
            Jacobi O                        55.7S  11.9E   17.0 Crater                 NLF?
            Jacobi P                        57.3S  13.8E   15.0 Crater                 NLF?
            Jacobi Q                        55.8S  14.0E    4.0 Crater                 NLF?
            Jacobi R                        55.3S  13.8E    5.0 Crater                 NLF?
            Jacobi S                        57.5S  14.9E    5.0 Crater                 NLF?
            Jacobi T                        56.0S  15.2E    6.0 Crater                 NLF?
            Jacobi U                        55.0S  13.2E    7.0 Crater                 NLF?
            Jacobi W                        56.0S  10.8E    7.0 Crater                 NLF?
            Jacobi Z                        59.1S  11.9E    5.0 Crater                 NLF?
            Jansen                          13.5N  28.7E   23.0 Crater         M1834   M1834
            Jansen D                        15.7N  28.4E    7.0 Crater                 NLF?
            Jansen E                        14.5N  27.8E    7.0 Crater                 NLF?
            Jansen G                         9.3N  26.0E    6.0 Crater                 NLF?
            Jansen H                        11.4N  28.4E    7.0 Crater                 NLF?
            Jansen K                        11.5N  29.7E    6.0 Crater                 NLF?
            Jansen L                        14.7N  30.1E    7.0 Crater                 NLF?
            Jansen R                        15.2N  28.8E   25.0 Crater                 NLF?
            Jansen T                        11.4N  33.5E    5.0 Crater                 NLF?
            Jansen U                        11.9N  32.3E    4.0 Crater                 NLF?
            Jansen W                        10.2N  29.5E    3.0 Crater                 NLF?
            Jansen Y                        13.4N  28.6E    4.0 Crater                 NLF?
            Jansky                           8.5N  89.5E   72.0 Crater         RLA1963 IAU1964
            Jansky D                         9.5N  91.2E   20.0 Crater                 RLA1963?
            Jansky F                         8.8N  92.2E   50.0 Crater                 RLA1963?
            Jansky H                         7.8N  91.3E   11.0 Crater                 RLA1963?
            Janssen                         45.4S  40.3E  199.0 Crater         S1878   S1878
            Janssen B                       43.2S  34.4E   22.0 Crater                 NLF?
            Janssen C                       42.8S  34.9E    7.0 Crater                 NLF?
            Janssen D                       48.5S  41.1E   29.0 Crater                 NLF?
            Janssen E                       48.8S  39.9E   25.0 Crater                 NLF?
            Janssen F                       49.7S  41.9E   36.0 Crater                 NLF?
            Janssen H                       45.3S  41.7E   11.0 Crater                 NLF?
            Janssen J                       43.4S  36.6E   30.0 Crater                 NLF?
            Janssen K                       46.1S  42.3E   16.0 Crater                 NLF?
            Janssen L                       45.9S  43.4E   12.0 Crater                 NLF?
            Janssen M                       41.8S  35.4E   16.0 Crater                 NLF?
            Janssen N                       41.4S  32.2E    5.0 Crater                 NLF?
            Janssen P                       45.3S  39.7E    5.0 Crater                 NLF?
            Janssen Q                       46.2S  39.4E    5.0 Crater                 NLF?
            Janssen R                       48.1S  38.7E   17.0 Crater                 NLF?
            Janssen S                       50.4S  41.9E    8.0 Crater                 NLF?
            Janssen T                       48.8S  42.2E   31.0 Crater                 NLF?
            Janssen X                       42.9S  33.3E   24.0 Crater                 NLF?
            Jarvis                          34.9S 148.9W   38.0 Crater         N       IAU1988
            Jeans                           55.8S  91.4E   79.0 Crater         RLA1963 IAU1964
            Jeans B                         52.4S  94.8E   11.0 Crater                 RLA1963?
            Jeans G                         56.0S  93.3E   22.0 Crater                 RLA1963?
            Jeans N                         58.7S  90.5E   64.0 Crater                 RLA1963?
            Jeans S                         56.8S  86.8E   56.0 Crater                 RLA1963?
            Jeans U                         54.7S  86.5E   57.0 Crater                 RLA1963?
            Jeans X                         53.5S  89.4E   44.0 Crater                 RLA1963?
            Jeans Y                         51.2S  90.5E   17.0 Crater                 RLA1963?
            Jehan                           20.7N  31.9W    5.0 Crater         X       IAU1976
            Jenkins                          0.3N  78.1E   38.0 Crater                 IAU1982
            Jenner                          42.1S  95.9E   71.0 Crater                 IAU1970
            Jenner M                        46.0S  95.5E   11.0 Crater                 AW82
            Jenner X                        37.4S  93.7E   13.0 Crater                 AW82
            Jenner Y                        38.6S  94.7E   29.0 Crater                 AW82
            Jerik                           18.5N  27.6E    1.0 Crater         X       IAU1976
            Joliot                          25.8N  93.1E  164.0 Crater         BML1960 IAU1961
            Joliot P                        22.2N  91.9E   12.0 Crater                 AW82
            Jomo                            24.4N   2.4E    7.0 Crater         X       IAU1979
            Jos|%e                          12.7S   1.6W    2.0 Crater         X       IAU1976
            Joule                           27.3N 144.2W   96.0 Crater                 IAU1970
            Joule K                         25.8N 141.9W   16.0 Crater                 AW82
            Joule L                         26.1N 144.1W   69.0 Crater                 AW82
            Joule T                         27.7N 148.2W   37.0 Crater                 AW82
            Joy                             25.0N   6.6E    5.0 Crater                 IAU1973
            Jules Verne                     35.0S 147.0E  143.0 Crater         BML1960 IAU1961
            Jules Verne C                   33.2S 149.7E   30.0 Crater                 AW82
            Jules Verne G                   35.1S 150.0E   42.0 Crater                 AW82
            Jules Verne P                   38.0S 145.1E   62.0 Crater                 AW82
            Jules Verne R                   36.9S 140.9E   49.0 Crater                 AW82
            Jules Verne X                   32.1S 145.2E   15.0 Crater                 AW82
            Jules Verne Y                   31.3S 146.0E   30.0 Crater                 AW82
            Jules Verne Z                   32.5S 146.8E   20.0 Crater                 AW82
            Julienne                        26.0N   3.2E    2.0 Crater         X       IAU1976
            Julius Caesar                    9.0N  15.4E   90.0 Crater         VL1645  NLF
            Julius Caesar A                  7.6N  14.4E   13.0 Crater                 NLF?
            Julius Caesar B                  9.8N  14.0E    7.0 Crater                 NLF?
            Julius Caesar C                  7.3N  15.4E    5.0 Crater                 NLF?
            Julius Caesar D                  7.2N  16.5E    5.0 Crater                 NLF?
            Julius Caesar F                 11.5N  12.9E   19.0 Crater                 NLF?
            Julius Caesar G                 10.2N  15.7E   20.0 Crater                 NLF?
            Julius Caesar H                  8.8N  13.6E    3.0 Crater                 NLF?
            Julius Caesar J                  9.2N  13.8E    3.0 Crater                 NLF?
            Julius Caesar P                 11.2N  14.1E   37.0 Crater                 NLF?
            Julius Caesar Q                 12.9N  14.0E   32.0 Crater                 NLF?
            Kaiser                          36.5S   6.5E   52.0 Crater         R1651   S1878
            Kaiser A                        36.3S   7.3E   20.0 Crater                 NLF?
            Kaiser B                        36.6S   5.6E    6.0 Crater                 NLF?
            Kaiser C                        36.5S   9.7E   12.0 Crater                 NLF?
            Kaiser D                        37.0S   7.4E    5.0 Crater                 NLF?
            Kaiser E                        34.9S   7.1E    5.0 Crater                 NLF?
            Kaiser R                        34.3S   7.2E    4.0 Crater                 NLF?
            Kamerlingh Onnes                15.0N 115.8W   66.0 Crater                 IAU1970
            Kane                            63.1N  26.1E   54.0 Crater         S1878   S1878
            Kane A                          61.2N  27.0E    5.0 Crater                 NLF?
            Kane F                          59.6N  23.1E    7.0 Crater                 NLF?
            Kane G                          59.2N  25.3E   10.0 Crater                 NLF?
            Kant                            10.6S  20.1E   33.0 Crater         VL1645  M1834
            Kant B                           9.7S  18.6E   16.0 Crater                 NLF?
            Kant C                           9.3S  22.1E   20.0 Crater                 NLF?
            Kant D                          11.5S  18.7E   52.0 Crater                 NLF?
            Kant G                           9.2S  19.5E   32.0 Crater                 NLF?
            Kant H                           9.1S  20.8E    7.0 Crater                 NLF?
            Kant N                           9.9S  19.7E   10.0 Crater                 NLF?
            Kant O                          12.0S  17.2E    7.0 Crater                 NLF?
            Kant P                          10.8S  17.4E    5.0 Crater                 NLF?
            Kant Q                          13.1S  18.8E    5.0 Crater                 NLF?
            Kant S                          11.5S  19.7E    5.0 Crater                 NLF?
            Kant T                          11.3S  20.2E    5.0 Crater                 NLF?
            Kant Z                          10.4S  17.5E    3.0 Crater                 NLF?
            Kao                              6.7S  87.6E   34.0 Crater                 IAU1982
            Kapteyn                         10.8S  70.6E   49.0 Crater         RLA1963 IAU1964
            Kapteyn A                       14.2S  71.3E   31.0 Crater                 NLF?
            Kapteyn B                       15.6S  71.0E   39.0 Crater                 NLF?
            Kapteyn C                       13.3S  70.2E   48.0 Crater                 NLF?
            Kapteyn D                       14.5S  70.6E   12.0 Crater                 NLF?
            Kapteyn E                        8.8S  69.3E   31.0 Crater                 NLF?
            Kapteyn F                       14.5S  70.3E    9.0 Crater                 NLF?
            Kapteyn K                       13.1S  71.9E    8.0 Crater                 NLF?
            Kapteyn Z                       11.2S  72.5E    6.0 Crater                 NLF?
            Karima                          25.9S 103.0E    3.0 Crater         X       IAU1976
            Karpinskiy                      73.3N 166.3E   92.0 Crater                 IAU1970
            Karpinskiy J                    71.5N 175.1E   25.0 Crater                 AW82
            Karrer                          52.1S 141.8W   51.0 Crater                 IAU1979
            Kasper                           8.3N 122.1E   12.0 Crater         X       IAU1979
            K|:astner                        6.8S  78.5E  108.0 Crater         S1791   S1791
            K|:astner A                      4.5S  77.3E   25.0 Crater                 NLF?
            K|:astner B                      6.3S  80.7E   20.0 Crater                 NLF?
            K|:astner C                      8.0S  76.9E   19.0 Crater                 NLF?
            K|:astner E                      8.1S  77.6E   10.0 Crater                 NLF?
            K|:astner G                      4.2S  79.0E   72.0 Crater                 NLF?
            K|:astner R                      6.9S  82.3E   17.0 Crater                 NLF?
            K|:astner S                      8.0S  83.2E   30.0 Crater                 NLF?
            Katchalsky                       5.9N 116.1E   32.0 Crater                 IAU1973
            Kathleen                        25.4N   0.7W    5.0 Crater         X       IAU1976
            Kearons                         11.4S 112.6W   23.0 Crater                 IAU1970
            Kearons U                       10.5S 115.9W   13.0 Crater                 AW82
            Keeler                          10.2S 161.9E  160.0 Crater                 IAU1970
            Keeler L                        13.3S 163.2E   71.0 Crater                 AW82
            Keeler S                        11.4S 158.0E   30.0 Crater                 AW82
            Keeler U                         9.1S 156.9E   29.0 Crater                 AW82
            Keeler V                         8.9S 158.3E   53.0 Crater                 AW82
            Kekul|%e                        16.4N 138.1W   94.0 Crater                 IAU1970
            Kekul|%e K                      13.9N 135.8W   16.0 Crater                 AW82
            Kekul|%e M                      12.2N 137.4W   19.0 Crater                 AW82
            Kekul|%e S                      15.4N 143.0W   21.0 Crater                 AW82
            Kekul|%e V                      18.4N 142.0W   67.0 Crater                 AW82
            Keldysh                         51.2N  43.6E   33.0 Crater         VL1645  IAU1982
            Kelvin A                        27.5S  31.5W    8.0 Crater                 NLF?
            Kelvin B                        27.6S  32.1W    7.0 Crater                 NLF?
            Kelvin C                        27.6S  32.5W    5.0 Crater                 NLF?
            Kelvin D                        27.9S  33.1W    7.0 Crater                 NLF?
            Kelvin E                        26.7S  31.7W    4.0 Crater                 NLF?
            Kelvin F                        26.7S  35.6W    4.0 Crater                 NLF?
            Kelvin G                        26.2S  33.9W    3.0 Crater                 NLF?
            Kep|%inski                      28.8N 126.6E   31.0 Crater                 IAU1979
            Kep|%inski C                    30.2N 128.0E   20.0 Crater                 AW82
            Kep|%inski N                    26.6N 126.2E   40.0 Crater                 AW82
            Kep|%inski W                    30.1N 124.9E   25.0 Crater                 AW82
            Kepler                           8.1N  38.0W   31.0 Crater         VL1645  R1651
            Kepler A                         7.2N  36.1W   11.0 Crater                 NLF?
            Kepler B                         7.8N  35.3W    7.0 Crater                 NLF?
            Kepler C                        10.0N  41.8W   11.0 Crater                 NLF?
            Kepler D                         7.4N  41.9W   10.0 Crater                 NLF?
            Kepler E                         7.4N  43.9W    6.0 Crater                 NLF?
            Kepler F                         8.3N  39.0W    7.0 Crater                 NLF?
            Kepler P                        12.2N  34.0W    4.0 Crater                 NLF?
            Kepler T                         9.0N  34.6W    3.0 Crater                 NLF?
            Khvol'son                       13.8S 111.4E   54.0 Crater                 IAU1970
            Khvol'son C                     13.5S 111.9E   15.0 Crater                 AW82
            Kibal'chich                      3.0N 146.5W   92.0 Crater                 IAU1970
            Kibal'chich H                    2.0N 144.2W   40.0 Crater                 AW82
            Kibal'chich Q                    0.7S 149.0W   25.0 Crater                 AW82
            Kibal'chich R                    0.6N 150.1W   29.0 Crater                 AW82
            Kidinnu                         35.9N 122.9E   56.0 Crater                 IAU1970
            Kidinnu E                       36.3N 124.5E   60.0 Crater                 AW82
            Kies                            26.3S  22.5W   45.0 Crater         S1791   S1791
            Kies A                          28.3S  22.7W   16.0 Crater                 NLF?
            Kies B                          28.7S  21.9W    9.0 Crater                 NLF?
            Kies C                          26.0S  26.1W    5.0 Crater                 NLF?
            Kies D                          24.9S  18.5W    6.0 Crater                 NLF?
            Kies E                          28.7S  22.7W    6.0 Crater                 NLF?
            Kiess                            6.4S  84.0E   63.0 Crater                 IAU1973
            Kimura                          57.1S 118.4E   28.0 Crater                 IAU1970
            Kinau                           60.8S  15.1E   41.0 Crater         N1876   N1876
            Kinau A                         62.1S  20.0E   35.0 Crater                 NLF?
            Kinau B                         61.6S  19.2E    8.0 Crater                 NLF?
            Kinau C                         60.6S  20.5E   30.0 Crater                 NLF?
            Kinau D                         60.6S  18.5E   27.0 Crater                 NLF?
            Kinau E                         60.1S  20.0E    7.0 Crater                 NLF?
            Kinau F                         62.1S  13.5E   10.0 Crater                 NLF?
            Kinau G                         61.5S  12.7E   25.0 Crater                 NLF?
            Kinau H                         59.8S  19.7E    6.0 Crater                 NLF?
            Kinau J                         59.6S  16.0E    5.0 Crater                 NLF?
            Kinau K                         58.6S  18.1E   10.0 Crater                 NLF?
            Kinau L                         59.3S  18.8E   11.0 Crater                 NLF?
            Kinau M                         60.4S  14.3E   12.0 Crater                 NLF?
            Kinau N                         61.4S  15.5E    7.0 Crater                 NLF?
            Kinau P                         61.4S  17.4E    5.0 Crater                 NLF?
            Kinau Q                         62.4S  21.1E   11.0 Crater                 NLF?
            Kinau R                         59.9S  11.6E   61.0 Crater                 NLF?
            King                             5.0N 120.5E   76.0 Crater                 IAU1970
            King J                           3.2N 121.8E   14.0 Crater                 AW82
            King Y                           6.5N 119.8E   48.0 Crater                 AW82
            Kira                            17.6S 132.8E    3.0 Crater         X       IAU1976
            Kirch                           39.2N   5.6W   11.0 Crater                 NLF
            Kirch E                         36.5N   6.9W    3.0 Crater                 NLF?
            Kirch F                         38.0N   6.0W    4.0 Crater                 NLF?
            Kirch G                         37.4N   8.1W    3.0 Crater                 NLF?
            Kirch H                         39.0N   7.0W    3.0 Crater                 NLF?
            Kirch K                         39.2N   4.0W    3.0 Crater                 NLF?
            Kirch M                         39.5N   9.9W    3.0 Crater                 NLF?
            Kircher                         67.1S  45.3W   72.0 Crater         VL1645  R1651
            Kircher A                       66.1S  42.1W   29.0 Crater                 NLF?
            Kircher B                       65.0S  43.1W   12.0 Crater                 NLF?
            Kircher C                       66.9S  37.5W   11.0 Crater                 NLF?
            Kircher D                       67.5S  49.8W   39.0 Crater                 NLF?
            Kircher E                       69.1S  50.1W   20.0 Crater                 NLF?
            Kircher F                       66.1S  38.9W   10.0 Crater                 NLF?
            Kirchhoff                       30.3N  38.8E   24.0 Crater         S1878   S1878
            Kirchhoff C                     30.3N  39.7E   23.0 Crater                 NLF?
            Kirchhoff E                     30.7N  40.4E   26.0 Crater                 NLF?
            Kirchhoff F                     31.5N  40.9E   23.0 Crater                 NLF?
            Kirchhoff G                     29.8N  40.2E   22.0 Crater                 NLF?
            Kirkwood                        68.8N 156.1W   67.0 Crater                 IAU1970
            Kirkwood T                      69.4N 165.2W   19.0 Crater                 AW82
            Kirkwood Y                      72.2N 157.5W   19.0 Crater                 AW82
            Kiva                             8.6S  15.5E    1.0 Crater (A)             IAU1973
            Klaproth                        69.8S  26.0W  119.0 Crater         M1834   M1834
            Klaproth A                      68.2S  21.6W   30.0 Crater                 NLF?
            Klaproth B                      72.0S  24.7W   11.0 Crater                 NLF?
            Klaproth C                      69.1S  19.5W    8.0 Crater                 NLF?
            Klaproth D                      70.2S  20.4W    8.0 Crater                 NLF?
            Klaproth G                      68.6S  31.2W   30.0 Crater                 NLF?
            Klaproth H                      69.4S  33.1W   41.0 Crater                 NLF?
            Klaproth L                      70.1S  36.5W   11.0 Crater                 NLF?
            Klein                           12.0S   2.6E   44.0 Crater         K1898   K1898
            Klein A                         11.4S   3.0E    9.0 Crater                 NLF?
            Klein B                         12.5S   1.8E    6.0 Crater                 NLF?
            Klein C                         12.5S   2.6E    6.0 Crater                 NLF?
            Kleymenov                       32.4S 140.2W   55.0 Crater                 IAU1970
            Klute                           37.2N 141.3W   75.0 Crater                 IAU1970
            Klute M                         35.1N 141.1W   24.0 Crater                 AW82
            Klute W                         38.2N 143.0W   13.0 Crater                 AW82
            Klute X                         39.5N 143.0W   40.0 Crater                 AW82
            Knox-Shaw                        5.3N  80.2E   12.0 Crater                 IAU1973
            Koch                            42.8S 150.1E   95.0 Crater                 IAU1970
            Koch R                          44.5S 146.3E   20.0 Crater                 AW82
            Koch U                          42.3S 147.4E   25.0 Crater                 AW82
            Kohlsch|:utter                  14.4N 154.0E   53.0 Crater                 IAU1970
            Kohlsch|:utter N                11.6N 153.7E   27.0 Crater                 AW82
            Kohlsch|:utter Q                13.2N 153.0E   20.0 Crater                 AW82
            Kohlsch|:utter W                16.3N 151.2E   32.0 Crater                 AW82
            Kolh|:orster                    11.2N 114.6W   97.0 Crater                 IAU1970
            Komarov                         24.7N 152.5E   78.0 Crater                 IAU1970
            Kondratyuk                      14.9S 115.5E  108.0 Crater                 IAU1970
            Kondratyuk A                    14.2S 115.5E   25.0 Crater                 AW82
            Kondratyuk Q                    15.7S 114.7E   28.0 Crater                 AW82
            K|:onig                         24.1S  24.6W   23.0 Crater         M1935   M1935
            K|:onig A                       24.7S  24.0W    3.0 Crater                 NLF?
            Konoplev                        28.5S 125.5W   25.0 Crater         N       IAU1991
            Konstantinov                    19.8N 158.4E   66.0 Crater                 IAU1970
            Kopff                           17.4S  89.6W   41.0 Crater                 IAU1970
            Kopff B                         16.9S  86.2W    8.0 Crater                 AW82
            Kopff C                         18.3S  86.1W   14.0 Crater                 AW82
            Kopff D                         19.9S  89.8W   13.0 Crater                 AW82
            Kopff E                         16.0S  89.8W   12.0 Crater                 AW82
            Korolev                          4.0S 157.4W  437.0 Crater                 IAU1970
            Korolev B                        3.9S 156.1W   22.0 Crater                 AW82
            Korolev C                        1.3S 153.2W   68.0 Crater                 AW82
            Korolev D                        0.8S 151.5W   26.0 Crater                 AW82
            Korolev E                        3.9S 153.2W   37.0 Crater                 AW82
            Korolev F                        4.6S 152.6W   31.0 Crater                 AW82
            Korolev G                        6.0S 153.3W   12.0 Crater                 AW82
            Korolev L                        6.0S 156.7W   30.0 Crater                 AW82
            Korolev M                        8.8S 157.3W   58.0 Crater                 AW82
            Korolev P                        8.1S 159.9W   17.0 Crater                 AW82
            Korolev T                        4.4S 157.7W   22.0 Crater                 AW82
            Korolev V                        1.3S 161.8W   20.0 Crater                 AW82
            Korolev W                        0.4S 160.3W   34.0 Crater                 AW82
            Korolev X                        0.6N 159.0W   28.0 Crater                 AW82
            Korolev Y                        0.7S 158.2W   19.0 Crater                 AW82
            Kosberg                         20.2S 149.6E   15.0 Crater                 IAU1976
            Kostinskiy                      14.7N 118.8E   75.0 Crater                 IAU1970
            Kostinskiy B                    16.3N 119.9E   20.0 Crater                 AW82
            Kostinskiy D                    16.0N 122.8E   32.0 Crater                 AW82
            Kostinskiy E                    15.1N 122.4E   26.0 Crater                 AW82
            Kostinskiy W                    17.2N 115.6E   24.0 Crater                 AW82
            Koval'skiy                      21.9S 101.0E   49.0 Crater                 IAU1970
            Koval'skiy B                    20.8S 101.5E   28.0 Crater                 AW82
            Koval'skiy D                    20.9S 103.0E   19.0 Crater                 AW82
            Koval'skiy H                    22.5S 102.6E   37.0 Crater                 AW82
            Koval'skiy M                    23.8S 100.8E   18.0 Crater                 AW82
            Koval'skiy P                    22.4S 100.3E   25.0 Crater                 AW82
            Koval'skiy Q                    23.5S  98.7E   35.0 Crater                 AW82
            Koval'skiy U                    21.1S  98.1E   25.0 Crater                 AW82
            Koval'skiy Y                    20.8S 100.5E   19.0 Crater                 AW82
            Kovalevskaya                    30.8N 129.6W  115.0 Crater                 IAU1970
            Kovalevskaya D                  32.7N 124.4W   21.0 Crater                 AW82
            Kovalevskaya Q                  29.4N 131.0W  101.0 Crater                 AW82
            Kozyrev                         46.8S 129.3E   65.0 Crater         N       IAU1997
            Krafft                          16.6N  72.6W   51.0 Crater         S1791   S1791
            Krafft C                        16.4N  72.3W   13.0 Crater                 NLF?
            Krafft D                        15.1N  73.3W   12.0 Crater                 NLF?
            Krafft E                        15.9N  71.7W   10.0 Crater                 NLF?
            Krafft H                        17.0N  77.8W   15.0 Crater                 NLF?
            Krafft K                        16.5N  74.5W   11.0 Crater                 NLF?
            Krafft L                        16.0N  76.3W   20.0 Crater                 NLF?
            Krafft M                        17.8N  75.5W   10.0 Crater                 NLF?
            Krafft U                        17.2N  64.7W    3.0 Crater                 NLF?
            Kramarov                         2.3S  98.8W   20.0 Crater         N       IAU1991
            Kramers                         53.6N 127.6W   61.0 Crater                 IAU1970
            Kramers C                       55.0N 125.6W   60.0 Crater                 AW82
            Kramers M                       50.4N 126.9W   30.0 Crater                 AW82
            Kramers S                       52.8N 132.2W   26.0 Crater                 AW82
            Kramers U                       54.2N 132.4W   38.0 Crater                 AW82
            Krasnov                         29.9S  79.6W   40.0 Crater         RLA1963 IAU1964
            Krasnov A                       29.9S  80.4W   10.0 Crater                 RLA1963?
            Krasnov B                       29.4S  80.2W   13.0 Crater                 RLA1963?
            Krasnov C                       26.2S  81.4W   12.0 Crater                 RLA1963?
            Krasnov D                       33.9S  80.1W   13.0 Crater                 RLA1963?
            Krasovskiy                       3.9N 175.5W   59.0 Crater                 IAU1970
            Krasovskiy C                     6.1N 173.6W   23.0 Crater                 AW82
            Krasovskiy F                     3.7N 172.5W   15.0 Crater                 AW82
            Krasovskiy H                     2.7N 171.4W   46.0 Crater                 AW82
            Krasovskiy J                     3.2N 174.1W   32.0 Crater                 AW82
            Krasovskiy L                     0.4S 174.8W   58.0 Crater                 AW82
            Krasovskiy N                     1.0N 176.0W   22.0 Crater                 AW82
            Krasovskiy P                     0.8N 177.3W   41.0 Crater                 AW82
            Krasovskiy T                     3.6N 177.1W  100.0 Crater                 AW82
            Krasovskiy Z                     5.9N 175.6W   15.0 Crater                 AW82
            Kreiken                          9.0S  84.6E   23.0 Crater                 IAU1973
            Krieger                         29.0N  45.6W   22.0 Crater         VL1645  NLF
            Krieger C                       27.7N  44.6W    4.0 Crater                 NLF?
            Krogh                            9.4N  65.7E   19.0 Crater                 IAU1976
            Krusenstern                     26.2S   5.9E   47.0 Crater         S1878   S1878
            Krusenstern A                   26.9S   5.9E    5.0 Crater                 NLF?
            Krylov                          35.6N 165.8W   49.0 Crater                 IAU1970
            Krylov A                        38.9N 165.1W   63.0 Crater                 AW82
            Krylov B                        37.3N 163.6W   40.0 Crater                 AW82
            Kugler                          53.8S 103.7E   65.0 Crater                 IAU1970
            Kugler N                        56.3S 102.8E   42.0 Crater                 AW82
            Kugler R                        55.5S  98.6E   13.0 Crater                 AW82
            Kugler U                        54.0S 101.5E   37.0 Crater                 AW82
            Kuiper                           9.8S  22.7W    6.0 Crater                 IAU1976
            Kulik                           42.4N 154.5W   58.0 Crater                 IAU1970
            Kulik J                         40.6N 151.4W   46.0 Crater                 AW82
            Kulik K                         39.1N 151.6W   42.0 Crater                 AW82
            Kulik L                         40.8N 153.5W   33.0 Crater                 AW82
            Kundt                           11.5S  11.5W   10.0 Crater                 IAU1976
            Kunowsky                         3.2N  32.5W   18.0 Crater         VL1645  S1878
            Kunowsky C                       0.2S  32.4W    3.0 Crater                 NLF?
            Kunowsky D                       1.5N  28.8W    5.0 Crater                 NLF?
            Kunowsky G                       1.7N  30.7W    4.0 Crater                 NLF?
            Kunowsky H                       1.1N  30.0W    3.0 Crater                 NLF?
            Kuo Shou Ching                   8.4N 133.7W   34.0 Crater                 IAU1970
            Kurchatov                       38.3N 142.1E  106.0 Crater         BML1960 IAU1961
            Kurchatov T                     38.0N 138.0E   27.0 Crater                 AW82
            Kurchatov W                     40.4N 140.4E   33.0 Crater                 AW82
            Kurchatov X                     41.3N 139.9E   17.0 Crater                 AW82
            Kurchatov Z                     41.0N 141.8E   27.0 Crater                 AW82
            L. Clark                        43.7S 147.7W   16.0 Crater                 IAU2006?
            la Caille                       23.8S   1.1E   67.0 Crater         S1791   S1791
            la Caille A                     22.8S   0.4E    8.0 Crater                 NLF?
            la Caille B                     20.9S   1.4E    7.0 Crater                 NLF?
            la Caille C                     21.2S   1.4E   15.0 Crater                 NLF?
            la Caille D                     23.6S   2.2E   12.0 Crater                 NLF?
            la Caille E                     23.5S   2.8E   27.0 Crater                 NLF?
            la Caille F                     23.6S   3.4E    8.0 Crater                 NLF?
            la Caille G                     20.5S   2.0E   11.0 Crater                 NLF?
            la Caille H                     24.7S   0.8E    6.0 Crater                 NLF?
            la Caille J                     22.5S   0.9E    5.0 Crater                 NLF?
            la Caille K                     21.0S   0.6E   30.0 Crater                 NLF?
            la Caille L                     24.6S   1.4E    5.0 Crater                 NLF?
            la Caille M                     22.3S   1.6E   15.0 Crater                 NLF?
            la Caille N                     21.9S   1.3E   10.0 Crater                 NLF?
            la Caille P                     22.5S   0.0E   25.0 Crater                 NLF?
            la Condamine                    53.4N  28.2W   37.0 Crater         S1791   S1791
            la Condamine A                  54.4N  30.1W   18.0 Crater                 NLF?
            la Condamine B                  58.8N  31.5W   17.0 Crater                 NLF?
            la Condamine C                  52.4N  30.2W   10.0 Crater                 NLF?
            la Condamine D                  53.5N  30.8W   10.0 Crater                 NLF?
            la Condamine E                  57.7N  31.9W    8.0 Crater                 NLF?
            la Condamine F                  57.3N  31.0W    7.0 Crater                 NLF?
            la Condamine G                  54.8N  28.1W    8.0 Crater                 NLF?
            la Condamine H                  53.1N  26.6W    6.0 Crater                 NLF?
            la Condamine J                  56.0N  19.3W    7.0 Crater                 NLF?
            la Condamine K                  51.9N  25.5W    7.0 Crater                 NLF?
            la Condamine L                  53.6N  26.7W    6.0 Crater                 NLF?
            la Condamine M                  54.2N  26.6W    7.0 Crater                 NLF?
            la Condamine N                  53.8N  25.6W    9.0 Crater                 NLF?
            la Condamine O                  55.1N  25.6W    7.0 Crater                 NLF?
            la Condamine P                  52.9N  23.5W    6.0 Crater                 NLF?
            la Condamine Q                  52.6N  23.9W    9.0 Crater                 NLF?
            la Condamine R                  55.0N  21.3W    7.0 Crater                 NLF?
            la Condamine S                  57.3N  25.2W    4.0 Crater                 NLF?
            la Condamine T                  59.2N  29.6W    6.0 Crater                 NLF?
            la Condamine U                  54.5N  22.7W    7.0 Crater                 NLF?
            la Condamine V                  54.5N  24.1W    6.0 Crater                 NLF?
            la Condamine X                  57.2N  21.4W    4.0 Crater                 NLF?
            la Hire A                       28.5N  23.4W    5.0 Crater                 NLF?
            la Hire B                       27.7N  23.0W    4.0 Crater                 NLF?
            la P|%erouse                    10.7S  76.3E   77.0 Crater         M1834   M1834
            la P|%erouse A                   9.3S  74.7E    4.0 Crater                 NLF?
            la P|%erouse D                  11.2S  76.6E    7.0 Crater                 NLF?
            la P|%erouse E                  10.2S  78.5E   34.0 Crater                 NLF?
            Lacchini                        41.7N 107.5W   58.0 Crater                 IAU1970
            Lacroix                         37.9S  59.0W   37.0 Crater         N1876   N1876
            Lacroix A                       35.1S  55.2W   13.0 Crater                 NLF?
            Lacroix B                       37.0S  60.4W    8.0 Crater                 NLF?
            Lacroix E                       40.0S  62.9W   19.0 Crater                 NLF?
            Lacroix F                       40.7S  61.6W   15.0 Crater                 NLF?
            Lacroix G                       36.7S  59.1W   47.0 Crater                 NLF?
            Lacroix H                       38.6S  57.8W   13.0 Crater                 NLF?
            Lacroix J                       38.4S  59.3W   18.0 Crater                 NLF?
            Lacroix K                       35.2S  57.7W   45.0 Crater                 NLF?
            Lacroix L                       35.7S  58.3W    8.0 Crater                 NLF?
            Lacroix M                       36.0S  56.9W   13.0 Crater                 NLF?
            Lacroix N                       37.2S  57.8W   14.0 Crater                 NLF?
            Lacroix P                       35.2S  53.7W    9.0 Crater                 NLF?
            Lacroix R                       34.5S  60.1W   19.0 Crater                 NLF?
            Lade                             1.3S  10.1E   55.0 Crater         K1898   K1898
            Lade A                           0.2S  12.9E   57.0 Crater                 NLF?
            Lade B                           0.1N   9.8E   24.0 Crater                 NLF?
            Lade D                           0.9S  13.7E   16.0 Crater                 NLF?
            Lade E                           1.9S  13.0E   21.0 Crater                 NLF?
            Lade M                           1.1S   9.4E   12.0 Crater                 NLF?
            Lade S                           1.2S   8.3E   24.0 Crater                 NLF?
            Lade T                           1.0S   9.0E   18.0 Crater                 NLF?
            Lade U                           0.1S   9.6E    4.0 Crater                 NLF?
            Lade V                           0.2S   9.1E    4.0 Crater                 NLF?
            Lade W                           0.2N   8.6E    4.0 Crater                 NLF?
            Lade X                           1.7S  11.0E    3.0 Crater                 NLF?
            Lagalla                         44.6S  22.5W   85.0 Crater         F1936   F1936
            Lagalla F                       44.6S  25.3W   29.0 Crater                 NLF?
            Lagalla H                       44.4S  27.0W    5.0 Crater                 NLF?
            Lagalla J                       46.0S  25.1W   22.0 Crater                 NLF?
            Lagalla K                       43.7S  24.3W   10.0 Crater                 NLF?
            Lagalla M                       46.6S  25.7W    6.0 Crater                 NLF?
            Lagalla N                       44.9S  26.1W   12.0 Crater                 NLF?
            Lagalla P                       45.2S  24.4W   11.0 Crater                 NLF?
            Lagalla T                       47.3S  26.5W    7.0 Crater                 NLF?
            Lagalla V                       47.0S  24.3W    5.0 Crater                 NLF?
            Lagrange                        32.3S  72.8W  225.0 Crater         M1834   M1834
            Lagrange A                      32.5S  69.2W    6.0 Crater                 NLF?
            Lagrange B                      31.4S  61.5W   16.0 Crater                 NLF?
            Lagrange C                      29.8S  64.9W   23.0 Crater                 NLF?
            Lagrange D                      34.9S  72.5W   11.0 Crater                 NLF?
            Lagrange E                      29.1S  72.6W   46.0 Crater                 NLF?
            Lagrange F                      32.8S  67.4W   14.0 Crater                 NLF?
            Lagrange G                      28.5S  62.7W   18.0 Crater                 NLF?
            Lagrange H                      29.5S  66.2W   11.0 Crater                 NLF?
            Lagrange J                      34.0S  68.9W    8.0 Crater                 NLF?
            Lagrange K                      30.7S  70.3W   31.0 Crater                 NLF?
            Lagrange L                      32.1S  65.1W   18.0 Crater                 NLF?
            Lagrange N                      32.1S  73.8W   31.0 Crater                 NLF?
            Lagrange R                      31.3S  76.5W  130.0 Crater                 NLF?
            Lagrange S                      33.9S  74.6W   12.0 Crater                 NLF?
            Lagrange T                      33.0S  62.6W   12.0 Crater                 NLF?
            Lagrange W                      33.0S  63.7W   56.0 Crater                 NLF?
            Lagrange X                      28.7S  69.2W    9.0 Crater                 NLF?
            Lagrange Y                      28.2S  68.4W   16.0 Crater                 NLF?
            Lagrange Z                      32.6S  64.6W   13.0 Crater                 NLF?
            Lalande                          4.4S   8.6W   24.0 Crater         VL1645  L1824
            Lalande A                        6.6S   9.8W   13.0 Crater                 NLF?
            Lalande B                        3.1S   9.0W    8.0 Crater                 NLF?
            Lalande C                        5.6S   6.9W   11.0 Crater                 NLF?
            Lalande D                        6.1S   7.5W    8.0 Crater                 NLF?
            Lalande E                        3.4S  10.7W    4.0 Crater                 NLF?
            Lalande F                        2.6S  10.0W    3.0 Crater                 NLF?
            Lalande G                        6.2S   7.9W    5.0 Crater                 NLF?
            Lalande N                        5.6S   5.7W    6.0 Crater                 NLF?
            Lalande R                        4.7S   7.0W   24.0 Crater                 NLF?
            Lalande T                        5.2S   7.5W    4.0 Crater                 NLF?
            Lalande U                        3.2S   8.1W    4.0 Crater                 NLF?
            Lalande W                        6.5S   5.6W   11.0 Crater                 NLF?
            Lallemand                       14.3S  84.1W   18.0 Crater         N       IAU1985
            Lamarck                         22.9S  69.8W  100.0 Crater         RLA1963 IAU1964
            Lamarck A                       25.2S  70.8W   51.0 Crater                 NLF?
            Lamarck B                       22.8S  69.7W    7.0 Crater                 NLF?
            Lamarck D                       25.0S  74.1W  131.0 Crater                 NLF?
            Lamarck E                       26.8S  75.7W    9.0 Crater                 NLF?
            Lamarck F                       27.0S  73.9W    9.0 Crater                 NLF?
            Lamarck G                       27.1S  72.1W   15.0 Crater                 NLF?
            Lamb                            42.9S 100.1E  106.0 Crater                 IAU1970
            Lamb A                          39.9S 101.6E   20.0 Crater                 AW82
            Lamb E                          41.6S 107.1E   11.0 Crater                 AW82
            Lamb G                          43.2S 105.9E   69.0 Crater                 AW82
            Lambert                         25.8N  21.0W   30.0 Crater         VL1645  S1791
            Lambert A                       26.4N  21.5W    4.0 Crater                 NLF?
            Lambert B                       24.3N  20.1W    4.0 Crater                 NLF?
            Lambert R                       23.9N  20.6W   55.0 Crater                 NLF?
            Lambert T                       28.5N  20.3W    3.0 Crater                 NLF?
            Lambert W                       24.5N  22.6W    2.0 Crater                 NLF?
            Lam|%e                          14.7S  64.5E   84.0 Crater         RLA1963 IAU1964
            Lam|%e E                        13.9S  66.8E   11.0 Crater                 NLF?
            Lam|%e F                        13.9S  66.4E   10.0 Crater                 NLF?
            Lam|%e G                        15.4S  65.5E   26.0 Crater                 NLF?
            Lam|%e H                        15.8S  68.2E   12.0 Crater                 NLF?
            Lam|%e J                        14.3S  65.7E   18.0 Crater                 NLF?
            Lam|%e K                        13.3S  64.2E    8.0 Crater                 NLF?
            Lam|%e L                        14.4S  68.6E    6.0 Crater                 NLF?
            Lam|%e M                        15.8S  66.5E   13.0 Crater                 NLF?
            Lam|%e N                        12.8S  67.1E    9.0 Crater                 NLF?
            Lam|%e T                        12.5S  66.5E   11.0 Crater                 NLF?
            Lam|%e W                        13.1S  65.9E    6.0 Crater                 NLF?
            Lam|%e Z                        15.9S  65.9E   17.0 Crater                 NLF?
            Lam|`ech                        42.7N  13.1E   13.0 Crater         M1935   M1935
            Lamont                           4.4N  23.7E  106.0 Crater         K1898   K1898
            Lampland                        31.0S 131.0E   65.0 Crater                 IAU1970
            Lampland A                      30.4S 131.2E   14.0 Crater                 AW82
            Lampland B                      29.5S 131.6E   12.0 Crater                 AW82
            Lampland K                      33.0S 132.5E   47.0 Crater                 AW82
            Lampland M                      33.5S 130.8E   38.0 Crater                 AW82
            Lampland Q                      32.5S 129.4E   12.0 Crater                 AW82
            Lampland R                      31.7S 129.2E   45.0 Crater                 AW82
            Landau                          41.6N 118.1W  214.0 Crater                 IAU1970
            Landau Q                        41.0N 121.7W   32.0 Crater                 AW82
            Lander                          15.3S 131.8E   40.0 Crater                 IAU1976
            Lander K                        16.2S 132.2E   23.0 Crater                 AW82
            Landsteiner                     31.3N  14.8W    6.0 Crater                 IAU1976
            Lane                             9.5S 132.0E   55.0 Crater                 IAU1970
            Lane B                           7.5S 132.9E   13.0 Crater                 AW82
            Lane S                           9.7S 130.7E   35.0 Crater                 AW82
            Langemak                        10.3S 118.7E   97.0 Crater                 IAU1970
            Langemak N                      12.9S 119.0E  126.0 Crater                 AW82
            Langemak X                       6.6S 117.5E   47.0 Crater                 AW82
            Langemak Z                       5.6S 119.3E   27.0 Crater                 AW82
            Langevin                        44.3N 162.7E   58.0 Crater                 IAU1970
            Langevin C                      46.4N 165.5E   19.0 Crater                 AW82
            Langevin K                      41.6N 163.8E   17.0 Crater                 AW82
            Langley                         51.1N  86.3W   59.0 Crater         RLA1963 IAU1964
            Langley J                       51.7N  85.2W   20.0 Crater                 RLA1963?
            Langley K                       52.0N  86.3W   20.0 Crater                 RLA1963?
            Langmuir                        35.7S 128.4W   91.0 Crater                 IAU1970
            Langrenus                        8.9S  61.1E  127.0 Crater         VL1645  VL1645
            Langrenus E                     12.7S  60.6E   30.0 Crater                 NLF?
            Langrenus G                     12.1S  65.4E   23.0 Crater                 NLF?
            Langrenus H                      8.0S  64.3E   23.0 Crater                 NLF?
            Langrenus L                     13.2S  62.2E   12.0 Crater                 NLF?
            Langrenus M                      9.8S  66.4E   17.0 Crater                 NLF?
            Langrenus N                      9.0S  65.7E   12.0 Crater                 NLF?
            Langrenus P                     12.1S  63.1E   42.0 Crater                 NLF?
            Langrenus Q                     11.9S  60.7E   12.0 Crater                 NLF?
            Langrenus R                      7.7S  63.6E    5.0 Crater                 NLF?
            Langrenus S                      6.7S  64.7E    9.0 Crater                 NLF?
            Langrenus T                      4.6S  62.5E   42.0 Crater                 NLF?
            Langrenus U                     12.6S  57.1E    4.0 Crater                 NLF?
            Langrenus V                     13.2S  55.9E    5.0 Crater                 NLF?
            Langrenus W                      8.6S  67.3E   23.0 Crater                 NLF?
            Langrenus X                     12.4S  64.7E   25.0 Crater                 NLF?
            Langrenus Y                      7.8S  66.9E   27.0 Crater                 NLF?
            Langrenus Z                      7.1S  66.4E   20.0 Crater                 NLF?
            Lansberg                         0.3S  26.6W   38.0 Crater         VL1645  R1651
            Lansberg A                       0.2N  31.1W    9.0 Crater                 NLF?
            Lansberg B                       2.5S  28.1W    9.0 Crater                 NLF?
            Lansberg C                       1.5S  29.2W   17.0 Crater                 NLF?
            Lansberg D                       3.0S  30.6W   11.0 Crater                 NLF?
            Lansberg E                       1.8S  30.3W    6.0 Crater                 NLF?
            Lansberg F                       2.2S  30.7W    9.0 Crater                 NLF?
            Lansberg G                       0.6S  29.4W   10.0 Crater                 NLF?
            Lansberg L                       3.5S  26.4W    5.0 Crater                 NLF?
            Lansberg N                       1.9S  26.4W    4.0 Crater                 NLF?
            Lansberg P                       2.3S  23.0W    2.0 Crater                 NLF?
            Lansberg X                       1.2N  27.8W    3.0 Crater                 NLF?
            Lansberg Y                       0.7N  28.2W    4.0 Crater                 NLF?
            Laplace A                       43.7N  26.8W    9.0 Crater                 NLF?
            Laplace B                       51.3N  19.8W    5.0 Crater                 NLF?
            Laplace D                       47.3N  25.5W   11.0 Crater                 NLF?
            Laplace E                       50.3N  19.8W    6.0 Crater                 NLF?
            Laplace F                       45.6N  19.8W    6.0 Crater                 NLF?
            Laplace L                       51.7N  21.0W    7.0 Crater                 NLF?
            Laplace M                       52.2N  19.9W    6.0 Crater                 NLF?
            Lara                            20.4N  30.5E    0.0 Crater (A)             IAU1973
            Larmor                          32.1N 179.7W   97.0 Crater                 IAU1970
            Larmor K                        30.3N 179.0W   24.0 Crater                 AW82
            Larmor Q                        28.6N 176.2E   22.0 Crater                 AW82
            Larmor W                        33.9N 177.6E   27.0 Crater                 AW82
            Larmor Z                        33.7N 179.8W   49.0 Crater                 AW82
            Lassell                         15.5S   7.9W   23.0 Crater                 NLF
            Lassell A                       16.8S   6.8W    3.0 Crater                 NLF?
            Lassell B                       16.1S   7.7W    4.0 Crater                 NLF?
            Lassell C                       14.7S   9.3W    9.0 Crater                 NLF?
            Lassell D                       14.5S  10.5W    2.0 Crater                 NLF?
            Lassell E                       18.2S  10.2W    5.0 Crater                 NLF?
            Lassell F                       17.1S  12.5W    5.0 Crater                 NLF?
            Lassell G                       14.8S   9.0W    7.0 Crater                 NLF?
            Lassell H                       14.5S  11.2W    5.0 Crater                 NLF?
            Lassell J                       14.8S  10.4W    4.0 Crater                 NLF?
            Lassell K                       15.1S   8.9W    4.0 Crater                 NLF?
            Lassell M                       14.2S   8.8W    3.0 Crater                 NLF?
            Lassell S                       18.2S   8.5W    4.0 Crater                 NLF?
            Lassell T                       17.1S   8.8W    2.0 Crater                 NLF?
            Last                            26.1N   3.7E    0.0 Crater (A)             IAU1973
            Laue                            28.0N  96.7W   87.0 Crater                 IAU1970
            Laue G                          27.8N  93.2W   36.0 Crater                 AW82
            Laue U                          28.8N 101.4W   56.0 Crater                 AW82
            Lauritsen                       27.6S  96.1E   52.0 Crater                 IAU1970
            Lauritsen A                     24.8S  96.6E   35.0 Crater                 AW82
            Lauritsen B                     26.7S  96.8E   26.0 Crater                 AW82
            Lauritsen G                     28.0S  97.3E   16.0 Crater                 AW82
            Lauritsen H                     28.5S  97.5E   28.0 Crater                 AW82
            Lauritsen Y                     27.5S  96.1E   14.0 Crater                 AW82
            Lauritsen Z                     26.0S  96.2E   52.0 Crater                 AW82
            Lavoisier                       38.2N  81.2W   70.0 Crater         M1834   M1834
            Lavoisier A                     36.9N  73.2W   28.0 Crater                 NLF?
            Lavoisier B                     39.8N  79.7W   25.0 Crater                 NLF?
            Lavoisier C                     35.8N  76.7W   35.0 Crater                 NLF?
            Lavoisier E                     40.9N  80.4W   49.0 Crater                 NLF?
            Lavoisier F                     37.1N  80.5W   33.0 Crater                 NLF?
            Lavoisier G                     37.3N  85.7W   19.0 Crater                 NLF?
            Lavoisier H                     38.3N  78.8W   29.0 Crater                 NLF?
            Lavoisier J                     37.5N  86.5W   22.0 Crater                 NLF?
            Lavoisier K                     39.7N  74.4W    7.0 Crater                 NLF?
            Lavoisier L                     39.7N  75.0W    6.0 Crater                 NLF?
            Lavoisier N                     41.9N  82.4W   24.0 Crater                 NLF?
            Lavoisier S                     39.1N  83.1W   24.0 Crater                 NLF?
            Lavoisier T                     36.5N  76.6W   19.0 Crater                 NLF?
            Lavoisier W                     36.9N  81.8W   16.0 Crater                 NLF?
            Lavoisier Z                     36.1N  86.2W   12.0 Crater                 NLF?
            Lawrence                         7.4N  43.2E   24.0 Crater                 IAU1973
            le Gentil                       74.6S  75.7W  128.0 Crater         S1791   S1791
            le Gentil A                     74.6S  52.4W   33.0 Crater                 NLF?
            le Gentil B                     75.0S  73.0W   16.0 Crater                 NLF?
            le Gentil C                     74.4S  75.1W   19.0 Crater                 NLF?
            le Gentil D                     74.6S  63.8W   12.0 Crater                 NLF?
            le Gentil G                     71.8S  58.8W   17.0 Crater                 NLF?
            le Monnier                      26.6N  30.6E   60.0 Crater         L1824   L1824
            le Monnier A                    26.9N  32.5E   21.0 Crater                 NLF?
            le Monnier H                    25.0N  29.6E    6.0 Crater                 NLF?
            le Monnier K                    27.7N  30.2E    4.0 Crater                 NLF?
            le Monnier S                    26.8N  33.9E   40.0 Crater                 NLF?
            le Monnier T                    25.1N  31.4E   18.0 Crater                 NLF?
            le Monnier U                    26.1N  33.5E   25.0 Crater                 NLF?
            le Monnier V                    26.0N  34.3E   23.0 Crater                 NLF?
            le Verrier                      40.3N  20.6W   20.0 Crater         S1878   S1878
            le Verrier A                    38.1N  17.3W    4.0 Crater                 NLF?
            le Verrier B                    40.1N  12.9W    5.0 Crater                 NLF?
            le Verrier D                    39.7N  12.3W    9.0 Crater                 NLF?
            le Verrier E                    42.4N  16.9W    7.0 Crater                 NLF?
            le Verrier S                    38.9N  20.6W    3.0 Crater                 NLF?
            le Verrier T                    39.8N  20.7W    4.0 Crater                 NLF?
            le Verrier U                    37.2N  13.1W    4.0 Crater                 NLF?
            le Verrier V                    37.8N  14.2W    3.0 Crater                 NLF?
            le Verrier W                    39.4N  13.9W    3.0 Crater                 NLF?
            le Verrier X                    41.6N  12.1W    3.0 Crater                 NLF?
            Leakey                           3.2S  37.4E   12.0 Crater                 IAU1976
            Leavitt                         44.8S 139.3W   66.0 Crater                 IAU1970
            Leavitt Z                       42.7S 139.2W   65.0 Crater                 AW82
            Lebedev                         47.3S 107.8E  102.0 Crater                 IAU1970
            Lebedev C                       45.0S 111.0E   34.0 Crater                 AW82
            Lebedev D                       44.6S 112.5E   34.0 Crater                 AW82
            Lebedev F                       47.5S 110.8E   18.0 Crater                 AW82
            Lebedev K                       49.7S 108.9E   22.0 Crater                 AW82
            Lebedinskiy                      8.3N 164.3W   62.0 Crater                 IAU1970
            Lebedinskiy A                   10.9N 163.7W   38.0 Crater                 AW82
            Lebedinskiy B                   10.5N 163.2W   37.0 Crater                 AW82
            Lebedinskiy K                    6.6N 163.3W   29.0 Crater                 AW82
            Lebedinskiy P                    6.0N 165.0W   51.0 Crater                 AW82
            Lebesgue                         5.1S  89.0E   11.0 Crater                 IAU1976
            Lee                             30.7S  40.7W   41.0 Crater                 NLF
            Lee A                           31.4S  41.2W   18.0 Crater                 NLF?
            Lee H                           30.9S  38.9W    4.0 Crater                 NLF?
            Lee M                           29.8S  39.7W   77.0 Crater                 NLF?
            Lee S                           30.8S  42.8W    6.0 Crater                 NLF?
            Lee T                           30.1S  42.0W    4.0 Crater                 NLF?
            Leeuwenhoek                     29.3S 178.7W  125.0 Crater                 IAU1970
            Leeuwenhoek E                   28.2S 176.7W  117.0 Crater                 AW82
            Legendre                        28.9S  70.2E   78.0 Crater         M1834   M1834
            Legendre D                      31.5S  75.2E   58.0 Crater                 NLF?
            Legendre E                      33.8S  78.5E   28.0 Crater                 NLF?
            Legendre F                      33.8S  76.4E   40.0 Crater                 NLF?
            Legendre G                      32.3S  73.8E   15.0 Crater                 NLF?
            Legendre H                      32.5S  78.1E    7.0 Crater                 NLF?
            Legendre J                      30.8S  74.5E   16.0 Crater                 NLF?
            Legendre K                      29.8S  72.8E   90.0 Crater                 NLF?
            Legendre L                      28.2S  73.5E   30.0 Crater                 NLF?
            Legendre M                      28.2S  71.5E    8.0 Crater                 NLF?
            Legendre N                      27.5S  70.5E    8.0 Crater                 NLF?
            Legendre P                      27.3S  69.2E    7.0 Crater                 NLF?
            Lehmann                         40.0S  56.0W   53.0 Crater         M1834   M1834
            Lehmann A                       39.5S  54.0W   34.0 Crater                 NLF?
            Lehmann C                       35.5S  50.1W   16.0 Crater                 NLF?
            Lehmann D                       39.6S  57.3W   14.0 Crater                 NLF?
            Lehmann E                       37.5S  54.9W   48.0 Crater                 NLF?
            Lehmann H                       41.0S  58.6W   16.0 Crater                 NLF?
            Lehmann K                       36.4S  50.3W    5.0 Crater                 NLF?
            Lehmann L                       36.4S  51.9W    6.0 Crater                 NLF?
            Leibnitz                        38.3S 179.2E  245.0 Crater         S1791   S1791
            Leibnitz R                      39.3S 176.3E   19.0 Crater                 AW82
            Leibnitz S                      39.6S 171.8E   28.0 Crater                 AW82
            Leibnitz X                      36.5S 177.3E   19.0 Crater                 AW82
            Lema|^itre                      61.2S 149.6W   91.0 Crater                 IAU1970
            Lema|^itre C                    59.4S 145.6W   27.0 Crater                 AW82
            Lema|^itre F                    61.4S 148.4W   32.0 Crater                 AW82
            Lema|^itre S                    61.6S 156.3W   34.0 Crater                 AW82
            Lents                            2.8N 102.1W   21.0 Crater                 IAU1970
            Lents C                          3.3N 101.6W   23.0 Crater                 AW82
            Lents J                          3.7S  97.3W   16.0 Crater                 AW82
            Leonov                          19.0N 148.2E   33.0 Crater                 IAU1970
            Lepaute                         33.3S  33.6W   16.0 Crater         K1898   K1898
            Lepaute D                       34.3S  36.2W   22.0 Crater                 NLF?
            Lepaute E                       35.7S  35.0W   10.0 Crater                 NLF?
            Lepaute F                       37.2S  34.8W    7.0 Crater                 NLF?
            Lepaute K                       34.3S  33.9W   12.0 Crater                 NLF?
            Lepaute L                       34.5S  35.2W    9.0 Crater                 NLF?
            Letronne                        10.8S  42.5W  116.0 Crater         M1834   M1834
            Letronne A                      12.1S  39.1W    7.0 Crater                 NLF?
            Letronne B                      11.2S  41.2W    5.0 Crater                 NLF?
            Letronne C                      10.7S  38.5W    4.0 Crater                 NLF?
            Letronne F                       9.2S  46.1W    8.0 Crater                 NLF?
            Letronne G                      12.7S  46.5W   10.0 Crater                 NLF?
            Letronne H                      12.6S  46.0W    4.0 Crater                 NLF?
            Letronne K                      14.5S  43.6W    5.0 Crater                 NLF?
            Letronne L                      14.3S  44.3W    5.0 Crater                 NLF?
            Letronne M                      12.0S  44.1W    3.0 Crater                 NLF?
            Letronne N                      12.3S  39.8W    4.0 Crater                 NLF?
            Letronne T                      12.5S  42.6W    3.0 Crater                 NLF?
            Leucippus                       29.1N 116.0W   56.0 Crater                 IAU1970
            Leucippus F                     29.1N 113.0W   19.0 Crater                 AW82
            Leucippus K                     27.2N 115.0W   14.0 Crater                 AW82
            Leucippus Q                     25.9N 118.8W   84.0 Crater                 AW82
            Leucippus X                     33.4N 118.8W   36.0 Crater                 AW82
            Leuschner                        1.8N 108.8W   49.0 Crater                 IAU1970
            Leuschner L                      1.1S 108.8W   18.0 Crater                 AW82
            Leuschner Z                      5.3N 109.3W   18.0 Crater                 AW82
            Levi-Civita                     23.7S 143.4E  121.0 Crater                 IAU1970
            Levi-Civita A                   20.5S 144.0E   17.0 Crater                 AW82
            Levi-Civita F                   23.4S 145.4E   16.0 Crater                 AW82
            Levi-Civita S                   24.1S 138.8E   43.0 Crater                 AW82
            Lewis                           18.5S 113.8W   42.0 Crater                 IAU1970
            Lexell                          35.8S   4.2W   62.0 Crater         S1791   S1791
            Lexell A                        36.9S   1.4W   34.0 Crater         VL1645  NLF?
            Lexell B                        37.3S   3.4W   23.0 Crater                 NLF?
            Lexell D                        36.1S   0.7W   20.0 Crater                 NLF?
            Lexell E                        37.2S   0.4W   16.0 Crater                 NLF?
            Lexell F                        36.5S   5.4W    8.0 Crater                 NLF?
            Lexell G                        37.2S   4.9W   10.0 Crater                 NLF?
            Lexell H                        36.5S   4.9W   10.0 Crater                 NLF?
            Lexell K                        35.9S   6.4W   10.0 Crater                 NLF?
            Lexell L                        36.0S   6.0W    8.0 Crater                 NLF?
            Ley                             42.2N 154.9E   79.0 Crater                 IAU1970
            Licetus                         47.1S   6.7E   74.0 Crater                 NLF
            Licetus A                       47.8S   3.2E    8.0 Crater                 NLF?
            Licetus B                       46.5S   4.9E   13.0 Crater                 NLF?
            Licetus C                       47.4S   5.6E   10.0 Crater                 NLF?
            Licetus D                       48.0S   4.5E    6.0 Crater                 NLF?
            Licetus E                       44.7S   1.9E   19.0 Crater                 NLF?
            Licetus F                       46.0S   1.0E   32.0 Crater                 NLF?
            Licetus G                       43.8S   1.9E   11.0 Crater                 NLF?
            Licetus H                       45.9S   3.1E   10.0 Crater                 NLF?
            Licetus J                       44.2S   3.2E   12.0 Crater                 NLF?
            Licetus K                       45.5S   0.0E    6.0 Crater                 NLF?
            Licetus L                       47.2S   1.1E    5.0 Crater                 NLF?
            Licetus M                       46.8S   1.9E    9.0 Crater                 NLF?
            Licetus N                       45.5S   2.2E    9.0 Crater                 NLF?
            Licetus P                       47.6S   2.4E   21.0 Crater                 NLF?
            Licetus Q                       47.2S   9.7E    8.0 Crater                 NLF?
            Licetus R                       45.1S   3.9E    7.0 Crater                 NLF?
            Licetus S                       45.2S   8.2E   11.0 Crater                 NLF?
            Licetus T                       45.8S   6.7E    7.0 Crater                 NLF?
            Licetus U                       46.9S   7.4E    7.0 Crater                 NLF?
            Licetus W                       45.9S   8.5E    7.0 Crater                 NLF?
            Lichtenberg                     31.8N  67.7W   20.0 Crater         S1791   S1791
            Lichtenberg A                   29.0N  60.1W    7.0 Crater                 NLF?
            Lichtenberg B                   33.3N  61.5W    5.0 Crater                 NLF?
            Lichtenberg F                   33.2N  65.3W    5.0 Crater                 NLF?
            Lichtenberg H                   31.5N  58.9W    4.0 Crater                 NLF?
            Lichtenberg R                   34.7N  70.2W   34.0 Crater                 NLF?
            Lick                            12.4N  52.7E   31.0 Crater         K1898   K1898
            Lick A                          11.5N  52.8E   23.0 Crater                 NLF?
            Lick B                          11.2N  51.4E   24.0 Crater                 NLF?
            Lick C                          11.5N  52.0E    9.0 Crater                 NLF?
            Lick E                          10.6N  50.7E    8.0 Crater                 NLF?
            Lick F                          10.1N  50.2E   22.0 Crater                 NLF?
            Lick G                          10.1N  50.9E    5.0 Crater                 NLF?
            Lick K                          10.2N  52.8E    6.0 Crater                 NLF?
            Lick L                           8.7N  49.0E    5.0 Crater                 NLF?
            Lick N                           9.7N  47.9E   23.0 Crater                 NLF?
            Liebig                          24.3S  48.2W   37.0 Crater         S1878   S1878
            Liebig A                        24.3S  47.7W   12.0 Crater                 NLF?
            Liebig B                        25.0S  47.1W    9.0 Crater                 NLF?
            Liebig F                        24.6S  45.7W    9.0 Crater                 NLF?
            Liebig G                        26.1S  45.8W   20.0 Crater                 NLF?
            Liebig H                        26.3S  47.3W   11.0 Crater                 NLF?
            Liebig J                        24.8S  45.0W    4.0 Crater                 NLF?
            Lilius                          54.5S   6.2E   61.0 Crater         VL1645  NLF
            Lilius A                        55.4S   8.8E   41.0 Crater                 NLF?
            Lilius B                        53.0S   3.8E   29.0 Crater                 NLF?
            Lilius C                        54.4S   3.3E   40.0 Crater                 NLF?
            Lilius D                        50.6S   3.0E   51.0 Crater                 NLF?
            Lilius E                        50.1S   2.9E   38.0 Crater                 NLF?
            Lilius F                        49.4S   1.7E   43.0 Crater                 NLF?
            Lilius G                        50.0S   0.7E    7.0 Crater                 NLF?
            Lilius H                        50.5S   0.8E    9.0 Crater                 NLF?
            Lilius J                        56.3S   1.8E   13.0 Crater                 NLF?
            Lilius K                        53.6S   2.2E   23.0 Crater                 NLF?
            Lilius L                        54.9S   2.5E    6.0 Crater                 NLF?
            Lilius M                        56.2S   2.9E   11.0 Crater                 NLF?
            Lilius N                        49.0S   2.8E    5.0 Crater                 NLF?
            Lilius O                        55.4S   3.6E    7.0 Crater                 NLF?
            Lilius P                        55.9S   3.9E    4.0 Crater                 NLF?
            Lilius R                        54.6S   4.4E    9.0 Crater                 NLF?
            Lilius S                        52.8S   5.9E   14.0 Crater                 NLF?
            Lilius T                        55.9S   7.5E    5.0 Crater                 NLF?
            Lilius U                        53.5S   7.6E    8.0 Crater                 NLF?
            Lilius W                        53.6S   8.3E    9.0 Crater                 NLF?
            Lilius X                        53.5S   9.9E    4.0 Crater                 NLF?
            Linda                           30.7N  33.4W    1.0 Crater         X       IAU1979
            Lindbergh                        5.4S  52.9E   12.0 Crater                 IAU1976
            Lindblad                        70.4N  98.8W   66.0 Crater                 IAU1970
            Lindblad F                      70.6N  94.3W   42.0 Crater                 AW82
            Lindblad S                      69.7N 104.9W   25.0 Crater                 AW82
            Lindblad Y                      73.0N 101.2W   28.0 Crater                 AW82
            Lindenau                        32.3S  24.9E   53.0 Crater         VL1645  M1834
            Lindenau D                      30.4S  24.9E   10.0 Crater                 NLF?
            Lindenau E                      31.6S  26.5E    8.0 Crater                 NLF?
            Lindenau F                      32.4S  26.4E   10.0 Crater                 NLF?
            Lindenau G                      33.2S  27.3E   10.0 Crater                 NLF?
            Lindenau H                      31.3S  26.3E   11.0 Crater                 NLF?
            Lindsay                          7.0S  13.0E   32.0 Crater                 IAU1979
            Linn|%e                         27.7N  11.8E    2.0 Crater         M1834   M1834
            Linn|%e A                       28.9N  14.4E    4.0 Crater                 NLF?
            Linn|%e B                       30.5N  14.2E    5.0 Crater                 NLF?
            Linn|%e D                       28.7N  17.1E    5.0 Crater                 NLF?
            Linn|%e F                       32.3N  13.9E    5.0 Crater                 NLF?
            Linn|%e G                       35.9N  13.3E    5.0 Crater                 NLF?
            Linn|%e H                       33.7N  13.8E    3.0 Crater                 NLF?
            Liouville                        2.6N  73.5E   16.0 Crater                 IAU1973
            Lippershey                      25.9S  10.3W    6.0 Crater         K1898   K1898
            Lippershey K                    26.7S  11.4W    2.0 Crater                 NLF?
            Lippershey L                    25.7S  11.7W    3.0 Crater                 NLF?
            Lippershey M                    24.3S  10.9W    2.0 Crater                 NLF?
            Lippershey N                    24.5S   9.5W    3.0 Crater                 NLF?
            Lippershey P                    26.3S   8.3W    2.0 Crater                 NLF?
            Lippershey R                    26.6S  10.1W    4.0 Crater                 NLF?
            Lippershey T                    25.2S  11.1W    5.0 Crater                 NLF?
            Lippmann                        56.0S 114.9W  160.0 Crater                 IAU1979
            Lippmann B                      52.6S 110.9W   29.0 Crater                 AW82
            Lippmann E                      55.4S 107.6W   23.0 Crater                 AW82
            Lippmann J                      59.0S 106.6W   19.0 Crater                 AW82
            Lippmann L                      57.6S 112.5W   54.0 Crater                 AW82
            Lippmann P                      56.1S 115.0W   29.0 Crater                 AW82
            Lippmann Q                      57.0S 118.7W   27.0 Crater                 AW82
            Lippmann R                      57.2S 121.3W   37.0 Crater                 AW82
            Lipskiy                          2.2S 179.5W   80.0 Crater                 IAU1979
            Lipskiy S                        2.2S 179.9W   23.0 Crater                 AW82
            Lipskiy V                        1.2S 178.7E   36.0 Crater                 AW82
            Lipskiy X                        0.4N 178.9E   24.0 Crater                 AW82
            Litke                           16.8S 123.1E   39.0 Crater                 IAU1970
            Littrow                         21.5N  31.4E   30.0 Crater         M1834   M1834
            Littrow A                       22.2N  32.2E   23.0 Crater                 NLF?
            Littrow D                       23.7N  32.8E    8.0 Crater                 NLF?
            Littrow F                       22.0N  34.1E   12.0 Crater                 NLF?
            Littrow P                       23.2N  32.8E   36.0 Crater                 NLF?
            Lobachevskiy                     9.9N 112.6E   84.0 Crater         BML1960 IAU1961
            Lobachevskiy M                   8.0N 112.8E   41.0 Crater                 AW82
            Lobachevskiy P                   7.7N 111.3E   26.0 Crater                 AW82
            Lockyer                         46.2S  36.7E   34.0 Crater         S1878   S1878
            Lockyer A                       44.0S  31.0E   10.0 Crater                 NLF?
            Lockyer F                       47.5S  36.5E   20.0 Crater                 NLF?
            Lockyer G                       45.7S  33.3E   24.0 Crater                 NLF?
            Lockyer H                       44.5S  32.5E   31.0 Crater                 NLF?
            Lockyer J                       45.0S  32.3E   13.0 Crater                 NLF?
            Lodygin                         17.7S 146.8W   62.0 Crater                 IAU1970
            Lodygin C                       15.9S 144.5W   30.0 Crater                 AW82
            Lodygin F                       17.6S 142.8W   47.0 Crater                 AW82
            Lodygin J                       18.5S 145.1W   25.0 Crater                 AW82
            Lodygin L                       22.6S 145.4W   25.0 Crater                 AW82
            Lodygin M                       19.2S 146.2W   14.0 Crater                 AW82
            Lodygin R                       18.3S 149.2W   30.0 Crater                 AW82
            Loewy                           22.7S  32.8W   24.0 Crater         K1898   K1898
            Loewy A                         22.3S  32.5W    7.0 Crater                 NLF?
            Loewy B                         23.2S  32.9W    4.0 Crater                 NLF?
            Loewy G                         23.0S  31.9W    5.0 Crater                 NLF?
            Loewy H                         22.8S  31.9W    5.0 Crater                 NLF?
            Lohrmann                         0.5S  67.2W   30.0 Crater         M1834   M1834
            Lohrmann A                       0.7S  62.7W   12.0 Crater                 NLF?
            Lohrmann B                       0.7S  69.4W   14.0 Crater                 NLF?
            Lohrmann D                       0.1S  65.2W   11.0 Crater                 NLF?
            Lohrmann E                       1.7S  67.4W   10.0 Crater                 NLF?
            Lohrmann F                       1.4S  69.1W   11.0 Crater                 NLF?
            Lohrmann M                       0.5S  68.9W    7.0 Crater                 NLF?
            Lohrmann N                       0.6S  70.1W    8.0 Crater                 NLF?
            Lohse                           13.7S  60.2E   41.0 Crater         K1898   K1898
            Lomonosov                       27.3N  98.0E   92.0 Crater         BML1960 IAU1961
            Longomontanus                   49.6S  21.8W  157.0 Crater         VL1645  R1651
            Longomontanus A                 52.8S  24.0W   29.0 Crater                 NLF?
            Longomontanus B                 52.9S  20.7W   48.0 Crater                 NLF?
            Longomontanus C                 53.4S  19.0W   31.0 Crater                 NLF?
            Longomontanus D                 54.3S  22.9W   29.0 Crater                 NLF?
            Longomontanus E                 51.4S  18.0W    8.0 Crater                 NLF?
            Longomontanus F                 48.2S  23.5W   19.0 Crater                 NLF?
            Longomontanus G                 48.7S  18.5W   15.0 Crater                 NLF?
            Longomontanus H                 52.0S  23.2W    7.0 Crater                 NLF?
            Longomontanus K                 47.9S  20.9W   15.0 Crater                 NLF?
            Longomontanus L                 49.1S  23.6W   16.0 Crater                 NLF?
            Longomontanus M                 48.6S  23.2W   10.0 Crater                 NLF?
            Longomontanus N                 50.8S  25.7W   12.0 Crater                 NLF?
            Longomontanus P                 48.1S  25.3W    7.0 Crater                 NLF?
            Longomontanus Q                 52.0S  20.5W   11.0 Crater                 NLF?
            Longomontanus R                 52.4S  26.1W    9.0 Crater                 NLF?
            Longomontanus S                 47.4S  23.3W   12.0 Crater                 NLF?
            Longomontanus T                 46.8S  22.7W    5.0 Crater                 NLF?
            Longomontanus U                 52.0S  22.0W    7.0 Crater                 NLF?
            Longomontanus V                 50.7S  18.9W    5.0 Crater                 NLF?
            Longomontanus W                 47.1S  21.3W   10.0 Crater                 NLF?
            Longomontanus X                 53.0S  17.7W    5.0 Crater                 NLF?
            Longomontanus Y                 52.3S  28.2W    4.0 Crater                 NLF?
            Longomontanus Z                 50.0S  18.7W   95.0 Crater                 NLF?
            Lorentz                         32.6N  95.3W  312.0 Crater                 IAU1970
            Lorentz P                       31.8N  98.5W   38.0 Crater                 AW82
            Lorentz R                       33.4N  99.2W   33.0 Crater                 AW82
            Lorentz T                       34.6N 100.3W   20.0 Crater                 AW82
            Lorentz U                       35.0N 100.0W   22.0 Crater                 AW82
            Louise                          28.5N  34.2W    0.8 Crater         X       IAU1979
            Louville                        44.0N  46.0W   36.0 Crater         VL1645  S1791
            Louville A                      43.2N  45.3W    8.0 Crater                 NLF?
            Louville B                      44.0N  46.5W    8.0 Crater                 NLF?
            Louville D                      46.9N  52.1W    7.0 Crater                 NLF?
            Louville DA                     46.6N  51.7W   11.0 Crater                 NLF?
            Louville E                      43.1N  45.9W    6.0 Crater                 NLF?
            Louville K                      46.8N  55.2W    5.0 Crater                 NLF?
            Louville P                      45.6N  52.2W    7.0 Crater                 NLF?
            Love                             6.3S 129.0E   84.0 Crater                 IAU1970
            Love G                           6.5S 131.3E   54.0 Crater                 AW82
            Love H                           6.9S 130.4E   29.0 Crater                 AW82
            Love T                           6.0S 126.1E   13.0 Crater                 AW82
            Love U                           5.9S 127.8E   12.0 Crater                 AW82
            Lovelace                        82.3N 106.4W   54.0 Crater                 IAU1970
            Lovelace E                      82.1N  93.3W   23.0 Crater                 AW82
            Lovell                          36.8S 141.9W   34.0 Crater                 IAU1970
            Lovell F                        36.7S 138.2W   24.0 Crater                 AW82
            Lovell R                        37.7S 144.0W   24.0 Crater                 AW82
            Lowell                          12.9S 103.1W   66.0 Crater                 IAU1970
            Lowell W                        10.0S 107.0W   18.0 Crater                 AW82
            Lubbock                          3.9S  41.8E   13.0 Crater         N1876   N1876
            Lubbock C                        4.8S  39.8E    8.0 Crater                 NLF?
            Lubbock D                        4.5S  39.1E   13.0 Crater                 NLF?
            Lubbock G                        3.7S  39.2E   10.0 Crater                 NLF?
            Lubbock H                        2.6S  41.8E   10.0 Crater                 NLF?
            Lubbock K                        5.1S  38.3E    7.0 Crater                 NLF?
            Lubbock L                        4.9S  39.3E    7.0 Crater                 NLF?
            Lubbock M                        0.3S  38.6E   19.0 Crater                 NLF?
            Lubbock N                        1.5S  39.7E   26.0 Crater         VL1645  NLF?
            Lubbock P                        2.9S  39.5E    7.0 Crater                 NLF?
            Lubbock R                        0.1S  40.4E   24.0 Crater                 NLF?
            Lubbock S                        0.7N  41.2E   24.0 Crater                 NLF?
            Lubiniezky                      17.8S  23.8W   43.0 Crater         S1791   S1791
            Lubiniezky A                    16.4S  25.6W   30.0 Crater         VL1645  NLF?
            Lubiniezky D                    16.5S  23.4W    8.0 Crater                 NLF?
            Lubiniezky E                    16.6S  27.3W   37.0 Crater                 NLF?
            Lubiniezky F                    18.3S  21.8W    8.0 Crater                 NLF?
            Lubiniezky G                    15.3S  20.2W    4.0 Crater                 NLF?
            Lubiniezky H                    17.0S  21.2W    4.0 Crater                 NLF?
            Lucian                          14.3N  36.7E    7.0 Crater                 IAU1973
            Lucretius                        8.2S 120.8W   63.0 Crater                 IAU1970
            Lucretius C                      3.7S 114.4W   20.0 Crater                 AW82
            Lucretius U                      7.7S 123.6W   24.0 Crater                 AW82
            Ludwig                           7.7S  97.4E   23.0 Crater                 IAU1973
            Lundmark                        39.7S 152.5E  106.0 Crater                 IAU1970
            Lundmark B                      37.7S 153.2E   30.0 Crater                 AW82
            Lundmark C                      35.8S 155.6E   25.0 Crater                 AW82
            Lundmark D                      38.8S 154.3E   29.0 Crater                 AW82
            Lundmark F                      39.4S 157.2E   26.0 Crater                 AW82
            Lundmark G                      40.5S 155.5E   35.0 Crater                 AW82
            Luther                          33.2N  24.1E    9.0 Crater         S1878   S1878
            Luther H                        36.0N  22.8E    7.0 Crater                 NLF?
            Luther K                        37.5N  23.3E    4.0 Crater                 NLF?
            Luther X                        36.1N  24.3E    4.0 Crater                 NLF?
            Luther Y                        38.1N  24.4E    4.0 Crater                 NLF?
            Lyapunov                        26.3N  89.3E   66.0 Crater         RLA1963 IAU1964
            Lyell                           13.6N  40.6E   32.0 Crater         S1878   S1878
            Lyell A                         14.3N  39.6E    7.0 Crater                 NLF?
            Lyell B                         14.4N  38.4E    5.0 Crater                 NLF?
            Lyell C                         15.2N  39.4E    5.0 Crater                 NLF?
            Lyell D                         14.7N  41.5E   18.0 Crater                 NLF?
            Lyell K                         15.3N  40.9E    5.0 Crater                 NLF?
            Lyman                           64.8S 163.6E   84.0 Crater                 IAU1970
            Lyman P                         67.6S 158.5E   14.0 Crater                 AW82
            Lyman Q                         68.6S 156.7E   56.0 Crater                 AW82
            Lyman T                         64.1S 157.7E   59.0 Crater                 AW82
            Lyman V                         62.6S 154.2E   37.0 Crater                 AW82
            Lyot                            49.8S  84.5E  132.0 Crater         RLA1963 IAU1964
            Lyot A                          49.0S  79.6E   38.0 Crater                 RLA1963?
            Lyot B                          50.4S  82.2E    9.0 Crater                 RLA1963?
            Lyot C                          50.4S  80.4E   17.0 Crater                 RLA1963?
            Lyot D                          51.6S  82.2E   14.0 Crater                 RLA1963?
            Lyot E                          52.0S  82.9E   13.0 Crater                 RLA1963?
            Lyot F                          52.3S  82.8E   21.0 Crater                 RLA1963?
            Lyot H                          51.4S  78.2E   63.0 Crater                 RLA1963?
            Lyot L                          54.4S  83.1E   70.0 Crater                 RLA1963?
            Lyot M                          53.3S  86.2E   24.0 Crater                 RLA1963?
            Lyot N                          52.8S  83.4E   12.0 Crater                 RLA1963?
            Lyot P                          47.7S  85.0E   13.0 Crater                 RLA1963?
            Lyot R                          46.1S  87.6E   30.0 Crater                 RLA1963?
            Lyot S                          46.0S  85.6E   26.0 Crater                 RLA1963?
            Lyot T                          46.8S  78.6E    8.0 Crater                 RLA1963?
            M. Anderson                     41.6S 149.0W   17.0 Crater                 IAU2006?
            Mach                            18.5N 149.3W  180.0 Crater                 IAU1970
            Mach H                          14.9N 144.1W   40.0 Crater                 AW82
            Mackin                          20.1N  30.7E    0.0 Crater (A)             IAU1973
            Maclaurin                        1.9S  68.0E   50.0 Crater         M1834   M1834
            Maclaurin A                      3.0S  67.6E   29.0 Crater                 NLF?
            Maclaurin B                      3.6S  71.4E   43.0 Crater                 NLF?
            Maclaurin C                      1.1S  69.6E   26.0 Crater                 NLF?
            Maclaurin D                      7.1S  69.9E   10.0 Crater                 NLF?
            Maclaurin E                      3.5S  65.7E   20.0 Crater                 NLF?
            Maclaurin G                      7.0S  66.9E   12.0 Crater                 NLF?
            Maclaurin H                      1.6S  64.1E   41.0 Crater                 NLF?
            Maclaurin J                      2.2S  69.4E   16.0 Crater                 NLF?
            Maclaurin K                      0.9S  66.9E   34.0 Crater                 NLF?
            Maclaurin L                      1.4S  71.7E   30.0 Crater                 NLF?
            Maclaurin M                      4.8S  69.4E   42.0 Crater                 NLF?
            Maclaurin N                      3.8S  68.4E   29.0 Crater                 NLF?
            Maclaurin O                      0.3S  67.9E   37.0 Crater                 NLF?
            Maclaurin P                      6.0S  69.4E   29.0 Crater                 NLF?
            Maclaurin T                      1.8S  65.4E   35.0 Crater                 NLF?
            Maclaurin U                      3.9S  66.2E   19.0 Crater                 NLF?
            Maclaurin W                      0.5N  68.1E   21.0 Crater                 NLF?
            Maclaurin X                      0.1S  68.7E   24.0 Crater                 NLF?
            Maclear                         10.5N  20.1E   20.0 Crater                 IAU1961
            Maclear A                       11.3N  18.0E    5.0 Crater                 NLF?
            MacMillan                       24.2N   7.8W    7.0 Crater                 IAU1976
            Macrobius                       21.3N  46.0E   64.0 Crater         VL1645  R1651
            Macrobius C                     20.8N  45.0E   10.0 Crater                 NLF?
            Macrobius E                     18.7N  46.8E   10.0 Crater                 NLF?
            Macrobius F                     22.5N  48.5E   11.0 Crater                 NLF?
            Macrobius K                     21.5N  40.2E   12.0 Crater                 NLF?
            Macrobius M                     25.0N  41.0E   42.0 Crater                 NLF?
            Macrobius N                     22.8N  40.8E    5.0 Crater                 NLF?
            Macrobius P                     23.0N  39.5E   18.0 Crater                 NLF?
            Macrobius Q                     20.4N  47.6E    9.0 Crater                 NLF?
            Macrobius S                     23.3N  49.6E   26.0 Crater                 NLF?
            Macrobius T                     23.8N  48.6E   29.0 Crater                 NLF?
            Macrobius U                     25.0N  42.8E    6.0 Crater                 NLF?
            Macrobius V                     25.4N  43.3E    5.0 Crater                 NLF?
            Macrobius W                     24.8N  44.6E   26.0 Crater                 NLF?
            Macrobius X                     23.0N  42.2E    4.0 Crater                 NLF?
            Macrobius Y                     23.6N  42.2E    5.0 Crater                 NLF?
            Macrobius Z                     24.3N  42.6E    5.0 Crater                 NLF?
            M|:adler                        11.0S  29.8E   27.0 Crater         VL1645  S1878
            M|:adler A                       9.5S  29.8E    5.0 Crater                 NLF?
            M|:adler D                      12.6S  31.1E    4.0 Crater                 NLF?
            Maestlin                         4.9N  40.6W    7.0 Crater         S1878   S1878
            Maestlin G                       2.0N  42.1W    4.0 Crater                 NLF?
            Maestlin H                       4.7N  43.5W    7.0 Crater                 NLF?
            Maestlin R                       3.5N  41.5W   61.0 Crater                 NLF?
            Magelhaens                      11.9S  44.1E   40.0 Crater         M1834   M1834
            Magelhaens A                    12.6S  45.0E   32.0 Crater                 NLF?
            Maginus                         50.5S   6.3W  194.0 Crater         VL1645  R1651
            Maginus A                       48.8S   4.4W   14.0 Crater                 NLF?
            Maginus B                       52.4S   6.2W   12.0 Crater                 NLF?
            Maginus C                       51.7S   9.4W   42.0 Crater                 NLF?
            Maginus D                       47.9S   2.2W   40.0 Crater                 NLF?
            Maginus E                       49.0S   1.4W   37.0 Crater                 NLF?
            Maginus F                       48.9S   8.2W   18.0 Crater                 NLF?
            Maginus G                       48.0S   7.6W   23.0 Crater                 NLF?
            Maginus H                       52.5S  10.0W   15.0 Crater                 NLF?
            Maginus J                       49.9S   2.8W    8.0 Crater                 NLF?
            Maginus K                       47.4S   3.9W   31.0 Crater                 NLF?
            Maginus L                       49.2S   8.9W   11.0 Crater                 NLF?
            Maginus M                       50.4S   9.3W   10.0 Crater                 NLF?
            Maginus N                       48.5S   9.0W   24.0 Crater                 NLF?
            Maginus O                       50.6S  12.6W   12.0 Crater                 NLF?
            Maginus P                       50.7S  11.8W   10.0 Crater                 NLF?
            Maginus Q                       50.8S   2.3W    9.0 Crater                 NLF?
            Maginus R                       48.9S  10.4W    9.0 Crater                 NLF?
            Maginus S                       49.7S   1.4W   13.0 Crater                 NLF?
            Maginus T                       52.3S   7.1W    6.0 Crater                 NLF?
            Maginus U                       47.4S   8.2W    9.0 Crater                 NLF?
            Maginus V                       49.3S   7.3W    9.0 Crater                 NLF?
            Maginus W                       49.3S   7.8W    8.0 Crater                 NLF?
            Maginus X                       51.3S   7.6W    7.0 Crater                 NLF?
            Maginus Y                       51.8S   9.1W    7.0 Crater                 NLF?
            Maginus Z                       50.2S   3.6W   18.0 Crater                 NLF?
            Main                            80.8N  10.1E   46.0 Crater                 NLF
            Main L                          81.7N  23.2E   14.0 Crater                 NLF?
            Main N                          82.3N  22.0E   11.0 Crater                 NLF?
            Mairan                          41.6N  43.4W   40.0 Crater         VL1645  S1791
            Mairan A                        38.6N  38.8W   16.0 Crater                 NLF?
            Mairan C                        38.6N  46.0W    7.0 Crater                 NLF?
            Mairan D                        40.9N  45.4W   10.0 Crater                 NLF?
            Mairan E                        37.8N  37.2W    6.0 Crater                 NLF?
            Mairan F                        40.3N  45.1W    9.0 Crater                 NLF?
            Mairan G                        40.9N  50.8W    6.0 Crater                 NLF?
            Mairan H                        39.3N  40.0W    5.0 Crater                 NLF?
            Mairan K                        40.8N  41.0W    6.0 Crater                 NLF?
            Mairan L                        39.0N  43.2W    6.0 Crater                 NLF?
            Mairan N                        39.2N  45.5W    6.0 Crater                 NLF?
            Mairan T                        41.7N  48.3W    3.0 Crater                 NLF?
            Mairan Y                        42.7N  44.0W    7.0 Crater                 NLF?
            Maksutov                        40.5S 168.7W   83.0 Crater                 IAU1970
            Maksutov U                      40.1S 170.9W   21.0 Crater                 AW82
            Malapert                        84.9S  12.9E   69.0 Crater         VL1645  NLF
            Malapert A                      80.4S   3.4W   24.0 Crater                 NLF?
            Malapert B                      79.1S   2.4W   37.0 Crater                 NLF?
            Malapert C                      81.5S  10.5E   40.0 Crater                 NLF?
            Malapert E                      84.3S  21.2E   17.0 Crater                 NLF?
            Malapert F                      81.5S  14.9E   11.0 Crater                 NLF?
            Malapert K                      78.8S   6.8E   36.0 Crater                 NLF?
            Mallet                          45.4S  54.2E   58.0 Crater         S1878   S1878
            Mallet A                        45.9S  53.8E   28.0 Crater                 NLF?
            Mallet B                        46.6S  52.0E   32.0 Crater                 NLF?
            Mallet C                        44.0S  53.8E   28.0 Crater                 NLF?
            Mallet D                        46.0S  57.0E   42.0 Crater                 NLF?
            Mallet E                        45.0S  54.3E    5.0 Crater                 NLF?
            Mallet J                        48.7S  55.9E   52.0 Crater                 NLF?
            Mallet K                        47.6S  57.0E   43.0 Crater                 NLF?
            Mallet L                        47.7S  55.5E   13.0 Crater                 NLF?
            Malyy                           21.9N 105.3E   41.0 Crater                 IAU1970
            Malyy G                         21.7N 106.9E   28.0 Crater                 AW82
            Malyy K                         19.6N 107.0E   15.0 Crater                 AW82
            Malyy L                         19.9N 106.1E   14.0 Crater                 AW82
            Mandel'shtam                     5.4N 162.4E  197.0 Crater                 IAU1970
            Mandel'shtam A                   5.7N 162.4E   64.0 Crater                 AW82
            Mandel'shtam F                   5.2N 166.2E   17.0 Crater                 AW82
            Mandel'shtam G                   4.5N 166.4E   29.0 Crater                 AW82
            Mandel'shtam N                   3.3N 161.6E   25.0 Crater                 AW82
            Mandel'shtam Q                   2.4N 158.8E   20.0 Crater                 AW82
            Mandel'shtam R                   4.5N 159.8E   57.0 Crater                 AW82
            Mandel'shtam T                   5.7N 160.4E   37.0 Crater                 AW82
            Mandel'shtam Y                   9.1N 161.8E   32.0 Crater                 AW82
            Manilius                        14.5N   9.1E   38.0 Crater         VL1645  R1651
            Manilius B                      16.6N   7.3E    6.0 Crater                 NLF?
            Manilius C                      12.1N  10.4E    7.0 Crater                 NLF?
            Manilius D                      13.2N   7.0E    5.0 Crater                 NLF?
            Manilius E                      18.3N   6.4E   49.0 Crater                 NLF?
            Manilius G                      15.5N   9.7E    5.0 Crater                 NLF?
            Manilius H                      17.8N   8.6E    3.0 Crater                 NLF?
            Manilius K                      11.9N  11.2E    3.0 Crater                 NLF?
            Manilius T                      13.4N  10.6E    4.0 Crater                 NLF?
            Manilius U                      13.8N  10.8E    4.0 Crater                 NLF?
            Manilius W                      13.4N  12.9E    4.0 Crater                 NLF?
            Manilius X                      14.4N  13.4E    3.0 Crater                 NLF?
            Manilius Z                      16.4N  11.7E    3.0 Crater                 NLF?
            Manners                          4.6N  20.0E   15.0 Crater                 NLF
            Manners A                        4.6N  19.1E    3.0 Crater                 NLF?
            Manuel                          24.5N  11.3E    0.5 Crater         X       IAU1976
            Manzinus                        67.7S  26.8E   98.0 Crater         R1651   NLF
            Manzinus A                      68.4S  27.5E   20.0 Crater                 NLF?
            Manzinus B                      63.7S  21.1E   28.0 Crater                 NLF?
            Manzinus C                      70.1S  22.1E   25.0 Crater                 NLF?
            Manzinus D                      69.6S  24.7E   34.0 Crater                 NLF?
            Manzinus E                      68.9S  25.4E   18.0 Crater                 NLF?
            Manzinus F                      63.9S  19.7E   18.0 Crater                 NLF?
            Manzinus G                      69.6S  26.0E   16.0 Crater                 NLF?
            Manzinus H                      68.6S  19.2E   13.0 Crater                 NLF?
            Manzinus J                      66.3S  23.5E   12.0 Crater                 NLF?
            Manzinus K                      63.3S  20.3E   12.0 Crater                 NLF?
            Manzinus L                      64.3S  22.7E   20.0 Crater                 NLF?
            Manzinus M                      63.4S  22.8E    6.0 Crater                 NLF?
            Manzinus N                      70.2S  28.8E   14.0 Crater                 NLF?
            Manzinus O                      64.9S  25.0E    5.0 Crater                 NLF?
            Manzinus P                      67.8S  29.4E    6.0 Crater                 NLF?
            Manzinus R                      65.9S  30.0E   16.0 Crater                 NLF?
            Manzinus S                      66.4S  27.3E   11.0 Crater                 NLF?
            Manzinus T                      67.5S  32.9E   21.0 Crater                 NLF?
            Manzinus U                      68.6S  34.5E   21.0 Crater                 NLF?
            Maraldi                         19.4N  34.9E   39.0 Crater         S1791   S1791
            Maraldi A                       20.0N  36.3E    8.0 Crater                 NLF?
            Maraldi D                       16.7N  36.1E   67.0 Crater                 NLF?
            Maraldi E                       17.8N  35.8E   31.0 Crater                 NLF?
            Maraldi F                       19.2N  35.8E   18.0 Crater                 NLF?
            Maraldi N                       18.4N  36.8E    5.0 Crater                 NLF?
            Maraldi R                       20.3N  33.2E    5.0 Crater                 NLF?
            Maraldi W                       13.2N  36.1E    4.0 Crater                 NLF?
            Marci                           22.6N 167.0W   25.0 Crater                 IAU1970
            Marci B                         25.2N 166.3W   28.0 Crater                 AW82
            Marci C                         24.3N 165.4W   26.0 Crater                 AW82
            Marco Polo                      15.4N   2.0W   28.0 Crater         M1834   M1834
            Marco Polo A                    14.9N   2.0W    7.0 Crater                 NLF?
            Marco Polo B                    17.2N   1.9W    7.0 Crater                 NLF?
            Marco Polo C                    14.0N   5.0W    7.0 Crater                 NLF?
            Marco Polo D                    15.0N   3.7W    6.0 Crater                 NLF?
            Marco Polo F                    15.7N   4.5W    4.0 Crater                 NLF?
            Marco Polo G                    16.7N   1.9W    5.0 Crater                 NLF?
            Marco Polo H                    17.8N   1.7W    6.0 Crater                 NLF?
            Marco Polo J                    17.9N   1.2W    5.0 Crater                 NLF?
            Marco Polo K                    18.2N   1.4W   10.0 Crater                 NLF?
            Marco Polo L                    14.8N   5.0W   19.0 Crater                 NLF?
            Marco Polo M                    17.6N   1.1W   37.0 Crater                 NLF?
            Marco Polo P                    16.9N   0.2W   31.0 Crater                 NLF?
            Marco Polo S                    17.8N   0.0E   21.0 Crater                 NLF?
            Marco Polo T                    13.6N   1.0W    3.0 Crater                 NLF?
            Marconi                          9.6S 145.1E   73.0 Crater                 IAU1970
            Marconi C                        8.4S 146.8E    9.0 Crater                 AW82
            Marconi H                       11.0S 147.5E   41.0 Crater                 AW82
            Marconi L                       11.7S 145.3E   38.0 Crater                 AW82
            Marconi S                       10.0S 143.1E   14.0 Crater                 AW82
            Marinus                         39.4S  76.5E   58.0 Crater         M1834   M1834
            Marinus A                       39.9S  73.2E   27.0 Crater                 NLF?
            Marinus B                       39.6S  74.8E   59.0 Crater                 NLF?
            Marinus C                       38.0S  73.5E   37.0 Crater                 NLF?
            Marinus E                       36.2S  76.7E   17.0 Crater                 NLF?
            Marinus F                       41.3S  74.8E   17.0 Crater                 NLF?
            Marinus G                       40.4S  76.6E   21.0 Crater                 NLF?
            Marinus H                       40.2S  77.7E   16.0 Crater                 NLF?
            Marinus J                       39.6S  71.0E   10.0 Crater                 NLF?
            Marinus M                       37.5S  80.8E   26.0 Crater                 NLF?
            Marinus N                       37.6S  78.4E   16.0 Crater                 NLF?
            Marinus R                       38.0S  75.3E   44.0 Crater                 NLF?
            Mariotte                        28.5S 139.1W   65.0 Crater                 IAU1970
            Mariotte P                      29.9S 139.7W   30.0 Crater                 AW82
            Mariotte R                      30.1S 141.6W   33.0 Crater                 AW82
            Mariotte U                      27.9S 142.8W   34.0 Crater                 AW82
            Mariotte X                      25.3S 140.0W   20.0 Crater                 AW82
            Mariotte Z                      22.9S 139.0W   47.0 Crater                 AW82
            Marius                          11.9N  50.8W   41.0 Crater         VL1645  R1651
            Marius A                        12.6N  46.0W   15.0 Crater         VL1645  NLF?
            Marius B                        16.3N  47.3W   12.0 Crater         VL1645  NLF?
            Marius C                        14.0N  47.6W   11.0 Crater                 NLF?
            Marius D                        11.4N  45.0W    9.0 Crater                 NLF?
            Marius E                        12.1N  52.7W    6.0 Crater                 NLF?
            Marius F                        12.1N  45.3W    6.0 Crater                 NLF?
            Marius G                        12.1N  50.6W    3.0 Crater                 NLF?
            Marius H                        11.3N  50.3W    5.0 Crater                 NLF?
            Marius J                        10.5N  46.9W    3.0 Crater                 NLF?
            Marius K                         9.4N  50.6W    4.0 Crater                 NLF?
            Marius L                        15.9N  55.7W    8.0 Crater                 NLF?
            Marius M                        17.4N  54.9W    6.0 Crater                 NLF?
            Marius N                        18.7N  54.7W    4.0 Crater                 NLF?
            Marius P                        17.9N  51.3W    4.0 Crater                 NLF?
            Marius Q                        16.5N  56.2W    5.0 Crater                 NLF?
            Marius R                        13.6N  50.3W    5.0 Crater                 NLF?
            Marius S                        13.9N  47.1W    7.0 Crater                 NLF?
            Marius U                         9.6N  47.6W    3.0 Crater                 NLF?
            Marius V                         9.9N  48.3W    2.0 Crater                 NLF?
            Marius W                         9.4N  49.7W    3.0 Crater                 NLF?
            Marius X                         9.7N  54.9W    5.0 Crater                 NLF?
            Marius Y                         9.8N  50.7W    2.0 Crater                 NLF?
            Markov                          53.4N  62.7W   40.0 Crater         RLA1963 IAU1964
            Markov E                        50.6N  60.1W   13.0 Crater                 NLF?
            Markov F                        50.0N  61.8W    8.0 Crater                 NLF?
            Markov G                        50.0N  56.2W    5.0 Crater                 NLF?
            Markov U                        51.9N  60.2W   29.0 Crater                 NLF?
            Marth                           31.1S  29.3W    6.0 Crater         K1898   K1898
            Marth K                         29.9S  28.7W    3.0 Crater                 NLF?
            Mary                            18.9N  27.4E    1.0 Crater         X       IAU1976
            Maskelyne                        2.2N  30.1E   23.0 Crater         VL1645  L1824
            Maskelyne A                      0.1N  34.0E   29.0 Crater         VL1645  NLF?
            Maskelyne B                      2.0N  28.9E    9.0 Crater                 NLF?
            Maskelyne C                      1.1N  32.7E    9.0 Crater                 NLF?
            Maskelyne D                      2.5N  32.5E   33.0 Crater                 NLF?
            Maskelyne F                      4.2N  35.3E   21.0 Crater                 NLF?
            Maskelyne G                      2.4N  26.7E    6.0 Crater                 NLF?
            Maskelyne J                      3.2N  32.7E    4.0 Crater                 NLF?
            Maskelyne K                      3.3N  29.6E    5.0 Crater                 NLF?
            Maskelyne M                      7.8N  27.9E    8.0 Crater                 NLF?
            Maskelyne N                      5.4N  30.3E    5.0 Crater                 NLF?
            Maskelyne P                      0.5N  34.1E   10.0 Crater                 NLF?
            Maskelyne R                      3.0N  31.3E   13.0 Crater                 NLF?
            Maskelyne T                      0.0S  36.6E    5.0 Crater                 NLF?
            Maskelyne W                      0.9N  29.2E    4.0 Crater                 NLF?
            Maskelyne X                      1.3N  27.4E    4.0 Crater                 NLF?
            Maskelyne Y                      1.8N  28.1E    4.0 Crater                 NLF?
            Mason                           42.6N  30.5E   33.0 Crater         M1834   M1834
            Mason A                         42.8N  30.1E    5.0 Crater                 NLF?
            Mason B                         41.8N  29.6E   10.0 Crater                 NLF?
            Mason C                         42.9N  33.8E   12.0 Crater                 NLF?
            Maunder                         14.6S  93.8W   55.0 Crater                 IAU1970
            Maunder A                        3.2S  90.5W   15.0 Crater                 AW82
            Maunder B                        9.0S  90.3W   17.0 Crater                 AW82
            Maupertuis                      49.6N  27.3W   45.0 Crater         S1791   S1791
            Maupertuis A                    50.6N  24.7W   14.0 Crater                 NLF?
            Maupertuis B                    51.3N  26.7W    6.0 Crater                 NLF?
            Maupertuis C                    50.2N  24.0W   11.0 Crater                 NLF?
            Maupertuis K                    49.3N  25.0W    6.0 Crater                 NLF?
            Maupertuis L                    51.3N  29.2W    6.0 Crater                 NLF?
            Maurolycus                      42.0S  14.0E  114.0 Crater         VL1645  R1651
            Maurolycus A                    43.5S  14.2E   15.0 Crater                 NLF?
            Maurolycus B                    40.3S  11.7E   12.0 Crater                 NLF?
            Maurolycus C                    38.6S  10.8E    9.0 Crater                 NLF?
            Maurolycus D                    39.0S  13.2E   45.0 Crater                 NLF?
            Maurolycus E                    38.4S   9.8E    6.0 Crater                 NLF?
            Maurolycus F                    40.6S  12.2E   25.0 Crater                 NLF?
            Maurolycus G                    44.4S  11.5E    7.0 Crater                 NLF?
            Maurolycus H                    38.2S  10.4E    7.0 Crater                 NLF?
            Maurolycus J                    42.5S  14.0E    9.0 Crater                 NLF?
            Maurolycus K                    40.0S  12.6E    8.0 Crater                 NLF?
            Maurolycus L                    42.1S  14.5E    6.0 Crater                 NLF?
            Maurolycus M                    41.9S  12.6E   10.0 Crater                 NLF?
            Maurolycus N                    41.0S  14.1E    7.0 Crater                 NLF?
            Maurolycus P                    38.1S  12.8E    4.0 Crater                 NLF?
            Maurolycus R                    40.7S  16.2E    5.0 Crater                 NLF?
            Maurolycus S                    42.0S  17.1E    7.0 Crater                 NLF?
            Maurolycus T                    41.3S  11.4E   10.0 Crater                 NLF?
            Maurolycus W                    42.7S  15.2E    4.0 Crater                 NLF?
            Maury                           37.1N  39.6E   17.0 Crater                 NLF
            Maury A                         36.0N  41.8E   21.0 Crater                 NLF?
            Maury B                         35.1N  42.0E    9.0 Crater                 NLF?
            Maury C                         37.0N  38.6E   28.0 Crater                 NLF?
            Maury D                         38.2N  37.8E    8.0 Crater                 NLF?
            Maury J                         39.1N  40.1E    6.0 Crater                 NLF?
            Maury K                         39.5N  41.1E    5.0 Crater                 NLF?
            Maury L                         40.3N  42.5E    4.0 Crater                 NLF?
            Maury M                         40.8N  42.6E   16.0 Crater                 NLF?
            Maury N                         40.4N  41.9E   17.0 Crater                 NLF?
            Maury P                         39.9N  38.0E   12.0 Crater                 NLF?
            Maury T                         40.0N  43.3E    3.0 Crater                 NLF?
            Maury U                         39.3N  37.0E    5.0 Crater                 NLF?
            Mavis                           29.8N  26.4W    1.0 Crater         X       IAU1976
            Maxwell                         30.2N  98.9E  107.0 Crater         BML1960 IAU1961
            McAdie                           2.1N  92.1E   45.0 Crater                 IAU1973
            McAuliffe                       33.0S 148.9W   19.0 Crater         N       IAU1988
            McClure                         15.3S  50.3E   23.0 Crater                 NLF
            McClure A                       15.7S  49.1E    6.0 Crater                 NLF?
            McClure B                       15.4S  49.3E    9.0 Crater                 NLF?
            McClure C                       14.7S  49.8E   27.0 Crater                 NLF?
            McClure D                       14.8S  51.8E   22.0 Crater                 NLF?
            McClure M                       14.2S  51.3E   21.0 Crater                 NLF?
            McClure N                       14.2S  52.7E    9.0 Crater                 NLF?
            McClure P                       14.8S  53.5E   16.0 Crater                 NLF?
            McClure S                       13.8S  53.4E    4.0 Crater                 NLF?
            McCool                          41.7S 146.3W   21.0 Crater                 IAU2006?
            McDonald                        30.4N  20.9W    7.0 Crater                 IAU1973
            McKellar                        15.7S 170.8W   51.0 Crater                 IAU1970
            McKellar B                      13.1S 169.1W   16.0 Crater                 AW82
            McKellar S                      16.0S 173.3W   23.0 Crater                 AW82
            McKellar T                      15.1S 173.0W   45.0 Crater                 AW82
            McKellar U                      13.9S 174.5W   37.0 Crater                 AW82
            McLaughlin                      47.1N  92.9W   79.0 Crater                 IAU1970
            McLaughlin A                    51.6N  92.4W   35.0 Crater                 AW82
            McLaughlin B                    50.2N  91.2W   43.0 Crater                 AW82
            McLaughlin C                    48.5N  91.9W   60.0 Crater                 AW82
            McLaughlin P                    45.0N  94.6W   34.0 Crater                 AW82
            McLaughlin U                    47.2N  97.0W   30.0 Crater                 AW82
            McLaughlin Z                    52.6N  92.8W   21.0 Crater                 AW82
            McMath                          17.3N 165.6W   86.0 Crater                 IAU1970
            McMath A                        19.2N 165.3W   15.0 Crater                 AW82
            McMath J                        14.8N 163.3W   36.0 Crater                 AW82
            McMath M                        16.1N 165.5W   15.0 Crater                 AW82
            McMath P                        13.4N 168.6W   28.0 Crater                 AW82
            McMath Q                        14.5N 167.7W   20.0 Crater                 AW82
            McNair                          35.7S 147.3W   29.0 Crater         N       IAU1988
            McNally                         22.6N 127.2W   47.0 Crater                 IAU1970
            McNally T                       22.3N 129.0W   19.0 Crater                 AW82
            McNally Y                       24.2N 127.5W   22.0 Crater                 AW82
            Mechnikov                       11.0S 149.0W   60.0 Crater                 IAU1970
            Mechnikov C                      9.9S 148.0W   35.0 Crater                 AW82
            Mechnikov D                     10.2S 147.2W   53.0 Crater                 AW82
            Mechnikov F                     11.3S 145.0W   30.0 Crater                 AW82
            Mechnikov G                     11.8S 146.6W   17.0 Crater                 AW82
            Mechnikov U                     10.6S 150.9W   30.0 Crater                 AW82
            Mechnikov Z                      9.3S 149.2W   21.0 Crater                 AW82
            Mee                             43.7S  35.3W  126.0 Crater         VL1645  W1926
            Mee A                           44.4S  29.1W   14.0 Crater                 NLF?
            Mee B                           44.6S  31.1W   15.0 Crater                 NLF?
            Mee C                           45.3S  28.7W   13.0 Crater                 NLF?
            Mee D                           45.3S  32.9W    9.0 Crater                 NLF?
            Mee E                           43.0S  35.3W   16.0 Crater                 NLF?
            Mee F                           43.3S  36.7W   12.0 Crater                 NLF?
            Mee G                           45.5S  40.7W   23.0 Crater                 NLF?
            Mee H                           44.1S  39.4W   48.0 Crater                 NLF?
            Mee J                           44.5S  40.6W   10.0 Crater                 NLF?
            Mee K                           44.4S  41.6W    9.0 Crater                 NLF?
            Mee L                           44.0S  41.5W    8.0 Crater                 NLF?
            Mee M                           45.8S  29.1W    8.0 Crater                 NLF?
            Mee N                           45.2S  42.2W    6.0 Crater                 NLF?
            Mee P                           45.9S  30.0W   14.0 Crater                 NLF?
            Mee Q                           43.6S  33.9W    1.0 Crater                 NLF?
            Mee R                           44.0S  43.4W   10.0 Crater                 NLF?
            Mee S                           43.2S  41.0W   12.0 Crater                 NLF?
            Mee T                           42.5S  38.2W    9.0 Crater                 NLF?
            Mee U                           42.8S  33.9W    8.0 Crater                 NLF?
            Mee V                           45.5S  42.4W    7.0 Crater                 NLF?
            Mee W                           43.6S  35.5W    5.0 Crater                 NLF?
            Mee X                           41.5S  36.0W    7.0 Crater                 NLF?
            Mee Y                           44.3S  36.8W    7.0 Crater                 NLF?
            Mee Z                           44.7S  42.6W   12.0 Crater                 NLF?
            Mees                            13.6N  96.1W   50.0 Crater                 IAU1970
            Mees A                          15.7N  95.2W   36.0 Crater                 AW82
            Mees J                          12.3N  94.7W   26.0 Crater                 AW82
            Mees Y                          15.7N  96.6W   85.0 Crater                 AW82
            Meggers                         24.3N 123.0E   52.0 Crater                 IAU1970
            Meggers S                       24.0N 119.8E   42.0 Crater                 AW82
            Meitner                         10.5S 112.7E   87.0 Crater                 IAU1970
            Meitner A                        8.1S 113.5E   17.0 Crater                 AW82
            Meitner C                        9.7S 113.7E   19.0 Crater                 AW82
            Meitner H                       11.9S 116.0E   13.0 Crater                 AW82
            Meitner J                       12.1S 115.1E   15.0 Crater                 AW82
            Meitner R                       12.0S 109.4E   16.0 Crater                 AW82
            Melissa                          8.1N 121.8E   18.0 Crater         X       IAU1979
            Mendel                          48.8S 109.4W  138.0 Crater         BML1960 IAU1961
            Mendel B                        46.5S 107.7W   18.0 Crater                 AW82
            Mendel J                        51.6S 107.4W   58.0 Crater                 AW82
            Mendel V                        46.7S 116.7W   66.0 Crater                 AW82
            Mendeleev                        5.7N 140.9E  313.0 Crater                 IAU1970
            Mendeleev P                      2.7N 139.4E   29.0 Crater                 AW82
            Menelaus                        16.3N  16.0E   26.0 Crater         VL1645  R1651
            Menelaus A                      17.1N  13.4E    7.0 Crater                 NLF?
            Menelaus C                      14.8N  14.5E    4.0 Crater                 NLF?
            Menelaus D                      13.2N  16.3E    4.0 Crater                 NLF?
            Menelaus E                      13.6N  15.9E    3.0 Crater                 NLF?
            Menzel                           3.4N  36.9E    3.0 Crater                 IAU1979
            Mercator                        29.3S  26.1W   46.0 Crater         S1791   S1791
            Mercator A                      30.6S  27.8W    9.0 Crater                 NLF?
            Mercator B                      29.1S  25.1W    8.0 Crater                 NLF?
            Mercator C                      29.1S  26.9W    8.0 Crater                 NLF?
            Mercator D                      29.3S  25.3W    7.0 Crater                 NLF?
            Mercator E                      30.0S  26.8W    5.0 Crater                 NLF?
            Mercator F                      29.6S  26.8W    4.0 Crater                 NLF?
            Mercator G                      31.1S  25.0W   14.0 Crater                 NLF?
            Mercator K                      30.6S  22.7W    4.0 Crater                 NLF?
            Mercator L                      30.7S  23.5W    4.0 Crater                 NLF?
            Mercator M                      30.2S  23.6W    4.0 Crater                 NLF?
            Mercurius                       46.6N  66.2E   67.0 Crater         VL1645  NLF
            Mercurius A                     48.0N  73.6E   20.0 Crater                 NLF?
            Mercurius B                     47.4N  70.0E   13.0 Crater                 NLF?
            Mercurius C                     47.5N  59.4E   26.0 Crater                 NLF?
            Mercurius D                     46.1N  68.6E   50.0 Crater                 NLF?
            Mercurius E                     49.7N  73.3E   29.0 Crater                 NLF?
            Mercurius F                     45.2N  62.9E   17.0 Crater                 NLF?
            Mercurius G                     45.1N  64.3E   13.0 Crater                 NLF?
            Mercurius H                     49.2N  63.6E   10.0 Crater                 NLF?
            Mercurius J                     47.2N  59.0E    9.0 Crater                 NLF?
            Mercurius K                     47.4N  73.2E   21.0 Crater                 NLF?
            Mercurius L                     45.9N  64.3E   12.0 Crater                 NLF?
            Mercurius M                     50.9N  73.9E   40.0 Crater                 NLF?
            Merrill                         75.2N 116.3W   57.0 Crater                 IAU1970
            Merrill X                       77.0N 119.2W   34.0 Crater                 AW82
            Merrill Y                       76.8N 115.4W   35.0 Crater                 AW82
            Mersenius                       21.5S  49.2W   84.0 Crater         R1651   R1651
            Mersenius B                     21.0S  51.6W   15.0 Crater                 NLF?
            Mersenius C                     19.8S  45.9W   14.0 Crater                 NLF?
            Mersenius D                     23.1S  46.8W   34.0 Crater                 NLF?
            Mersenius E                     22.5S  46.0W   10.0 Crater                 NLF?
            Mersenius H                     22.5S  49.9W   15.0 Crater                 NLF?
            Mersenius J                     21.0S  52.8W    5.0 Crater                 NLF?
            Mersenius K                     21.2S  50.7W    5.0 Crater                 NLF?
            Mersenius L                     19.9S  48.4W    3.0 Crater                 NLF?
            Mersenius M                     21.2S  48.3W    5.0 Crater                 NLF?
            Mersenius N                     22.1S  49.2W    3.0 Crater                 NLF?
            Mersenius P                     19.9S  47.8W   42.0 Crater                 NLF?
            Mersenius R                     19.3S  47.6W    4.0 Crater                 NLF?
            Mersenius S                     19.2S  46.9W   16.0 Crater                 NLF?
            Mersenius U                     23.0S  50.0W    4.0 Crater                 NLF?
            Mersenius V                     22.9S  50.5W    5.0 Crater                 NLF?
            Mersenius W                     23.0S  50.8W    5.0 Crater                 NLF?
            Mersenius X                     22.4S  47.9W    4.0 Crater                 NLF?
            Mersenius Y                     22.7S  48.2W    4.0 Crater                 NLF?
            Mersenius Z                     21.0S  50.6W    3.0 Crater                 NLF?
            Meshcherskiy                    12.2N 125.5E   65.0 Crater                 IAU1970
            Meshcherskiy K                   9.6N 126.8E   17.0 Crater                 AW82
            Meshcherskiy X                  16.0N 124.2E   39.0 Crater                 AW82
            Messala                         39.2N  60.5E  125.0 Crater         R1651   R1651
            Messala A                       36.6N  53.8E   26.0 Crater                 NLF?
            Messala B                       37.4N  59.9E   18.0 Crater                 NLF?
            Messala C                       40.9N  65.8E   12.0 Crater                 NLF?
            Messala D                       40.5N  67.8E   28.0 Crater                 NLF?
            Messala E                       40.0N  64.9E   40.0 Crater                 NLF?
            Messala F                       38.9N  64.4E   32.0 Crater                 NLF?
            Messala G                       39.1N  68.6E   29.0 Crater                 NLF?
            Messala J                       41.1N  61.2E   15.0 Crater                 NLF?
            Messala K                       41.1N  58.5E   13.0 Crater                 NLF?
            Messier                          1.9S  47.6E   11.0 Crater         M1834   M1834
            Messier A                        2.0S  47.0E   13.0 Crater                 NLF?
            Messier B                        0.9S  48.0E    6.0 Crater                 NLF?
            Messier D                        3.6S  46.3E    8.0 Crater                 NLF?
            Messier E                        3.3S  45.4E    5.0 Crater                 NLF?
            Messier J                        1.5S  52.1E    4.0 Crater                 NLF?
            Messier L                        1.2S  51.8E    6.0 Crater                 NLF?
            Metius                          40.3S  43.3E   87.0 Crater         VL1645  NLF
            Metius B                        40.1S  44.3E   14.0 Crater                 NLF?
            Metius C                        44.2S  49.1E   11.0 Crater                 NLF?
            Metius D                        42.6S  48.4E   11.0 Crater                 NLF?
            Metius E                        39.7S  42.8E    6.0 Crater                 NLF?
            Metius F                        39.1S  42.9E    8.0 Crater                 NLF?
            Metius G                        40.3S  45.3E    9.0 Crater                 NLF?
            Meton                           73.6N  18.8E  130.0 Crater         R1651   R1651
            Meton A                         73.3N  31.3E   14.0 Crater                 NLF?
            Meton B                         71.2N  18.0E    6.0 Crater                 NLF?
            Meton C                         70.6N  19.0E   77.0 Crater                 NLF?
            Meton D                         72.2N  24.7E   78.0 Crater                 NLF?
            Meton E                         75.3N  15.3E   42.0 Crater                 NLF?
            Meton F                         72.0N  14.2E   51.0 Crater                 NLF?
            Meton G                         72.9N  28.4E   10.0 Crater                 NLF?
            Meton W                         67.4N  17.3E    7.0 Crater                 NLF?
            Mezentsev                       72.1N 128.7W   89.0 Crater                 IAU1970
            Mezentsev M                     68.7N 126.8W   74.0 Crater                 AW82
            Mezentsev Q                     69.4N 135.6W   26.0 Crater                 AW82
            Mezentsev S                     71.5N 136.9W   21.0 Crater                 AW82
            Michael                         25.1N   0.2E    4.0 Crater         X       IAU1976
            Michelson                        7.2N 120.7W  123.0 Crater                 IAU1970
            Michelson G                      5.7N 118.8W   27.0 Crater                 AW82
            Michelson H                      4.6N 116.8W   35.0 Crater                 AW82
            Michelson V                      8.0N 124.4W   20.0 Crater                 AW82
            Michelson W                      7.5N 121.3W   25.0 Crater                 AW82
            Middle Crescent                  3.2S  23.4W    0.0 Crater (A)             IAU1973
            Milankovi|vc                    77.2N 168.8E  101.0 Crater                 IAU1970
            Milankovi|vc E                  78.0N 177.2W   46.0 Crater                 AW82
            Milichius                       10.0N  30.2W   12.0 Crater                 NLF
            Milichius A                      9.3N  32.0W    9.0 Crater                 NLF?
            Milichius C                     11.2N  29.4W    3.0 Crater                 NLF?
            Milichius D                      8.0N  28.2W    4.0 Crater                 NLF?
            Milichius E                     10.7N  28.1W    3.0 Crater                 NLF?
            Milichius K                      8.5N  30.3W    4.0 Crater                 NLF?
            Miller                          39.3S   0.8E   61.0 Crater                 NLF
            Miller A                        37.7S   1.8E   39.0 Crater                 NLF?
            Miller B                        37.6S   1.0E   12.0 Crater                 NLF?
            Miller C                        38.2S   0.3W   36.0 Crater                 NLF?
            Miller D                        38.0S   3.1E    5.0 Crater                 NLF?
            Miller E                        38.8S   2.8E    6.0 Crater                 NLF?
            Miller K                        39.8S   0.9E    4.0 Crater                 NLF?
            Millikan                        46.8N 121.5E   98.0 Crater                 IAU1970
            Millikan B                      49.8N 123.5E   21.0 Crater                 AW82
            Millikan J                      45.8N 124.6E   36.0 Crater                 AW82
            Millikan Q                      43.9N 118.6E   33.0 Crater                 AW82
            Millikan R                      46.0N 117.7E   49.0 Crater                 AW82
            Mills                            8.6N 156.0E   32.0 Crater                 IAU1970
            Mills B                         10.7N 156.9E   24.0 Crater                 AW82
            Mills C                          9.8N 157.3E   14.0 Crater                 AW82
            Mills K                          6.8N 157.0E   26.0 Crater                 AW82
            Mills R                          8.1N 154.8E   19.0 Crater                 AW82
            Mills W                         10.0N 154.2E   18.0 Crater                 AW82
            Milne                           31.4S 112.2E  272.0 Crater                 IAU1970
            Milne K                         32.5S 113.1E   65.0 Crater                 AW82
            Milne L                         33.7S 112.7E   26.0 Crater                 AW82
            Milne M                         35.7S 112.1E   54.0 Crater                 AW82
            Milne N                         35.5S 110.8E   37.0 Crater                 AW82
            Milne P                         37.1S 107.7E   95.0 Crater                 AW82
            Milne Q                         34.3S 107.3E   75.0 Crater                 AW82
            Mineur                          25.0N 161.3W   73.0 Crater                 IAU1970
            Mineur D                        25.9N 159.2W   20.0 Crater                 AW82
            Mineur V                        26.2N 163.1W   26.0 Crater                 AW82
            Mineur X                        27.1N 162.7W   31.0 Crater                 AW82
            Minkowski                       56.5S 146.0W  113.0 Crater                 IAU1970
            Minkowski S                     56.1S 145.6W   13.0 Crater                 AW82
            Minnaert                        67.8S 179.6E  125.0 Crater                 IAU1970
            Minnaert C                      64.2S 176.0W   15.0 Crater                 AW82
            Minnaert N                      71.1S 176.1E   33.0 Crater                 AW82
            Minnaert W                      63.4S 174.1E   24.0 Crater                 AW82
            Mitchell                        49.7N  20.2E   30.0 Crater                 NLF
            Mitchell B                      48.3N  19.3E    6.0 Crater                 NLF?
            Mitchell E                      47.6N  21.7E    8.0 Crater                 NLF?
            Mitra                           18.0N 154.7W   92.0 Crater                 IAU1970
            Mitra A                         20.8N 154.1W   46.0 Crater                 AW82
            Mitra J                         15.9N 153.2W   46.0 Crater                 AW82
            Mitra Y                         21.5N 155.2W   26.0 Crater                 AW82
            M|:obius                        15.8N 101.2E   50.0 Crater                 IAU1970
            Mohorovi|vci|%c                 19.0S 165.0W   51.0 Crater                 IAU1970
            Mohorovi|vci|%c A               16.0S 163.1W   20.0 Crater                 AW82
            Mohorovi|vci|%c D               17.8S 162.1W   18.0 Crater                 AW82
            Mohorovi|vci|%c F               18.9S 163.6W   23.0 Crater                 AW82
            Mohorovi|vci|%c R               19.9S 167.7W   42.0 Crater                 AW82
            Mohorovi|vci|%c W               17.7S 166.5W   21.0 Crater                 AW82
            Mohorovi|vci|%c Z               18.6S 165.1W   14.0 Crater                 AW82
            Moigno                          66.4N  28.9E   36.0 Crater                 NLF
            Moigno A                        64.8N  29.7E   16.0 Crater                 NLF?
            Moigno B                        64.6N  26.1E   26.0 Crater                 NLF?
            Moigno C                        65.9N  29.0E    9.0 Crater                 NLF?
            Moigno D                        65.2N  27.7E   23.0 Crater                 NLF?
            Moiseev                          9.5N 103.3E   59.0 Crater                 IAU1970
            Moiseev S                        8.7N 100.7E   29.0 Crater                 AW82
            Moiseev Z                       11.2N 103.4E   80.0 Crater                 AW82
            Moissan                          4.8N 137.4E   21.0 Crater                 IAU1976
            Moltke                           0.6S  24.2E    6.0 Crater         K1898   K1898
            Moltke A                         1.0S  23.2E    4.0 Crater                 NLF?
            Moltke B                         1.0S  25.2E    5.0 Crater                 NLF?
            Monge                           19.2S  47.6E   36.0 Crater         S1878   S1878
            Monira                          12.6S   1.7W    2.0 Crater         X       IAU1976
            Montanari                       45.8S  20.6W   76.0 Crater         F1936   F1936
            Montanari D                     45.9S  22.1W   24.0 Crater                 NLF?
            Montanari W                     44.8S  18.1W    7.0 Crater                 NLF?
            Montes Recti B                  48.4N  18.3W    8.0 Crater                 NLF?
            Montgolfier                     47.3N 159.8W   88.0 Crater                 IAU1970
            Montgolfier J                   46.4N 158.2W   28.0 Crater                 AW82
            Montgolfier P                   46.1N 160.9W   36.0 Crater                 AW82
            Montgolfier W                   49.3N 164.4W   37.0 Crater                 AW82
            Montgolfier Y                   50.5N 161.3W   40.0 Crater                 AW82
            Moore                           37.4N 177.5W   54.0 Crater                 IAU1970
            Moore F                         37.4N 175.0W   24.0 Crater                 AW82
            Moore L                         36.1N 177.1W   27.0 Crater                 AW82
            Moretus                         70.6S   5.8W  111.0 Crater         R1651   R1651
            Moretus A                       70.4S  13.8W   32.0 Crater                 NLF?
            Moretus C                       72.6S  11.2W   17.0 Crater                 NLF?
            Morley                           2.8S  64.6E   14.0 Crater                 IAU1976
            Morozov                          5.0N 127.4E   42.0 Crater                 IAU1970
            Morozov C                        6.1N 128.5E   10.0 Crater                 AW82
            Morozov E                        6.0N 130.2E   15.0 Crater                 AW82
            Morozov F                        5.4N 130.0E   60.0 Crater                 AW82
            Morozov Y                        7.3N 127.0E   45.0 Crater                 AW82
            Morse                           22.1N 175.1W   77.0 Crater                 IAU1970
            Morse N                         20.2N 176.1W   25.0 Crater                 AW82
            Morse T                         22.0N 179.5W   34.0 Crater                 AW82
            Moseley                         20.9N  90.1W   90.0 Crater         RLA1963 IAU1964
            Moseley C                       22.3N  88.5W   18.0 Crater                 RLA1963?
            Moseley D                       22.9N  87.6W   17.0 Crater                 RLA1963?
            M|:osting                        0.7S   5.9W   24.0 Crater         VL1645  M1834
            M|:osting A                      3.2S   5.2W   13.0 Crater                 NLF?
            M|:osting B                      2.7S   7.4W    7.0 Crater                 NLF?
            M|:osting C                      1.8S   8.0W    4.0 Crater                 NLF?
            M|:osting D                      0.3S   5.1W    7.0 Crater                 NLF?
            M|:osting E                      0.3N   4.6W   44.0 Crater                 NLF?
            M|:osting K                      0.7S   7.4W    3.0 Crater                 NLF?
            M|:osting L                      0.6S   3.4W    3.0 Crater                 NLF?
            M|:osting M                      1.3S   4.3W   31.0 Crater                 NLF?
            M|:osting U                      3.2S   6.6W   18.0 Crater                 NLF?
            Mouchez                         78.3N  26.6W   81.0 Crater                 NLF
            Mouchez A                       80.8N  29.9W   51.0 Crater                 NLF?
            Mouchez B                       78.2N  22.8W    8.0 Crater                 NLF?
            Mouchez C                       77.4N  26.0W   13.0 Crater                 NLF?
            Mouchez J                       79.4N  38.2W   17.0 Crater                 NLF?
            Mouchez L                       78.6N  40.3W   20.0 Crater                 NLF?
            Mouchez M                       80.2N  49.3W   17.0 Crater                 NLF?
            Moulton                         61.1S  97.2E   49.0 Crater                 IAU1970
            Moulton H                       61.5S 100.6E   44.0 Crater                 AW82
            Moulton P                       63.9S  93.5E   14.0 Crater                 AW82
            M|:uller                         7.6S   2.1E   22.0 Crater         L1935   L1935
            M|:uller A                       8.2S   2.1E   10.0 Crater                 NLF?
            M|:uller F                       7.8S   1.5E    6.0 Crater                 NLF?
            M|:uller O                       7.9S   2.4E   11.0 Crater                 NLF?
            Murakami                        23.3S 140.5W   45.0 Crater         N       IAU1991
            Murchison                        5.1N   0.1W   57.0 Crater                 NLF
            Murchison T                      4.4N   0.1E    3.0 Crater                 NLF?
            Mutus                           63.6S  30.1E   77.0 Crater         VL1645  NLF
            Mutus A                         63.8S  31.8E   16.0 Crater                 NLF?
            Mutus B                         63.9S  29.5E   17.0 Crater                 NLF?
            Mutus C                         61.2S  27.2E   32.0 Crater                 NLF?
            Mutus D                         58.4S  23.3E   22.0 Crater                 NLF?
            Mutus E                         65.5S  36.1E   22.0 Crater                 NLF?
            Mutus F                         66.2S  34.1E   42.0 Crater                 NLF?
            Mutus G                         67.2S  35.1E   17.0 Crater                 NLF?
            Mutus H                         63.6S  24.2E   21.0 Crater                 NLF?
            Mutus J                         62.7S  23.3E    8.0 Crater                 NLF?
            Mutus K                         57.8S  21.5E    7.0 Crater                 NLF?
            Mutus L                         61.8S  24.9E   20.0 Crater                 NLF?
            Mutus M                         59.1S  24.4E   20.0 Crater                 NLF?
            Mutus N                         62.4S  27.6E   11.0 Crater                 NLF?
            Mutus O                         57.7S  23.8E   14.0 Crater                 NLF?
            Mutus P                         59.1S  25.7E   16.0 Crater                 NLF?
            Mutus Q                         62.2S  30.4E    8.0 Crater                 NLF?
            Mutus R                         60.8S  23.9E   27.0 Crater                 NLF?
            Mutus S                         60.5S  22.0E   25.0 Crater                 NLF?
            Mutus T                         59.2S  21.2E   34.0 Crater                 NLF?
            Mutus V                         62.9S  31.3E   24.0 Crater                 NLF?
            Mutus W                         66.6S  40.0E   21.0 Crater                 NLF?
            Mutus X                         67.1S  36.8E   21.0 Crater                 NLF?
            Mutus Y                         64.8S  35.0E   26.0 Crater                 NLF?
            Mutus Z                         64.0S  34.5E   30.0 Crater                 NLF?
            Nagaoka                         19.4N 154.0E   46.0 Crater                 IAU1970
            Nagaoka U                       19.9N 151.4E   30.0 Crater                 AW82
            Nagaoka W                       20.0N 153.0E   29.0 Crater                 AW82
            Nansen                          80.9N  95.3E  104.0 Crater         RLA1963 IAU1964
            Nansen A                        82.8N  63.0E   46.0 Crater                 RLA1963?
            Nansen C                        83.2N  55.5E   34.0 Crater                 RLA1963?
            Nansen D                        83.8N  64.0E   21.0 Crater                 RLA1963?
            Nansen E                        83.3N  71.0E   15.0 Crater                 RLA1963?
            Nansen F                        84.7N  60.0E   62.0 Crater                 RLA1963?
            Nansen U                        81.6N  81.4E   16.0 Crater                 RLA1963?
            Nansen-Apollo                   20.1N  30.5E    1.0 Crater (A)             IAU1973
            Naonobu                          4.6S  57.8E   34.0 Crater                 IAU1976
            Nasireddin                      41.0S   0.2E   52.0 Crater         VL1645  M1834
            Nasireddin B                    39.4S   1.1W    9.0 Crater                 NLF?
            Nasmyth                         50.5S  56.2W   76.0 Crater                 NLF
            Nasmyth D                       49.2S  55.3W   13.0 Crater                 NLF?
            Nasmyth E                       49.9S  57.6W    5.0 Crater                 NLF?
            Nasmyth F                       50.0S  53.5W    9.0 Crater                 NLF?
            Nasmyth G                       49.6S  53.8W    7.0 Crater                 NLF?
            Nassau                          24.9S 177.4E   76.0 Crater                 IAU1970
            Nassau D                        23.7S 179.2W   62.0 Crater                 AW82
            Nassau F                        24.7S 179.2W  112.0 Crater                 AW82
            Nassau Y                        22.5S 176.8E   38.0 Crater                 AW82
            Natasha                         20.0N  31.3W   12.0 Crater         X       IAU1976
            Naumann                         35.4N  62.0W    9.0 Crater         S1878   S1878
            Naumann B                       37.4N  60.6W   10.0 Crater                 NLF?
            Naumann G                       33.6N  60.7W    6.0 Crater                 NLF?
            Neander                         31.3S  39.9E   50.0 Crater         R1651   R1651
            Neander A                       30.9S  39.6E   11.0 Crater                 NLF?
            Neander B                       28.2S  40.1E    9.0 Crater                 NLF?
            Neander C                       28.6S  36.0E   20.0 Crater                 NLF?
            Neander D                       26.5S  42.4E   11.0 Crater                 NLF?
            Neander E                       29.8S  40.7E   25.0 Crater                 NLF?
            Neander F                       32.1S  37.9E   22.0 Crater                 NLF?
            Neander G                       33.4S  43.8E   18.0 Crater                 NLF?
            Neander H                       33.0S  42.4E   13.0 Crater                 NLF?
            Neander J                       34.0S  43.4E   13.0 Crater                 NLF?
            Neander K                       35.0S  39.8E   14.0 Crater                 NLF?
            Neander L                       31.3S  41.8E   21.0 Crater                 NLF?
            Neander M                       34.8S  37.7E   11.0 Crater                 NLF?
            Neander N                       32.4S  37.2E   17.0 Crater                 NLF?
            Neander O                       35.6S  39.1E   13.0 Crater                 NLF?
            Neander P                       28.4S  41.1E    6.0 Crater                 NLF?
            Neander Q                       28.8S  41.4E    6.0 Crater                 NLF?
            Neander R                       33.2S  38.6E   12.0 Crater                 NLF?
            Neander S                       31.9S  42.1E   12.0 Crater                 NLF?
            Neander T                       29.9S  38.4E   10.0 Crater                 NLF?
            Neander V                       31.3S  38.2E    5.0 Crater                 NLF?
            Neander W                       32.3S  38.5E    9.0 Crater                 NLF?
            Neander X                       33.1S  37.8E    8.0 Crater                 NLF?
            Neander Y                       34.5S  38.2E    8.0 Crater                 NLF?
            Neander Z                       33.8S  42.0E    7.0 Crater                 NLF?
            Nearch                          58.5S  39.1E   75.0 Crater         M1834   M1834
            Nearch A                        60.1S  40.1E   43.0 Crater                 NLF?
            Nearch B                        60.9S  35.8E   43.0 Crater                 NLF?
            Nearch C                        62.2S  35.8E   41.0 Crater                 NLF?
            Nearch D                        57.0S  38.0E   10.0 Crater                 NLF?
            Nearch E                        61.4S  33.9E   11.0 Crater                 NLF?
            Nearch F                        62.9S  37.9E    8.0 Crater                 NLF?
            Nearch G                        63.3S  39.8E    5.0 Crater                 NLF?
            Nearch H                        57.6S  40.6E    9.0 Crater                 NLF?
            Nearch J                        57.6S  37.4E    7.0 Crater                 NLF?
            Nearch K                        57.9S  35.3E   13.0 Crater                 NLF?
            Nearch L                        58.4S  35.6E   18.0 Crater                 NLF?
            Nearch M                        58.4S  35.0E    7.0 Crater                 NLF?
            Necho                            5.0S 123.1E   30.0 Crater                 IAU1976
            Necho M                          6.0S 123.1E   12.0 Crater                 AW82
            Necho P                          6.8S 122.0E   75.0 Crater                 AW82
            Necho R                          5.6S 122.0E   18.0 Crater                 AW82
            Necho V                          4.3S 120.6E   16.0 Crater                 AW82
            Neison                          68.3N  25.1E   53.0 Crater         L1935   L1935
            Neison A                        67.4N  26.7E    9.0 Crater                 NLF?
            Neison B                        67.4N  25.9E    8.0 Crater                 NLF?
            Neison C                        67.0N  23.2E    9.0 Crater                 NLF?
            Neison D                        68.0N  22.6E    6.0 Crater                 NLF?
            Neper                            8.5N  84.6E  137.0 Crater         S1791   S1791
            Neper D                          9.2N  80.8E   40.0 Crater                 AW82
            Neper H                         10.4N  78.2E    9.0 Crater                 AW82
            Neper Q                          8.0N  83.1E   12.0 Crater                 AW82
            Nernst                          35.3N  94.8W  116.0 Crater                 IAU1970
            Nernst T                        35.8N  96.9W   25.0 Crater                 AW82
            Neujmin                         27.0S 125.0E  101.0 Crater                 IAU1970
            Neujmin P                       28.5S 124.2E   38.0 Crater                 AW82
            Neujmin Q                       30.0S 121.8E   17.0 Crater                 AW82
            Neujmin T                       27.1S 122.0E   24.0 Crater                 AW82
            Neumayer                        71.1S  70.7E   76.0 Crater         S1878   S1878
            Neumayer A                      75.0S  73.6E   31.0 Crater                 NLF?
            Neumayer M                      71.6S  78.5E   31.0 Crater                 NLF?
            Neumayer N                      70.4S  78.7E   36.0 Crater                 NLF?
            Neumayer P                      70.6S  86.0E   22.0 Crater                 NLF?
            Newcomb                         29.9N  43.8E   41.0 Crater         VL1645  N1876
            Newcomb A                       29.4N  43.5E   19.0 Crater                 NLF?
            Newcomb B                       28.4N  45.6E   23.0 Crater                 NLF?
            Newcomb C                       29.1N  45.3E   29.0 Crater                 NLF?
            Newcomb F                       31.4N  42.5E   28.0 Crater                 NLF?
            Newcomb G                       28.2N  44.6E   16.0 Crater                 NLF?
            Newcomb H                       28.9N  42.4E   12.0 Crater                 NLF?
            Newcomb J                       28.7N  44.3E   23.0 Crater                 NLF?
            Newcomb Q                       30.3N  42.8E   14.0 Crater                 NLF?
            Newton                          76.7S  16.9W   78.0 Crater         VL1645  S1791
            Newton A                        79.7S  19.7W   64.0 Crater                 NLF?
            Newton B                        81.1S  15.4W   44.0 Crater                 NLF?
            Newton C                        74.8S  14.4W   35.0 Crater                 NLF?
            Newton D                        75.9S  14.8W   37.0 Crater                 NLF?
            Newton E                        79.8S  36.9W   17.0 Crater                 NLF?
            Newton F                        72.2S  16.1W    7.0 Crater                 NLF?
            Newton G                        78.2S  18.3W   67.0 Crater                 NLF?
            Nicholson                       26.2S  85.1W   38.0 Crater                 IAU1970
            Nicolai                         42.4S  25.9E   42.0 Crater         VL1645  M1834
            Nicolai A                       42.4S  23.6E   13.0 Crater                 NLF?
            Nicolai B                       43.2S  25.3E   13.0 Crater                 NLF?
            Nicolai C                       44.0S  29.0E   25.0 Crater                 NLF?
            Nicolai D                       41.7S  25.5E    6.0 Crater                 NLF?
            Nicolai E                       40.6S  25.3E   13.0 Crater                 NLF?
            Nicolai G                       42.8S  22.4E   11.0 Crater                 NLF?
            Nicolai H                       43.5S  26.8E   17.0 Crater                 NLF?
            Nicolai J                       40.5S  22.0E    8.0 Crater                 NLF?
            Nicolai K                       42.9S  28.2E   25.0 Crater                 NLF?
            Nicolai L                       44.1S  25.6E   13.0 Crater                 NLF?
            Nicolai M                       42.4S  29.0E   11.0 Crater                 NLF?
            Nicolai P                       43.1S  29.7E   30.0 Crater                 NLF?
            Nicolai Q                       42.3S  30.1E   26.0 Crater                 NLF?
            Nicolai R                       41.5S  25.9E    6.0 Crater                 NLF?
            Nicolai Z                       40.9S  21.5E   24.0 Crater                 NLF?
            Nicollet                        21.9S  12.5W   15.0 Crater         N1876   N1876
            Nicollet B                      20.1S  13.5W    5.0 Crater                 NLF?
            Nicollet D                      23.2S  12.2W    2.0 Crater                 NLF?
            Nielsen                         31.8N  51.8W    9.0 Crater                 IAU1973
            Niepce                          72.7N 119.1W   57.0 Crater                 IAU1970
            Niepce F                        72.5N 113.5W   44.0 Crater                 AW82
            Nijland                         33.0N 134.1E   35.0 Crater                 IAU1970
            Nijland A                       36.2N 134.4E   26.0 Crater                 AW82
            Nijland V                       34.5N 131.6E   35.0 Crater                 AW82
            Nikolaev                        35.2N 151.3E   41.0 Crater                 IAU1970
            Nikolaev G                      34.5N 154.2E   20.0 Crater                 AW82
            Nikolaev J                      31.7N 155.5E   18.0 Crater                 AW82
            Nishina                         44.6S 170.4W   65.0 Crater                 IAU1970
            Nishina T                       43.7S 174.4W   28.0 Crater                 AW82
            Nobel                           15.0N 101.3W   48.0 Crater                 IAU1970
            Nobel B                         17.3N  99.5W   24.0 Crater                 AW82
            Nobel K                         13.1N 100.2W   20.0 Crater                 AW82
            Nobel L                         12.5N 100.9W   38.0 Crater                 AW82
            Nobile                          85.2S  53.5E   73.0 Crater         N       IAU1994
            Nobili                           0.2N  75.9E   42.0 Crater                 IAU1976
            N|:oggerath                     48.8S  45.7W   30.0 Crater         S1878   S1878
            N|:oggerath A                   47.9S  43.4W    7.0 Crater                 NLF?
            N|:oggerath B                   47.0S  43.4W    5.0 Crater                 NLF?
            N|:oggerath C                   45.8S  43.1W   13.0 Crater                 NLF?
            N|:oggerath D                   47.2S  41.5W   14.0 Crater                 NLF?
            N|:oggerath E                   45.2S  43.9W    5.0 Crater                 NLF?
            N|:oggerath F                   48.0S  46.9W    9.0 Crater                 NLF?
            N|:oggerath G                   50.3S  45.8W   21.0 Crater                 NLF?
            N|:oggerath H                   49.6S  47.9W   26.0 Crater                 NLF?
            N|:oggerath J                   48.4S  47.9W   17.0 Crater                 NLF?
            N|:oggerath K                   44.9S  46.3W    4.0 Crater                 NLF?
            N|:oggerath L                   45.2S  47.2W    5.0 Crater                 NLF?
            N|:oggerath M                   44.0S  46.6W   11.0 Crater                 NLF?
            N|:oggerath P                   47.7S  41.8W   10.0 Crater                 NLF?
            N|:oggerath S                   44.5S  46.2W    6.0 Crater                 NLF?
            Nonius                          34.8S   3.8E   69.0 Crater         R1651   NLF
            Nonius A                        35.4S   5.6E   10.0 Crater                 NLF?
            Nonius B                        35.8S   2.0E   21.0 Crater                 NLF?
            Nonius C                        35.4S   1.1E    7.0 Crater                 NLF?
            Nonius D                        35.5S   1.8E    6.0 Crater                 NLF?
            Nonius F                        35.9S   3.8E    7.0 Crater                 NLF?
            Nonius G                        34.7S   5.7E    6.0 Crater                 NLF?
            Nonius K                        33.7S   3.9E   18.0 Crater                 NLF?
            Nonius L                        33.5S   3.5E   31.0 Crater                 NLF?
            Nonius Q                        35.9S   4.2E    7.0 Crater                 NLF?
            Nonius R                        35.9S   3.3E   10.0 Crater                 NLF?
            Nonius S                        34.8S   4.3E    4.0 Crater                 NLF?
            Norman                          11.8S  30.4W   10.0 Crater                 IAU1976
            North Complex                   26.2N   3.6E    2.0 Crater (A)             IAU1973
            North Ray                        8.8S  15.5E    1.0 Crater (A)             IAU1973
            N|:other                        66.6N 113.5W   67.0 Crater                 IAU1970
            N|:other A                      69.3N 112.1W   29.0 Crater                 AW82
            N|:other E                      67.6N 105.1W   47.0 Crater                 AW82
            N|:other T                      66.3N 121.5W   44.0 Crater                 AW82
            N|:other U                      67.6N 123.4W   36.0 Crater                 AW82
            N|:other V                      68.9N 122.4W   26.0 Crater                 AW82
            N|:other X                      68.9N 116.8W   30.0 Crater                 AW82
            Numerov                         70.7S 160.7W  113.0 Crater                 IAU1970
            Numerov G                       71.7S 151.9W   26.0 Crater                 AW82
            Numerov Z                       68.1S 160.0W   44.0 Crater                 AW82
            Nunn                             4.6N  91.1E   19.0 Crater                 IAU1973
            Nu|vsl                          32.3N 167.6E   61.0 Crater                 IAU1970
            Nu|vsl E                        32.9N 168.9E   29.0 Crater                 AW82
            Nu|vsl S                        31.2N 164.1E   42.0 Crater                 AW82
            Nu|vsl Y                        34.3N 166.9E   51.0 Crater                 AW82
            O'Day                           30.6S 157.5E   71.0 Crater                 IAU1970
            O'Day B                         29.1S 158.0E   16.0 Crater                 AW82
            O'Day M                         31.7S 157.1E   16.0 Crater                 AW82
            O'Day T                         30.4S 154.4E   24.0 Crater                 AW82
            Oberth                          62.4N 155.4E   60.0 Crater         N       IAU1997
            Obruchev                        38.9S 162.1E   71.0 Crater                 IAU1970
            Obruchev M                      40.5S 162.2E   46.0 Crater                 AW82
            Obruchev T                      38.5S 157.7E   21.0 Crater                 AW82
            Obruchev V                      36.6S 158.3E   39.0 Crater                 AW82
            Obruchev X                      34.7S 159.5E   18.0 Crater                 AW82
            Oenopides                       57.0N  64.1W   67.0 Crater         R1651   R1651
            Oenopides B                     58.5N  68.6W   34.0 Crater                 NLF?
            Oenopides K                     55.8N  61.2W    6.0 Crater                 NLF?
            Oenopides L                     55.5N  61.9W   10.0 Crater                 NLF?
            Oenopides M                     55.5N  61.1W    6.0 Crater                 NLF?
            Oenopides R                     55.6N  67.9W   56.0 Crater                 NLF?
            Oenopides S                     58.1N  69.9W    7.0 Crater                 NLF?
            Oenopides T                     57.2N  68.9W    8.0 Crater                 NLF?
            Oenopides X                     57.5N  62.4W    5.0 Crater                 NLF?
            Oenopides Y                     57.0N  63.3W    6.0 Crater                 NLF?
            Oenopides Z                     58.9N  67.0W    7.0 Crater                 NLF?
            Oersted                         43.1N  47.2E   42.0 Crater         M1834   M1834
            Oersted A                       43.4N  47.2E    7.0 Crater                 NLF?
            Oersted P                       43.6N  46.0E   21.0 Crater                 NLF?
            Oersted U                       42.4N  44.6E    5.0 Crater                 NLF?
            Ohm                             18.4N 113.5W   64.0 Crater                 IAU1970
            Oken                            43.7S  75.9E   71.0 Crater         M1834   M1834
            Oken A                          43.2S  71.3E   36.0 Crater                 NLF?
            Oken E                          46.1S  78.9E   12.0 Crater                 NLF?
            Oken F                          44.4S  71.5E   21.0 Crater                 NLF?
            Oken L                          43.1S  78.2E   10.0 Crater                 NLF?
            Oken M                          41.8S  75.4E    7.0 Crater                 NLF?
            Oken N                          42.4S  74.5E   40.0 Crater                 NLF?
            Olbers                           7.4N  75.9W   74.0 Crater         M1834   M1834
            Olbers B                         6.8N  74.1W   16.0 Crater                 NLF?
            Olbers D                        10.2N  78.2W  116.0 Crater                 NLF?
            Olbers G                         8.4N  74.5W   10.0 Crater                 NLF?
            Olbers H                         8.7N  74.4W    8.0 Crater                 NLF?
            Olbers K                         6.8N  78.2W   24.0 Crater                 NLF?
            Olbers M                         8.0N  81.2W   33.0 Crater                 NLF?
            Olbers N                         9.0N  79.7W   22.0 Crater                 NLF?
            Olbers S                         6.8N  76.7W   21.0 Crater                 NLF?
            Olbers V                         9.1N  73.0W    7.0 Crater                 NLF?
            Olbers W                         5.9N  81.5W   18.0 Crater                 NLF?
            Olbers Y                         6.5N  83.6W   21.0 Crater                 NLF?
            Olcott                          20.6N 117.8E   81.0 Crater                 IAU1970
            Olcott E                        20.9N 119.8E   59.0 Crater                 AW82
            Olcott L                        18.3N 118.6E   36.0 Crater                 AW82
            Olcott M                        17.9N 117.6E   46.0 Crater                 AW82
            Old Nameless                     3.7S  17.5W    0.0 Crater (A)             IAU1973
            Olivier                         59.1N 138.5E   69.0 Crater                 IAU1979
            Olivier N                       56.7N 137.1E   63.0 Crater                 AW82
            Olivier Y                       61.9N 136.5E   47.0 Crater                 AW82
            Omar Khayyam                    58.0N 102.1W   70.0 Crater                 IAU1970
            Onizuka                         36.2S 148.9W   29.0 Crater         N       IAU1988
            Opelt                           16.3S  17.5W   48.0 Crater         S1878   S1878
            Opelt E                         17.0S  17.8W    8.0 Crater                 NLF?
            Opelt F                         18.1S  18.7W    4.0 Crater                 NLF?
            Opelt G                         16.8S  17.2W    4.0 Crater                 NLF?
            Opelt H                         15.8S  17.3W    3.0 Crater                 NLF?
            Opelt K                         13.6S  17.1W    5.0 Crater                 NLF?
            Oppenheimer                     35.2S 166.3W  208.0 Crater                 IAU1970
            Oppenheimer F                   34.7S 161.5W   35.0 Crater                 AW82
            Oppenheimer H                   36.5S 163.1W   33.0 Crater                 AW82
            Oppenheimer R                   37.3S 170.4W   26.0 Crater                 AW82
            Oppenheimer U                   34.3S 167.9W   38.0 Crater                 AW82
            Oppenheimer V                   32.0S 172.7W   32.0 Crater                 AW82
            Oppenheimer W                   32.1S 169.0W   20.0 Crater                 AW82
            Oppolzer                         1.5S   0.5W   40.0 Crater         K1898   K1898
            Oppolzer A                       0.5S   0.3W    3.0 Crater                 NLF?
            Oppolzer K                       1.7S   0.3W    3.0 Crater                 NLF?
            Oresme                          42.4S 169.2E   76.0 Crater                 IAU1970
            Oresme K                        43.9S 170.0E   24.0 Crater                 AW82
            Oresme Q                        44.0S 167.2E   23.0 Crater                 AW82
            Oresme U                        41.6S 164.8E   84.0 Crater                 AW82
            Oresme V                        40.5S 165.6E   51.0 Crater                 AW82
            Orlov                           25.7S 175.0W   81.0 Crater                 IAU1970
            Orlov D                         24.8S 173.4W   27.0 Crater                 AW82
            Orlov Y                         22.8S 175.1W  126.0 Crater                 AW82
            Orontius                        40.6S   4.6W  105.0 Crater         VL1645  R1651
            Orontius A                      39.1S   2.6W    7.0 Crater                 NLF?
            Orontius B                      40.0S   3.1W   10.0 Crater                 NLF?
            Orontius C                      37.9S   4.1W   15.0 Crater                 NLF?
            Orontius D                      39.4S   6.2W   15.0 Crater                 NLF?
            Orontius E                      39.5S   4.8W    6.0 Crater                 NLF?
            Orontius F                      39.1S   3.9W   41.0 Crater                 NLF?
            Osama                           18.6N   5.2E    0.5 Crater         X       IAU1976
            Osiris                          18.6N  27.6E    1.0 Crater         X       IAU1976
            Osman                           11.0S   6.2W    2.0 Crater         X       IAU1976
            Ostwald                         10.4N 121.9E  104.0 Crater                 IAU1970
            Ostwald Y                       13.6N 121.0E   24.0 Crater                 AW82
            Palisa                           9.4S   7.2W   33.0 Crater         K1898   K1898
            Palisa A                         9.0S   6.7W    5.0 Crater                 NLF?
            Palisa C                         7.7S   6.4W    9.0 Crater                 NLF?
            Palisa D                         8.6S   6.9W    8.0 Crater                 NLF?
            Palisa E                         8.4S   5.7W   18.0 Crater                 NLF?
            Palisa P                         9.6S   7.3W    5.0 Crater                 NLF?
            Palisa T                         8.2S   8.2W   12.0 Crater                 NLF?
            Palisa W                         9.1S   6.3W    4.0 Crater                 NLF?
            Palitzsch                       28.0S  64.5E   41.0 Crater         S1791   S1791
            Palitzsch A                     26.9S  65.8E   31.0 Crater                 NLF?
            Palitzsch B                     26.4S  68.4E   39.0 Crater                 NLF?
            Pallas                           5.5N   1.6W   46.0 Crater         M1834   M1834
            Pallas A                         6.0N   2.3W   11.0 Crater                 NLF?
            Pallas B                         4.2N   2.6W    4.0 Crater                 NLF?
            Pallas C                         4.5N   1.1W    6.0 Crater                 NLF?
            Pallas D                         2.4N   2.6W    4.0 Crater                 NLF?
            Pallas E                         4.0N   1.4W   26.0 Crater                 NLF?
            Pallas F                         3.4N   1.3W   18.0 Crater                 NLF?
            Pallas H                         4.6N   1.5W    5.0 Crater                 NLF?
            Pallas N                         7.0N   0.5E    6.0 Crater                 NLF?
            Pallas V                         1.7N   1.5W    3.0 Crater                 NLF?
            Pallas W                         3.6N   1.3W    3.0 Crater                 NLF?
            Pallas X                         5.2N   3.2W    3.0 Crater                 NLF?
            Palmetto                         8.9S  15.5E    0.0 Crater (A)             IAU1973
            Palmieri                        28.6S  47.7W   40.0 Crater         S1878   S1878
            Palmieri A                      32.2S  48.4W   21.0 Crater                 NLF?
            Palmieri B                      30.8S  48.2W    9.0 Crater                 NLF?
            Palmieri E                      29.2S  48.5W   14.0 Crater                 NLF?
            Palmieri G                      32.5S  47.6W    9.0 Crater                 NLF?
            Palmieri H                      31.5S  47.7W   19.0 Crater                 NLF?
            Palmieri J                      33.6S  49.3W   10.0 Crater                 NLF?
            Paneth                          63.0N  94.8W   65.0 Crater                 IAU1970
            Paneth A                        65.3N  94.1W   47.0 Crater                 AW82
            Paneth K                        61.7N  92.9W   31.0 Crater                 AW82
            Paneth W                        65.0N 101.2W   28.0 Crater                 AW82
            Pannekoek                        4.2S 140.5E   71.0 Crater                 IAU1970
            Pannekoek A                      0.9S 141.0E   28.0 Crater                 AW82
            Pannekoek D                      2.6S 143.5E   28.0 Crater                 AW82
            Pannekoek R                      5.4S 138.3E   71.0 Crater                 AW82
            Pannekoek S                      4.4S 140.1E   18.0 Crater                 AW82
            Pannekoek T                      4.1S 138.2E   25.0 Crater                 AW82
            Papaleksi                       10.2N 164.0E   97.0 Crater                 IAU1970
            Papaleksi Q                      9.0N 162.7E   14.0 Crater                 AW82
            Paracelsus                      23.0S 163.1E   83.0 Crater                 IAU1970
            Paracelsus C                    21.7S 165.1E   24.0 Crater                 AW82
            Paracelsus E                    23.0S 167.2E   66.0 Crater                 AW82
            Paracelsus G                    24.6S 165.7E   27.0 Crater                 AW82
            Paracelsus H                    26.0S 166.2E   12.0 Crater                 AW82
            Paracelsus M                    26.1S 163.0E   41.0 Crater                 AW82
            Paracelsus N                    25.4S 162.0E    7.0 Crater                 AW82
            Paracelsus P                    24.9S 161.7E   63.0 Crater                 AW82
            Paracelsus Y                    21.5S 162.7E   26.0 Crater                 AW82
            Paraskevopoulos                 50.4N 149.9W   94.0 Crater                 IAU1970
            Paraskevopoulos E               50.6N 149.4W   24.0 Crater                 AW82
            Paraskevopoulos H               49.7N 147.2W   48.0 Crater                 AW82
            Paraskevopoulos N               47.2N 150.8W   26.0 Crater                 AW82
            Paraskevopoulos Q               48.6N 152.3W   35.0 Crater                 AW82
            Paraskevopoulos R               48.6N 154.7W   23.0 Crater                 AW82
            Paraskevopoulos S               49.1N 154.9W   67.0 Crater                 AW82
            Paraskevopoulos U               50.4N 154.7W   30.0 Crater                 AW82
            Paraskevopoulos X               53.6N 152.2W   26.0 Crater                 AW82
            Paraskevopoulos Y               53.1N 150.4W   46.0 Crater                 AW82
            Parenago                        25.9N 108.5W   93.0 Crater                 IAU1970
            Parenago T                      26.0N 110.7W   18.0 Crater                 AW82
            Parenago W                      27.8N 109.7W   49.0 Crater                 AW82
            Parenago Z                      28.9N 109.0W   18.0 Crater                 AW82
            Parkhurst                       33.4S 103.6E   96.0 Crater                 IAU1970
            Parkhurst B                     32.0S 104.4E   30.0 Crater                 AW82
            Parkhurst D                     32.8S 105.4E   27.0 Crater                 AW82
            Parkhurst K                     36.3S 105.2E   11.0 Crater                 AW82
            Parkhurst Q                     35.0S 101.6E   37.0 Crater                 AW82
            Parkhurst X                     31.5S 102.3E   12.0 Crater                 AW82
            Parkhurst Y                     29.9S 102.8E   49.0 Crater                 AW82
            Parrot                          14.5S   3.3E   70.0 Crater         M1834   M1834
            Parrot A                        15.3S   2.1E   21.0 Crater                 NLF?
            Parrot B                        13.6S   2.5E   10.0 Crater                 NLF?
            Parrot C                        18.5S   1.2E   31.0 Crater         H1647   NLF?
            Parrot D                        14.2S   3.6E   21.0 Crater                 NLF?
            Parrot E                        16.0S   2.3E   20.0 Crater                 NLF?
            Parrot F                        16.1S   1.4E   19.0 Crater                 NLF?
            Parrot G                        17.4S   2.6E   28.0 Crater                 NLF?
            Parrot H                        17.6S   1.2E   19.0 Crater                 NLF?
            Parrot J                        17.0S   1.8E   23.0 Crater                 NLF?
            Parrot K                        14.1S   1.8E   44.0 Crater                 NLF?
            Parrot L                        18.0S   0.9E    7.0 Crater                 NLF?
            Parrot M                        18.0S   2.0E    7.0 Crater                 NLF?
            Parrot N                        13.8S   0.5E    5.0 Crater                 NLF?
            Parrot O                        16.9S   2.6E   10.0 Crater                 NLF?
            Parrot P                        18.6S   3.0E    6.0 Crater                 NLF?
            Parrot Q                        15.1S   1.1E    5.0 Crater                 NLF?
            Parrot R                        13.5S   3.2E   10.0 Crater                 NLF?
            Parrot S                        15.9S   3.6E   10.0 Crater                 NLF?
            Parrot T                        15.9S   4.2E    8.0 Crater                 NLF?
            Parrot U                        14.1S   4.5E   10.0 Crater                 NLF?
            Parrot V                        13.2S   0.8E   24.0 Crater                 NLF?
            Parrot W                        13.2S   1.5E    5.0 Crater                 NLF?
            Parrot X                        14.5S   1.9E    4.0 Crater                 NLF?
            Parrot Y                        13.9S   0.7E   10.0 Crater                 NLF?
            Parry                            7.9S  15.8W   47.0 Crater         VL1645  M1834
            Parry B                          8.9S  13.0W    1.0 Crater                 NLF?
            Parry C                          6.8S  12.7W    3.0 Crater                 NLF?
            Parry D                          7.9S  15.7W    3.0 Crater                 NLF?
            Parry E                          8.3S  16.3W    6.0 Crater                 NLF?
            Parry F                          7.6S  14.7W    4.0 Crater                 NLF?
            Parry L                          6.3S  14.7W    7.0 Crater                 NLF?
            Parry M                          8.9S  14.5W   26.0 Crater                 NLF?
            Parsons                         37.3N 171.2W   40.0 Crater                 IAU1970
            Parsons D                       38.5N 168.6W   54.0 Crater                 AW82
            Parsons E                       37.6N 167.8W   26.0 Crater                 AW82
            Parsons L                       33.6N 170.0W   31.0 Crater                 AW82
            Parsons M                       33.8N 171.7W   23.0 Crater                 AW82
            Parsons N                       34.2N 173.2W   43.0 Crater                 AW82
            Parsons P                       35.2N 172.8W   28.0 Crater                 AW82
            Pascal                          74.6N  70.3W  115.0 Crater         R1651   IAU1964
            Pascal A                        72.9N  74.6W   28.0 Crater                 NLF?
            Pascal F                        75.6N  75.6W   27.0 Crater                 NLF?
            Pascal G                        73.0N  65.7W   14.0 Crater                 NLF?
            Pascal J                        72.2N  69.0W   14.0 Crater                 NLF?
            Pascal L                        73.8N  63.0W   15.0 Crater                 NLF?
            Paschen                         13.5S 139.8W  124.0 Crater                 IAU1970
            Paschen G                       14.3S 135.4W   29.0 Crater                 AW82
            Paschen H                       16.0S 135.6W   27.0 Crater                 AW82
            Paschen K                       17.9S 138.9W   57.0 Crater                 AW82
            Paschen L                       16.4S 139.5W   38.0 Crater                 AW82
            Paschen M                       16.1S 140.0W   94.0 Crater                 AW82
            Paschen S                       14.5S 142.0W   48.0 Crater                 AW82
            Paschen U                       13.2S 143.0W   29.0 Crater                 AW82
            Pasteur                         11.9S 104.6E  224.0 Crater         BML1960 IAU1961
            Pasteur A                        7.0S 105.7E   25.0 Crater                 AW82
            Pasteur B                        8.2S 105.8E   20.0 Crater                 AW82
            Pasteur D                        8.8S 108.8E   36.0 Crater                 AW82
            Pasteur E                       10.8S 108.5E   19.0 Crater                 AW82
            Pasteur G                       11.6S 105.7E   21.0 Crater                 AW82
            Pasteur H                       12.1S 106.4E   21.0 Crater                 AW82
            Pasteur M                       12.2S 104.6E   10.0 Crater                 AW82
            Pasteur Q                       13.6S 101.5E   24.0 Crater                 AW82
            Pasteur S                       12.2S 102.0E   29.0 Crater                 AW82
            Pasteur T                       11.6S 100.1E   41.0 Crater                 AW82
            Pasteur U                        9.8S 101.5E   37.0 Crater                 AW82
            Pasteur V                        9.0S 100.8E   22.0 Crater                 AW82
            Pasteur Y                        8.0S 103.5E   52.0 Crater                 AW82
            Pasteur Z                        6.8S 104.2E   15.0 Crater                 AW82
            Patricia                        25.0N   0.3E    5.0 Crater         X       IAU1976
            Patsaev                         16.7S 133.4E   55.0 Crater                 IAU1973
            Patsaev K                       18.8S 134.5E   53.0 Crater                 AW82
            Patsaev Q                       17.8S 132.7E   34.0 Crater                 AW82
            Pauli                           44.5S 137.5E   84.0 Crater                 IAU1970
            Pauli E                         44.1S 141.4E   24.0 Crater                 AW82
            Pavlov                          28.8S 142.5E  148.0 Crater                 IAU1970
            Pavlov G                        29.1S 145.4E   43.0 Crater                 AW82
            Pavlov H                        28.6S 143.5E   18.0 Crater                 AW82
            Pavlov M                        32.3S 141.8E   74.0 Crater                 AW82
            Pavlov P                        33.7S 139.5E   44.0 Crater                 AW82
            Pavlov T                        28.0S 138.0E   46.0 Crater                 AW82
            Pavlov V                        26.7S 138.0E   38.0 Crater                 AW82
            Pawsey                          44.5N 145.0E   60.0 Crater                 IAU1970
            Peary                           88.6N  33.0E   73.0 Crater         RLA1963 IAU1964
            Pease                           12.5N 106.1W   38.0 Crater                 IAU1970
            Peek                             2.6N  86.9E   12.0 Crater                 IAU1973
            Peirce                          18.3N  53.5E   18.0 Crater         N1876   N1876
            Peirce C                        18.8N  49.9E   19.0 Crater                 NLF?
            Peirescius                      46.5S  67.6E   61.0 Crater         S1878   S1878
            Peirescius A                    45.2S  71.3E   15.0 Crater                 NLF?
            Peirescius B                    45.6S  70.5E   18.0 Crater                 NLF?
            Peirescius C                    46.2S  71.5E   41.0 Crater                 NLF?
            Peirescius D                    48.1S  71.9E   43.0 Crater                 NLF?
            Peirescius G                    48.1S  67.7E   25.0 Crater                 NLF?
            Peirescius H                    45.3S  73.1E    8.0 Crater                 NLF?
            Peirescius J                    45.1S  66.8E   15.0 Crater                 NLF?
            Pentland                        64.6S  11.5E   56.0 Crater         VL1645  M1834
            Pentland A                      67.4S  13.5E   44.0 Crater                 NLF?
            Pentland B                      66.2S  14.1E   30.0 Crater                 NLF?
            Pentland C                      65.0S  16.3E   37.0 Crater                 NLF?
            Pentland D                      63.2S  14.1E   35.0 Crater                 NLF?
            Pentland DA                     62.9S  14.3E   54.0 Crater                 NLF?
            Pentland E                      67.9S  13.4E   11.0 Crater                 NLF?
            Pentland F                      62.1S  11.3E   12.0 Crater                 NLF?
            Pentland J                      64.4S  14.6E    9.0 Crater                 NLF?
            Pentland K                      66.7S  17.7E   12.0 Crater                 NLF?
            Pentland L                      65.6S  17.8E   23.0 Crater                 NLF?
            Pentland M                      64.5S  17.2E    7.0 Crater                 NLF?
            Pentland N                      63.5S  17.2E   25.0 Crater                 NLF?
            Pentland O                      63.0S  18.3E   15.0 Crater                 NLF?
            Pentland P                      67.7S  14.5E    8.0 Crater                 NLF?
            Perel'man                       24.0S 106.0E   46.0 Crater                 IAU1970
            Perel'man E                     23.9S 107.2E   28.0 Crater                 AW82
            Perel'man S                     24.3S 104.4E   26.0 Crater                 AW82
            Perepelkin                      10.0S 129.0E   97.0 Crater                 IAU1970
            Perepelkin P                    12.4S 127.3E   25.0 Crater                 AW82
            Perkin                          47.2N 175.9W   62.0 Crater                 IAU1970
            Perrine                         42.5N 127.8W   86.0 Crater                 IAU1970
            Perrine E                       42.8N 124.9W   40.0 Crater                 AW82
            Perrine G                       42.1N 124.6W   58.0 Crater                 AW82
            Perrine L                       39.2N 127.2W   37.0 Crater                 AW82
            Perrine S                       42.5N 128.6W   63.0 Crater                 AW82
            Perrine T                       42.4N 130.2W   34.0 Crater                 AW82
            Petavius                        25.1S  60.4E  188.0 Crater         VL1645  R1651
            Petavius A                      26.0S  61.6E    5.0 Crater                 NLF?
            Petavius B                      19.9S  57.1E   33.0 Crater                 NLF?
            Petavius C                      27.7S  60.1E   11.0 Crater                 NLF?
            Petavius D                      24.0S  64.4E   17.0 Crater                 NLF?
            Petermann                       74.2N  66.3E   73.0 Crater         S1878   S1878
            Petermann A                     75.0N  87.1E   17.0 Crater                 NLF?
            Petermann B                     72.8N  63.8E   11.0 Crater                 NLF?
            Petermann C                     71.6N  57.7E   13.0 Crater                 NLF?
            Petermann D                     77.1N  65.8E   31.0 Crater                 NLF?
            Petermann E                     72.5N  53.7E   13.0 Crater                 NLF?
            Petermann R                     75.0N  56.7E  115.0 Crater                 NLF?
            Petermann S                     75.2N  61.9E    8.0 Crater                 NLF?
            Petermann X                     75.1N  73.3E    9.0 Crater                 NLF?
            Petermann Y                     76.0N  87.4E   13.0 Crater                 NLF?
            Peters                          68.1N  29.5E   15.0 Crater         S1878   S1878
            Petit                            2.3N  63.5E    5.0 Crater                 IAU1976
            Petrie                          45.3N 108.4E   33.0 Crater                 IAU1970
            Petrie U                        45.6N 106.3E   20.0 Crater                 AW82
            Petropavlovskiy                 37.2N 114.8W   63.0 Crater                 IAU1970
            Petropavlovskiy M               34.5N 114.7W   22.0 Crater                 AW82
            Petrov                          61.4S  88.0E   49.0 Crater                 IAU1970
            Petrov A                        62.5S  88.3E   17.0 Crater                 AW82
            Petrov B                        62.3S  90.5E   31.0 Crater                 AW82
            Pettit                          27.5S  86.6W   35.0 Crater                 IAU1970
            Pettit C                        24.8S  88.9W    8.0 Crater                 AW82
            Petzval                         62.7S 110.4W   90.0 Crater                 IAU1970
            Petzval C                       60.3S 107.8W   52.0 Crater                 AW82
            Petzval D                       60.2S 105.9W   23.0 Crater                 AW82
            Phillips                        26.6S  75.3E  122.0 Crater                 NLF
            Phillips A                      27.1S  73.6E   13.0 Crater                 NLF?
            Phillips B                      23.3S  70.5E   40.0 Crater                 NLF?
            Phillips C                      26.5S  71.2E    6.0 Crater                 NLF?
            Phillips D                      25.0S  70.8E   61.0 Crater                 NLF?
            Phillips E                      25.6S  68.3E    8.0 Crater                 NLF?
            Phillips F                      25.1S  68.8E   11.0 Crater                 NLF?
            Phillips G                      24.6S  68.7E    8.0 Crater                 NLF?
            Phillips H                      25.3S  71.6E    7.0 Crater                 NLF?
            Phillips W                      25.3S  72.8E   63.0 Crater                 NLF?
            Philolaus                       72.1N  32.4W   70.0 Crater         R1651   R1651
            Philolaus B                     69.6N  24.3W   11.0 Crater                 NLF?
            Philolaus C                     71.1N  32.7W   95.0 Crater                 NLF?
            Philolaus D                     73.9N  27.8W   91.0 Crater                 NLF?
            Philolaus E                     69.6N  18.7W   12.0 Crater                 NLF?
            Philolaus F                     68.1N  18.3W    8.0 Crater                 NLF?
            Philolaus G                     69.0N  23.6W   95.0 Crater                 NLF?
            Philolaus U                     75.0N  33.0W   13.0 Crater                 NLF?
            Philolaus W                     75.6N  35.9W   17.0 Crater                 NLF?
            Phocylides                      52.7S  57.0W  121.0 Crater         R1651   R1651
            Phocylides A                    54.6S  51.6W   19.0 Crater                 NLF?
            Phocylides B                    53.8S  51.7W    8.0 Crater                 NLF?
            Phocylides C                    51.0S  52.6W   46.0 Crater                 NLF?
            Phocylides D                    53.2S  51.6W    7.0 Crater                 NLF?
            Phocylides E                    55.5S  57.7W   32.0 Crater                 NLF?
            Phocylides F                    54.8S  57.4W   23.0 Crater                 NLF?
            Phocylides G                    51.2S  50.8W   14.0 Crater                 NLF?
            Phocylides J                    54.1S  62.7W   22.0 Crater                 NLF?
            Phocylides K                    52.2S  48.9W   14.0 Crater                 NLF?
            Phocylides KA                   52.0S  48.9W   12.0 Crater                 NLF?
            Phocylides KB                   51.7S  48.8W   14.0 Crater                 NLF?
            Phocylides L                    56.9S  62.7W    9.0 Crater                 NLF?
            Phocylides M                    55.5S  60.5W    9.0 Crater                 NLF?
            Phocylides N                    52.1S  55.5W   15.0 Crater                 NLF?
            Phocylides S                    55.9S  59.8W   10.0 Crater                 NLF?
            Phocylides V                    56.6S  60.6W    8.0 Crater                 NLF?
            Phocylides X                    50.5S  50.6W    7.0 Crater                 NLF?
            Phocylides Z                    50.0S  50.8W    8.0 Crater                 NLF?
            Piazzi                          36.6S  67.9W  134.0 Crater         M1834   M1834
            Piazzi A                        39.5S  66.7W   13.0 Crater                 NLF?
            Piazzi B                        37.5S  66.2W    8.0 Crater                 NLF?
            Piazzi C                        37.1S  62.6W   28.0 Crater                 NLF?
            Piazzi F                        35.7S  61.1W   11.0 Crater                 NLF?
            Piazzi G                        40.2S  64.6W   10.0 Crater                 NLF?
            Piazzi H                        40.2S  65.7W    8.0 Crater                 NLF?
            Piazzi K                        37.5S  68.0W    8.0 Crater                 NLF?
            Piazzi M                        35.9S  67.4W    6.0 Crater                 NLF?
            Piazzi N                        35.4S  66.0W   16.0 Crater                 NLF?
            Piazzi P                        38.8S  67.3W   20.0 Crater                 NLF?
            Piazzi Smyth                    41.9N   3.2W   13.0 Crater                 NLF
            Piazzi Smyth B                  40.5N   3.4W    4.0 Crater                 NLF?
            Piazzi Smyth M                  45.0N   4.2W    2.0 Crater                 NLF?
            Piazzi Smyth U                  40.8N   2.7W    3.0 Crater                 NLF?
            Piazzi Smyth V                  40.9N   4.7W    7.0 Crater                 NLF?
            Piazzi Smyth W                  42.2N   1.9W    3.0 Crater                 NLF?
            Piazzi Smyth Y                  42.8N   3.4W    4.0 Crater                 NLF?
            Piazzi Smyth Z                  42.1N   4.6W    3.0 Crater                 NLF?
            Picard                          14.6N  54.7E   22.0 Crater         H1647   S1791
            Picard K                         9.7N  54.5E    8.0 Crater                 NLF?
            Picard L                        10.3N  54.3E    8.0 Crater                 NLF?
            Picard M                        10.3N  54.0E    9.0 Crater                 NLF?
            Picard N                        10.5N  53.6E   20.0 Crater                 NLF?
            Picard P                         8.9N  53.7E    7.0 Crater                 NLF?
            Picard Y                        13.2N  60.1E    6.0 Crater                 NLF?
            Piccolomini                     29.7S  32.2E   87.0 Crater         VL1645  R1651
            Piccolomini A                   26.4S  30.4E   16.0 Crater                 NLF?
            Piccolomini B                   25.8S  30.5E   12.0 Crater                 NLF?
            Piccolomini C                   27.6S  31.1E   26.0 Crater                 NLF?
            Piccolomini D                   26.9S  32.2E   17.0 Crater                 NLF?
            Piccolomini E                   26.1S  31.8E   18.0 Crater                 NLF?
            Piccolomini F                   26.3S  31.8E   72.0 Crater                 NLF?
            Piccolomini G                   27.2S  34.7E   18.0 Crater                 NLF?
            Piccolomini H                   27.9S  27.6E    9.0 Crater                 NLF?
            Piccolomini J                   25.0S  30.1E   28.0 Crater                 NLF?
            Piccolomini K                   25.7S  29.7E    8.0 Crater                 NLF?
            Piccolomini L                   26.1S  33.7E   12.0 Crater                 NLF?
            Piccolomini M                   27.8S  31.8E   23.0 Crater                 NLF?
            Piccolomini N                   27.3S  26.2E    9.0 Crater                 NLF?
            Piccolomini O                   26.6S  30.5E   11.0 Crater                 NLF?
            Piccolomini P                   30.4S  35.9E   12.0 Crater                 NLF?
            Piccolomini Q                   30.8S  36.4E   14.0 Crater                 NLF?
            Piccolomini R                   29.3S  35.3E   16.0 Crater                 NLF?
            Piccolomini S                   31.6S  34.1E   21.0 Crater                 NLF?
            Piccolomini T                   28.5S  29.0E    8.0 Crater                 NLF?
            Piccolomini W                   26.8S  29.2E    6.0 Crater                 NLF?
            Piccolomini X                   26.9S  31.5E    8.0 Crater                 NLF?
            Pickering                        2.9S   7.0E   15.0 Crater         K1898   K1898
            Pickering A                      1.5S   7.1E    5.0 Crater                 NLF?
            Pickering B                      2.1S   7.4E    6.0 Crater                 NLF?
            Pickering C                      1.5S   6.1E    4.0 Crater                 NLF?
            Pico B                          46.5N  15.3W   12.0 Crater                 NLF?
            Pico C                          47.2N   6.6W    5.0 Crater                 NLF?
            Pico D                          43.4N  11.3W    7.0 Crater                 NLF?
            Pico E                          43.0N  10.3W    9.0 Crater                 NLF?
            Pico F                          42.2N  10.2W    4.0 Crater                 NLF?
            Pico G                          46.6N  10.4W    4.0 Crater                 NLF?
            Pico K                          44.6N   7.5W    3.0 Crater                 NLF?
            Pictet                          43.6S   7.4W   62.0 Crater         M1834   M1834
            Pictet A                        45.0S   7.9W   34.0 Crater                 NLF?
            Pictet C                        42.7S   7.7W    7.0 Crater                 NLF?
            Pictet D                        46.0S   9.0W   21.0 Crater                 NLF?
            Pictet E                        41.3S   7.7W   70.0 Crater                 NLF?
            Pictet F                        42.8S   6.3W   11.0 Crater                 NLF?
            Pictet N                        41.5S   8.1W    7.0 Crater                 NLF?
            Pikel'ner                       47.9S 123.3E   47.0 Crater                 IAU1979
            Pikel'ner F                     48.0S 127.6E   30.0 Crater                 AW82
            Pikel'ner G                     49.1S 128.3E   24.0 Crater                 AW82
            Pikel'ner K                     50.3S 124.8E   36.0 Crater                 AW82
            Pikel'ner S                     48.7S 120.2E   62.0 Crater                 AW82
            Pikel'ner Y                     47.4S 123.1E   52.0 Crater                 AW82
            Pil|^atre                       60.2S  86.9W   50.0 Crater         N       IAU1991
            Pingr|%e                        58.7S  73.7W   88.0 Crater         S1791   S1791
            Pingr|%e B                      57.6S  65.3W   19.0 Crater                 NLF?
            Pingr|%e C                      58.4S  68.3W   23.0 Crater                 NLF?
            Pingr|%e D                      56.6S  84.1W   16.0 Crater                 NLF?
            Pingr|%e E                      56.5S  78.9W   14.0 Crater                 NLF?
            Pingr|%e F                      59.9S  71.0W   16.0 Crater                 NLF?
            Pingr|%e G                      57.9S  68.9W   13.0 Crater                 NLF?
            Pingr|%e J                      59.1S  68.8W   18.0 Crater                 NLF?
            Pingr|%e K                      55.2S  77.7W   13.0 Crater                 NLF?
            Pingr|%e L                      53.8S  85.8W   17.0 Crater                 NLF?
            Pingr|%e M                      53.5S  83.6W   19.0 Crater                 NLF?
            Pingr|%e N                      58.1S  83.7W   19.0 Crater                 NLF?
            Pingr|%e P                      54.0S  69.5W   16.0 Crater                 NLF?
            Pingr|%e S                      60.3S  82.0W   70.0 Crater                 NLF?
            Pingr|%e U                      56.3S  66.0W   12.0 Crater                 NLF?
            Pingr|%e W                      56.4S  70.9W    9.0 Crater                 NLF?
            Pingr|%e X                      58.9S  79.3W    9.0 Crater                 NLF?
            Pingr|%e Y                      58.4S  78.0W   13.0 Crater                 NLF?
            Pingr|%e Z                      55.1S  82.7W   12.0 Crater                 NLF?
            Pirquet                         20.3S 139.6E   65.0 Crater                 IAU1970
            Pirquet S                       20.6S 137.7E   30.0 Crater                 AW82
            Pirquet X                       17.2S 138.5E   17.0 Crater                 AW82
            Pitatus                         29.9S  13.5W  106.0 Crater         R1651   R1651
            Pitatus A                       31.4S  13.2W    7.0 Crater                 NLF?
            Pitatus B                       32.3S  10.4W   16.0 Crater                 NLF?
            Pitatus C                       28.4S  12.4W   12.0 Crater                 NLF?
            Pitatus D                       30.9S  12.0W   10.0 Crater                 NLF?
            Pitatus E                       28.9S  10.1W    6.0 Crater                 NLF?
            Pitatus G                       29.8S  11.4W   18.0 Crater                 NLF?
            Pitatus H                       30.5S  15.7W   15.0 Crater                 NLF?
            Pitatus J                       26.5S  13.5W    5.0 Crater                 NLF?
            Pitatus K                       30.4S   8.9W    6.0 Crater                 NLF?
            Pitatus L                       29.1S   8.6W    5.0 Crater                 NLF?
            Pitatus M                       32.1S  11.0W   14.0 Crater                 NLF?
            Pitatus N                       31.2S  10.9W   12.0 Crater                 NLF?
            Pitatus P                       31.0S  10.9W   16.0 Crater                 NLF?
            Pitatus Q                       30.5S  10.8W   12.0 Crater                 NLF?
            Pitatus R                       31.1S  14.6W    7.0 Crater                 NLF?
            Pitatus S                       27.3S  14.0W   12.0 Crater                 NLF?
            Pitatus T                       29.4S  11.2W    5.0 Crater                 NLF?
            Pitatus V                       28.9S  11.7W    5.0 Crater                 NLF?
            Pitatus W                       27.9S  11.2W   13.0 Crater                 NLF?
            Pitatus X                       28.4S  11.6W   19.0 Crater                 NLF?
            Pitatus Z                       28.3S  10.3W   25.0 Crater                 NLF?
            Pitiscus                        50.4S  30.9E   82.0 Crater         VL1645  NLF
            Pitiscus A                      50.3S  30.9E   10.0 Crater                 NLF?
            Pitiscus B                      47.7S  30.5E   25.0 Crater                 NLF?
            Pitiscus C                      47.1S  28.3E   17.0 Crater                 NLF?
            Pitiscus D                      49.0S  26.5E   22.0 Crater                 NLF?
            Pitiscus E                      50.9S  29.3E   13.0 Crater                 NLF?
            Pitiscus F                      46.9S  29.5E   13.0 Crater                 NLF?
            Pitiscus G                      47.6S  25.2E   15.0 Crater                 NLF?
            Pitiscus J                      48.2S  26.5E    7.0 Crater                 NLF?
            Pitiscus K                      46.3S  29.9E   16.0 Crater                 NLF?
            Pitiscus L                      51.2S  33.6E    9.0 Crater                 NLF?
            Pitiscus R                      48.6S  28.3E   25.0 Crater                 NLF?
            Pitiscus S                      47.7S  27.6E   28.0 Crater                 NLF?
            Pitiscus T                      46.9S  27.9E    8.0 Crater                 NLF?
            Pitiscus U                      48.9S  33.3E    6.0 Crater                 NLF?
            Pitiscus V                      49.3S  34.3E    5.0 Crater                 NLF?
            Pitiscus W                      50.3S  27.7E   24.0 Crater                 NLF?
            Piton A                         39.8N   1.0W    6.0 Crater                 NLF?
            Piton B                         39.3N   0.1W    5.0 Crater                 NLF?
            Pizzetti                        34.9S 118.8E   44.0 Crater                 IAU1970
            Pizzetti C                      33.1S 121.1E   10.0 Crater                 AW82
            Pizzetti W                      33.8S 117.7E   14.0 Crater                 AW82
            Plana                           42.2N  28.2E   44.0 Crater         M1834   M1834
            Plana C                         42.7N  27.1E   14.0 Crater                 NLF?
            Plana D                         41.7N  26.2E    7.0 Crater                 NLF?
            Plana E                         40.5N  23.6E    6.0 Crater                 NLF?
            Plana F                         39.8N  24.0E    5.0 Crater                 NLF?
            Plana G                         39.1N  22.9E    9.0 Crater                 NLF?
            Planck                          57.9S 136.8E  314.0 Crater         RLA1963 IAU1964
            Planck A                        54.7S 137.3E   19.0 Crater                 AW82
            Planck B                        56.0S 137.4E   46.0 Crater                 AW82
            Planck C                        53.4S 141.3E   43.0 Crater                 AW82
            Planck J                        62.9S 145.3E   26.0 Crater                 AW82
            Planck K                        65.0S 146.2E   23.0 Crater                 AW82
            Planck L                        66.9S 141.8E   23.0 Crater                 AW82
            Planck W                        56.0S 131.2E   17.0 Crater                 AW82
            Planck X                        54.3S 129.5E   25.0 Crater                 AW82
            Planck Y                        55.0S 132.0E   40.0 Crater                 AW82
            Planck Z                        56.4S 135.2E   72.0 Crater                 AW82
            Plant|%e                        10.2S 163.3E   37.0 Crater                 IAU1979
            Plaskett                        82.1N 174.3E  109.0 Crater                 IAU1976
            Plaskett H                      80.2N 165.1W   20.0 Crater                 AW82
            Plaskett S                      81.6N 148.7E   17.0 Crater                 AW82
            Plaskett U                      83.0N 160.2E   14.0 Crater                 AW82
            Plaskett V                      82.5N 118.5E   49.0 Crater                 AW82
            Plato                           51.6N   9.4W  109.0 Crater         VL1645  R1651
            Plato B                         53.0N  17.2W   13.0 Crater                 NLF?
            Plato C                         53.2N  19.4W   10.0 Crater                 NLF?
            Plato D                         49.6N  14.5W   10.0 Crater                 NLF?
            Plato E                         49.7N  16.2W    7.0 Crater                 NLF?
            Plato F                         51.7N  17.4W    7.0 Crater                 NLF?
            Plato G                         52.1N   6.3W    8.0 Crater                 NLF?
            Plato H                         55.1N   2.0W   11.0 Crater                 NLF?
            Plato J                         49.0N   4.6W    8.0 Crater                 NLF?
            Plato K                         46.8N   3.3W    6.0 Crater                 NLF?
            Plato KA                        46.8N   3.6W    6.0 Crater                 NLF?
            Plato L                         51.6N   4.3W   10.0 Crater                 NLF?
            Plato M                         53.1N  15.4W    8.0 Crater                 NLF?
            Plato O                         52.3N  15.4W    9.0 Crater                 NLF?
            Plato P                         51.5N  15.2W    8.0 Crater                 NLF?
            Plato Q                         54.5N   4.8W    8.0 Crater                 NLF?
            Plato R                         53.8N  18.3W    6.0 Crater                 NLF?
            Plato S                         53.8N  14.9W    6.0 Crater                 NLF?
            Plato T                         54.5N  11.2W    8.0 Crater                 NLF?
            Plato U                         49.6N   7.4W    6.0 Crater                 NLF?
            Plato V                         55.8N   7.4W    6.0 Crater                 NLF?
            Plato W                         57.2N  17.8W    4.0 Crater                 NLF?
            Plato X                         50.1N  13.8W    5.0 Crater                 NLF?
            Plato Y                         53.1N  16.3W   10.0 Crater                 NLF?
            Playfair                        23.5S   8.4E   47.0 Crater         VL1645  M1834
            Playfair A                      22.3S   6.9E   21.0 Crater                 NLF?
            Playfair B                      23.2S   7.6E    6.0 Crater                 NLF?
            Playfair C                      24.3S   8.0E    5.0 Crater                 NLF?
            Playfair D                      24.3S   8.8E    5.0 Crater                 NLF?
            Playfair E                      21.7S   8.9E    6.0 Crater                 NLF?
            Playfair F                      21.9S   8.1E    5.0 Crater                 NLF?
            Playfair G                      24.2S   6.7E   94.0 Crater                 NLF?
            Playfair H                      23.3S   8.5E    4.0 Crater                 NLF?
            Playfair J                      24.3S   9.3E    4.0 Crater                 NLF?
            Playfair K                      23.3S   9.8E    4.0 Crater                 NLF?
            Plinius                         15.4N  23.7E   43.0 Crater         VL1645  R1651
            Plinius A                       13.0N  24.2E    4.0 Crater                 NLF?
            Plinius B                       14.1N  26.2E    5.0 Crater                 NLF?
            Plum                             9.0S  15.5E    0.0 Crater (A)             IAU1973
            Plummer                         25.0S 155.0W   73.0 Crater                 IAU1970
            Plummer C                       23.7S 153.1W   29.0 Crater                 AW82
            Plummer M                       26.8S 154.9W   41.0 Crater                 AW82
            Plummer N                       27.7S 156.3W   42.0 Crater                 AW82
            Plummer R                       26.0S 157.7W   22.0 Crater                 AW82
            Plummer W                       23.9S 156.2W   33.0 Crater                 AW82
            Plutarch                        24.1N  79.0E   68.0 Crater         R1651   R1651
            Plutarch C                      23.1N  71.0E   11.0 Crater                 NLF?
            Plutarch D                      24.3N  75.7E   15.0 Crater                 NLF?
            Plutarch F                      23.5N  73.5E   12.0 Crater                 NLF?
            Plutarch G                      23.0N  75.2E   11.0 Crater                 NLF?
            Plutarch H                      24.4N  72.7E   11.0 Crater                 NLF?
            Plutarch K                      25.1N  72.8E   11.0 Crater                 NLF?
            Plutarch L                      25.8N  71.6E    8.0 Crater                 NLF?
            Plutarch M                      23.8N  77.6E   11.0 Crater                 NLF?
            Plutarch N                      23.8N  77.1E   12.0 Crater                 NLF?
            Poczobutt                       57.1N  98.8W  195.0 Crater                 IAU1979
            Poczobutt J                     56.6N  96.8W   24.0 Crater                 AW82
            Poczobutt R                     56.0N 103.5W   39.0 Crater                 AW82
            Pogson                          42.2S 110.5E   50.0 Crater                 IAU1970
            Pogson C                        41.5S 111.5E   20.0 Crater                 AW82
            Pogson F                        42.0S 114.6E   35.0 Crater                 AW82
            Pogson G                        42.7S 112.7E   39.0 Crater                 AW82
            Poincar|%e                      56.7S 163.6E  319.0 Crater                 IAU1970
            Poincar|%e C                    54.4S 169.0E   20.0 Crater                 AW82
            Poincar|%e J                    59.4S 168.7E   20.0 Crater                 AW82
            Poincar|%e Q                    59.3S 160.9E   26.0 Crater                 AW82
            Poincar|%e X                    53.8S 161.9E   19.0 Crater                 AW82
            Poincar|%e Z                    53.7S 164.9E   35.0 Crater                 AW82
            Poinsot                         79.5N 145.7W   68.0 Crater                 IAU1970
            Poinsot E                       80.2N 129.8W   25.0 Crater                 AW82
            Poinsot K                       77.6N 141.3W   16.0 Crater                 AW82
            Poinsot P                       77.2N 149.7W   27.0 Crater                 AW82
            Poisson                         30.4S  10.6E   42.0 Crater         M1834   M1834
            Poisson A                       29.6S   9.1E   17.0 Crater                 NLF?
            Poisson B                       30.8S  10.9E   11.0 Crater                 NLF?
            Poisson C                       33.1S   8.6E   26.0 Crater                 NLF?
            Poisson D                       31.4S   7.7E   12.0 Crater                 NLF?
            Poisson E                       34.2S   8.6E   14.0 Crater                 NLF?
            Poisson F                       33.7S   8.0E   14.0 Crater                 NLF?
            Poisson G                       31.7S   7.4E   16.0 Crater                 NLF?
            Poisson H                       33.0S   7.4E   19.0 Crater                 NLF?
            Poisson J                       35.0S   8.3E   27.0 Crater                 NLF?
            Poisson K                       32.7S   9.6E   13.0 Crater                 NLF?
            Poisson L                       32.7S   8.2E   16.0 Crater                 NLF?
            Poisson M                       33.9S   7.6E    7.0 Crater                 NLF?
            Poisson N                       30.7S   8.4E    4.0 Crater                 NLF?
            Poisson O                       35.0S   9.1E    4.0 Crater                 NLF?
            Poisson P                       31.9S   8.9E    7.0 Crater                 NLF?
            Poisson Q                       32.6S  10.2E   28.0 Crater                 NLF?
            Poisson R                       30.0S   8.4E    5.0 Crater                 NLF?
            Poisson S                       29.9S  11.4E    4.0 Crater                 NLF?
            Poisson T                       31.1S   9.2E   25.0 Crater                 NLF?
            Poisson U                       31.6S  10.3E   25.0 Crater                 NLF?
            Poisson V                       32.0S  10.6E   16.0 Crater                 NLF?
            Poisson W                       29.6S  11.9E    3.0 Crater                 NLF?
            Poisson X                       29.0S  12.3E    5.0 Crater                 NLF?
            Poisson Z                       29.6S  10.5E    5.0 Crater                 NLF?
            Polybius                        22.4S  25.6E   41.0 Crater         VL1645  M1834
            Polybius A                      23.0S  28.0E   17.0 Crater                 NLF?
            Polybius B                      25.5S  25.5E   12.0 Crater                 NLF?
            Polybius C                      22.0S  23.6E   29.0 Crater                 NLF?
            Polybius D                      26.9S  27.9E    9.0 Crater                 NLF?
            Polybius E                      24.4S  26.2E    9.0 Crater                 NLF?
            Polybius F                      22.2S  23.0E   21.0 Crater                 NLF?
            Polybius G                      22.5S  22.7E    5.0 Crater                 NLF?
            Polybius H                      21.1S  22.7E    8.0 Crater                 NLF?
            Polybius J                      22.7S  23.5E    9.0 Crater                 NLF?
            Polybius K                      24.3S  24.3E   14.0 Crater                 NLF?
            Polybius L                      22.0S  28.2E    7.0 Crater                 NLF?
            Polybius M                      21.3S  22.1E    6.0 Crater                 NLF?
            Polybius N                      23.4S  26.8E   13.0 Crater                 NLF?
            Polybius P                      21.5S  22.9E   17.0 Crater                 NLF?
            Polybius Q                      25.1S  27.5E    6.0 Crater                 NLF?
            Polybius R                      25.6S  27.3E    7.0 Crater                 NLF?
            Polybius T                      26.1S  25.5E   12.0 Crater                 NLF?
            Polybius V                      25.2S  29.1E    6.0 Crater                 NLF?
            Polzunov                        25.3N 114.6E   67.0 Crater                 IAU1970
            Polzunov J                      23.6N 117.4E   30.0 Crater                 AW82
            Polzunov N                      23.7N 113.8E   35.0 Crater                 AW82
            Pomortsev                        0.7N  66.9E   23.0 Crater                 IAU1976
            Poncelet                        75.8N  54.1W   69.0 Crater         RLA1963 IAU1964
            Poncelet A                      79.5N  74.7W   31.0 Crater                 NLF?
            Poncelet B                      78.6N  62.3W   32.0 Crater                 NLF?
            Poncelet C                      77.4N  73.7W   67.0 Crater                 NLF?
            Poncelet D                      77.7N  70.0W   23.0 Crater                 NLF?
            Poncelet H                      75.7N  55.2W    7.0 Crater                 NLF?
            Poncelet P                      80.6N  61.1W   15.0 Crater                 NLF?
            Poncelet Q                      79.9N  59.9W   14.0 Crater                 NLF?
            Poncelet R                      79.3N  57.3W   10.0 Crater                 NLF?
            Poncelet S                      78.7N  56.2W   10.0 Crater                 NLF?
            Pons                            25.3S  21.5E   41.0 Crater         M1834   M1834
            Pons A                          27.3S  20.0E   12.0 Crater                 NLF?
            Pons B                          28.7S  20.7E   13.0 Crater                 NLF?
            Pons C                          27.9S  22.3E   18.0 Crater                 NLF?
            Pons D                          25.5S  22.1E   15.0 Crater                 NLF?
            Pons E                          25.8S  23.8E   18.0 Crater                 NLF?
            Pons F                          23.7S  21.2E   12.0 Crater                 NLF?
            Pons G                          28.3S  21.4E    6.0 Crater                 NLF?
            Pons H                          26.9S  22.3E   10.0 Crater                 NLF?
            Pons J                          24.9S  22.2E    5.0 Crater                 NLF?
            Pons K                          27.4S  22.8E    7.0 Crater                 NLF?
            Pons L                          27.5S  20.9E    8.0 Crater                 NLF?
            Pons M                          27.1S  24.1E   11.0 Crater                 NLF?
            Pons N                          26.0S  23.0E    6.0 Crater                 NLF?
            Pons P                          25.0S  23.1E    5.0 Crater                 NLF?
            Pontanus                        28.4S  14.4E   57.0 Crater         R1651   R1651
            Pontanus A                      31.1S  15.3E   10.0 Crater                 NLF?
            Pontanus B                      30.9S  15.9E   12.0 Crater                 NLF?
            Pontanus C                      30.0S  15.5E   23.0 Crater                 NLF?
            Pontanus D                      25.9S  13.2E   20.0 Crater                 NLF?
            Pontanus E                      25.2S  13.2E   13.0 Crater                 NLF?
            Pontanus F                      27.8S  11.6E   10.0 Crater                 NLF?
            Pontanus G                      30.6S  15.3E   21.0 Crater                 NLF?
            Pontanus H                      31.4S  16.1E   30.0 Crater                 NLF?
            Pontanus J                      30.0S  13.1E    9.0 Crater                 NLF?
            Pontanus K                      25.7S  12.7E    9.0 Crater                 NLF?
            Pontanus L                      28.6S  13.4E    6.0 Crater                 NLF?
            Pontanus M                      29.7S  14.1E    5.0 Crater                 NLF?
            Pontanus N                      24.6S  13.8E   10.0 Crater                 NLF?
            Pontanus O                      26.0S  14.1E   10.0 Crater                 NLF?
            Pontanus P                      29.9S  14.8E    3.0 Crater                 NLF?
            Pontanus Q                      27.4S  14.5E    5.0 Crater                 NLF?
            Pontanus R                      28.1S  15.6E    6.0 Crater                 NLF?
            Pontanus S                      31.4S  16.8E    7.0 Crater                 NLF?
            Pontanus T                      29.2S  16.6E    8.0 Crater                 NLF?
            Pontanus U                      29.5S  17.5E    5.0 Crater                 NLF?
            Pontanus V                      29.2S  13.2E   33.0 Crater                 NLF?
            Pontanus W                      29.1S  17.6E    7.0 Crater                 NLF?
            Pontanus X                      28.5S  15.8E   13.0 Crater                 NLF?
            Pontanus Y                      28.7S  17.2E   23.0 Crater                 NLF?
            Pontanus Z                      27.9S  12.9E    5.0 Crater                 NLF?
            Pont|%ecoulant                  58.7S  66.0E   91.0 Crater         M1834   M1834
            Pont|%ecoulant A                57.7S  62.9E   19.0 Crater                 NLF?
            Pont|%ecoulant B                57.9S  58.5E   39.0 Crater                 NLF?
            Pont|%ecoulant C                55.6S  59.1E   30.0 Crater                 NLF?
            Pont|%ecoulant D                60.2S  71.9E   10.0 Crater                 NLF?
            Pont|%ecoulant E                60.5S  64.5E   44.0 Crater                 NLF?
            Pont|%ecoulant F                57.4S  67.7E   60.0 Crater                 NLF?
            Pont|%ecoulant G                57.2S  60.1E   36.0 Crater                 NLF?
            Pont|%ecoulant H                58.4S  65.2E    9.0 Crater                 NLF?
            Pont|%ecoulant J                61.6S  64.3E   39.0 Crater                 NLF?
            Pont|%ecoulant K                61.5S  61.0E   13.0 Crater                 NLF?
            Pont|%ecoulant L                59.0S  59.7E   17.0 Crater                 NLF?
            Pont|%ecoulant M                60.8S  74.1E   10.0 Crater                 NLF?
            Popov                           17.2N  99.7E   65.0 Crater         BML1960 IAU1961
            Popov D                         17.8N 102.6E   15.0 Crater                 AW82
            Popov W                         19.1N  97.8E   25.0 Crater                 AW82
            Porter                          56.1S  10.1W   51.0 Crater                 IAU1970
            Porter B                        54.4S   8.6W   12.0 Crater                 NLF?
            Porter C                        54.8S  10.3W   12.0 Crater                 NLF?
            Posidonius                      31.8N  29.9E   95.0 Crater         VL1645  R1651
            Posidonius A                    31.7N  29.5E   11.0 Crater                 NLF?
            Posidonius B                    33.1N  30.9E   14.0 Crater                 NLF?
            Posidonius C                    31.1N  29.6E    2.0 Crater                 NLF?
            Posidonius E                    30.5N  19.7E    3.0 Crater                 NLF?
            Posidonius F                    32.8N  27.1E    6.0 Crater                 NLF?
            Posidonius G                    34.8N  27.2E    5.0 Crater                 NLF?
            Posidonius J                    33.8N  30.7E   22.0 Crater                 NLF?
            Posidonius M                    34.3N  30.0E   10.0 Crater                 NLF?
            Posidonius N                    29.7N  21.0E    6.0 Crater                 NLF?
            Posidonius P                    33.6N  27.5E   15.0 Crater                 NLF?
            Posidonius W                    31.6N  20.1E    3.0 Crater                 NLF?
            Posidonius Y                    30.0N  24.9E    2.0 Crater                 NLF?
            Posidonius Z                    30.7N  22.9E    6.0 Crater                 NLF?
            Powell                          20.2N  30.8E    1.0 Crater (A)             IAU1973
            Poynting                        18.1N 133.4W  128.0 Crater                 IAU1970
            Poynting X                      23.3N 136.2W   22.0 Crater                 AW82
            Prager                           3.9S 130.5E   60.0 Crater                 IAU1971
            Prager C                         1.4S 132.4E   40.0 Crater                 AW82
            Prager E                         3.0S 133.1E   14.0 Crater                 AW82
            Prager G                         4.6S 134.0E   76.0 Crater                 AW82
            Prandtl                         60.1S 141.8E   91.0 Crater                 IAU1970
            Priestley                       57.3S 108.4E   52.0 Crater                 IAU1970
            Priestley K                     59.0S 110.5E   35.0 Crater                 AW82
            Priestley X                     56.5S 107.8E   14.0 Crater                 AW82
            Prinz                           25.5N  44.1W   46.0 Crater         K1898   K1898
            Priscilla                       10.9S   6.2W    1.8 Crater         X       IAU1976
            Proclus                         16.1N  46.8E   28.0 Crater         VL1645  R1651
            Proclus A                       13.4N  42.3E   15.0 Crater                 NLF?
            Proclus C                       12.9N  43.6E   10.0 Crater                 NLF?
            Proclus D                       17.5N  41.0E   13.0 Crater                 NLF?
            Proclus E                       16.6N  40.9E   12.0 Crater                 NLF?
            Proclus G                       12.7N  42.7E   33.0 Crater                 NLF?
            Proclus J                       17.1N  44.0E    6.0 Crater                 NLF?
            Proclus K                       16.5N  46.2E   16.0 Crater                 NLF?
            Proclus L                       17.1N  46.4E    9.0 Crater                 NLF?
            Proclus M                       16.4N  45.2E    8.0 Crater                 NLF?
            Proclus P                       15.3N  48.7E   30.0 Crater                 NLF?
            Proclus R                       15.8N  45.5E   28.0 Crater                 NLF?
            Proclus S                       15.7N  47.9E   18.0 Crater                 NLF?
            Proclus T                       15.4N  46.7E   21.0 Crater                 NLF?
            Proclus U                       15.2N  48.0E   13.0 Crater                 NLF?
            Proclus V                       14.8N  48.3E   19.0 Crater                 NLF?
            Proclus W                       17.5N  46.2E    7.0 Crater                 NLF?
            Proclus X                       17.7N  45.1E    6.0 Crater                 NLF?
            Proclus Y                       17.5N  44.9E    8.0 Crater                 NLF?
            Proclus Z                       17.9N  44.7E    6.0 Crater                 NLF?
            Proctor                         46.4S   5.1W   52.0 Crater         IC1935  IC1935
            Proctor A                       47.0S   6.7W    8.0 Crater                 NLF?
            Proctor B                       46.4S   6.7W    8.0 Crater                 NLF?
            Proctor C                       47.7S   6.6W    5.0 Crater                 NLF?
            Proctor D                       46.1S   6.0W   12.0 Crater                 NLF?
            Proctor E                       45.4S   5.1W    8.0 Crater                 NLF?
            Proctor F                       47.7S   5.2W    7.0 Crater                 NLF?
            Proctor G                       47.7S   4.8W    7.0 Crater                 NLF?
            Proctor H                       45.7S   2.5W    5.0 Crater                 NLF?
            Protagoras                      56.0N   7.3E   21.0 Crater         S1878   S1878
            Protagoras B                    56.3N   5.7E    4.0 Crater                 NLF?
            Protagoras E                    49.5N   0.5E    6.0 Crater                 NLF?
            Ptolemaeus                       9.3S   1.9W  164.0 Crater         VL1645  R1651
            Ptolemaeus B                     7.9S   0.7W   17.0 Crater                 NLF?
            Ptolemaeus C                    10.1S   3.3W    3.0 Crater                 NLF?
            Ptolemaeus D                     8.2S   2.5W    4.0 Crater                 NLF?
            Ptolemaeus E                    10.2S   4.5W   32.0 Crater                 NLF?
            Ptolemaeus G                     7.1S   0.1E    7.0 Crater                 NLF?
            Ptolemaeus H                     7.1S   5.4W    7.0 Crater                 NLF?
            Ptolemaeus J                     9.6S   5.4W    5.0 Crater                 NLF?
            Ptolemaeus K                     8.2S   4.6W    8.0 Crater                 NLF?
            Ptolemaeus L                     8.8S   4.0W    4.0 Crater                 NLF?
            Ptolemaeus M                     9.4S   3.4W    3.0 Crater                 NLF?
            Ptolemaeus O                     7.2S   3.6W    5.0 Crater                 NLF?
            Ptolemaeus P                    11.4S   3.2W    4.0 Crater                 NLF?
            Ptolemaeus R                     6.7S   1.2W    6.0 Crater                 NLF?
            Ptolemaeus S                    10.5S   0.5W    4.0 Crater                 NLF?
            Ptolemaeus T                     7.5S   0.0E    7.0 Crater                 NLF?
            Ptolemaeus W                     9.1S   1.4E    4.0 Crater                 NLF?
            Ptolemaeus X                    10.9S   0.3E    4.0 Crater                 NLF?
            Ptolemaeus Y                     9.3S   0.7E    6.0 Crater                 NLF?
            Puiseux                         27.8S  39.0W   24.0 Crater         K1898   K1898
            Puiseux A                       26.5S  39.7W    3.0 Crater                 NLF?
            Puiseux B                       25.7S  38.8W    4.0 Crater                 NLF?
            Puiseux C                       24.7S  37.8W    3.0 Crater                 NLF?
            Puiseux D                       25.7S  36.1W    7.0 Crater                 NLF?
            Puiseux F                       23.4S  38.8W    4.0 Crater                 NLF?
            Puiseux G                       28.2S  37.8W    3.0 Crater                 NLF?
            Puiseux H                       27.4S  37.0W    3.0 Crater                 NLF?
            Pupin                           23.8N  11.0W    2.0 Crater                 IAU1976
            Purbach                         25.5S   2.3W  115.0 Crater         VL1645  R1651
            Purbach A                       26.1S   1.9W    8.0 Crater                 NLF?
            Purbach B                       26.9S   4.2W   16.0 Crater                 NLF?
            Purbach C                       27.7S   4.6W   18.0 Crater                 NLF?
            Purbach D                       22.8S   1.6W   12.0 Crater                 NLF?
            Purbach E                       21.7S   0.7W   23.0 Crater                 NLF?
            Purbach F                       24.6S   0.0W    9.0 Crater                 NLF?
            Purbach G                       23.9S   2.8W   27.0 Crater                 NLF?
            Purbach H                       25.5S   5.6W   29.0 Crater                 NLF?
            Purbach J                       27.5S   3.9W   12.0 Crater                 NLF?
            Purbach K                       25.2S   4.6W    8.0 Crater                 NLF?
            Purbach L                       25.1S   5.0W   17.0 Crater                 NLF?
            Purbach M                       24.8S   4.4W   17.0 Crater                 NLF?
            Purbach N                       26.2S   5.4W    7.0 Crater                 NLF?
            Purbach O                       24.7S   3.8W    5.0 Crater                 NLF?
            Purbach P                       26.4S   3.7W    5.0 Crater                 NLF?
            Purbach Q                       25.9S   0.0W    4.0 Crater                 NLF?
            Purbach R                       26.5S   3.2W    4.0 Crater                 NLF?
            Purbach S                       27.3S   2.3W    9.0 Crater                 NLF?
            Purbach T                       24.6S   0.9W    5.0 Crater                 NLF?
            Purbach U                       27.0S   2.0W   15.0 Crater                 NLF?
            Purbach V                       26.7S   0.3W    6.0 Crater                 NLF?
            Purbach W                       25.5S   2.3W   20.0 Crater                 NLF?
            Purbach X                       25.4S   1.1W    4.0 Crater                 NLF?
            Purbach Y                       25.8S   6.8W   16.0 Crater                 NLF?
            Purkyn|ve                        1.6S  94.9E   48.0 Crater                 IAU1970
            Purkyn|ve D                      1.0S  96.0E   13.0 Crater                 AW82
            Purkyn|ve K                      2.7S  95.4E   21.0 Crater                 AW82
            Purkyn|ve S                      1.8S  90.6E   34.0 Crater                 AW82
            Purkyn|ve U                      0.7S  91.9E   51.0 Crater                 AW82
            Purkyn|ve V                      0.8S  92.7E   24.0 Crater                 AW82
            Pythagoras                      63.5N  63.0W  142.0 Crater         VL1645  VL1645
            Pythagoras B                    66.1N  73.0W   17.0 Crater                 NLF?
            Pythagoras D                    64.5N  72.0W   30.0 Crater                 NLF?
            Pythagoras G                    67.8N  75.3W   16.0 Crater                 NLF?
            Pythagoras H                    67.1N  73.3W   18.0 Crater                 NLF?
            Pythagoras K                    67.3N  75.4W   12.0 Crater                 NLF?
            Pythagoras L                    67.3N  77.6W   12.0 Crater                 NLF?
            Pythagoras M                    67.5N  81.1W   10.0 Crater                 NLF?
            Pythagoras N                    66.6N  78.1W   14.0 Crater                 NLF?
            Pythagoras P                    65.3N  75.2W   10.0 Crater                 NLF?
            Pythagoras S                    67.7N  64.7W    8.0 Crater                 NLF?
            Pythagoras T                    62.5N  51.4W    6.0 Crater                 NLF?
            Pythagoras W                    63.1N  48.9W    4.0 Crater                 NLF?
            Pytheas                         20.5N  20.6W   20.0 Crater         VL1645  R1651
            Pytheas A                       20.5N  21.7W    6.0 Crater                 NLF?
            Pytheas B                       17.5N  19.4W    4.0 Crater                 NLF?
            Pytheas C                       18.8N  19.1W    4.0 Crater                 NLF?
            Pytheas D                       21.1N  20.5W    5.0 Crater                 NLF?
            Pytheas E                       18.1N  19.0W    4.0 Crater                 NLF?
            Pytheas F                       16.5N  19.1W    5.0 Crater                 NLF?
            Pytheas G                       21.6N  17.7W    4.0 Crater                 NLF?
            Pytheas H                       20.5N  16.5W    3.0 Crater                 NLF?
            Pytheas J                       21.6N  21.1W    3.0 Crater                 NLF?
            Pytheas K                       19.9N  16.2W    2.0 Crater                 NLF?
            Pytheas L                       18.6N  16.9W    3.0 Crater                 NLF?
            Pytheas M                       19.9N  17.7W    3.0 Crater                 NLF?
            Pytheas N                       22.5N  20.5W    3.0 Crater                 NLF?
            Pytheas U                       21.7N  19.4W    3.0 Crater                 NLF?
            Pytheas W                       21.7N  23.7W    3.0 Crater                 NLF?
            Quetelet                        43.1N 134.9W   55.0 Crater                 IAU1970
            Quetelet T                      42.8N 137.6W   46.0 Crater                 AW82
            Rabbi Levi                      34.7S  23.6E   81.0 Crater         VL1645  R1651
            Rabbi Levi A                    34.3S  22.7E   12.0 Crater                 NLF?
            Rabbi Levi B                    34.5S  24.8E   13.0 Crater                 NLF?
            Rabbi Levi C                    34.3S  27.0E   20.0 Crater                 NLF?
            Rabbi Levi D                    35.4S  22.8E   10.0 Crater                 NLF?
            Rabbi Levi E                    36.7S  22.1E   35.0 Crater                 NLF?
            Rabbi Levi F                    36.0S  20.5E   12.0 Crater                 NLF?
            Rabbi Levi G                    36.9S  22.0E   12.0 Crater                 NLF?
            Rabbi Levi H                    36.4S  20.2E    8.0 Crater                 NLF?
            Rabbi Levi J                    37.6S  22.7E    7.0 Crater                 NLF?
            Rabbi Levi L                    34.7S  23.0E   13.0 Crater                 NLF?
            Rabbi Levi M                    35.2S  23.2E   11.0 Crater                 NLF?
            Rabbi Levi N                    36.4S  23.7E    8.0 Crater                 NLF?
            Rabbi Levi O                    35.7S  25.1E    7.0 Crater                 NLF?
            Rabbi Levi P                    34.5S  25.8E   15.0 Crater                 NLF?
            Rabbi Levi Q                    33.7S  25.8E    6.0 Crater                 NLF?
            Rabbi Levi R                    33.6S  28.2E   12.0 Crater                 NLF?
            Rabbi Levi S                    34.2S  27.5E   14.0 Crater                 NLF?
            Rabbi Levi T                    36.2S  22.4E   10.0 Crater                 NLF?
            Rabbi Levi U                    35.6S  21.9E   14.0 Crater                 NLF?
            Racah                           13.8S 179.8W   63.0 Crater                 IAU1970
            Racah B                         10.5S 178.4W   27.0 Crater                 AW82
            Racah J                         16.5S 177.4W   37.0 Crater                 AW82
            Racah K                         16.8S 178.6W   52.0 Crater                 AW82
            Racah N                         17.0S 179.0E   35.0 Crater                 AW82
            Racah T                         13.8S 177.5E   21.0 Crater                 AW82
            Racah U                         13.2S 177.2E   25.0 Crater                 AW82
            Racah W                         12.5S 178.9E   39.0 Crater                 AW82
            Racah X                         10.2S 179.0E   14.0 Crater                 AW82
            Raimond                         14.6N 159.3W   70.0 Crater                 IAU1970
            Raimond K                       13.3N 158.2W   34.0 Crater                 AW82
            Raimond Q                       11.6N 161.7W   32.0 Crater                 AW82
            Raman                           27.0N  55.1W   10.0 Crater                 IAU1976
            Ramon                           41.6S 148.1W   17.0 Crater                 IAU2006?
            Ramsay                          40.2S 144.5E   81.0 Crater                 IAU1970
            Ramsay U                        40.0S 142.4E   23.0 Crater                 AW82
            Ramsden                         32.9S  31.8W   24.0 Crater         VL1645  M1834
            Ramsden A                       33.4S  31.3W    5.0 Crater                 NLF?
            Ramsden G                       35.3S  31.6W   11.0 Crater                 NLF?
            Ramsden H                       35.7S  32.4W   11.0 Crater                 NLF?
            Rankine                          3.9S  71.5E    8.0 Crater                 IAU1976
            Raspletin                       22.5S 151.8E   48.0 Crater                 IAU1976
            Ravi                            12.5S   1.9W    2.5 Crater         X       IAU1976
            Ravine                           8.9S  15.6E    1.0 Crater (A)             IAU1973
            Rayet                           44.7N 114.5E   27.0 Crater                 IAU1970
            Rayet H                         43.4N 116.7E   16.0 Crater                 AW82
            Rayet P                         43.3N 114.0E   17.0 Crater                 AW82
            Rayet Y                         47.2N 113.0E   14.0 Crater                 AW82
            Rayleigh                        29.3N  89.6E  114.0 Crater         RLA1963 IAU1964
            Rayleigh B                      28.9N  88.4E   14.0 Crater                 RLA1963?
            Rayleigh C                      31.4N  85.7E   22.0 Crater                 RLA1963?
            Rayleigh D                      28.9N  89.7E   22.0 Crater                 RLA1963?
            Razumov                         39.1N 114.3W   70.0 Crater                 IAU1970
            Razumov C                       40.8N 112.4W   48.0 Crater                 AW82
            R|%eaumur                        2.4S   0.7E   52.0 Crater         M1834   M1834
            R|%eaumur A                      4.3S   0.2E   16.0 Crater                 NLF?
            R|%eaumur B                      4.2S   0.9E    5.0 Crater                 NLF?
            R|%eaumur C                      3.4S   0.2E    5.0 Crater                 NLF?
            R|%eaumur D                      0.2S   2.8E    4.0 Crater                 NLF?
            R|%eaumur K                      3.8S   1.0E    7.0 Crater                 NLF?
            R|%eaumur R                      3.5S   2.1E   14.0 Crater                 NLF?
            R|%eaumur W                      3.2S   2.8E    3.0 Crater                 NLF?
            R|%eaumur X                      2.9S   0.6W    5.0 Crater                 NLF?
            R|%eaumur Y                      1.3S   0.6E    3.0 Crater                 NLF?
            Recht                            9.8N 124.0E   20.0 Crater                 IAU1982
            Regiomontanus                   28.3S   1.0W  108.0 Crater         VL1645  R1651
            Regiomontanus A                 28.0S   0.6W    6.0 Crater                 NLF?
            Regiomontanus B                 29.0S   3.7W   10.0 Crater                 NLF?
            Regiomontanus C                 28.7S   5.2W    8.0 Crater                 NLF?
            Regiomontanus E                 28.2S   6.2W    6.0 Crater                 NLF?
            Regiomontanus F                 27.8S   1.9W   11.0 Crater                 NLF?
            Regiomontanus G                 28.2S   3.6W    5.0 Crater                 NLF?
            Regiomontanus H                 28.6S   4.0W    6.0 Crater                 NLF?
            Regiomontanus J                 29.4S   1.9W    8.0 Crater                 NLF?
            Regiomontanus K                 30.3S   0.0W    6.0 Crater                 NLF?
            Regiomontanus L                 29.7S   1.1E    6.0 Crater                 NLF?
            Regiomontanus M                 29.6S   2.1W    5.0 Crater                 NLF?
            Regiomontanus N                 28.9S   0.1E    3.0 Crater                 NLF?
            Regiomontanus R                 28.4S   0.0W    3.0 Crater                 NLF?
            Regiomontanus S                 28.6S   2.0W    4.0 Crater                 NLF?
            Regiomontanus T                 28.1S   2.9W    5.0 Crater                 NLF?
            Regiomontanus U                 27.9S   3.5W   11.0 Crater                 NLF?
            Regiomontanus W                 29.5S   1.4W    3.0 Crater                 NLF?
            Regiomontanus Y                 30.1S   1.6W    5.0 Crater                 NLF?
            Regiomontanus Z                 27.5S   3.0W    6.0 Crater                 NLF?
            Regnault                        54.1N  88.0W   46.0 Crater         S1878   S1878
            Regnault C                      55.2N  89.2W   14.0 Crater                 NLF?
            Regnault W                      53.5N  89.9W   15.0 Crater                 NLF?
            Reichenbach                     30.3S  48.0E   71.0 Crater         M1834   M1834
            Reichenbach A                   28.3S  49.0E   34.0 Crater                 NLF?
            Reichenbach B                   28.4S  48.0E   44.0 Crater                 NLF?
            Reichenbach C                   29.3S  43.9E   27.0 Crater                 NLF?
            Reichenbach D                   28.1S  44.7E   35.0 Crater                 NLF?
            Reichenbach F                   31.4S  48.4E   15.0 Crater                 NLF?
            Reichenbach G                   31.7S  49.4E   15.0 Crater                 NLF?
            Reichenbach H                   28.9S  49.7E   10.0 Crater                 NLF?
            Reichenbach J                   30.7S  49.4E   15.0 Crater                 NLF?
            Reichenbach K                   28.8S  42.4E   11.0 Crater                 NLF?
            Reichenbach L                   30.5S  46.7E    8.0 Crater                 NLF?
            Reichenbach M                   33.0S  46.5E   13.0 Crater                 NLF?
            Reichenbach N                   30.5S  43.9E   14.0 Crater                 NLF?
            Reichenbach P                   32.0S  49.9E   12.0 Crater                 NLF?
            Reichenbach Q                   32.4S  50.2E   10.0 Crater                 NLF?
            Reichenbach R                   26.9S  42.9E    7.0 Crater                 NLF?
            Reichenbach S                   27.1S  43.1E    9.0 Crater                 NLF?
            Reichenbach T                   29.3S  45.7E   64.0 Crater                 NLF?
            Reichenbach U                   32.7S  49.5E   14.0 Crater                 NLF?
            Reichenbach W                   30.7S  43.1E   18.0 Crater                 NLF?
            Reichenbach X                   30.9S  43.9E   11.0 Crater                 NLF?
            Reichenbach Y                   31.2S  43.6E   16.0 Crater                 NLF?
            Reichenbach Z                   31.9S  46.0E   15.0 Crater                 NLF?
            Reimarus                        47.7S  60.3E   48.0 Crater         S1878   S1878
            Reimarus A                      48.8S  59.9E   29.0 Crater                 NLF?
            Reimarus B                      49.5S  60.6E   16.0 Crater                 NLF?
            Reimarus C                      50.2S  59.5E   11.0 Crater                 NLF?
            Reimarus F                      49.5S  58.7E    7.0 Crater                 NLF?
            Reimarus H                      49.3S  62.3E   10.0 Crater                 NLF?
            Reimarus R                      47.7S  63.9E   35.0 Crater                 NLF?
            Reimarus S                      47.8S  62.8E    9.0 Crater                 NLF?
            Reimarus T                      48.4S  63.5E   24.0 Crater                 NLF?
            Reimarus U                      48.5S  62.2E   20.0 Crater                 NLF?
            Reiner                           7.0N  54.9W   29.0 Crater         R1651   R1651
            Reiner A                         5.2N  51.4W   10.0 Crater                 NLF?
            Reiner C                         3.5N  51.5W    7.0 Crater                 NLF?
            Reiner E                         1.9N  49.6W    4.0 Crater                 NLF?
            Reiner G                         3.3N  54.3W    3.0 Crater                 NLF?
            Reiner H                         9.1N  54.7W    8.0 Crater                 NLF?
            Reiner K                         8.1N  53.9W    3.0 Crater                 NLF?
            Reiner L                         8.0N  54.6W    6.0 Crater                 NLF?
            Reiner M                         8.6N  56.1W    3.0 Crater                 NLF?
            Reiner N                         5.4N  57.5W    4.0 Crater                 NLF?
            Reiner Q                         1.4N  50.9W    3.0 Crater                 NLF?
            Reiner R                         3.7N  55.5W   45.0 Crater                 NLF?
            Reiner S                         2.2N  50.7W    4.0 Crater                 NLF?
            Reiner T                         3.7N  52.2W    2.0 Crater                 NLF?
            Reiner U                         4.1N  52.5W    3.0 Crater                 NLF?
            Reinhold                         3.3N  22.8W   42.0 Crater         VL1645  R1651
            Reinhold A                       4.1N  21.7W    4.0 Crater                 NLF?
            Reinhold B                       4.3N  21.7W   26.0 Crater                 NLF?
            Reinhold C                       4.4N  24.5W    4.0 Crater                 NLF?
            Reinhold D                       2.6N  24.5W    2.0 Crater                 NLF?
            Reinhold F                       3.4N  21.4W    5.0 Crater                 NLF?
            Reinhold G                       4.8N  19.8W    3.0 Crater                 NLF?
            Reinhold H                       4.2N  20.9W    4.0 Crater                 NLF?
            Reinhold N                       1.6N  25.4W    4.0 Crater                 NLF?
            Repsold                         51.3N  78.6W  109.0 Crater         M1834   M1834
            Repsold A                       51.8N  77.0W    9.0 Crater                 NLF?
            Repsold B                       53.2N  75.8W   38.0 Crater                 NLF?
            Repsold C                       48.9N  73.6W  133.0 Crater                 NLF?
            Repsold G                       50.5N  80.6W   44.0 Crater                 NLF?
            Repsold H                       51.7N  81.6W   12.0 Crater                 NLF?
            Repsold N                       49.0N  78.2W   14.0 Crater                 NLF?
            Repsold R                       49.8N  72.2W   13.0 Crater                 NLF?
            Repsold S                       47.8N  75.2W    9.0 Crater                 NLF?
            Repsold T                       47.7N  79.9W   13.0 Crater                 NLF?
            Repsold V                       50.8N  75.4W    7.0 Crater                 NLF?
            Repsold W                       52.6N  79.8W   10.0 Crater                 NLF?
            Resnik                          33.8S 150.1W   20.0 Crater         N       IAU1988
            Respighi                         2.8N  71.9E   18.0 Crater                 IAU1976
            Rhaeticus                        0.0N   4.9E   45.0 Crater                 NLF
            Rhaeticus A                      1.8N   5.2E   11.0 Crater                 NLF?
            Rhaeticus B                      1.7N   6.8E    6.0 Crater                 NLF?
            Rhaeticus D                      0.9N   6.2E    7.0 Crater                 NLF?
            Rhaeticus E                      0.1S   6.0E    5.0 Crater                 NLF?
            Rhaeticus F                      0.1S   6.5E   18.0 Crater                 NLF?
            Rhaeticus G                      1.0N   6.4E    6.0 Crater                 NLF?
            Rhaeticus H                      1.0S   5.4E    6.0 Crater                 NLF?
            Rhaeticus J                      0.7S   3.2E    4.0 Crater                 NLF?
            Rhaeticus L                      0.2N   3.6E   14.0 Crater                 NLF?
            Rhaeticus M                      1.0N   3.8E    7.0 Crater                 NLF?
            Rhaeticus N                      1.2N   4.2E   12.0 Crater                 NLF?
            Rheita                          37.1S  47.2E   70.0 Crater         R1651   R1651
            Rheita A                        38.0S  50.0E   11.0 Crater                 NLF?
            Rheita B                        39.1S  52.8E   21.0 Crater                 NLF?
            Rheita C                        35.1S  44.2E    8.0 Crater                 NLF?
            Rheita D                        39.1S  50.1E    6.0 Crater                 NLF?
            Rheita E                        34.2S  49.1E   66.0 Crater                 NLF?
            Rheita F                        35.4S  48.4E   14.0 Crater                 NLF?
            Rheita G                        40.5S  54.3E   15.0 Crater                 NLF?
            Rheita H                        39.8S  51.7E    7.0 Crater                 NLF?
            Rheita L                        37.7S  52.9E   10.0 Crater                 NLF?
            Rheita M                        35.3S  50.1E   25.0 Crater                 NLF?
            Rheita N                        35.1S  49.5E    8.0 Crater                 NLF?
            Rheita P                        37.9S  44.4E   11.0 Crater                 NLF?
            Rhysling                        26.1N   3.7E    0.0 Crater (A)             IAU1973
            Riccioli                         3.3S  74.6W  139.0 Crater         R1651   R1651
            Riccioli C                       0.6N  73.0W   31.0 Crater                 NLF?
            Riccioli CA                      0.6N  73.0W   14.0 Crater                 NLF?
            Riccioli F                       8.6S  73.9W   28.0 Crater                 NLF?
            Riccioli G                       1.3S  71.0W   15.0 Crater                 NLF?
            Riccioli H                       1.1N  74.9W   18.0 Crater                 NLF?
            Riccioli K                       2.2S  77.5W   43.0 Crater                 NLF?
            Riccioli U                       5.7S  72.8W    9.0 Crater                 NLF?
            Riccioli Y                       3.0S  73.2W    7.0 Crater                 NLF?
            Riccius                         36.9S  26.5E   71.0 Crater         VL1645  R1651
            Riccius A                       35.9S  27.4E   24.0 Crater                 NLF?
            Riccius B                       37.5S  27.8E   19.0 Crater                 NLF?
            Riccius C                       36.2S  28.8E   24.0 Crater                 NLF?
            Riccius D                       40.3S  28.9E   17.0 Crater                 NLF?
            Riccius E                       39.9S  26.4E   22.0 Crater                 NLF?
            Riccius G                       38.5S  24.4E   13.0 Crater                 NLF?
            Riccius H                       35.4S  26.1E   20.0 Crater                 NLF?
            Riccius J                       40.7S  26.0E   13.0 Crater                 NLF?
            Riccius K                       39.1S  25.7E    6.0 Crater                 NLF?
            Riccius L                       41.5S  26.8E    8.0 Crater                 NLF?
            Riccius M                       37.8S  26.4E   14.0 Crater                 NLF?
            Riccius N                       41.1S  27.6E   13.0 Crater                 NLF?
            Riccius O                       36.2S  27.8E    9.0 Crater                 NLF?
            Riccius P                       35.7S  28.1E   11.0 Crater                 NLF?
            Riccius R                       41.4S  30.7E    7.0 Crater                 NLF?
            Riccius S                       37.0S  26.5E   11.0 Crater                 NLF?
            Riccius T                       36.3S  25.0E    7.0 Crater                 NLF?
            Riccius W                       38.9S  25.2E   19.0 Crater                 NLF?
            Riccius X                       38.8S  26.7E   11.0 Crater                 NLF?
            Riccius Y                       35.8S  29.1E   10.0 Crater                 NLF?
            Ricco                           75.6N 176.3E   65.0 Crater                 IAU1970
            Richards                         7.7N 140.1E   16.0 Crater                 IAU1976
            Richardson                      31.1N 100.5E  141.0 Crater                 IAU1979
            Richardson E                    31.9N 103.6E   22.0 Crater                 AW82
            Richardson W                    33.5N  98.3E   23.0 Crater                 AW82
            Riedel                          48.9S 139.6W   47.0 Crater                 IAU1970
            Riedel G                        49.2S 133.6W   26.0 Crater                 AW82
            Riedel Q                        49.9S 141.7W   25.0 Crater                 AW82
            Riedel Z                        47.4S 139.7W   30.0 Crater                 AW82
            Riemann                         38.9N  86.8E  163.0 Crater         RLA1963 IAU1964
            Riemann B                       41.6N  85.2E   24.0 Crater                 RLA1963?
            Riemann J                       37.4N  90.2E   39.0 Crater                 RLA1963?
            Ritchey                         11.1S   8.5E   24.0 Crater         K1898   K1898
            Ritchey A                       11.3S   7.7E    6.0 Crater                 NLF?
            Ritchey B                       11.9S   8.9E    7.0 Crater                 NLF?
            Ritchey C                       10.9S   9.2E    6.0 Crater                 NLF?
            Ritchey D                       10.2S   9.2E    6.0 Crater                 NLF?
            Ritchey E                       10.7S   8.4E   14.0 Crater                 NLF?
            Ritchey F                       10.5S   7.6E    4.0 Crater                 NLF?
            Ritchey J                       12.3S   9.9E   17.0 Crater                 NLF?
            Ritchey M                       12.4S   9.5E    8.0 Crater                 NLF?
            Ritchey N                       11.1S  10.0E   17.0 Crater                 NLF?
            Rittenhouse                     74.5S 106.5E   26.0 Crater                 IAU1970
            Ritter                           2.0N  19.2E   29.0 Crater         M1834   M1834
            Ritter B                         3.3N  18.9E   14.0 Crater                 NLF?
            Ritter C                         2.8N  18.9E   14.0 Crater                 NLF?
            Ritter D                         3.7N  18.8E    7.0 Crater                 NLF?
            Ritz                            15.1S  92.2E   51.0 Crater                 IAU1970
            Ritz B                          13.7S  92.8E   26.0 Crater                 AW82
            Ritz J                          16.0S  92.9E   11.0 Crater                 AW82
            Robert                          19.0N  27.4E    1.0 Crater         X       IAU1976
            Roberts                         71.1N 174.5W   89.0 Crater                 IAU1970
            Roberts M                       68.2N 174.3W   46.0 Crater                 AW82
            Roberts N                       69.0N 176.3W   49.0 Crater                 AW82
            Roberts P                       67.4N 178.7E   30.0 Crater                 AW82
            Roberts Q                       68.9N 177.3E   19.0 Crater                 AW82
            Roberts R                       69.6N 178.4E   59.0 Crater                 AW82
            Robertson                       21.8N 105.2W   88.0 Crater                 IAU1970
            Robinson                        59.0N  45.9W   24.0 Crater                 NLF
            Rocca                           12.7S  72.8W   89.0 Crater                 NLF
            Rocca A                         13.8S  70.0W   63.0 Crater                 NLF?
            Rocca B                         12.6S  67.4W   25.0 Crater                 NLF?
            Rocca C                         10.7S  70.2W   19.0 Crater                 NLF?
            Rocca D                         11.0S  68.0W   24.0 Crater                 NLF?
            Rocca E                         11.8S  69.4W   43.0 Crater                 NLF?
            Rocca F                         13.6S  66.6W   27.0 Crater                 NLF?
            Rocca G                         13.3S  64.9W   23.0 Crater                 NLF?
            Rocca H                         12.9S  65.4W   26.0 Crater                 NLF?
            Rocca J                         14.9S  73.9W   13.0 Crater                 NLF?
            Rocca L                         13.9S  72.6W   17.0 Crater                 NLF?
            Rocca M                         14.5S  70.7W   42.0 Crater                 NLF?
            Rocca N                         11.6S  70.3W   24.0 Crater                 NLF?
            Rocca P                         11.2S  71.7W   32.0 Crater                 NLF?
            Rocca Q                         15.3S  69.0W   59.0 Crater                 NLF?
            Rocca R                         11.4S  72.9W   46.0 Crater                 NLF?
            Rocca S                         10.3S  71.5W   10.0 Crater                 NLF?
            Rocca T                          9.7S  71.0W   16.0 Crater                 NLF?
            Rocca W                         10.3S  67.0W  102.0 Crater                 NLF?
            Rocca Z                         16.0S  75.4W   55.0 Crater                 NLF?
            Rocco                           28.9N  45.0W    4.0 Crater         X       IAU1976
            Roche                           42.3S 136.5E  160.0 Crater                 IAU1970
            Roche B                         40.1S 137.2E   24.0 Crater                 AW82
            Roche C                         39.0S 139.2E   18.0 Crater                 AW82
            Roche V                         38.5S 129.3E   30.0 Crater                 AW82
            Roche W                         39.0S 130.5E   20.0 Crater                 AW82
            Romeo                            7.5N 122.6E    8.0 Crater         X       IAU1979
            R|:omer                         25.4N  36.4E   39.0 Crater         VL1645  S1791
            R|:omer A                       28.1N  37.1E   35.0 Crater                 NLF?
            R|:omer B                       28.6N  38.2E   20.0 Crater                 NLF?
            R|:omer C                       27.7N  37.0E    8.0 Crater                 NLF?
            R|:omer D                       24.5N  35.8E   13.0 Crater                 NLF?
            R|:omer E                       28.5N  39.2E   31.0 Crater                 NLF?
            R|:omer F                       27.1N  37.2E   22.0 Crater                 NLF?
            R|:omer G                       26.8N  36.2E   14.0 Crater                 NLF?
            R|:omer H                       25.9N  35.7E    6.0 Crater                 NLF?
            R|:omer J                       22.4N  37.9E    8.0 Crater                 NLF?
            R|:omer M                       25.3N  34.6E   10.0 Crater                 NLF?
            R|:omer N                       25.3N  38.0E   26.0 Crater                 NLF?
            R|:omer P                       26.5N  39.6E   61.0 Crater                 NLF?
            R|:omer R                       24.2N  34.6E   42.0 Crater                 NLF?
            R|:omer S                       24.9N  36.8E   44.0 Crater                 NLF?
            R|:omer T                       23.6N  36.1E   47.0 Crater                 NLF?
            R|:omer U                       24.3N  39.1E   28.0 Crater                 NLF?
            R|:omer V                       24.5N  38.6E   28.0 Crater                 NLF?
            R|:omer W                       26.4N  40.4E    7.0 Crater                 NLF?
            R|:omer X                       24.3N  40.1E   22.0 Crater                 NLF?
            R|:omer Y                       25.7N  36.3E    7.0 Crater                 NLF?
            R|:omer Z                       24.1N  36.9E   12.0 Crater                 NLF?
            R|:ontgen                       33.0N  91.4W  126.0 Crater         RLA1963 IAU1964
            R|:ontgen A                     36.9N  88.1W   18.0 Crater                 RLA1963?
            R|:ontgen B                     35.7N  88.1W   16.0 Crater                 RLA1963?
            Rosa                            20.3N  32.3W    1.0 Crater         X       IAU1976
            Rosenberger                     55.4S  43.1E   95.0 Crater         M1834   M1834
            Rosenberger A                   53.5S  47.0E   49.0 Crater                 NLF?
            Rosenberger B                   51.9S  46.1E   33.0 Crater                 NLF?
            Rosenberger C                   52.1S  42.1E   47.0 Crater                 NLF?
            Rosenberger D                   57.5S  42.9E   50.0 Crater                 NLF?
            Rosenberger E                   59.3S  43.2E   11.0 Crater                 NLF?
            Rosenberger F                   56.0S  40.6E    6.0 Crater                 NLF?
            Rosenberger G                   53.9S  41.4E    9.0 Crater                 NLF?
            Rosenberger H                   55.0S  46.5E   12.0 Crater                 NLF?
            Rosenberger J                   52.9S  43.3E   22.0 Crater                 NLF?
            Rosenberger K                   54.5S  47.7E   18.0 Crater                 NLF?
            Rosenberger L                   52.6S  44.6E    9.0 Crater                 NLF?
            Rosenberger N                   54.3S  44.1E    8.0 Crater                 NLF?
            Rosenberger S                   55.8S  42.6E   14.0 Crater                 NLF?
            Rosenberger T                   56.5S  43.1E    8.0 Crater                 NLF?
            Rosenberger W                   58.7S  42.4E   32.0 Crater                 NLF?
            Ross                            11.7N  21.7E   24.0 Crater         VL1645  M1834
            Ross B                          11.4N  20.2E    6.0 Crater                 NLF?
            Ross C                          11.7N  19.0E    5.0 Crater                 NLF?
            Ross D                          12.6N  23.3E    9.0 Crater                 NLF?
            Ross E                          11.1N  23.4E    4.0 Crater                 NLF?
            Ross F                          10.9N  24.2E    5.0 Crater                 NLF?
            Ross G                          10.7N  24.9E    5.0 Crater                 NLF?
            Ross H                          10.2N  21.8E    5.0 Crater                 NLF?
            Rosse                           17.9S  35.0E   11.0 Crater                 NLF
            Rosse C                         18.5S  34.4E    5.0 Crater                 NLF?
            Rosseland                       41.0S 131.0E   75.0 Crater         N       IAU1994
            Rost                            56.4S  33.7W   48.0 Crater         H1760   H1760
            Rost A                          56.5S  36.7W   39.0 Crater                 NLF?
            Rost B                          54.6S  36.0W   21.0 Crater                 NLF?
            Rost D                          56.6S  30.9W   29.0 Crater                 NLF?
            Rost M                          55.5S  31.4W   26.0 Crater                 NLF?
            Rost N                          57.2S  33.0W    6.0 Crater                 NLF?
            Rothmann                        30.8S  27.7E   42.0 Crater                 NLF
            Rothmann A                      29.4S  27.6E    8.0 Crater                 NLF?
            Rothmann B                      31.8S  28.4E   21.0 Crater                 NLF?
            Rothmann C                      28.6S  25.1E   19.0 Crater                 NLF?
            Rothmann D                      28.9S  22.8E   14.0 Crater                 NLF?
            Rothmann E                      32.9S  29.2E   10.0 Crater                 NLF?
            Rothmann F                      29.1S  28.0E    7.0 Crater                 NLF?
            Rothmann G                      28.4S  24.3E   92.0 Crater                 NLF?
            Rothmann H                      29.1S  25.4E   11.0 Crater                 NLF?
            Rothmann J                      29.3S  25.7E    8.0 Crater                 NLF?
            Rothmann K                      28.8S  24.4E    6.0 Crater                 NLF?
            Rothmann L                      29.2S  28.7E   14.0 Crater                 NLF?
            Rothmann M                      31.2S  29.8E   16.0 Crater                 NLF?
            Rothmann W                      30.8S  26.6E   11.0 Crater                 NLF?
            Rowland                         57.4N 162.5W  171.0 Crater                 IAU1970
            Rowland G                       57.0N 159.4W   18.0 Crater                 AW82
            Rowland J                       53.1N 155.5W   49.0 Crater                 AW82
            Rowland K                       51.4N 157.1W   25.0 Crater                 AW82
            Rowland M                       51.9N 162.4W   58.0 Crater                 AW82
            Rowland N                       55.4N 163.7W   30.0 Crater                 AW82
            Rowland R                       53.7N 169.5W   24.0 Crater                 AW82
            Rowland Y                       59.1N 163.0W   54.0 Crater                 AW82
            Rozhdestvenskiy                 85.2N 155.4W  177.0 Crater                 IAU1970
            Rozhdestvenskiy H               83.6N 131.0W   21.0 Crater                 AW82
            Rozhdestvenskiy K               82.7N 144.6W   42.0 Crater                 AW82
            Rozhdestvenskiy U               85.3N 151.9E   44.0 Crater                 AW82
            Rozhdestvenskiy W               84.8N 137.2E   75.0 Crater                 AW82
            Rumford                         28.8S 169.8W   61.0 Crater                 IAU1970
            Rumford A                       25.2S 169.2W   30.0 Crater                 AW82
            Rumford B                       25.2S 168.1W   25.0 Crater                 AW82
            Rumford C                       27.4S 168.1W   26.0 Crater                 AW82
            Rumford F                       28.9S 165.3W   13.0 Crater                 AW82
            Rumford Q                       30.7S 171.6W   29.0 Crater                 AW82
            Rumford T                       28.6S 172.1W  108.0 Crater                 AW82
            Rumker C                        41.6N  58.1W    4.0 Crater                 NLF?
            Rumker E                        38.7N  57.1W    7.0 Crater                 NLF?
            Rumker F                        37.3N  57.2W    5.0 Crater                 NLF?
            Rumker H                        40.3N  52.6W    4.0 Crater                 NLF?
            Rumker K                        42.3N  56.0W    3.0 Crater                 NLF?
            Rumker L                        43.6N  57.3W    3.0 Crater                 NLF?
            Rumker S                        42.6N  63.0W    3.0 Crater                 NLF?
            Rumker T                        42.5N  64.8W    3.0 Crater                 NLF?
            Runge                            2.5S  86.7E   38.0 Crater                 IAU1973
            Russell                         26.5N  75.4W  103.0 Crater         RLA1963 IAU1964
            Russell B                       26.4N  78.2W   19.0 Crater                 NLF?
            Russell E                       28.6N  74.5W    9.0 Crater                 NLF?
            Russell F                       28.0N  76.4W    9.0 Crater                 NLF?
            Russell R                       28.7N  75.3W   45.0 Crater                 NLF?
            Russell S                       29.4N  77.1W   25.0 Crater                 NLF?
            Ruth                            28.7N  45.1W    3.0 Crater         X       IAU1976
            Rutherford                      10.7N 137.0E   13.0 Crater                 IAU1976
            Rutherfurd                      60.9S  12.1W   48.0 Crater                 NLF
            Rutherfurd A                    62.2S  11.9W   10.0 Crater                 NLF?
            Rutherfurd B                    62.6S  11.4W    6.0 Crater                 NLF?
            Rutherfurd C                    62.5S  10.7W   14.0 Crater                 NLF?
            Rutherfurd D                    63.2S   8.8W    8.0 Crater                 NLF?
            Rutherfurd E                    62.8S   8.3W    9.0 Crater                 NLF?
            Rydberg                         46.5S  96.3W   49.0 Crater                 IAU1970
            Ryder                           44.5S 143.2E   17.0 Crater                 IAU2006
            Rynin                           47.0N 103.5W   75.0 Crater                 IAU1970
            Sabatier                        13.2N  79.0E   10.0 Crater                 IAU1979
            Sabine                           1.4N  20.1E   30.0 Crater         VL1645  M1834
            Sabine A                         1.3N  19.5E    4.0 Crater                 NLF?
            Sabine C                         1.0N  23.0E    3.0 Crater                 NLF?
            Sacrobosco                      23.7S  16.7E   98.0 Crater         R1651   R1651
            Sacrobosco A                    24.0S  16.2E   17.0 Crater                 NLF?
            Sacrobosco B                    23.9S  16.9E   14.0 Crater                 NLF?
            Sacrobosco C                    23.0S  15.8E   13.0 Crater                 NLF?
            Sacrobosco D                    21.6S  17.7E   24.0 Crater                 NLF?
            Sacrobosco E                    26.1S  17.7E   13.0 Crater                 NLF?
            Sacrobosco F                    21.1S  16.7E   19.0 Crater                 NLF?
            Sacrobosco G                    20.7S  16.2E   20.0 Crater                 NLF?
            Sacrobosco H                    23.7S  18.7E   13.0 Crater                 NLF?
            Sacrobosco J                    23.6S  14.6E    5.0 Crater                 NLF?
            Sacrobosco K                    22.9S  14.7E    6.0 Crater                 NLF?
            Sacrobosco L                    25.6S  15.1E    9.0 Crater                 NLF?
            Sacrobosco M                    25.3S  16.3E    8.0 Crater                 NLF?
            Sacrobosco N                    27.0S  16.5E    6.0 Crater                 NLF?
            Sacrobosco O                    21.1S  16.0E    6.0 Crater                 NLF?
            Sacrobosco P                    20.6S  17.3E    5.0 Crater                 NLF?
            Sacrobosco Q                    21.6S  17.5E   42.0 Crater                 NLF?
            Sacrobosco R                    22.3S  15.7E   21.0 Crater                 NLF?
            Sacrobosco S                    26.5S  18.0E   19.0 Crater                 NLF?
            Sacrobosco T                    24.9S  16.8E   12.0 Crater                 NLF?
            Sacrobosco U                    24.0S  14.3E    5.0 Crater                 NLF?
            Sacrobosco V                    24.5S  16.1E    4.0 Crater                 NLF?
            Sacrobosco W                    24.3S  17.3E    2.0 Crater                 NLF?
            Sacrobosco X                    26.5S  16.3E   23.0 Crater                 NLF?
            Saenger                          4.3N 102.4E   75.0 Crater                 IAU1970
            Saenger B                        5.6N 103.1E   64.0 Crater                 AW82
            Saenger C                        6.1N 103.9E   20.0 Crater                 AW82
            Saenger D                        4.9N 103.0E   23.0 Crater                 AW82
            Saenger P                        2.7N 101.7E   41.0 Crater                 AW82
            Saenger Q                        3.4N 101.5E   14.0 Crater                 AW82
            Saenger R                        3.3N 100.3E   14.0 Crater                 AW82
            Saenger V                        5.2N 101.5E   21.0 Crater                 AW82
            Saenger X                        6.3N 101.8E   18.0 Crater                 AW82
            |vSafa|vr|%ik                   10.6N 176.9E   27.0 Crater                 IAU1970
            Safarik A                       12.6N 177.2E   19.0 Crater                 AW82
            Safarik H                        9.7N 179.3E   16.0 Crater                 AW82
            Safarik S                       10.0N 174.4E   14.0 Crater                 AW82
            Saha                             1.6S 102.7E   99.0 Crater                 IAU1970
            Saha B                           1.5N 104.5E   34.0 Crater                 AW82
            Saha C                           1.4N 107.8E   64.0 Crater                 AW82
            Saha D                           0.1N 107.5E   35.0 Crater                 AW82
            Saha E                           0.2S 107.6E   28.0 Crater                 AW82
            Saha J                           4.0S 105.3E   52.0 Crater                 AW82
            Saha M                           2.2S 102.6E   18.0 Crater                 AW82
            Saha N                           4.1S 101.5E   49.0 Crater                 AW82
            Saha W                           0.6S 101.4E   34.0 Crater                 AW82
            Samir                           28.5N  34.3W    2.0 Crater         X       IAU1979
            Sampson                         29.7N  16.5W    1.0 Crater                 IAU1976
            Sanford                         32.6N 138.9W   55.0 Crater                 IAU1970
            Sanford C                       34.1N 137.0W   18.0 Crater                 AW82
            Sanford T                       32.7N 143.3W   43.0 Crater                 AW82
            Sanford W                       33.7N 140.2W   38.0 Crater                 AW82
            Sanford Y                       33.7N 139.2W   22.0 Crater                 AW82
            Santbech                        20.9S  44.0E   64.0 Crater         VL1645  R1651
            Santbech A                      24.2S  42.3E   25.0 Crater                 NLF?
            Santbech B                      24.7S  41.6E   16.0 Crater                 NLF?
            Santbech C                      22.3S  39.5E   18.0 Crater                 NLF?
            Santbech D                      21.0S  45.2E    8.0 Crater                 NLF?
            Santbech E                      22.3S  44.8E   12.0 Crater                 NLF?
            Santbech F                      25.5S  41.9E   13.0 Crater                 NLF?
            Santbech G                      22.9S  44.5E    5.0 Crater                 NLF?
            Santbech H                      20.4S  42.8E   10.0 Crater                 NLF?
            Santbech J                      19.7S  43.3E   14.0 Crater                 NLF?
            Santbech K                      19.1S  43.1E   10.0 Crater                 NLF?
            Santbech L                      21.3S  39.4E    8.0 Crater                 NLF?
            Santbech M                      20.4S  39.3E   13.0 Crater                 NLF?
            Santbech N                      20.8S  39.6E   13.0 Crater                 NLF?
            Santbech P                      21.3S  40.0E    9.0 Crater                 NLF?
            Santbech Q                      23.2S  39.0E   12.0 Crater                 NLF?
            Santbech R                      23.3S  38.9E    5.0 Crater                 NLF?
            Santbech S                      23.5S  39.1E   10.0 Crater                 NLF?
            Santbech T                      24.1S  38.1E    5.0 Crater                 NLF?
            Santbech U                      24.0S  38.8E    9.0 Crater                 NLF?
            Santbech V                      24.6S  39.3E    7.0 Crater                 NLF?
            Santbech W                      24.3S  40.7E   13.0 Crater                 NLF?
            Santbech X                      25.2S  42.5E    7.0 Crater                 NLF?
            Santbech Y                      25.2S  42.9E    8.0 Crater                 NLF?
            Santbech Z                      25.8S  43.1E    5.0 Crater                 NLF?
            Santos-Dumont                   27.7N   4.8E    8.0 Crater                 IAU1976
            Sarabhai                        24.7N  21.0E    7.0 Crater                 IAU1973
            Sarton                          49.3N 121.1W   69.0 Crater                 IAU1970
            Sarton L                        47.0N 120.0W   48.0 Crater                 AW82
            Sarton Y                        51.5N 121.3W   26.0 Crater                 AW82
            Sarton Z                        51.6N 120.6W   29.0 Crater                 AW82
            Sasserides                      39.1S   9.3W   90.0 Crater                 NLF
            Sasserides A                    39.9S   7.0W   48.0 Crater                 NLF?
            Sasserides B                    39.5S  11.2W    9.0 Crater                 NLF?
            Sasserides D                    36.7S   6.5W   11.0 Crater                 NLF?
            Sasserides E                    38.9S   7.7W    8.0 Crater                 NLF?
            Sasserides F                    40.5S   9.9W   16.0 Crater                 NLF?
            Sasserides H                    39.2S  10.9W   12.0 Crater                 NLF?
            Sasserides K                    39.0S   7.4W    8.0 Crater                 NLF?
            Sasserides L                    40.0S   6.6W    5.0 Crater                 NLF?
            Sasserides M                    37.9S   7.1W   11.0 Crater                 NLF?
            Sasserides N                    38.7S   7.0W    7.0 Crater                 NLF?
            Sasserides P                    38.0S  10.7W   21.0 Crater                 NLF?
            Sasserides S                    38.7S   8.0W   15.0 Crater                 NLF?
            Saunder                          4.2S   8.8E   44.0 Crater         M1935   M1935
            Saunder A                        4.0S  12.3E    8.0 Crater                 NLF?
            Saunder B                        3.9S   9.8E    6.0 Crater                 NLF?
            Saunder C                        2.7S  10.5E    4.0 Crater                 NLF?
            Saunder S                        2.3S   9.7E    4.0 Crater                 NLF?
            Saunder T                        4.0S  10.4E    6.0 Crater                 NLF?
            Saussure                        43.4S   3.8W   54.0 Crater         M1834   M1834
            Saussure A                      43.8S   0.5W   19.0 Crater                 NLF?
            Saussure B                      42.2S   3.9W    5.0 Crater                 NLF?
            Saussure C                      44.8S   0.6W   16.0 Crater                 NLF?
            Saussure CA                     45.2S   0.5W   16.0 Crater                 NLF?
            Saussure D                      46.9S   0.2E   20.0 Crater                 NLF?
            Saussure E                      44.7S   2.1W   12.0 Crater                 NLF?
            Saussure F                      44.3S   4.6W    4.0 Crater                 NLF?
            Scaliger                        27.1S 108.9E   84.0 Crater                 IAU1970
            Scaliger U                      26.6S 106.5E   11.0 Crater                 AW82
            Schaeberle                      26.2S 117.2E   62.0 Crater                 IAU1970
            Schaeberle S                    26.4S 114.3E   15.0 Crater                 AW82
            Schaeberle U                    25.5S 113.9E   24.0 Crater                 AW82
            Scheele                          9.4S  37.8W    4.0 Crater                 IAU1976
            Scheiner                        60.5S  27.5W  110.0 Crater         R1651   R1651
            Scheiner A                      60.4S  28.2W   12.0 Crater                 NLF?
            Scheiner B                      59.5S  33.3W   29.0 Crater                 NLF?
            Scheiner C                      60.0S  30.7W   13.0 Crater                 NLF?
            Scheiner D                      60.7S  32.1W   17.0 Crater                 NLF?
            Scheiner E                      63.4S  29.3W   24.0 Crater                 NLF?
            Scheiner F                      56.7S  25.0W    6.0 Crater                 NLF?
            Scheiner G                      62.5S  28.2W   14.0 Crater                 NLF?
            Scheiner H                      56.2S  27.2W    9.0 Crater                 NLF?
            Scheiner J                      59.5S  28.4W   12.0 Crater                 NLF?
            Scheiner K                      58.0S  25.9W    7.0 Crater                 NLF?
            Scheiner L                      65.8S  35.1W    9.0 Crater                 NLF?
            Scheiner M                      65.8S  33.4W   10.0 Crater                 NLF?
            Scheiner P                      62.6S  31.0W   11.0 Crater                 NLF?
            Scheiner Q                      58.7S  29.4W    8.0 Crater                 NLF?
            Scheiner R                      58.0S  24.2W    8.0 Crater                 NLF?
            Scheiner S                      58.4S  25.3W    7.0 Crater                 NLF?
            Scheiner T                      60.9S  34.8W   12.0 Crater                 NLF?
            Scheiner U                      60.9S  36.0W    7.0 Crater                 NLF?
            Scheiner V                      60.6S  36.7W    5.0 Crater                 NLF?
            Scheiner W                      60.3S  37.5W    6.0 Crater                 NLF?
            Scheiner X                      59.6S  24.8W    7.0 Crater                 NLF?
            Scheiner Y                      59.1S  25.2W    9.0 Crater                 NLF?
            Schiaparelli                    23.4N  58.8W   24.0 Crater                 NLF
            Schiaparelli A                  23.0N  62.0W    7.0 Crater                 NLF?
            Schiaparelli C                  25.8N  62.2W    6.0 Crater                 NLF?
            Schiaparelli E                  27.1N  62.0W    5.0 Crater                 NLF?
            Schickard                       44.3S  55.3W  206.0 Crater         R1651   R1651
            Schickard A                     46.9S  53.6W   14.0 Crater                 NLF?
            Schickard B                     43.6S  51.9W   13.0 Crater                 NLF?
            Schickard C                     45.8S  55.8W   13.0 Crater                 NLF?
            Schickard D                     45.7S  57.4W    9.0 Crater                 NLF?
            Schickard E                     47.2S  51.6W   32.0 Crater                 NLF?
            Schickard F                     48.1S  53.6W   17.0 Crater                 NLF?
            Schickard G                     43.0S  58.9W   12.0 Crater                 NLF?
            Schickard H                     43.5S  62.2W   16.0 Crater                 NLF?
            Schickard J                     45.0S  62.1W   11.0 Crater                 NLF?
            Schickard K                     43.9S  63.8W   16.0 Crater                 NLF?
            Schickard L                     44.1S  59.6W    7.0 Crater                 NLF?
            Schickard M                     44.2S  58.9W    7.0 Crater                 NLF?
            Schickard N                     41.3S  54.6W    6.0 Crater                 NLF?
            Schickard P                     42.9S  48.3W   92.0 Crater                 NLF?
            Schickard Q                     42.7S  52.9W    5.0 Crater                 NLF?
            Schickard R                     44.1S  53.6W    5.0 Crater                 NLF?
            Schickard S                     46.6S  56.7W   15.0 Crater                 NLF?
            Schickard T                     44.8S  50.2W    4.0 Crater                 NLF?
            Schickard W                     45.0S  57.8W    7.0 Crater                 NLF?
            Schickard X                     43.6S  51.1W    8.0 Crater                 NLF?
            Schickard Y                     47.3S  57.2W    5.0 Crater                 NLF?
            Schiller                        51.9S  39.0W  180.0 Crater         VL1645  R1651
            Schiller A                      47.2S  37.6W   11.0 Crater                 NLF?
            Schiller B                      48.9S  39.0W   17.0 Crater                 NLF?
            Schiller C                      55.3S  48.8W   49.0 Crater                 NLF?
            Schiller D                      55.0S  49.2W    8.0 Crater                 NLF?
            Schiller E                      54.6S  48.8W    7.0 Crater                 NLF?
            Schiller F                      50.6S  42.8W   12.0 Crater                 NLF?
            Schiller G                      51.3S  38.3W   10.0 Crater                 NLF?
            Schiller H                      50.8S  37.7W   66.0 Crater                 NLF?
            Schiller J                      49.6S  36.6W    9.0 Crater                 NLF?
            Schiller K                      46.7S  38.7W   11.0 Crater                 NLF?
            Schiller L                      47.1S  40.2W   11.0 Crater                 NLF?
            Schiller M                      48.2S  41.1W    9.0 Crater                 NLF?
            Schiller N                      53.6S  42.0W    6.0 Crater                 NLF?
            Schiller P                      53.4S  43.4W    7.0 Crater                 NLF?
            Schiller R                      52.5S  45.3W    6.0 Crater                 NLF?
            Schiller S                      55.0S  40.3W   17.0 Crater                 NLF?
            Schiller T                      50.7S  41.4W    6.0 Crater                 NLF?
            Schiller W                      54.3S  40.8W   16.0 Crater                 NLF?
            Schjellerup                     69.7N 157.1E   62.0 Crater                 IAU1970
            Schjellerup H                   68.5N 167.4E   21.0 Crater                 AW82
            Schjellerup J                   68.1N 161.4E   37.0 Crater                 AW82
            Schjellerup N                   66.6N 154.3E   38.0 Crater                 AW82
            Schjellerup R                   68.7N 152.2E   54.0 Crater                 AW82
            Schlesinger                     47.4N 138.6W   97.0 Crater                 IAU1970
            Schlesinger A                   50.1N 137.2W   32.0 Crater                 AW82
            Schlesinger B                   51.4N 134.9W   66.0 Crater                 AW82
            Schlesinger M                   45.2N 138.5W   45.0 Crater                 AW82
            Schliemann                       2.1S 155.2E   80.0 Crater                 IAU1970
            Schliemann A                     1.2N 155.4E   64.0 Crater                 AW82
            Schliemann B                     2.1N 156.2E   32.0 Crater                 AW82
            Schliemann G                     2.4S 156.8E   19.0 Crater                 AW82
            Schliemann T                     2.0S 152.8E   21.0 Crater                 AW82
            Schliemann W                     0.2N 152.4E   19.0 Crater                 AW82
            Schl|:uter                       5.9S  83.3W   89.0 Crater         RLA1963 IAU1964
            Schl|:uter A                     9.2S  82.4W   37.0 Crater                 RLA1963?
            Schl|:uter P                     0.1N  85.1W   20.0 Crater                 RLA1963?
            Schl|:uter S                     7.9S  89.9W   13.0 Crater                 RLA1963?
            Schl|:uter U                     5.0S  89.9W   10.0 Crater                 RLA1963?
            Schl|:uter V                     4.4S  86.8W   12.0 Crater                 RLA1963?
            Schl|:uter X                     1.2N  88.2W   13.0 Crater                 RLA1963?
            Schl|:uter Z                     2.8S  83.7W   11.0 Crater                 RLA1963?
            Schmidt                          1.0N  18.8E   11.0 Crater                 NLF
            Schneller                       41.8N 163.6W   54.0 Crater                 IAU1970
            Schneller G                     40.8N 159.8W   20.0 Crater                 AW82
            Schneller H                     39.9N 160.2W   35.0 Crater                 AW82
            Schneller L                     39.5N 162.7W   25.0 Crater                 AW82
            Schneller S                     40.8N 166.3W   37.0 Crater                 AW82
            Schomberger                     76.7S  24.9E   85.0 Crater         R1651   R1651
            Schomberger A                   78.8S  24.4E   31.0 Crater                 NLF?
            Schomberger C                   77.2S  15.7E   43.0 Crater                 NLF?
            Schomberger D                   73.5S  24.6E   24.0 Crater                 NLF?
            Schomberger F                   80.1S  20.8E   11.0 Crater                 NLF?
            Schomberger G                   77.1S   7.7E   17.0 Crater                 NLF?
            Schomberger H                   77.4S   4.0E   17.0 Crater                 NLF?
            Schomberger J                   78.8S  19.6E    9.0 Crater                 NLF?
            Schomberger K                   79.7S  14.3E    9.0 Crater                 NLF?
            Schomberger L                   80.6S  17.5E   17.0 Crater                 NLF?
            Schomberger X                   75.2S  34.9E    8.0 Crater                 NLF?
            Schomberger Y                   74.6S  29.0E   17.0 Crater                 NLF?
            Schomberger Z                   73.5S  27.3E    5.0 Crater                 NLF?
            Sch|:onfeld                     44.8N  98.1W   25.0 Crater                 IAU1970
            Schorr                          19.5S  89.7E   53.0 Crater                 IAU1970
            Schorr A                        20.5S  88.4E   64.0 Crater                 AW82
            Schorr B                        16.5S  88.5E   26.0 Crater                 AW82
            Schorr C                        13.5S  88.2E   13.0 Crater                 AW82
            Schorr D                        18.6S  91.2E   21.0 Crater                 AW82
            Schr|:odinger                   75.0S 132.4E  312.0 Crater                 IAU1970
            Schr|:odinger B                 68.4S 141.3E   25.0 Crater                 AW82
            Schr|:odinger G                 75.4S 137.2E    8.0 Crater                 AW82
            Schr|:odinger J                 78.4S 154.6E   16.0 Crater                 AW82
            Schr|:odinger W                 68.5S 115.6E   12.0 Crater                 AW82
            Schr|:oter                       2.6N   7.0W   35.0 Crater                 NLF
            Schr|:oter A                     4.8N   7.8W    4.0 Crater                 NLF?
            Schr|:oter C                     8.3N   9.8W    8.0 Crater                 NLF?
            Schr|:oter D                     4.5N   9.5W    5.0 Crater                 NLF?
            Schr|:oter E                     2.4N   6.8W    3.0 Crater                 NLF?
            Schr|:oter F                     7.4N   5.9W   34.0 Crater                 NLF?
            Schr|:oter G                     3.2N   9.4W    5.0 Crater                 NLF?
            Schr|:oter H                     3.2N   8.6W    4.0 Crater                 NLF?
            Schr|:oter J                     8.5N   6.1W    6.0 Crater                 NLF?
            Schr|:oter K                     3.1N   7.9W    5.0 Crater                 NLF?
            Schr|:oter L                     1.8N   7.4W    4.0 Crater                 NLF?
            Schr|:oter M                     7.0N  11.6W    5.0 Crater                 NLF?
            Schr|:oter S                     7.1N   9.2W    3.0 Crater                 NLF?
            Schr|:oter T                     7.0N   8.0W    4.0 Crater                 NLF?
            Schr|:oter U                     4.1N   6.6W    4.0 Crater                 NLF?
            Schr|:oter W                     4.8N   7.7W   10.0 Crater                 NLF?
            Schubert                         2.8N  81.0E   54.0 Crater         M1834   M1834
            Schubert A                       2.1N  79.3E    2.0 Crater                 NLF?
            Schubert C                       1.8N  84.6E   31.0 Crater                 NLF?
            Schubert E                       4.0N  78.6E   27.0 Crater                 NLF?
            Schubert F                       3.2N  77.9E   35.0 Crater                 NLF?
            Schubert G                       4.1N  75.2E   56.0 Crater                 NLF?
            Schubert H                       1.4N  76.1E   31.0 Crater                 NLF?
            Schubert J                       0.1S  78.9E   20.0 Crater                 NLF?
            Schubert K                       2.3N  75.9E   29.0 Crater                 NLF?
            Schubert N                       1.8N  72.7E   75.0 Crater                 NLF?
            Schubert X                       0.3N  76.8E   51.0 Crater                 NLF?
            Schumacher                      42.4N  60.7E   60.0 Crater         M1834   M1834
            Schumacher B                    42.1N  59.4E   24.0 Crater                 NLF?
            Schuster                         4.2N 146.5E  108.0 Crater                 IAU1970
            Schuster J                       1.8N 149.6E   14.0 Crater                 AW82
            Schuster K                       1.3N 147.7E   17.0 Crater                 AW82
            Schuster N                       3.4N 145.8E   27.0 Crater                 AW82
            Schuster P                       1.9N 144.4E   16.0 Crater                 AW82
            Schuster Q                       1.0N 143.4E   45.0 Crater                 AW82
            Schuster R                       3.5N 144.8E   40.0 Crater                 AW82
            Schuster Y                       6.7N 145.5E   17.0 Crater                 AW82
            Schwabe                         65.1N  45.6E   25.0 Crater         S1878   S1878
            Schwabe C                       67.8N  46.9E   29.0 Crater                 NLF?
            Schwabe D                       64.5N  44.6E   17.0 Crater                 NLF?
            Schwabe E                       64.0N  43.4E   19.0 Crater                 NLF?
            Schwabe F                       66.4N  50.0E   20.0 Crater                 NLF?
            Schwabe G                       65.5N  42.2E   15.0 Crater                 NLF?
            Schwabe K                       67.5N  48.8E    9.0 Crater                 NLF?
            Schwabe U                       66.5N  57.1E   17.0 Crater                 NLF?
            Schwabe W                       69.6N  52.2E    9.0 Crater                 NLF?
            Schwabe X                       68.3N  56.6E    8.0 Crater                 NLF?
            Schwarzschild                   70.1N 121.2E  212.0 Crater                 IAU1970
            Schwarzschild A                 78.7N 124.0E   50.0 Crater                 AW82
            Schwarzschild D                 71.9N 132.4E   24.0 Crater                 AW82
            Schwarzschild K                 67.5N 125.0E   45.0 Crater                 AW82
            Schwarzschild L                 69.3N 122.1E   45.0 Crater                 AW82
            Schwarzschild Q                 66.3N 108.9E   19.0 Crater                 AW82
            Schwarzschild S                 67.8N 104.7E   17.0 Crater                 AW82
            Schwarzschild T                 69.9N 107.7E   16.0 Crater                 AW82
            Scobee                          31.1S 148.9W   40.0 Crater         N       IAU1988
            Scoresby                        77.7N  14.1E   55.0 Crater         VL1645  M1834
            Scoresby K                      76.3N   2.9E   23.0 Crater                 NLF?
            Scoresby M                      75.6N   8.1E   54.0 Crater                 NLF?
            Scoresby P                      75.8N  13.0E   26.0 Crater                 NLF?
            Scoresby Q                      77.4N   8.7E   40.0 Crater                 NLF?
            Scoresby W                      74.5N  11.2E   10.0 Crater                 NLF?
            Scott                           82.1S  48.5E  103.0 Crater         W1926   IAU1964
            Scott E                         81.1S  35.5E   28.0 Crater                 NLF?
            Scott M                         84.3S  39.7E   16.0 Crater                 NLF?
            Seares                          73.5N 145.8E  110.0 Crater                 IAU1970
            Seares B                        75.7N 149.7E   26.0 Crater                 AW82
            Seares Y                        77.9N 139.5E   37.0 Crater                 AW82
            Secchi                           2.4N  43.5E   22.0 Crater                 NLF
            Secchi A                         3.3N  41.5E    5.0 Crater                 NLF?
            Secchi B                         3.7N  41.5E    5.0 Crater                 NLF?
            Secchi G                         4.0N  44.6E    8.0 Crater                 NLF?
            Secchi K                         0.2S  45.4E    3.0 Crater                 NLF?
            Secchi U                         1.1N  42.2E    6.0 Crater                 NLF?
            Secchi X                         0.7S  43.6E    6.0 Crater                 NLF?
            Sechenov                         7.1S 142.6W   62.0 Crater                 IAU1970
            Sechenov C                       5.2S 141.3W   19.0 Crater                 AW82
            Sechenov P                       9.8S 143.8W   23.0 Crater                 AW82
            Seeliger                         2.2S   3.0E    8.0 Crater         K1898   K1898
            Seeliger A                       1.8S   3.0E    4.0 Crater                 NLF?
            Seeliger S                       2.1S   2.1E    4.0 Crater                 NLF?
            Seeliger T                       2.2S   4.4E    4.0 Crater                 NLF?
            Segers                          47.1N 127.7E   17.0 Crater                 IAU1970
            Segers H                        46.7N 129.0E   29.0 Crater                 AW82
            Segers M                        44.5N 127.6E   54.0 Crater                 AW82
            Segers N                        44.0N 127.5E   27.0 Crater                 AW82
            Segner                          58.9S  48.3W   67.0 Crater         S1791   S1791
            Segner A                        57.2S  47.0W    9.0 Crater                 NLF?
            Segner B                        57.8S  56.0W   35.0 Crater                 NLF?
            Segner C                        57.7S  45.9W   19.0 Crater                 NLF?
            Segner E                        57.6S  56.9W   13.0 Crater                 NLF?
            Segner G                        56.4S  55.3W   12.0 Crater                 NLF?
            Segner H                        58.4S  48.0W    7.0 Crater                 NLF?
            Segner K                        56.1S  54.1W   10.0 Crater                 NLF?
            Segner L                        58.7S  47.0W    5.0 Crater                 NLF?
            Segner M                        59.8S  45.3W    5.0 Crater                 NLF?
            Segner N                        59.2S  44.2W    5.0 Crater                 NLF?
            Seidel                          32.8S 152.2E   62.0 Crater                 IAU1970
            Seidel J                        33.5S 152.8E   19.0 Crater                 AW82
            Seidel M                        35.3S 152.0E   28.0 Crater                 AW82
            Seidel U                        32.5S 150.2E   33.0 Crater                 AW82
            Seleucus                        21.0N  66.6W   43.0 Crater         VL1645  R1651
            Seleucus A                      22.0N  60.5W    6.0 Crater                 NLF?
            Seleucus E                      22.4N  63.9W    4.0 Crater                 NLF?
            Seneca                          26.6N  80.2E   46.0 Crater         R1651   R1651
            Seneca A                        26.4N  75.7E   17.0 Crater                 NLF?
            Seneca B                        27.2N  77.4E   28.0 Crater                 NLF?
            Seneca C                        26.3N  75.1E   22.0 Crater                 NLF?
            Seneca D                        26.6N  81.3E   18.0 Crater                 NLF?
            Seneca E                        29.2N  79.6E   16.0 Crater                 NLF?
            Seneca F                        29.5N  81.9E   15.0 Crater                 NLF?
            Seneca G                        29.4N  83.2E   19.0 Crater                 NLF?
            Seyfert                         29.1N 114.6E  110.0 Crater                 IAU1970
            Seyfert A                       30.5N 114.9E   53.0 Crater                 AW82
            Shackleton                      89.9S   0.0E   19.0 Crater         N       IAU1994
            Shahinaz                         7.5N 122.4E   15.0 Crater         X       IAU1979
            Shakespeare                     20.2N  30.8E    1.0 Crater (A)             IAU1973
            Shaler                          32.9S  85.2W   48.0 Crater         RLA1963 IAU1964
            Shapley                          9.4N  56.9E   23.0 Crater                 IAU1973
            Sharonov                        12.4N 173.3E   74.0 Crater                 IAU1970
            Sharonov D                      13.5N 175.4E   17.0 Crater                 AW82
            Sharonov F                      12.3N 176.2E   14.0 Crater                 AW82
            Sharonov X                      14.1N 172.7E   36.0 Crater                 AW82
            Sharp                           45.7N  40.2W   39.0 Crater         VL1645  H1760
            Sharp A                         47.6N  42.6W   17.0 Crater                 NLF?
            Sharp B                         47.0N  45.3W   21.0 Crater                 NLF?
            Sharp D                         44.8N  42.1W    8.0 Crater                 NLF?
            Sharp J                         46.9N  37.9W    6.0 Crater                 NLF?
            Sharp K                         47.4N  38.5W    5.0 Crater                 NLF?
            Sharp L                         45.8N  38.2W    6.0 Crater                 NLF?
            Sharp M                         47.3N  41.4W    4.0 Crater                 NLF?
            Sharp U                         47.4N  48.6W    6.0 Crater                 NLF?
            Sharp V                         46.2N  46.9W    7.0 Crater                 NLF?
            Sharp W                         50.2N  45.3W    4.0 Crater                 NLF?
            Sharp-Apollo                     3.2S  23.4W    0.0 Crater (A)             IAU1973
            Shatalov                        24.3N 141.5E   21.0 Crater                 IAU1970
            Shayn                           32.6N 172.5E   93.0 Crater                 IAU1970
            Shayn B                         34.5N 173.5E   35.0 Crater                 AW82
            Shayn F                         33.0N 175.5E   38.0 Crater                 AW82
            Shayn H                         31.4N 175.5E   38.0 Crater                 AW82
            Shayn Y                         35.9N 171.7E   23.0 Crater                 AW82
            Sheepshanks                     59.2N  16.9E   25.0 Crater                 NLF
            Sheepshanks A                   60.0N  19.0E    7.0 Crater                 NLF?
            Sheepshanks B                   60.3N  21.1E    5.0 Crater                 NLF?
            Sheepshanks C                   57.0N  18.1E   11.0 Crater                 NLF?
            Sherlock                        20.2N  30.8E    0.0 Crater (A)             IAU1973
            Sherrington                     11.1S 118.0E   18.0 Crater                 IAU1976
            Shi Shen                        76.0N 104.1E   43.0 Crater                 IAU1970
            Shi Shen P                      71.7N  97.0E   22.0 Crater                 AW82
            Shi Shen Q                      74.2N  96.3E   45.0 Crater                 AW82
            Shirakatsi                      12.1S 128.6E   51.0 Crater                 IAU1979
            Shoemaker                       88.1S  44.9E   50.9 Crater         N       IAU2000
            Short                           74.6S   7.3W   70.0 Crater         S1791   S1791
            Short A                         76.9S   0.5W   34.0 Crater                 NLF?
            Short B                         75.5S   5.0W   71.0 Crater                 NLF?
            Shorty                          20.2N  30.6E    0.0 Crater (A)             IAU1973
            Shternberg                      19.5N 116.3W   70.0 Crater                 IAU1970
            Shternberg C                    20.9N 114.3W   29.0 Crater                 AW82
            Shuckburgh                      42.6N  52.8E   38.0 Crater                 NLF
            Shuckburgh A                    43.1N  55.5E   19.0 Crater                 NLF?
            Shuckburgh C                    43.5N  52.7E   12.0 Crater                 NLF?
            Shuckburgh E                    44.1N  56.9E    9.0 Crater                 NLF?
            Shuleykin                       27.1S  92.5W   15.0 Crater         N       IAU1985
            Siedentopf                      22.0N 135.5E   61.0 Crater                 IAU1970
            Siedentopf F                    22.1N 138.5E   42.0 Crater                 AW82
            Siedentopf G                    20.5N 138.4E   61.0 Crater                 AW82
            Siedentopf H                    20.9N 137.2E   42.0 Crater                 AW82
            Siedentopf M                    19.0N 135.5E   31.0 Crater                 AW82
            Siedentopf Q                    20.7N 133.7E   42.0 Crater                 AW82
            Sierpinski                      27.2S 154.5E   69.0 Crater                 IAU1970
            Sierpinski Q                    28.3S 153.6E   15.0 Crater                 AW82
            Sikorsky                        66.1S 103.2E   98.0 Crater                 IAU1979
            Sikorsky Q                      66.0S 103.1E   15.0 Crater                 AW82
            Silberschlag                     6.2N  12.5E   13.0 Crater         S1791   S1791
            Silberschlag A                   6.9N  13.2E    7.0 Crater                 NLF?
            Silberschlag D                   7.5N  11.2E    4.0 Crater                 NLF?
            Silberschlag E                   5.2N  12.8E    4.0 Crater                 NLF?
            Silberschlag G                   5.7N  13.8E    3.0 Crater                 NLF?
            Silberschlag P                   6.7N  12.0E   25.0 Crater                 NLF?
            Silberschlag S                   8.0N  12.1E   34.0 Crater                 NLF?
            Simpelius                       73.0S  15.2E   70.0 Crater         R1651   R1651
            Simpelius A                     70.1S  16.5E   60.0 Crater                 NLF?
            Simpelius B                     75.2S  10.2E   50.0 Crater         R1651   NLF?
            Simpelius C                     72.6S   5.9E   49.0 Crater                 NLF?
            Simpelius D                     71.6S   8.6E   54.0 Crater                 NLF?
            Simpelius E                     70.1S  11.0E   45.0 Crater                 NLF?
            Simpelius F                     68.7S  16.8E   29.0 Crater                 NLF?
            Simpelius G                     71.8S  23.0E   24.0 Crater                 NLF?
            Simpelius H                     68.0S  15.5E   29.0 Crater                 NLF?
            Simpelius J                     76.1S   8.4E   17.0 Crater                 NLF?
            Simpelius K                     74.8S  15.7E   23.0 Crater                 NLF?
            Simpelius L                     70.4S   6.7E   16.0 Crater                 NLF?
            Simpelius M                     70.4S  16.4E    7.0 Crater                 NLF?
            Simpelius N                     71.3S  24.3E    8.0 Crater                 NLF?
            Simpelius P                     75.5S   5.0E    8.0 Crater                 NLF?
            Sinas                            8.8N  31.6E   11.0 Crater         S1878   S1878
            Sinas A                          7.8N  32.6E    6.0 Crater                 NLF?
            Sinas E                          9.7N  31.0E    9.0 Crater                 NLF?
            Sinas G                          9.6N  34.3E    5.0 Crater                 NLF?
            Sinas H                         10.0N  33.5E    6.0 Crater                 NLF?
            Sinas J                         10.3N  33.7E    6.0 Crater                 NLF?
            Sinas K                          6.8N  33.1E    5.0 Crater                 NLF?
            Sirsalis                        12.5S  60.4W   42.0 Crater         R1651   R1651
            Sirsalis A                      12.7S  61.3W   49.0 Crater         VL1645  NLF?
            Sirsalis B                      11.1S  63.7W   16.0 Crater                 NLF?
            Sirsalis C                      10.3S  63.8W   22.0 Crater                 NLF?
            Sirsalis D                       9.9S  58.6W   35.0 Crater                 NLF?
            Sirsalis E                       8.1S  56.5W   72.0 Crater                 NLF?
            Sirsalis F                      13.5S  60.1W   13.0 Crater                 NLF?
            Sirsalis G                      13.7S  61.7W   30.0 Crater                 NLF?
            Sirsalis H                      14.0S  62.4W   26.0 Crater                 NLF?
            Sirsalis J                      13.4S  59.8W   12.0 Crater                 NLF?
            Sirsalis K                      10.4S  57.3W    7.0 Crater                 NLF?
            Sirsalis T                       9.2S  53.4W   16.0 Crater                 NLF?
            Sirsalis Z                      10.7S  61.9W   91.0 Crater         R1651   NLF?
            Sisakyan                        41.2N 109.0E   34.0 Crater                 IAU1970
            Sisakyan C                      42.1N 110.9E   17.0 Crater                 AW82
            Sisakyan D                      42.0N 111.0E   52.0 Crater                 AW82
            Sisakyan E                      41.4N 110.7E   19.0 Crater                 AW82
            Sita                             4.6N 120.8E    2.0 Crater         X       IAU1976
            Sklodowska                      18.2S  95.5E  127.0 Crater                 IAU1970
            Sklodowska A                    14.7S  96.5E   44.0 Crater                 AW82
            Sklodowska D                    13.7S  99.0E   16.0 Crater                 AW82
            Sklodowska J                    19.3S  97.7E   16.0 Crater                 AW82
            Sklodowska R                    18.9S  92.2E   17.0 Crater                 AW82
            Sklodowska Y                    13.2S  95.4E   17.0 Crater                 AW82
            Slipher                         49.5N 160.1E   69.0 Crater                 IAU1970
            Slipher S                       49.2N 158.7E   26.0 Crater                 AW82
            Slocum                           3.0S  89.0E   13.0 Crater                 IAU1976
            Smith                           31.6S 150.2W   34.0 Crater         N       IAU1988
            Smithson                         2.4N  53.6E    5.0 Crater                 IAU1976
            Smoluchowski                    60.3N  96.8W   83.0 Crater                 IAU1970
            Smoluchowski F                  60.1N  90.9W   35.0 Crater                 AW82
            Smoluchowski H                  59.5N  92.7W   41.0 Crater                 AW82
            Snellius                        29.3S  55.7E   82.0 Crater         VL1645  NLF
            Snellius A                      27.4S  53.8E   37.0 Crater                 NLF?
            Snellius B                      30.1S  53.1E   29.0 Crater                 NLF?
            Snellius C                      29.0S  51.5E    9.0 Crater                 NLF?
            Snellius D                      28.7S  51.5E    9.0 Crater                 NLF?
            Snellius E                      28.0S  51.5E   12.0 Crater                 NLF?
            Snellius X                      27.4S  55.1E    7.0 Crater                 NLF?
            Snellius Y                      25.7S  52.2E   10.0 Crater                 NLF?
            Sniadecki                       22.5S 168.9W   43.0 Crater                 IAU1970
            Sniadecki F                     22.4S 166.9W   12.0 Crater                 AW82
            Sniadecki J                     24.7S 166.9W   27.0 Crater                 AW82
            Sniadecki Q                     23.0S 170.1W   77.0 Crater                 AW82
            Sniadecki Y                     21.1S 169.3W   35.0 Crater                 AW82
            Snowman                          3.2S  23.4W    1.0 Crater (A)             IAU1973
            Soddy                            0.4N 121.8E   42.0 Crater                 IAU1976
            Soddy E                          0.8N 123.4E   16.0 Crater                 AW82
            Soddy G                          0.5N 123.5E   13.0 Crater                 AW82
            Soddy P                          0.4S 120.9E    8.0 Crater                 AW82
            Soddy Q                          0.5S 120.2E   24.0 Crater                 AW82
            Somerville                       8.3S  64.9E   15.0 Crater                 IAU1976
            Sommerfeld                      65.2N 162.4W  169.0 Crater                 IAU1970
            Sommerfeld N                    62.3N 162.2W   39.0 Crater                 AW82
            Sommerfeld V                    66.9N 170.3W   32.0 Crater                 AW82
            S|:ommering                      0.1N   7.5W   28.0 Crater         M1834   M1834
            S|:ommering A                    1.1N  11.1W    3.0 Crater                 NLF?
            S|:ommering P                    2.2N  10.3W    6.0 Crater                 NLF?
            S|:ommering R                    1.9N   9.8W   17.0 Crater                 NLF?
            Soraya                          12.9S   1.6W    2.0 Crater         X       IAU1976
            Sosigenes                        8.7N  17.6E   17.0 Crater                 NLF
            Sosigenes A                      7.8N  18.5E   12.0 Crater                 NLF?
            Sosigenes B                      8.3N  17.2E    4.0 Crater                 NLF?
            Sosigenes C                      7.2N  18.9E    3.0 Crater                 NLF?
            South                           58.0N  50.8W  104.0 Crater                 NLF
            South A                         57.1N  49.9W    6.0 Crater                 NLF?
            South B                         57.5N  44.9W   14.0 Crater                 NLF?
            South C                         55.8N  49.4W    7.0 Crater                 NLF?
            South D                         55.2N  48.8W    5.0 Crater                 NLF?
            South E                         56.7N  52.8W    8.0 Crater                 NLF?
            South F                         57.2N  53.9W    7.0 Crater                 NLF?
            South G                         55.1N  53.3W    6.0 Crater                 NLF?
            South H                         57.2N  47.8W    4.0 Crater                 NLF?
            South K                         59.1N  49.9W    3.0 Crater                 NLF?
            South M                         55.4N  51.0W    6.0 Crater                 NLF?
            South Cluster                   26.0N   3.7E    2.0 Crater (A)             IAU1973
            South Ray                        9.2S  15.4E    1.0 Crater (A)             IAU1973
            Spallanzani                     46.3S  24.7E   32.0 Crater         S1878   S1878
            Spallanzani A                   46.2S  25.6E    6.0 Crater                 NLF?
            Spallanzani D                   46.1S  28.6E    6.0 Crater                 NLF?
            Spallanzani F                   45.6S  28.0E   22.0 Crater                 NLF?
            Spallanzani G                   45.3S  28.6E   15.0 Crater                 NLF?
            Spencer Jones                   13.3N 165.6E   85.0 Crater                 IAU1970
            Spencer Jones H                 12.1N 167.9E   17.0 Crater                 AW82
            Spencer Jones J                  9.7N 168.0E   12.0 Crater                 AW82
            Spencer Jones K                 10.4N 167.0E   29.0 Crater                 AW82
            Spencer Jones Q                 12.0N 164.4E   17.0 Crater                 AW82
            Spencer Jones W                 15.2N 163.3E   50.0 Crater                 AW82
            Spitzbergen A                   32.7N   7.1W    7.0 Crater                 NLF?
            Spitzbergen C                   32.8N   8.8W    7.0 Crater                 NLF?
            Spitzbergen D                   33.3N   8.7W    3.0 Crater                 NLF?
            Spook                            9.0S  15.5E    0.0 Crater (A)             IAU1973
            Sp|:orer                         4.3S   1.8W   27.0 Crater         K1898   K1898
            Sp|:orer A                       3.4S   2.1W    5.0 Crater                 NLF?
            Spot                             9.0S  15.5E    0.0 Crater (A)             IAU1973
            Spur                            25.9N   3.7E    0.1 Crater (A)             IAU1973
            Spurr                           27.9N   1.2W   13.0 Crater                 IAU1973
            St. George                      26.0N   3.5E    2.0 Crater (A)             IAU1973
            St. John                        10.2N 150.2E   68.0 Crater                 IAU1970
            St. John A                      12.4N 150.5E   16.0 Crater                 AW82
            St. John M                       7.5N 150.1E   16.0 Crater                 AW82
            St. John W                      12.6N 147.0E   18.0 Crater                 AW82
            St. John X                      13.9N 147.4E   30.0 Crater                 AW82
            St. John Y                      13.8N 149.0E   21.0 Crater                 AW82
            Stadius                         10.5N  13.7W   69.0 Crater                 NLF
            Stadius A                       10.4N  14.8W    5.0 Crater                 NLF?
            Stadius B                       11.8N  13.6W    6.0 Crater                 NLF?
            Stadius C                        9.7N  12.8W    3.0 Crater                 NLF?
            Stadius D                       10.3N  15.3W    4.0 Crater                 NLF?
            Stadius E                       12.6N  15.6W    5.0 Crater                 NLF?
            Stadius F                       13.0N  15.7W    5.0 Crater                 NLF?
            Stadius G                       11.2N  14.8W    5.0 Crater                 NLF?
            Stadius H                       11.6N  13.9W    4.0 Crater                 NLF?
            Stadius J                       13.8N  16.1W    4.0 Crater                 NLF?
            Stadius K                        9.7N  13.6W    4.0 Crater                 NLF?
            Stadius L                       10.1N  12.9W    3.0 Crater                 NLF?
            Stadius M                       14.7N  16.5W    7.0 Crater                 NLF?
            Stadius N                        9.4N  15.7W    5.0 Crater                 NLF?
            Stadius P                       11.8N  15.2W    6.0 Crater                 NLF?
            Stadius Q                       11.5N  14.8W    4.0 Crater                 NLF?
            Stadius R                       12.2N  15.2W    6.0 Crater                 NLF?
            Stadius S                       12.9N  15.5W    5.0 Crater                 NLF?
            Stadius T                       13.2N  15.7W    7.0 Crater                 NLF?
            Stadius U                       13.9N  16.4W    5.0 Crater                 NLF?
            Stadius W                       14.1N  16.4W    5.0 Crater                 NLF?
            Stark                           25.5S 134.6E   49.0 Crater                 IAU1970
            Stark R                         26.3S 133.2E   21.0 Crater                 AW82
            Stark V                         25.1S 133.3E   25.0 Crater                 AW82
            Stark Y                         24.4S 134.0E   31.0 Crater                 AW82
            Stearns                         34.8N 162.6E   36.0 Crater                 IAU1979
            Stebbins                        64.8N 141.8W  131.0 Crater                 IAU1970
            Stebbins C                      67.7N 133.6W   39.0 Crater                 AW82
            Stebbins U                      65.4N 147.6W   44.0 Crater                 AW82
            Stefan                          46.0N 108.3W  125.0 Crater         RLA1963 IAU1964
            Stefan L                        44.6N 107.7W   26.0 Crater                 AW82
            Stein                            7.2N 179.0E   33.0 Crater                 IAU1970
            Stein C                          8.9N 178.8W   27.0 Crater                 AW82
            Stein K                          5.2N 180.0W   20.0 Crater                 AW82
            Stein L                          4.6N 179.8W   15.0 Crater                 AW82
            Stein M                          3.8N 178.8E   28.0 Crater                 AW82
            Stein N                          2.2N 178.5E   16.0 Crater                 AW82
            Steinheil                       48.6S  46.5E   67.0 Crater         M1834   M1834
            Steinheil E                     44.9S  47.6E   16.0 Crater                 NLF?
            Steinheil F                     45.3S  48.4E   21.0 Crater                 NLF?
            Steinheil G                     45.6S  49.9E   19.0 Crater                 NLF?
            Steinheil H                     45.7S  46.9E   20.0 Crater                 NLF?
            Steinheil K                     48.6S  51.9E    5.0 Crater                 NLF?
            Steinheil X                     47.6S  45.8E   17.0 Crater                 NLF?
            Steinheil Y                     47.3S  45.1E   16.0 Crater                 NLF?
            Steinheil Z                     46.4S  45.4E   23.0 Crater                 NLF?
            Steklov                         36.7S 104.9W   36.0 Crater                 IAU1970
            Stella                          19.9N  29.8E    1.0 Crater         X       IAU1976
            Steno                           32.8N 161.8E   31.0 Crater                 IAU1970
            Steno N                         31.3N 161.4E   20.0 Crater                 AW82
            Steno Q                         29.3N 157.8E   29.0 Crater                 AW82
            Steno R                         31.3N 158.9E   17.0 Crater                 AW82
            Steno T                         32.7N 159.7E   37.0 Crater                 AW82
            Steno U                         33.1N 158.3E   27.0 Crater                 AW82
            Steno-Apollo                    20.1N  30.8E    1.0 Crater (A)             IAU1973
            Sternfeld                       19.6S 141.2W  100.0 Crater         N       IAU1991
            Stetson                         39.6S 118.3W   64.0 Crater                 IAU1970
            Stetson E                       39.4S 117.0W   38.0 Crater                 AW82
            Stetson G                       39.9S 117.2W   23.0 Crater                 AW82
            Stetson N                       43.2S 120.2W   18.0 Crater                 AW82
            Stetson P                       41.8S 119.8W   24.0 Crater                 AW82
            Stevinus                        32.5S  54.2E   74.0 Crater         VL1645  NLF
            Stevinus A                      31.8S  51.6E    8.0 Crater                 NLF?
            Stevinus B                      31.1S  52.6E   20.0 Crater                 NLF?
            Stevinus C                      33.4S  52.8E   19.0 Crater                 NLF?
            Stevinus D                      34.8S  50.9E   22.0 Crater                 NLF?
            Stevinus E                      35.3S  52.5E   16.0 Crater                 NLF?
            Stevinus F                      30.6S  52.7E   10.0 Crater                 NLF?
            Stevinus G                      33.7S  50.4E   13.0 Crater                 NLF?
            Stevinus H                      33.2S  50.6E   15.0 Crater                 NLF?
            Stevinus J                      36.1S  52.4E   13.0 Crater                 NLF?
            Stevinus K                      34.3S  55.4E    8.0 Crater                 NLF?
            Stevinus L                      33.8S  56.1E   14.0 Crater                 NLF?
            Stevinus R                      31.6S  50.9E   26.0 Crater                 NLF?
            Stevinus S                      30.7S  51.2E    7.0 Crater                 NLF?
            Stewart                          2.2N  67.0E   13.0 Crater                 IAU1976
            Stiborius                       34.4S  32.0E   43.0 Crater         VL1645  R1651
            Stiborius A                     36.9S  35.5E   32.0 Crater                 NLF?
            Stiborius B                     37.3S  33.5E    9.0 Crater                 NLF?
            Stiborius C                     33.9S  33.3E   22.0 Crater                 NLF?
            Stiborius D                     33.4S  35.7E   18.0 Crater                 NLF?
            Stiborius E                     34.8S  34.1E   15.0 Crater                 NLF?
            Stiborius F                     35.7S  32.4E    8.0 Crater                 NLF?
            Stiborius G                     37.3S  35.7E   10.0 Crater                 NLF?
            Stiborius J                     36.1S  35.6E   10.0 Crater                 NLF?
            Stiborius K                     35.5S  34.6E   16.0 Crater                 NLF?
            Stiborius L                     35.0S  33.5E   10.0 Crater                 NLF?
            Stiborius M                     35.5S  32.8E    7.0 Crater                 NLF?
            Stiborius N                     36.3S  32.9E    9.0 Crater                 NLF?
            Stiborius P                     33.2S  34.0E    6.0 Crater                 NLF?
            St|:ofler                       41.1S   6.0E  126.0 Crater         R1651   R1651
            St|:ofler D                     43.8S   4.3E   54.0 Crater                 NLF?
            St|:ofler E                     43.8S   5.8E   16.0 Crater                 NLF?
            St|:ofler F                     42.7S   4.9E   18.0 Crater                 NLF?
            St|:ofler G                     43.4S   2.0E   20.0 Crater                 NLF?
            St|:ofler H                     40.3S   1.7E   27.0 Crater                 NLF?
            St|:ofler J                     42.2S   2.4E   76.0 Crater                 NLF?
            St|:ofler K                     39.4S   4.2E   19.0 Crater                 NLF?
            St|:ofler L                     39.1S   7.8E   17.0 Crater                 NLF?
            St|:ofler M                     41.0S   8.1E    9.0 Crater                 NLF?
            St|:ofler N                     41.9S   6.6E   14.0 Crater                 NLF?
            St|:ofler O                     43.3S   1.3E    9.0 Crater                 NLF?
            St|:ofler P                     43.2S   7.3E   33.0 Crater                 NLF?
            St|:ofler R                     42.2S   1.8E    6.0 Crater                 NLF?
            St|:ofler S                     44.9S   5.8E    9.0 Crater                 NLF?
            St|:ofler T                     39.7S   8.2E    5.0 Crater                 NLF?
            St|:ofler U                     40.1S   9.6E    5.0 Crater                 NLF?
            St|:ofler X                     40.5S   5.5E    3.0 Crater                 NLF?
            St|:ofler Y                     39.9S   5.5E    3.0 Crater                 NLF?
            St|:ofler Z                     40.3S   3.2E    4.0 Crater                 NLF?
            Stokes                          52.5N  88.1W   51.0 Crater         RLA1963 IAU1964
            Stoletov                        45.1N 155.2W   42.0 Crater                 IAU1970
            Stoletov C                      46.3N 153.6W   36.0 Crater                 AW82
            Stoletov Y                      46.5N 155.6W   22.0 Crater                 AW82
            Stoney                          55.3S 156.1W   45.0 Crater                 IAU1970
            St|:ormer                       57.3N 146.3E   69.0 Crater                 IAU1970
            St|:ormer C                     58.3N 150.6E   61.0 Crater                 AW82
            St|:ormer H                     54.8N 150.2E   32.0 Crater                 AW82
            St|:ormer P                     56.1N 145.3E   22.0 Crater                 AW82
            St|:ormer T                     56.8N 141.7E   27.0 Crater                 AW82
            St|:ormer Y                     60.3N 144.8E   26.0 Crater                 AW82
            Strabo                          61.9N  54.3E   55.0 Crater         VL1645  M1834
            Strabo B                        64.6N  55.5E   23.0 Crater                 NLF?
            Strabo C                        67.1N  59.3E   17.0 Crater                 NLF?
            Strabo L                        64.2N  53.4E   26.0 Crater                 NLF?
            Strabo N                        64.8N  57.8E   25.0 Crater                 NLF?
            Stratton                         5.8S 164.6E   70.0 Crater                 IAU1970
            Stratton F                       5.5S 166.9E   22.0 Crater                 AW82
            Stratton K                       7.4S 165.8E   41.0 Crater                 AW82
            Stratton L                       7.2S 165.1E   13.0 Crater                 AW82
            Stratton Q                       6.3S 163.8E   13.0 Crater                 AW82
            Stratton R                       6.7S 163.0E   14.0 Crater                 AW82
            Stratton U                       5.3S 162.5E   12.0 Crater                 AW82
            Street                          46.5S  10.5W   57.0 Crater         VL1645  S1791
            Street A                        47.0S   9.0W   17.0 Crater                 NLF?
            Street B                        47.1S  12.1W   14.0 Crater                 NLF?
            Street C                        48.3S  15.4W   15.0 Crater                 NLF?
            Street D                        48.9S  12.6W   11.0 Crater                 NLF?
            Street E                        47.5S  11.8W   12.0 Crater                 NLF?
            Street F                        48.3S  16.6W    8.0 Crater                 NLF?
            Street G                        46.6S  15.0W   11.0 Crater                 NLF?
            Street H                        48.3S  12.2W   29.0 Crater                 NLF?
            Street J                        48.7S  13.7W    7.0 Crater                 NLF?
            Street K                        47.6S  13.1W    9.0 Crater                 NLF?
            Street L                        50.7S  13.5W    8.0 Crater                 NLF?
            Street M                        47.7S  14.6W   49.0 Crater                 NLF?
            Street N                        48.1S  10.4W    5.0 Crater                 NLF?
            Street P                        45.7S  11.9W    6.0 Crater                 NLF?
            Street R                        49.1S  14.5W    5.0 Crater                 NLF?
            Street S                        49.0S  14.7W    4.0 Crater                 NLF?
            Street T                        49.2S  15.1W    9.0 Crater                 NLF?
            Str|:omgren                     21.7S 132.4W   61.0 Crater                 IAU1970
            Str|:omgren A                   17.8S 131.7W   51.0 Crater                 AW82
            Str|:omgren X                   17.4S 134.6W   42.0 Crater                 AW82
            Stubby                           9.1S  15.5E    1.0 Crater (A)             IAU1973
            Struve                          22.4N  77.1W  164.0 Crater         M1834   M1834
            Struve B                        19.0N  77.0W   14.0 Crater                 NLF?
            Struve C                        22.9N  75.3W   11.0 Crater                 NLF?
            Struve D                        25.3N  73.6W   10.0 Crater                 NLF?
            Struve F                        22.5N  73.6W    9.0 Crater                 NLF?
            Struve G                        23.9N  73.9W   14.0 Crater                 NLF?
            Struve H                        25.2N  83.3W   21.0 Crater                 NLF?
            Struve K                        23.5N  73.0W    6.0 Crater                 NLF?
            Struve L                        20.7N  76.0W   15.0 Crater                 NLF?
            Struve M                        23.3N  75.2W   15.0 Crater                 NLF?
            Subbotin                        29.2S 135.3E   67.0 Crater                 IAU1970
            Subbotin J                      32.0S 138.1E   16.0 Crater                 AW82
            Subbotin Q                      30.8S 134.3E   17.0 Crater                 AW82
            Subbotin R                      31.3S 133.7E   16.0 Crater                 AW82
            Suess                            4.4N  47.6W    8.0 Crater         K1898   K1898
            Suess B                          5.7N  47.3W    8.0 Crater                 NLF?
            Suess D                          4.7N  46.5W    7.0 Crater                 NLF?
            Suess F                          1.1N  44.6W    7.0 Crater                 NLF?
            Suess G                          3.4N  48.4W    4.0 Crater                 NLF?
            Suess H                          4.0N  45.7W    4.0 Crater                 NLF?
            Suess J                          6.9N  48.5W    3.0 Crater                 NLF?
            Suess K                          6.5N  50.0W    3.0 Crater                 NLF?
            Suess L                          6.1N  50.5W    5.0 Crater                 NLF?
            Sulpicius Gallus                19.6N  11.6E   12.0 Crater                 NLF
            Sulpicius Gallus A              22.1N   8.9E    4.0 Crater                 NLF?
            Sulpicius Gallus B              18.0N  13.0E    7.0 Crater                 NLF?
            Sulpicius Gallus G              19.8N   6.3E    6.0 Crater                 NLF?
            Sulpicius Gallus H              20.6N   5.7E    5.0 Crater                 NLF?
            Sulpicius Gallus M              20.4N   8.7E    5.0 Crater         R1651   NLF?
            Sumner                          37.5N 108.7E   50.0 Crater                 IAU1970
            Sumner G                        37.2N 110.2E   18.0 Crater                 AW82
            Sundman                         10.8N  91.6W   40.0 Crater                 IAU1970
            Sundman J                        8.9N  90.2W   10.0 Crater                 AW82
            Sundman V                       11.9N  93.5W   19.0 Crater                 AW82
            Surveyor                         3.2S  23.4W    0.0 Crater (A)             IAU1973
            Susan                           11.0S   6.3W    1.0 Crater         X       IAU1976
            Sverdrup                        88.5S 152.0W   35.0 Crater         N       IAU2000
            Swann                           52.0N 112.7E   42.0 Crater                 IAU1970
            Swann A                         52.9N 113.3E   15.0 Crater                 AW82
            Swann C                         52.8N 114.4E   19.0 Crater                 AW82
            Swasey                           5.5S  89.7E   23.0 Crater                 IAU1976
            Swift                           19.3N  53.4E   10.0 Crater                 IAU1976
            Sylvester                       82.7N  79.6W   58.0 Crater         RLA1963 IAU1964
            Sylvester N                     82.4N  67.3W   20.0 Crater                 NLF?
            Szilard                         34.0N 105.7E  122.0 Crater                 IAU1970
            Szilard H                       32.5N 108.4E   50.0 Crater                 AW82
            Szilard M                       31.1N 106.6E   23.0 Crater                 AW82
            T. Mayer                        15.6N  29.1W   33.0 Crater         S1791   S1791
            T. Mayer A                      15.3N  28.3W   16.0 Crater         VL1645  NLF?
            T. Mayer B                      15.4N  30.9W   13.0 Crater                 NLF?
            T. Mayer C                      12.2N  26.0W   15.0 Crater                 NLF?
            T. Mayer D                      12.2N  26.8W    8.0 Crater                 NLF?
            T. Mayer E                      16.1N  26.2W    9.0 Crater                 NLF?
            T. Mayer F                      12.9N  28.9W    6.0 Crater                 NLF?
            T. Mayer G                      17.3N  27.1W    7.0 Crater                 NLF?
            T. Mayer H                      11.7N  25.5W    3.0 Crater                 NLF?
            T. Mayer K                      18.1N  27.6W    5.0 Crater                 NLF?
            T. Mayer L                      13.2N  24.7W    4.0 Crater                 NLF?
            T. Mayer M                      14.9N  25.6W    5.0 Crater                 NLF?
            T. Mayer N                      13.5N  25.6W    5.0 Crater                 NLF?
            T. Mayer P                      14.0N  29.5W   35.0 Crater                 NLF?
            T. Mayer R                      11.6N  26.4W    5.0 Crater                 NLF?
            T. Mayer S                      11.7N  28.3W    3.0 Crater                 NLF?
            T. Mayer W                      17.5N  34.9W   34.0 Crater                 NLF?
            T. Mayer Z                      14.2N  26.1W    4.0 Crater                 NLF?
            Tacchini                         4.9N  85.8E   40.0 Crater                 IAU1973
            Tacitus                         16.2S  19.0E   39.0 Crater         VL1645  R1651
            Tacitus A                       17.4S  20.5E   11.0 Crater                 NLF?
            Tacitus B                       14.0S  20.4E   13.0 Crater                 NLF?
            Tacitus C                       13.6S  19.8E    9.0 Crater                 NLF?
            Tacitus D                       13.5S  21.0E   15.0 Crater                 NLF?
            Tacitus E                       13.9S  20.1E    9.0 Crater                 NLF?
            Tacitus F                       17.1S  17.6E   10.0 Crater                 NLF?
            Tacitus G                       17.4S  18.2E    6.0 Crater                 NLF?
            Tacitus H                       17.8S  18.5E    7.0 Crater                 NLF?
            Tacitus J                       14.9S  19.7E    3.0 Crater                 NLF?
            Tacitus K                       13.1S  20.1E    3.0 Crater                 NLF?
            Tacitus L                       14.4S  20.9E    6.0 Crater                 NLF?
            Tacitus M                       13.9S  21.5E    6.0 Crater                 NLF?
            Tacitus N                       16.9S  19.4E    7.0 Crater                 NLF?
            Tacitus O                       14.0S  21.9E    5.0 Crater                 NLF?
            Tacitus Q                       18.0S  20.5E    5.0 Crater                 NLF?
            Tacitus R                       16.7S  19.7E    5.0 Crater                 NLF?
            Tacitus S                       14.5S  19.1E   10.0 Crater                 NLF?
            Tacitus X                       15.8S  18.2E    4.0 Crater                 NLF?
            Tacquet                         16.6N  19.2E    7.0 Crater         S1791   S1791
            Tacquet B                       15.8N  20.0E   14.0 Crater                 NLF?
            Tacquet C                       13.5N  21.1E    6.0 Crater                 NLF?
            Taizo                           24.7N   2.2E    6.0 Crater         X       IAU1979
            Talbot                           2.5S  85.3E   11.0 Crater                 IAU1976
            Tamm                             4.4S 146.4E   38.0 Crater                 IAU1979
            Tamm X                           2.7S 145.5E   13.0 Crater                 AW82
            Tannerus                        56.4S  22.0E   28.0 Crater                 NLF
            Tannerus A                      57.5S  18.2E    5.0 Crater                 NLF?
            Tannerus B                      57.5S  19.7E   14.0 Crater                 NLF?
            Tannerus C                      55.3S  22.7E   16.0 Crater                 NLF?
            Tannerus D                      55.8S  18.0E   32.0 Crater                 NLF?
            Tannerus E                      56.1S  19.6E   26.0 Crater                 NLF?
            Tannerus F                      55.0S  22.1E   36.0 Crater                 NLF?
            Tannerus G                      55.1S  16.2E   22.0 Crater                 NLF?
            Tannerus H                      54.2S  22.7E   20.0 Crater                 NLF?
            Tannerus J                      57.2S  24.6E   12.0 Crater                 NLF?
            Tannerus K                      55.5S  20.7E    8.0 Crater                 NLF?
            Tannerus L                      57.5S  22.3E    7.0 Crater                 NLF?
            Tannerus M                      54.9S  20.9E    6.0 Crater                 NLF?
            Tannerus N                      55.9S  24.1E   10.0 Crater                 NLF?
            Tannerus P                      55.6S  21.9E   20.0 Crater                 NLF?
            Taruntius                        5.6N  46.5E   56.0 Crater         R1651   R1651
            Taruntius B                      3.3N  46.6E    7.0 Crater                 NLF?
            Taruntius F                      4.0N  40.5E   11.0 Crater                 NLF?
            Taruntius H                      0.3N  49.9E    8.0 Crater                 NLF?
            Taruntius K                      0.6N  51.6E    5.0 Crater                 NLF?
            Taruntius L                      5.5N  44.4E   14.0 Crater                 NLF?
            Taruntius O                      2.2N  54.3E    7.0 Crater                 NLF?
            Taruntius P                      0.1N  51.6E    7.0 Crater                 NLF?
            Taruntius R                      6.1N  47.9E    5.0 Crater                 NLF?
            Taruntius S                      4.9N  42.4E    5.0 Crater                 NLF?
            Taruntius T                      3.4N  47.5E   10.0 Crater                 NLF?
            Taruntius U                      5.6N  50.1E   12.0 Crater                 NLF?
            Taruntius V                      4.5N  49.8E   21.0 Crater                 NLF?
            Taruntius W                      5.5N  48.9E   15.0 Crater                 NLF?
            Taruntius X                      7.7N  53.0E   23.0 Crater                 NLF?
            Taruntius Z                      7.6N  44.9E   17.0 Crater                 NLF?
            Taylor                           5.3S  16.7E   42.0 Crater         VL1645  M1834
            Taylor A                         4.2S  15.4E   38.0 Crater         R1651   NLF?
            Taylor AB                        3.1S  14.6E   23.0 Crater                 NLF?
            Taylor B                         4.3S  14.3E   29.0 Crater                 NLF?
            Taylor C                         5.6S  14.8E    5.0 Crater                 NLF?
            Taylor D                         5.3S  15.7E    8.0 Crater                 NLF?
            Taylor E                         6.0S  17.1E   14.0 Crater                 NLF?
            Tebbutt                          9.6N  53.6E   31.0 Crater                 IAU1973
            Teisserenc                      32.2N 135.9W   62.0 Crater                 IAU1970
            Teisserenc C                    33.1N 134.7W   47.0 Crater                 AW82
            Teisserenc P                    30.1N 137.1W   25.0 Crater                 AW82
            Teisserenc Q                    31.1N 137.3W   30.0 Crater                 AW82
            Tempel                           3.9N  11.9E   45.0 Crater         K1898   K1898
            ten Bruggencate                  9.5S 134.4E   59.0 Crater                 IAU1970
            ten Bruggencate C                7.9S 136.1E   19.0 Crater                 AW82
            ten Bruggencate D                8.1S 136.9E   43.0 Crater                 AW82
            ten Bruggencate H               10.0S 135.6E   33.0 Crater                 AW82
            ten Bruggencate Y                6.7S 134.0E   57.0 Crater                 AW82
            Tereshkova                      28.4N 144.3E   31.0 Crater                 IAU1970
            Tereshkova U                    28.7N 142.8E   23.0 Crater                 AW82
            Tesla                           38.5N 124.7E   43.0 Crater                 IAU1970
            Tesla J                         37.2N 126.7E   18.0 Crater                 AW82
            Thales                          61.8N  50.3E   31.0 Crater                 NLF
            Thales A                        58.5N  40.8E   12.0 Crater                 NLF?
            Thales E                        57.2N  43.2E   29.0 Crater                 NLF?
            Thales F                        59.4N  42.1E   37.0 Crater                 NLF?
            Thales G                        61.6N  45.3E   12.0 Crater                 NLF?
            Thales H                        60.3N  48.0E   10.0 Crater                 NLF?
            Thales W                        58.5N  39.8E    6.0 Crater                 NLF?
            Theaetetus                      37.0N   6.0E   24.0 Crater         VL1645  NLF
            Thebit                          22.0S   4.0W   56.0 Crater         VL1645  R1651
            Thebit A                        21.5S   4.9W   20.0 Crater                 NLF?
            Thebit B                        22.3S   6.2W    4.0 Crater                 NLF?
            Thebit C                        21.2S   4.1W    6.0 Crater                 NLF?
            Thebit D                        19.8S   8.3W    5.0 Crater                 NLF?
            Thebit E                        23.1S   4.6W    7.0 Crater                 NLF?
            Thebit F                        23.0S   5.3W    4.0 Crater                 NLF?
            Thebit J                        22.5S   5.5W   10.0 Crater                 NLF?
            Thebit K                        23.1S   3.7W    5.0 Crater                 NLF?
            Thebit L                        21.5S   5.4W   12.0 Crater                 NLF?
            Thebit P                        24.0S   5.7W   78.0 Crater                 NLF?
            Thebit Q                        20.1S   4.2W   16.0 Crater                 NLF?
            Thebit R                        20.2S   4.8W    9.0 Crater                 NLF?
            Thebit S                        24.8S   7.2W   16.0 Crater                 NLF?
            Thebit T                        20.7S   6.0W    3.0 Crater                 NLF?
            Thebit U                        20.3S   5.8W    4.0 Crater                 NLF?
            Theiler                         13.4N  83.3E    7.0 Crater                 IAU1979
            Theon Junior                     2.3S  15.8E   17.0 Crater                 NLF
            Theon Junior B                   2.1S  13.3E    8.0 Crater                 NLF?
            Theon Junior C                   2.3S  14.7E    4.0 Crater                 NLF?
            Theon Senior                     0.8S  15.4E   18.0 Crater                 NLF
            Theon Senior A                   0.2S  15.4E    5.0 Crater                 NLF?
            Theon Senior B                   0.2N  14.1E    6.0 Crater                 NLF?
            Theon Senior C                   1.4S  14.5E    6.0 Crater                 NLF?
            Theophilus                      11.4S  26.4E  110.0 Crater         VL1645  R1651
            Theophilus B                    10.5S  25.2E    8.0 Crater                 NLF?
            Theophilus E                     6.8S  24.0E   21.0 Crater                 NLF?
            Theophilus F                     8.0S  26.0E   13.0 Crater                 NLF?
            Theophilus G                     7.2S  25.7E   19.0 Crater                 NLF?
            Theophilus K                    12.5S  26.3E    6.0 Crater                 NLF?
            Theophilus W                     7.8S  28.6E    4.0 Crater                 NLF?
            Theophrastus                    17.5N  39.0E    9.0 Crater                 IAU1973
            Thiel                           40.7N 134.5W   32.0 Crater                 IAU1970
            Thiel T                         40.4N 136.6W   31.0 Crater                 AW82
            Thiessen                        75.4N 169.0W   66.0 Crater                 IAU1970
            Thiessen Q                      73.9N 174.6W   39.0 Crater                 AW82
            Thiessen W                      76.3N 173.2W   24.0 Crater                 AW82
            Thomson                         32.7S 166.2E  117.0 Crater                 IAU1970
            Thomson J                       35.9S 169.6E   44.0 Crater                 AW82
            Thomson M                       35.7S 166.0E  119.0 Crater                 AW82
            Thomson V                       30.7S 162.2E   13.0 Crater                 AW82
            Thomson W                       30.2S 163.3E   17.0 Crater                 AW82
            Tikhomirov                      25.2N 162.0E   65.0 Crater                 IAU1970
            Tikhomirov J                    20.9N 165.7E   29.0 Crater                 AW82
            Tikhomirov K                    21.3N 163.9E   23.0 Crater                 AW82
            Tikhomirov N                    21.1N 161.4E   18.0 Crater                 AW82
            Tikhomirov R                    24.1N 160.3E   21.0 Crater                 AW82
            Tikhomirov T                    25.4N 158.8E   26.0 Crater                 AW82
            Tikhomirov X                    27.3N 160.6E   24.0 Crater                 AW82
            Tikhomirov Y                    28.3N 160.3E   20.0 Crater                 AW82
            Tikhov                          62.3N 171.7E   83.0 Crater                 IAU1970
            Tiling                          53.1S 132.6W   38.0 Crater                 IAU1970
            Tiling C                        50.4S 129.7W   21.0 Crater                 AW82
            Tiling D                        52.0S 131.2W   34.0 Crater                 AW82
            Tiling F                        52.3S 129.0W   17.0 Crater                 AW82
            Tiling G                        53.0S 128.6W   14.0 Crater                 AW82
            Timaeus                         62.8N   0.5W   32.0 Crater         VL1645  NLF
            Timiryazev                       5.5S 147.0W   53.0 Crater                 IAU1970
            Timiryazev B                     2.3S 145.7W   23.0 Crater                 AW82
            Timiryazev L                     8.2S 146.4W   18.0 Crater                 AW82
            Timiryazev P                     7.9S 148.0W   21.0 Crater                 AW82
            Timiryazev S                     6.0S 149.4W   53.0 Crater                 AW82
            Timiryazev W                     3.0S 150.0W   32.0 Crater                 AW82
            Timocharis                      26.7N  13.1W   33.0 Crater         VL1645  R1651
            Timocharis B                    27.9N  12.1W    5.0 Crater                 NLF?
            Timocharis C                    24.8N  14.2W    4.0 Crater                 NLF?
            Timocharis D                    23.8N  15.1W    3.0 Crater                 NLF?
            Timocharis E                    24.6N  17.1W    4.0 Crater                 NLF?
            Timocharis H                    23.6N  16.6W    2.0 Crater                 NLF?
            Tiselius                         7.0N 176.5E   53.0 Crater                 IAU1979
            Tiselius E                       7.3N 177.7E   17.0 Crater                 AW82
            Tiselius L                       4.6N 177.4E   12.0 Crater                 AW82
            Tisserand                       21.4N  48.2E   36.0 Crater         K1898   K1898
            Tisserand A                     20.4N  49.4E   24.0 Crater                 NLF?
            Tisserand B                     20.7N  51.3E    8.0 Crater                 NLF?
            Tisserand D                     21.7N  49.4E    7.0 Crater                 NLF?
            Tisserand K                     19.8N  50.4E   11.0 Crater                 NLF?
            Titius                          26.8S 100.7E   73.0 Crater                 IAU1970
            Titius J                        27.6S 101.6E   24.0 Crater                 AW82
            Titius N                        28.1S 100.0E   20.0 Crater                 AW82
            Titius Q                        28.0S  98.6E   46.0 Crater                 AW82
            Titius R                        27.1S  99.9E   14.0 Crater                 AW82
            Titov                           28.6N 150.5E   31.0 Crater                 IAU1970
            Titov E                         29.1N 153.9E   22.0 Crater                 AW82
            Tolansky                         9.5S  16.0W   13.0 Crater                 IAU1976
            Torricelli                       4.6S  28.5E   22.0 Crater         VL1645  M1834
            Torricelli A                     4.5S  29.8E   11.0 Crater                 NLF?
            Torricelli B                     2.6S  29.1E    7.0 Crater                 NLF?
            Torricelli C                     2.7S  26.0E   11.0 Crater                 NLF?
            Torricelli F                     4.2S  29.4E    7.0 Crater                 NLF?
            Torricelli G                     1.4S  27.0E    4.0 Crater                 NLF?
            Torricelli H                     3.3S  25.3E    7.0 Crater                 NLF?
            Torricelli J                     3.6S  25.1E    5.0 Crater                 NLF?
            Torricelli K                     4.0S  25.2E    6.0 Crater                 NLF?
            Torricelli L                     3.5S  24.3E    4.0 Crater                 NLF?
            Torricelli M                     3.6S  31.2E   14.0 Crater                 NLF?
            Torricelli N                     6.1S  29.2E    4.0 Crater                 NLF?
            Torricelli P                     6.5S  29.9E    4.0 Crater                 NLF?
            Torricelli R                     5.2S  28.1E   87.0 Crater                 NLF?
            Torricelli T                     4.2S  27.5E    3.0 Crater                 NLF?
            Toscanelli                      27.4N  47.5W    7.0 Crater                 IAU1976
            Townley                          3.4N  63.3E   18.0 Crater                 IAU1976
            Tralles                         28.4N  52.8E   43.0 Crater         M1834   M1834
            Tralles A                       27.5N  47.0E   18.0 Crater                 NLF?
            Tralles B                       27.3N  50.6E   11.0 Crater                 NLF?
            Tralles C                       27.8N  49.4E    7.0 Crater                 NLF?
            Trap                             9.1S  15.4E    1.0 Crater (A)             IAU1973
            Trident                         20.2N  30.8E    0.0 Crater (A)             IAU1973
            Triesnecker                      4.2N   3.6E   26.0 Crater         VL1645  L1824
            Triesnecker D                    3.5N   6.0E    6.0 Crater                 NLF?
            Triesnecker E                    5.6N   2.5E    5.0 Crater                 NLF?
            Triesnecker F                    4.1N   4.8E    4.0 Crater                 NLF?
            Triesnecker G                    3.7N   5.2E    3.0 Crater                 NLF?
            Triesnecker H                    3.3N   2.8E    3.0 Crater                 NLF?
            Triesnecker J                    3.3N   2.5E    3.0 Crater                 NLF?
            Triplet                          3.7S  17.5W    0.0 Crater (A)             IAU1973
            Trouvelot                       49.3N   5.8E    9.0 Crater         F1936   F1936
            Trouvelot G                     47.5N   0.4E    5.0 Crater                 NLF?
            Trouvelot H                     49.8N   4.5E    5.0 Crater                 NLF?
            Trumpler                        29.3N 167.1E   77.0 Crater                 IAU1970
            Trumpler V                      29.8N 164.0E   36.0 Crater                 AW82
            Tsander                          6.2N 149.3W  181.0 Crater                 IAU1970
            Tsander B                        9.6N 147.0W   55.0 Crater                 AW82
            Tsander R                        3.4N 152.2W   36.0 Crater                 AW82
            Tsander S                        5.7N 149.4W   20.0 Crater                 AW82
            Tsander V                        7.9N 153.5W   37.0 Crater                 AW82
            Tseraskiy                       49.0S 141.6E   56.0 Crater                 IAU1970
            Tseraskiy K                     53.0S 144.6E   45.0 Crater                 AW82
            Tseraskiy P                     51.3S 139.6E   33.0 Crater                 AW82
            Tsinger                         56.7N 175.6E   44.0 Crater                 IAU1970
            Tsinger W                       58.1N 173.8E   53.0 Crater                 AW82
            Tsinger Y                       58.1N 175.1E   31.0 Crater                 AW82
            Tsiolkovskiy                    21.2S 128.9E  185.0 Crater         BML1960 IAU1961
            Tsiolkovskiy W                  16.0S 126.9E   13.0 Crater                 AW82
            Tsiolkovskiy X                  14.7S 126.5E   12.0 Crater                 AW82
            Tsu Chung-Chi                   17.3N 145.1E   28.0 Crater         BML1960 IAU1961
            Tsu Chung-Chi W                 18.5N 143.3E   24.0 Crater                 AW82
            Tucker                           5.6S  88.2E    7.0 Crater                 IAU1979
            Turner                           1.4S  13.2W   11.0 Crater         L1935   L1935
            Turner A                         1.1S  14.7W    6.0 Crater                 NLF?
            Turner B                         0.9S  10.6W    5.0 Crater                 NLF?
            Turner C                         2.4S  12.3W    5.0 Crater                 NLF?
            Turner F                         1.6S  14.1W    7.0 Crater                 NLF?
            Turner H                         2.8S  13.0W    4.0 Crater                 NLF?
            Turner K                         3.8S  13.4W    4.0 Crater                 NLF?
            Turner L                         3.4S  12.5W    5.0 Crater                 NLF?
            Turner M                         4.2S  11.8W    4.0 Crater                 NLF?
            Turner N                         2.9S  12.0W    4.0 Crater                 NLF?
            Turner Q                         1.0S  12.4W    3.0 Crater                 NLF?
            Tycho                           43.4S  11.1W  102.0 Crater         VL1645  R1651
            Tycho A                         39.9S  12.0W   31.0 Crater                 NLF?
            Tycho B                         43.9S  13.9W   13.0 Crater                 NLF?
            Tycho C                         44.3S  13.7W    7.0 Crater                 NLF?
            Tycho D                         45.6S  14.0W   27.0 Crater                 NLF?
            Tycho E                         42.2S  13.5W   14.0 Crater                 NLF?
            Tycho F                         40.9S  13.1W   16.0 Crater                 NLF?
            Tycho H                         45.2S  15.8W    8.0 Crater                 NLF?
            Tycho J                         42.5S  15.3W   11.0 Crater                 NLF?
            Tycho K                         45.1S  14.3W    6.0 Crater                 NLF?
            Tycho P                         45.3S  13.0W    8.0 Crater                 NLF?
            Tycho Q                         42.5S  15.9W   21.0 Crater                 NLF?
            Tycho R                         41.8S  13.6W    5.0 Crater                 NLF?
            Tycho S                         43.4S  16.1W    3.0 Crater                 NLF?
            Tycho T                         41.2S  12.5W   14.0 Crater                 NLF?
            Tycho U                         41.0S  13.8W   19.0 Crater                 NLF?
            Tycho V                         41.7S  15.3W    4.0 Crater                 NLF?
            Tycho W                         43.2S  15.3W   19.0 Crater                 NLF?
            Tycho X                         43.8S  15.2W   13.0 Crater                 NLF?
            Tycho Y                         44.1S  15.8W   19.0 Crater                 NLF?
            Tycho Z                         43.1S  16.2W   24.0 Crater                 NLF?
            Tyndall                         34.9S 117.0E   18.0 Crater                 IAU1970
            Tyndall S                       35.1S 115.7E   18.0 Crater                 AW82
            Ukert                            7.8N   1.4E   23.0 Crater         VL1645  M1834
            Ukert A                          8.7N   1.3E    9.0 Crater                 NLF?
            Ukert B                          8.3N   1.3E   21.0 Crater                 NLF?
            Ukert E                          9.0N   0.4E    5.0 Crater                 NLF?
            Ukert J                         11.1N   0.6W    3.0 Crater                 NLF?
            Ukert K                          6.5N   3.7E    4.0 Crater                 NLF?
            Ukert M                          7.9N   2.3E   26.0 Crater                 NLF?
            Ukert N                          7.6N   2.0E   17.0 Crater                 NLF?
            Ukert P                          7.8N   2.9E    5.0 Crater                 NLF?
            Ukert R                          8.2N   0.7E   18.0 Crater                 NLF?
            Ukert V                          8.7N   3.2E    3.0 Crater                 NLF?
            Ukert W                          9.5N   2.3E    3.0 Crater                 NLF?
            Ukert X                          9.2N   1.9E    3.0 Crater                 NLF?
            Ukert Y                         10.1N   0.2E    4.0 Crater                 NLF?
            Ulugh Beigh                     32.7N  81.9W   54.0 Crater         M1834   M1834
            Ulugh Beigh A                   34.1N  79.3W   41.0 Crater                 NLF?
            Ulugh Beigh B                   32.8N  79.3W    8.0 Crater                 NLF?
            Ulugh Beigh C                   31.4N  79.1W   31.0 Crater                 NLF?
            Ulugh Beigh D                   31.6N  82.4W   21.0 Crater                 NLF?
            Ulugh Beigh M                   35.7N  83.4W    7.0 Crater                 NLF?
            Urey                            27.9N  87.4E   38.0 Crater         N       IAU1985
            V|:ais|:al|:a                   25.9N  47.8W    8.0 Crater                 IAU1973
            Valier                           6.8N 174.5E   67.0 Crater                 IAU1970
            Valier J                         6.3N 174.9E   26.0 Crater                 AW82
            Valier P                         4.8N 173.3E    8.0 Crater                 AW82
            van Albada                       9.4N  64.3E   21.0 Crater                 IAU1976
            Van Biesbroeck                  28.7N  45.6W    9.0 Crater                 IAU1976
            Van de Graaff                   27.4S 172.2E  233.0 Crater                 IAU1970
            Van de Graaff C                 26.6S 172.8E   20.0 Crater                 AW82
            Van de Graaff F                 26.8S 174.6E   20.0 Crater                 AW82
            Van de Graaff J                 28.5S 174.1E   25.0 Crater                 AW82
            Van de Graaff M                 30.6S 171.5E   19.0 Crater                 AW82
            Van de Graaff Q                 27.6S 171.3E   15.0 Crater                 AW82
            van den Bergh                   31.3N 159.1W   42.0 Crater                 IAU1970
            van den Bergh F                 31.0N 155.0W   29.0 Crater                 AW82
            van den Bergh M                 30.7N 159.2W   15.0 Crater                 AW82
            van den Bergh P                 29.5N 160.1W   15.0 Crater                 AW82
            van den Bergh Y                 33.1N 159.7W   43.0 Crater                 AW82
            van den Bos                      5.3S 146.0E   22.0 Crater                 IAU1979
            van der Waals                   43.9S 119.9E  104.0 Crater                 IAU1970
            van der Waals B                 41.0S 121.0E   17.0 Crater                 AW82
            van der Waals C                 40.5S 123.6E   24.0 Crater                 AW82
            van der Waals H                 44.3S 121.7E   31.0 Crater                 AW82
            van der Waals K                 45.8S 122.0E   55.0 Crater                 AW82
            van der Waals W                 41.3S 117.1E   46.0 Crater                 AW82
            van Gent                        15.4N 160.4E   43.0 Crater                 IAU1970
            van Gent D                      16.3N 161.7E   35.0 Crater                 AW82
            van Gent N                      13.5N 160.0E   32.0 Crater                 AW82
            van Gent P                      12.6N 159.4E   47.0 Crater                 AW82
            van Gent T                      15.5N 157.2E   16.0 Crater                 AW82
            van Gent U                      17.0N 157.1E   20.0 Crater                 AW82
            van Gent X                      16.4N 159.7E   38.0 Crater                 AW82
            van Maanen                      35.7N 128.0E   60.0 Crater                 IAU1970
            van Maanen K                    33.2N 129.1E   23.0 Crater                 AW82
            van Rhijn                       52.6N 146.4E   46.0 Crater                 IAU1970
            van Rhijn T                     52.2N 140.0E   35.0 Crater                 AW82
            Van Serg                        20.2N  30.8E    0.0 Crater (A)             IAU1973
            Van Vleck                        1.9S  78.3E   31.0 Crater                 IAU1976
            van Wijk                        62.8S 118.8E   32.0 Crater                 IAU1970
            van't Hoff                      62.1N 131.8W   92.0 Crater                 IAU1970
            van't Hoff F                    61.5N 126.2W   41.0 Crater                 AW82
            van't Hoff M                    56.8N 132.1W   36.0 Crater                 AW82
            van't Hoff N                    57.9N 132.3W   46.0 Crater                 AW82
            Vasco da Gama                   13.6N  83.9W   83.0 Crater         M1834   M1834
            Vasco da Gama A                 12.7N  80.0W   23.0 Crater                 NLF?
            Vasco da Gama B                 15.7N  83.0W   27.0 Crater                 NLF?
            Vasco da Gama C                 11.4N  84.9W   44.0 Crater                 NLF?
            Vasco da Gama F                 14.0N  80.6W   53.0 Crater                 NLF?
            Vasco da Gama P                 12.0N  80.4W   91.0 Crater                 NLF?
            Vasco da Gama R                 10.0N  83.4W   59.0 Crater                 NLF?
            Vasco da Gama S                 12.6N  82.8W   28.0 Crater                 NLF?
            Vasco da Gama T                 11.8N  83.3W   20.0 Crater                 NLF?
            Vashakidze                      43.6N  93.3E   44.0 Crater                 IAU1970
            Vavilov                          0.8S 137.9W   98.0 Crater                 IAU1970
            Vavilov D                        0.1S 137.1W  102.0 Crater                 AW82
            Vavilov K                        5.2S 135.5W   30.0 Crater                 AW82
            Vavilov P                        3.4S 139.6W   23.0 Crater                 AW82
            Vega                            45.4S  63.4E   75.0 Crater         M1834   M1834
            Vega A                          47.2S  65.3E   12.0 Crater                 NLF?
            Vega B                          46.2S  63.5E   30.0 Crater                 NLF?
            Vega C                          45.2S  64.8E   21.0 Crater                 NLF?
            Vega D                          44.7S  64.3E   25.0 Crater                 NLF?
            Vega G                          44.4S  62.4E   11.0 Crater                 NLF?
            Vega H                          44.5S  60.1E    6.0 Crater                 NLF?
            Vega J                          45.6S  59.9E   19.0 Crater                 NLF?
            Vendelinus                      16.4S  61.6E  131.0 Crater         VL1645  R1651
            Vendelinus D                    19.0S  58.2E   10.0 Crater                 NLF?
            Vendelinus E                    17.9S  61.0E   21.0 Crater                 NLF?
            Vendelinus F                    18.5S  65.0E   32.0 Crater                 NLF?
            Vendelinus H                    15.3S  61.4E    7.0 Crater                 NLF?
            Vendelinus K                    13.8S  62.5E    9.0 Crater                 NLF?
            Vendelinus L                    17.6S  61.7E   17.0 Crater                 NLF?
            Vendelinus N                    16.8S  65.9E   18.0 Crater                 NLF?
            Vendelinus P                    17.6S  66.3E   16.0 Crater                 NLF?
            Vendelinus S                    15.4S  57.9E    5.0 Crater                 NLF?
            Vendelinus T                    13.5S  62.8E    5.0 Crater                 NLF?
            Vendelinus U                    16.0S  58.7E    5.0 Crater                 NLF?
            Vendelinus V                    15.5S  55.8E    5.0 Crater                 NLF?
            Vendelinus W                    14.6S  58.7E    5.0 Crater                 NLF?
            Vendelinus Y                    17.5S  62.2E   10.0 Crater                 NLF?
            Vendelinus Z                    17.2S  62.3E    7.0 Crater                 NLF?
            Vening Meinesz                   0.3S 162.6E   87.0 Crater                 IAU1970
            Vening Meinesz C                 1.2N 163.8E   46.0 Crater                 AW82
            Vening Meinesz Q                 2.6S 161.0E   17.0 Crater                 AW82
            Vening Meinesz T                 0.4S 159.3E   15.0 Crater                 AW82
            Vening Meinesz W                 1.5N 161.0E   39.0 Crater                 AW82
            Vening Meinesz Z                 0.8N 162.5E   25.0 Crater                 AW82
            Ventris                          4.9S 158.0E   95.0 Crater                 IAU1970
            Ventris A                        4.4S 158.2E   26.0 Crater                 AW82
            Ventris B                        2.4S 158.2E   18.0 Crater                 AW82
            Ventris C                        3.2S 158.9E   48.0 Crater                 AW82
            Ventris D                        3.4S 160.3E   21.0 Crater                 AW82
            Ventris M                        6.0S 157.9E   18.0 Crater                 AW82
            Ventris N                        7.1S 157.6E   63.0 Crater                 AW82
            Ventris R                        6.3S 155.1E   13.0 Crater                 AW82
            Vera                            26.3N  43.7W    2.0 Crater         X       IAU1976
            Vernadskiy                      23.2N 130.5E   91.0 Crater                 IAU1970
            Vernadskiy U                    23.7N 126.5E   37.0 Crater                 AW82
            Vernadskiy X                    25.9N 129.0E   64.0 Crater                 AW82
            Verne                           24.9N  25.3W    2.0 Crater         X       IAU1976
            Vertregt                        19.8S 171.1E  187.0 Crater                 IAU1979
            Vertregt J                      21.5S 174.3E   17.0 Crater                 AW82
            Vertregt K                      20.1S 172.0E   27.0 Crater                 AW82
            Vertregt L                      21.1S 171.5E   38.0 Crater                 AW82
            Vertregt P                      23.6S 170.0E   24.0 Crater                 AW82
            Vertregt R                      21.8S 167.1E   25.0 Crater                 AW82
            Very                            25.6N  25.3E    5.0 Crater                 IAU1973
            Vesalius                         3.1S 114.5E   61.0 Crater                 IAU1970
            Vesalius C                       0.8S 116.7E   22.0 Crater                 AW82
            Vesalius D                       2.2S 116.9E   50.0 Crater                 AW82
            Vesalius G                       3.7S 117.3E   14.0 Crater                 AW82
            Vesalius H                       3.9S 119.0E   36.0 Crater                 AW82
            Vesalius J                       4.8S 119.1E   25.0 Crater                 AW82
            Vesalius M                       5.7S 114.5E   31.0 Crater                 AW82
            Vestine                         33.9N  93.9E   96.0 Crater                 IAU1970
            Vestine A                       36.2N  94.8E   17.0 Crater                 AW82
            Vestine T                       33.9N  91.1E   49.0 Crater                 AW82
            Vetchinkin                      10.2N 131.3E   98.0 Crater                 IAU1970
            Vetchinkin F                    10.0N 134.0E   30.0 Crater                 AW82
            Vetchinkin K                     9.6N 132.3E   22.0 Crater                 AW82
            Vetchinkin P                     7.7N 130.3E   17.0 Crater                 AW82
            Vetchinkin Q                     9.6N 130.7E   23.0 Crater                 AW82
            Victory                         20.2N  30.7E    1.0 Crater (A)             IAU1973
            Vieta                           29.2S  56.3W   87.0 Crater                 NLF
            Vieta A                         30.3S  59.3W   34.0 Crater                 NLF?
            Vieta B                         30.5S  60.2W   40.0 Crater                 NLF?
            Vieta C                         28.7S  58.4W   12.0 Crater                 NLF?
            Vieta D                         27.8S  54.2W    8.0 Crater                 NLF?
            Vieta E                         27.0S  58.1W   11.0 Crater                 NLF?
            Vieta F                         26.8S  57.7W    6.0 Crater                 NLF?
            Vieta G                         29.4S  57.0W    6.0 Crater                 NLF?
            Vieta H                         29.1S  56.2W    5.0 Crater                 NLF?
            Vieta J                         28.9S  55.9W    6.0 Crater                 NLF?
            Vieta K                         28.0S  55.0W    5.0 Crater                 NLF?
            Vieta L                         29.5S  60.2W    8.0 Crater                 NLF?
            Vieta M                         29.8S  60.6W    5.0 Crater                 NLF?
            Vieta P                         27.5S  57.9W    8.0 Crater                 NLF?
            Vieta R                         26.6S  57.5W    3.0 Crater                 NLF?
            Vieta T                         32.4S  57.8W   28.0 Crater                 NLF?
            Vieta Y                         30.5S  55.8W   11.0 Crater                 NLF?
            Vil'ev                           6.1S 144.4E   45.0 Crater                 IAU1970
            Vil'ev B                         4.5S 144.7E   13.0 Crater                 AW82
            Vil'ev J                         6.6S 145.3E   17.0 Crater                 AW82
            Vil'ev V                         5.3S 142.9E   44.0 Crater                 AW82
            Virchow                          9.8N  83.7E   16.0 Crater                 IAU1979
            Virtanen                        15.5N 176.7E   44.0 Crater                 IAU1979
            Virtanen B                      17.8N 177.8E   24.0 Crater                 AW82
            Virtanen C                      17.3N 178.2E   20.0 Crater                 AW82
            Virtanen J                      14.0N 177.9E   21.0 Crater                 AW82
            Virtanen Z                      16.5N 176.6E   31.0 Crater                 AW82
            Vitello                         30.4S  37.5W   42.0 Crater         VL1645  S1791
            Vitello A                       34.1S  41.9W   21.0 Crater                 NLF?
            Vitello B                       31.1S  35.4W   11.0 Crater                 NLF?
            Vitello C                       32.4S  42.5W   14.0 Crater                 NLF?
            Vitello D                       33.2S  41.0W   18.0 Crater                 NLF?
            Vitello E                       29.2S  35.8W    7.0 Crater                 NLF?
            Vitello G                       32.3S  37.6W   10.0 Crater                 NLF?
            Vitello H                       32.8S  43.0W   12.0 Crater                 NLF?
            Vitello K                       31.9S  37.6W   13.0 Crater                 NLF?
            Vitello L                       31.6S  35.3W    7.0 Crater                 NLF?
            Vitello M                       32.4S  36.0W    7.0 Crater                 NLF?
            Vitello N                       32.1S  36.1W    5.0 Crater                 NLF?
            Vitello P                       31.2S  38.4W    9.0 Crater                 NLF?
            Vitello R                       33.0S  37.0W    3.0 Crater                 NLF?
            Vitello S                       30.8S  35.2W    6.0 Crater                 NLF?
            Vitello T                       33.8S  39.6W    9.0 Crater                 NLF?
            Vitello X                       32.2S  40.6W    8.0 Crater                 NLF?
            Vitruvius                       17.6N  31.3E   29.0 Crater         VL1645  NLF
            Vitruvius B                     16.4N  33.0E   18.0 Crater                 NLF?
            Vitruvius G                     13.9N  34.6E    6.0 Crater                 NLF?
            Vitruvius H                     16.4N  33.9E   22.0 Crater                 NLF?
            Vitruvius L                     19.0N  30.7E    6.0 Crater                 NLF?
            Vitruvius M                     16.1N  31.5E    5.0 Crater                 NLF?
            Vitruvius T                     17.1N  33.2E   15.0 Crater                 NLF?
            Viviani                          5.2N 117.1E   26.0 Crater                 IAU1976
            Viviani N                        3.5N 116.5E   16.0 Crater                 AW82
            Viviani P                        4.1N 116.5E   15.0 Crater                 AW82
            Vlacq                           53.3S  38.8E   89.0 Crater         VL1645  M1834
            Vlacq A                         51.2S  38.9E   17.0 Crater                 NLF?
            Vlacq B                         51.0S  39.7E   18.0 Crater                 NLF?
            Vlacq C                         50.3S  39.4E   19.0 Crater                 NLF?
            Vlacq D                         48.7S  36.2E   34.0 Crater                 NLF?
            Vlacq E                         52.0S  36.2E   11.0 Crater                 NLF?
            Vlacq G                         54.9S  38.1E   27.0 Crater                 NLF?
            Vlacq H                         47.9S  34.9E   11.0 Crater                 NLF?
            Vlacq K                         51.2S  36.6E   12.0 Crater                 NLF?
            Vogel                           15.1S   5.9E   26.0 Crater         K1898   K1898
            Vogel A                         14.1S   5.6E    9.0 Crater                 NLF?
            Vogel B                         14.4S   5.7E   22.0 Crater                 NLF?
            Vogel C                         14.1S   5.3E   10.0 Crater                 NLF?
            Volkov                          13.6S 131.7E   40.0 Crater                 IAU1973
            Volkov F                        13.5S 134.0E   10.0 Crater                 AW82
            Volkov J                        14.4S 132.4E   32.0 Crater                 AW82
            Volta                           53.9N  84.4W  123.0 Crater         RLA1963 IAU1964
            Volta B                         54.6N  83.5W    9.0 Crater                 RLA1963?
            Volta D                         52.5N  83.3W   20.0 Crater                 RLA1963?
            Volterra                        56.8N 132.2E   52.0 Crater                 IAU1970
            Volterra R                      56.2N 129.6E   31.0 Crater                 AW82
            von Behring                      7.8S  71.8E   38.0 Crater                 IAU1979
            von B|%ek|%esy                  51.9N 126.8E   96.0 Crater                 IAU1979
            von B|%ek|%esy F                52.9N 137.3E   18.0 Crater                 AW82
            von B|%ek|%esy T                52.2N 121.9E   29.0 Crater                 AW82
            von Braun                       41.1N  78.0W   60.0 Crater         N       IAU1994
            von der Pahlen                  24.8S 132.7W   56.0 Crater                 IAU1970
            von der Pahlen E                24.5S 128.8W   32.0 Crater                 AW82
            von der Pahlen H                27.1S 127.5W   35.0 Crater                 AW82
            von der Pahlen V                23.8S 135.6W   19.0 Crater                 AW82
            Von K|%arm|%an                  44.8S 175.9E  180.0 Crater                 IAU1970
            Von K|%arm|%an L                47.7S 177.9E   29.0 Crater                 AW82
            Von K|%arm|%an M                47.2S 176.2E  225.0 Crater                 AW82
            Von K|%arm|%an R                45.8S 170.8E   28.0 Crater                 AW82
            von Neumann                     40.4N 153.2E   78.0 Crater                 IAU1970
            von Zeipel                      42.6N 141.6W   83.0 Crater                 IAU1970
            von Zeipel J                    40.8N 139.3W   39.0 Crater                 AW82
            Voskresenskiy                   28.0N  88.1W   49.0 Crater                 IAU1970
            Voskresenskiy K                 28.8N  84.1W   34.0 Crater                 AW82
            W. Bond                         65.4N   4.5E  156.0 Crater                 NLF
            W. Bond B                       64.9N   7.6E   15.0 Crater                 NLF?
            W. Bond C                       65.6N   8.2E    7.0 Crater                 NLF?
            W. Bond D                       63.5N   3.2E    7.0 Crater                 NLF?
            W. Bond E                       63.8N   9.1E   25.0 Crater                 NLF?
            W. Bond F                       64.5N   9.6E    9.0 Crater                 NLF?
            W. Bond G                       63.0N   7.0E    4.0 Crater                 NLF?
            Walker                          26.0S 162.2W   32.0 Crater                 IAU1970
            Walker A                        24.9S 162.0W   20.0 Crater                 AW82
            Walker G                        26.9S 158.8W   20.0 Crater                 AW82
            Walker N                        29.0S 162.6W   17.0 Crater                 AW82
            Walker R                        26.5S 163.8W   17.0 Crater                 AW82
            Walker W                        24.6S 164.3W   44.0 Crater                 AW82
            Walker Z                        22.4S 161.9W   16.0 Crater                 AW82
            Wallace                         20.3N   8.7W   26.0 Crater         S1878   S1878
            Wallace A                       19.2N   5.6W    4.0 Crater                 NLF?
            Wallace C                       17.6N   6.4W    5.0 Crater                 NLF?
            Wallace D                       17.9N   5.7W    4.0 Crater                 NLF?
            Wallace H                       21.3N   9.1W    2.0 Crater                 NLF?
            Wallace K                       19.3N   6.8W    3.0 Crater                 NLF?
            Wallace T                       21.9N   5.1W    2.0 Crater                 NLF?
            Wallach                          4.9N  32.3E    6.0 Crater                 IAU1979
            Walter                          28.0N  33.8W    1.0 Crater         X       IAU1979
            Walther                         33.1S   1.0E  128.0 Crater         VL1645  R1651
            Walther A                       32.4S   0.7E   12.0 Crater                 NLF?
            Walther B                       30.5S   1.4W    9.0 Crater                 NLF?
            Walther C                       31.2S   0.8W   14.0 Crater                 NLF?
            Walther D                       32.0S   2.8E   18.0 Crater                 NLF?
            Walther E                       33.3S   1.2W   13.0 Crater                 NLF?
            Walther F                       33.1S   2.1E    6.0 Crater                 NLF?
            Walther G                       32.5S   3.9W    8.0 Crater                 NLF?
            Walther J                       34.4S   1.5W    7.0 Crater                 NLF?
            Walther K                       34.1S   1.4W    7.0 Crater                 NLF?
            Walther L                       31.9S   0.9W    5.0 Crater                 NLF?
            Walther M                       34.0S   0.3W    5.0 Crater                 NLF?
            Walther N                       33.7S   0.2W    6.0 Crater                 NLF?
            Walther O                       35.6S   0.1W    6.0 Crater                 NLF?
            Walther P                       35.4S   0.2E    9.0 Crater                 NLF?
            Walther Q                       33.5S   0.3E    4.0 Crater                 NLF?
            Walther R                       35.8S   0.4E    8.0 Crater                 NLF?
            Walther S                       36.4S   0.6E   12.0 Crater                 NLF?
            Walther T                       33.4S   1.8E    8.0 Crater                 NLF?
            Walther U                       33.4S   2.7E    4.0 Crater                 NLF?
            Walther W                       32.8S   2.5W   36.0 Crater                 NLF?
            Walther X                       32.1S   1.9W   10.0 Crater                 NLF?
            Wan-Hoo                          9.8S 138.8W   52.0 Crater                 IAU1970
            Wan-Hoo T                       10.0S 140.4W   21.0 Crater                 AW82
            Wargentin                       49.6S  60.2W   84.0 Crater         S1791   S1791
            Wargentin A                     47.1S  59.1W   21.0 Crater                 NLF?
            Wargentin B                     51.4S  67.6W   18.0 Crater                 NLF?
            Wargentin C                     47.4S  61.2W   12.0 Crater                 NLF?
            Wargentin D                     51.0S  65.1W   16.0 Crater                 NLF?
            Wargentin E                     50.9S  66.9W   16.0 Crater                 NLF?
            Wargentin F                     51.5S  66.1W   20.0 Crater                 NLF?
            Wargentin H                     47.4S  60.1W    9.0 Crater                 NLF?
            Wargentin K                     48.3S  57.8W    7.0 Crater                 NLF?
            Wargentin L                     48.1S  58.2W   11.0 Crater                 NLF?
            Wargentin M                     48.1S  58.9W    7.0 Crater                 NLF?
            Wargentin P                     48.7S  56.6W    9.0 Crater                 NLF?
            Warner                           4.0S  87.3E   35.0 Crater                 IAU1976
            Waterman                        25.9S 128.0E   76.0 Crater                 IAU1970
            Watson                          62.6S 124.5W   62.0 Crater                 IAU1970
            Watson G                        63.3S 120.3W   34.0 Crater                 AW82
            Watt                            49.5S  48.6E   66.0 Crater         VL1645  S1878
            Watt A                          50.3S  46.4E   10.0 Crater                 NLF?
            Watt B                          50.1S  48.0E    6.0 Crater                 NLF?
            Watt C                          50.0S  51.5E   24.0 Crater                 NLF?
            Watt D                          50.3S  55.2E   32.0 Crater                 NLF?
            Watt E                          49.7S  55.3E   10.0 Crater                 NLF?
            Watt F                          50.5S  54.3E   16.0 Crater                 NLF?
            Watt G                          50.9S  58.7E   13.0 Crater                 NLF?
            Watt H                          51.2S  57.2E   16.0 Crater                 NLF?
            Watt J                          51.6S  58.3E   18.0 Crater                 NLF?
            Watt K                          51.4S  55.9E    8.0 Crater                 NLF?
            Watt L                          52.6S  57.6E   32.0 Crater                 NLF?
            Watt M                          53.1S  59.9E   42.0 Crater                 NLF?
            Watt N                          53.6S  58.7E   11.0 Crater                 NLF?
            Watt R                          51.0S  47.5E   12.0 Crater                 NLF?
            Watt S                          52.2S  47.8E    6.0 Crater                 NLF?
            Watt T                          51.6S  51.0E    4.0 Crater                 NLF?
            Watt U                          52.0S  51.7E    5.0 Crater                 NLF?
            Watt W                          51.1S  51.9E    7.0 Crater                 NLF?
            Watts                            8.9N  46.3E   15.0 Crater                 IAU1973
            Webb                             0.9S  60.0E   21.0 Crater         N1876   N1876
            Webb B                           0.8S  58.4E    6.0 Crater                 NLF?
            Webb C                           0.3N  63.8E   34.0 Crater                 NLF?
            Webb D                           2.3S  57.6E    7.0 Crater                 NLF?
            Webb E                           1.0N  61.1E    7.0 Crater                 NLF?
            Webb F                           1.5N  61.0E    9.0 Crater                 NLF?
            Webb G                           1.7N  61.2E    9.0 Crater                 NLF?
            Webb H                           2.1S  59.5E   10.0 Crater                 NLF?
            Webb J                           0.6S  64.0E   24.0 Crater                 NLF?
            Webb K                           0.7S  62.9E   21.0 Crater                 NLF?
            Webb L                           0.1N  62.7E    7.0 Crater                 NLF?
            Webb M                           0.2S  63.8E    5.0 Crater                 NLF?
            Webb N                           0.3S  63.6E    4.0 Crater                 NLF?
            Webb P                           2.3N  57.8E   36.0 Crater                 NLF?
            Webb Q                           1.0S  61.2E    5.0 Crater                 NLF?
            Webb U                           1.8N  56.3E    6.0 Crater                 NLF?
            Webb W                           3.0N  58.2E    8.0 Crater                 NLF?
            Webb X                           3.2N  58.3E    8.0 Crater                 NLF?
            Weber                           50.4N 123.4W   42.0 Crater                 IAU1970
            Weber N                         49.2N 123.6W   21.0 Crater                 AW82
            Wegener                         45.2N 113.3W   88.0 Crater                 IAU1970
            Wegener K                       43.3N 111.9W   32.0 Crater                 AW82
            Wegener W                       47.5N 116.1W   53.0 Crater                 AW82
            Weierstrass                      1.3S  77.2E   33.0 Crater                 IAU1976
            Weigel                          58.2S  38.8W   35.0 Crater         S1791   S1791
            Weigel A                        58.6S  37.8W   17.0 Crater                 NLF?
            Weigel B                        58.8S  41.1W   37.0 Crater                 NLF?
            Weigel C                        59.5S  41.9W   10.0 Crater                 NLF?
            Weigel D                        58.0S  41.6W   16.0 Crater                 NLF?
            Weigel E                        56.9S  42.3W   11.0 Crater                 NLF?
            Weigel F                        57.5S  40.9W    7.0 Crater                 NLF?
            Weigel G                        57.7S  35.3W    7.0 Crater                 NLF?
            Weigel H                        58.2S  40.6W   15.0 Crater                 NLF?
            Weinek                          27.5S  37.0E   32.0 Crater         F1936   F1936
            Weinek A                        26.9S  35.5E   10.0 Crater                 NLF?
            Weinek B                        26.9S  38.2E   11.0 Crater                 NLF?
            Weinek D                        26.0S  36.6E    9.0 Crater                 NLF?
            Weinek E                        25.3S  37.5E    9.0 Crater                 NLF?
            Weinek F                        25.1S  38.2E    4.0 Crater                 NLF?
            Weinek G                        26.9S  39.0E   15.0 Crater                 NLF?
            Weinek H                        28.6S  38.5E    6.0 Crater                 NLF?
            Weinek K                        28.9S  38.4E   17.0 Crater                 NLF?
            Weinek L                        26.1S  39.7E    9.0 Crater                 NLF?
            Weinek M                        25.8S  40.0E    6.0 Crater                 NLF?
            Weird                            3.7S  17.5W    0.0 Crater (A)             IAU1973
            Weiss                           31.8S  19.5W   66.0 Crater         K1898   K1898
            Weiss A                         30.5S  18.6W    4.0 Crater                 NLF?
            Weiss B                         31.2S  18.4W   10.0 Crater                 NLF?
            Weiss D                         30.7S  20.3W    9.0 Crater                 NLF?
            Weiss E                         31.1S  19.2W   17.0 Crater                 NLF?
            Werner                          28.0S   3.3E   70.0 Crater         VL1645  R1651
            Werner A                        27.2S   1.1E   15.0 Crater                 NLF?
            Werner B                        26.2S   0.7E   13.0 Crater                 NLF?
            Werner D                        27.1S   3.2E    2.0 Crater                 NLF?
            Werner E                        27.4S   0.8E    7.0 Crater                 NLF?
            Werner F                        25.8S   0.8E   10.0 Crater                 NLF?
            Werner G                        27.6S   1.3E    9.0 Crater                 NLF?
            Werner H                        26.6S   1.5E   16.0 Crater                 NLF?
            West                             0.8N  23.5E    0.0 Crater (A)             IAU1973
            Wexler                          69.1S  90.2E   51.0 Crater                 IAU1970
            Wexler E                        68.8S  95.5E   23.0 Crater                 AW82
            Wexler H                        70.5S  96.7E   14.0 Crater                 AW82
            Wexler U                        68.2S  82.0E   51.0 Crater                 AW82
            Wexler V                        68.0S  83.9E   21.0 Crater                 AW82
            Weyl                            17.5N 120.2W  108.0 Crater                 IAU1970
            Whewell                          4.2N  13.7E   13.0 Crater                 NLF
            Whewell A                        4.7N  14.1E    4.0 Crater                 NLF?
            Whewell B                        5.0N  14.5E    3.0 Crater                 NLF?
            White                           44.6S 158.3W   39.0 Crater                 IAU1970
            White W                         42.1S 162.7W   24.0 Crater                 AW82
            Wichmann                         7.5S  38.1W   10.0 Crater         N1876   N1876
            Wichmann A                       7.4S  36.9W    4.0 Crater                 NLF?
            Wichmann B                       7.1S  39.1W    4.0 Crater                 NLF?
            Wichmann C                       4.7S  37.4W    3.0 Crater                 NLF?
            Wichmann D                       5.4S  36.0W    3.0 Crater                 NLF?
            Wichmann R                       6.6S  39.0W   62.0 Crater                 NLF?
            Widmannst|:atten                 6.1S  85.5E   46.0 Crater                 IAU1973
            Wiechert                        84.5S 165.0E   41.0 Crater                 IAU1970
            Wiechert A                      82.5S 167.1E   26.0 Crater                 AW82
            Wiechert E                      83.8S 175.8E   18.0 Crater                 AW82
            Wiechert J                      85.6S 177.0W   34.0 Crater                 AW82
            Wiechert P                      85.5S 150.5E   37.0 Crater                 AW82
            Wiechert U                      83.8S 147.5E   30.0 Crater                 AW82
            Wiener                          40.8N 146.6E  120.0 Crater                 IAU1970
            Wiener F                        41.2N 150.0E   47.0 Crater                 AW82
            Wiener H                        39.8N 149.9E   17.0 Crater                 AW82
            Wiener K                        39.3N 147.8E  101.0 Crater                 AW82
            Wiener Q                        39.5N 145.0E   30.0 Crater                 AW82
            Wildt                            9.0N  75.8E   11.0 Crater                 IAU1979
            Wilhelm                         43.4S  20.4W  106.0 Crater         VL1645  NLF
            Wilhelm A                       44.6S  22.0W   20.0 Crater                 NLF?
            Wilhelm B                       43.5S  22.8W   19.0 Crater                 NLF?
            Wilhelm C                       41.6S  19.5W   15.0 Crater                 NLF?
            Wilhelm D                       41.8S  17.7W   32.0 Crater                 NLF?
            Wilhelm E                       44.1S  17.9W   14.0 Crater                 NLF?
            Wilhelm F                       42.4S  23.1W    9.0 Crater                 NLF?
            Wilhelm G                       42.5S  25.9W   17.0 Crater                 NLF?
            Wilhelm H                       42.5S  23.8W    7.0 Crater                 NLF?
            Wilhelm J                       41.5S  26.2W   19.0 Crater                 NLF?
            Wilhelm K                       44.1S  21.7W   21.0 Crater                 NLF?
            Wilhelm L                       40.4S  22.1W    9.0 Crater                 NLF?
            Wilhelm M                       44.0S  17.3W    9.0 Crater                 NLF?
            Wilhelm N                       43.7S  18.5W    7.0 Crater                 NLF?
            Wilhelm O                       43.1S  17.2W   17.0 Crater                 NLF?
            Wilhelm P                       40.9S  20.5W   12.0 Crater                 NLF?
            Wilhelm Q                       43.2S  18.4W    8.0 Crater                 NLF?
            Wilhelm R                       41.3S  21.9W    7.0 Crater                 NLF?
            Wilhelm S                       41.7S  21.7W   10.0 Crater                 NLF?
            Wilhelm T                       41.3S  20.9W    8.0 Crater                 NLF?
            Wilhelm U                       41.4S  20.4W    5.0 Crater                 NLF?
            Wilhelm V                       43.9S  19.5W    8.0 Crater                 NLF?
            Wilhelm W                       42.5S  20.3W    5.0 Crater                 NLF?
            Wilhelm X                       40.9S  19.9W   12.0 Crater                 NLF?
            Wilhelm Y                       44.5S  20.9W    5.0 Crater                 NLF?
            Wilhelm Z                       44.8S  20.3W    8.0 Crater                 NLF?
            Wilkins                         29.4S  19.6E   57.0 Crater         M1935   M1935
            Wilkins A                       29.1S  18.9E   13.0 Crater                 NLF?
            Wilkins B                       29.5S  18.9E    8.0 Crater                 NLF?
            Wilkins C                       30.8S  20.1E   20.0 Crater                 NLF?
            Wilkins D                       28.0S  17.7E   34.0 Crater                 NLF?
            Wilkins E                       28.3S  19.5E    9.0 Crater                 NLF?
            Wilkins F                       30.3S  20.4E    7.0 Crater                 NLF?
            Wilkins G                       30.0S  18.4E    6.0 Crater                 NLF?
            Wilkins H                       28.6S  18.5E    6.0 Crater                 NLF?
            Williams                        42.0N  37.2E   36.0 Crater         K1898   K1898
            Williams F                      43.5N  38.2E    7.0 Crater                 NLF?
            Williams M                      41.2N  38.8E    5.0 Crater                 NLF?
            Williams N                      42.1N  36.3E    5.0 Crater                 NLF?
            Williams R                      42.5N  38.3E    4.0 Crater                 NLF?
            Wilsing                         21.5S 155.2W   73.0 Crater                 IAU1970
            Wilsing C                       19.0S 153.0W   33.0 Crater                 AW82
            Wilsing D                       20.0S 152.6W   15.0 Crater                 AW82
            Wilsing R                       22.5S 157.5W   24.0 Crater                 AW82
            Wilsing T                       21.3S 159.9W   19.0 Crater                 AW82
            Wilsing U                       20.6S 158.9W   26.0 Crater                 AW82
            Wilsing V                       20.5S 158.2W   51.0 Crater                 AW82
            Wilsing W                       18.5S 159.8W   36.0 Crater                 AW82
            Wilsing X                       17.4S 157.4W   23.0 Crater                 AW82
            Wilsing Z                       20.9S 155.2W   30.0 Crater                 AW82
            Wilson                          69.2S  42.4W   69.0 Crater         S1791   S1791
            Wilson A                        71.3S  53.5W   15.0 Crater                 NLF?
            Wilson C                        71.9S  45.1W   26.0 Crater                 NLF?
            Wilson E                        72.5S  55.0W   24.0 Crater                 NLF?
            Wilson F                        70.4S  39.3W   13.0 Crater                 NLF?
            Winkler                         42.2N 179.0W   22.0 Crater                 IAU1970
            Winkler A                       43.8N 178.4W   14.0 Crater                 AW82
            Winkler E                       42.7N 177.1W   18.0 Crater                 AW82
            Winkler L                       40.0N 178.4W   31.0 Crater                 AW82
            Winlock                         35.6N 105.6W   64.0 Crater                 IAU1970
            Winlock M                       32.3N 106.0W   68.0 Crater                 AW82
            Winlock W                       37.2N 107.4W   21.0 Crater                 AW82
            Winthrop                        10.7S  44.4W   17.0 Crater                 IAU1976
            W|:ohler                        38.2S  31.4E   27.0 Crater         S1878   S1878
            W|:ohler A                      37.7S  30.3E    8.0 Crater                 NLF?
            W|:ohler B                      37.2S  30.8E   11.0 Crater                 NLF?
            W|:ohler C                      36.7S  30.6E   12.0 Crater                 NLF?
            W|:ohler D                      36.2S  31.2E    7.0 Crater                 NLF?
            W|:ohler E                      38.9S  30.2E    7.0 Crater                 NLF?
            W|:ohler F                      40.1S  33.8E    8.0 Crater                 NLF?
            W|:ohler G                      40.1S  35.6E    7.0 Crater                 NLF?
            Wolf                            22.7S  16.6W   25.0 Crater         VL1645  K1898
            Wolf A                          22.2S  18.4W    6.0 Crater                 NLF?
            Wolf B                          23.1S  16.4W   17.0 Crater                 NLF?
            Wolf C                          24.1S  14.5W    3.0 Crater                 NLF?
            Wolf E                          23.9S  16.3W    2.0 Crater                 NLF?
            Wolf F                          22.0S  14.9W    3.0 Crater                 NLF?
            Wolf G                          22.5S  16.8W    7.0 Crater                 NLF?
            Wolf H                          23.0S  14.7W    8.0 Crater                 NLF?
            Wolf S                          21.2S  16.5W   35.0 Crater                 NLF?
            Wolf T                          23.4S  18.8W   27.0 Crater                 NLF?
            Wolff A                         15.8N   7.7W    7.0 Crater                 NLF?
            Wolff B                         16.0N   8.7W    8.0 Crater                 NLF?
            Wollaston                       30.6N  46.9W   10.0 Crater         M1834   M1834
            Wollaston D                     33.1N  48.7W    5.0 Crater                 NLF?
            Wollaston N                     28.3N  48.1W    6.0 Crater                 NLF?
            Wollaston P                     29.3N  49.9W    5.0 Crater                 NLF?
            Wollaston R                     29.5N  50.8W    6.0 Crater                 NLF?
            Wollaston U                     31.0N  52.9W    3.0 Crater                 NLF?
            Wollaston V                     30.9N  54.0W    4.0 Crater                 NLF?
            Woltjer                         45.2N 159.6W   46.0 Crater                 IAU1970
            Woltjer P                       43.4N 161.5W   33.0 Crater                 AW82
            Woltjer T                       45.1N 164.7W   15.0 Crater                 AW82
            Wood                            43.0N 120.8W   78.0 Crater                 IAU1970
            Wood S                          43.8N 123.6W   35.0 Crater                 AW82
            Wreck                            9.1S  15.5E    1.0 Crater (A)             IAU1973
            Wright                          31.6S  86.6W   39.0 Crater         RLA1963 IAU1964
            Wright A                        32.8S  87.2W   11.0 Crater                 RLA1963?
            Wr|%oblewski                    24.0S 152.8E   21.0 Crater                 IAU1976
            Wrottesley                      23.9S  56.8E   57.0 Crater         VL1645  NLF
            Wrottesley A                    23.5S  54.9E   10.0 Crater                 NLF?
            Wrottesley B                    24.8S  56.7E   10.0 Crater                 NLF?
            Wurzelbauer                     33.9S  15.9W   88.0 Crater         S1791   S1791
            Wurzelbauer A                   35.7S  15.4W   17.0 Crater                 NLF?
            Wurzelbauer B                   34.9S  14.5W   25.0 Crater                 NLF?
            Wurzelbauer C                   35.0S  15.1W   10.0 Crater                 NLF?
            Wurzelbauer D                   36.3S  17.6W   38.0 Crater         VL1645  NLF?
            Wurzelbauer E                   35.7S  17.2W   11.0 Crater                 NLF?
            Wurzelbauer F                   35.9S  18.1W    9.0 Crater                 NLF?
            Wurzelbauer G                   34.6S  18.6W   11.0 Crater                 NLF?
            Wurzelbauer H                   35.3S  17.2W    7.0 Crater                 NLF?
            Wurzelbauer L                   34.8S  17.8W    7.0 Crater                 NLF?
            Wurzelbauer M                   32.1S  16.0W    5.0 Crater                 NLF?
            Wurzelbauer N                   32.5S  14.8W   13.0 Crater                 NLF?
            Wurzelbauer O                   35.9S  14.6W    9.0 Crater                 NLF?
            Wurzelbauer P                   35.1S  14.2W    9.0 Crater                 NLF?
            Wurzelbauer S                   37.5S  19.3W   12.0 Crater                 NLF?
            Wurzelbauer W                   32.7S  15.1W    8.0 Crater                 NLF?
            Wurzelbauer X                   33.6S  14.4W    7.0 Crater                 NLF?
            Wurzelbauer Y                   33.2S  17.7W    9.0 Crater                 NLF?
            Wurzelbauer Z                   32.2S  14.9W   12.0 Crater                 NLF?
            Wyld                             1.4S  98.1E   93.0 Crater                 IAU1970
            Wyld C                           0.7N 100.5E   28.0 Crater                 AW82
            Wyld J                           3.8S  99.4E   24.0 Crater                 AW82
            Xenophanes                      57.5N  82.0W  125.0 Crater         R1651   R1651
            Xenophanes A                    60.1N  84.8W   42.0 Crater                 NLF?
            Xenophanes B                    59.4N  80.5W   15.0 Crater                 NLF?
            Xenophanes C                    59.6N  78.7W    8.0 Crater                 NLF?
            Xenophanes D                    58.6N  77.4W   12.0 Crater                 NLF?
            Xenophanes E                    58.1N  85.8W   12.0 Crater                 NLF?
            Xenophanes F                    56.7N  73.2W   24.0 Crater                 NLF?
            Xenophanes G                    56.9N  75.7W    7.0 Crater                 NLF?
            Xenophanes K                    58.7N  84.5W   13.0 Crater                 NLF?
            Xenophanes L                    54.8N  78.6W   21.0 Crater                 NLF?
            Xenophanes M                    54.8N  79.6W    9.0 Crater                 NLF?
            Xenophon                        22.8S 122.1E   25.0 Crater                 IAU1976
            Yablochkov                      60.9N 128.3E   99.0 Crater                 IAU1970
            Yablochkov U                    61.9N 120.8E   30.0 Crater                 AW82
            Yakovkin                        54.5S  78.8W   37.0 Crater         N       IAU1985
            Yamamoto                        58.1N 160.9E   76.0 Crater                 IAU1970
            Yangel'                         17.0N   4.7E    8.0 Crater                 IAU1973
            Yerkes                          14.6N  51.7E   36.0 Crater         K1898   K1898
            Yerkes E                        15.9N  50.6E   10.0 Crater                 NLF?
            Yoshi                           24.6N  11.0E    1.0 Crater         X       IAU1976
            Young                           41.5S  50.9E   71.0 Crater         S1878   S1878
            Young A                         41.1S  51.2E   13.0 Crater                 NLF?
            Young B                         40.9S  50.6E    7.0 Crater                 NLF?
            Young C                         41.5S  48.2E   30.0 Crater                 NLF?
            Young D                         43.5S  51.8E   46.0 Crater                 NLF?
            Young F                         44.8S  51.8E   23.0 Crater                 NLF?
            Young R                         42.4S  55.4E    9.0 Crater                 NLF?
            Young S                         43.3S  53.9E   11.0 Crater                 NLF?
            Zach                            60.9S   5.3E   70.0 Crater         VL1645  M1834
            Zach A                          62.5S   5.1E   36.0 Crater                 NLF?
            Zach B                          58.6S   3.0E   32.0 Crater                 NLF?
            Zach C                          58.5S   1.3E   13.0 Crater                 NLF?
            Zach D                          62.1S   7.9E   32.0 Crater                 NLF?
            Zach E                          59.4S   6.3E   24.0 Crater                 NLF?
            Zach F                          60.0S   3.2E   28.0 Crater                 NLF?
            Zach G                          58.4S   0.5E    6.0 Crater                 NLF?
            Zach H                          59.0S   2.9E    7.0 Crater                 NLF?
            Zach J                          57.4S   4.7E   11.0 Crater                 NLF?
            Zach K                          57.4S   6.2E    9.0 Crater                 NLF?
            Zach L                          58.1S   6.9E   16.0 Crater                 NLF?
            Zach M                          57.1S   7.0E    5.0 Crater                 NLF?
            Zagut                           32.0S  22.1E   84.0 Crater         VL1645  R1651
            Zagut A                         32.0S  21.6E   11.0 Crater                 NLF?
            Zagut B                         32.1S  18.7E   32.0 Crater                 NLF?
            Zagut C                         30.8S  18.5E   24.0 Crater                 NLF?
            Zagut D                         31.4S  19.3E   16.0 Crater                 NLF?
            Zagut E                         31.7S  23.1E   35.0 Crater                 NLF?
            Zagut F                         30.2S  17.5E    8.0 Crater                 NLF?
            Zagut H                         29.9S  20.7E    8.0 Crater                 NLF?
            Zagut K                         31.7S  22.2E    7.0 Crater                 NLF?
            Zagut L                         30.3S  22.1E   12.0 Crater                 NLF?
            Zagut M                         30.8S  22.9E    6.0 Crater                 NLF?
            Zagut N                         31.2S  23.5E    9.0 Crater                 NLF?
            Zagut O                         33.0S  16.7E   11.0 Crater                 NLF?
            Zagut P                         32.4S  17.4E   14.0 Crater                 NLF?
            Zagut R                         30.8S  20.7E    4.0 Crater                 NLF?
            Zagut S                         33.3S  22.6E    7.0 Crater                 NLF?
            Z|:ahringer                      5.6N  40.2E   11.0 Crater                 IAU1976
            Zanstra                          2.9N 124.7E   42.0 Crater                 IAU1973
            Zanstra A                        4.5N 125.2E   36.0 Crater                 AW82
            Zanstra K                        2.0N 125.2E   14.0 Crater                 AW82
            Zanstra M                        1.1N 125.0E   23.0 Crater                 AW82
            Zasyadko                         3.9N  94.2E   11.0 Crater                 IAU1976
            Zeeman                          75.2S 133.6W  190.0 Crater                 IAU1970
            Zeeman E                        74.2S 123.9W   29.0 Crater                 AW82
            Zeeman G                        74.3S 107.4W   45.0 Crater                 AW82
            Zeeman U                        73.8S 148.2W   26.0 Crater                 AW82
            Zeeman X                        71.5S 138.1W   26.0 Crater                 AW82
            Zeeman Y                        72.8S 137.6W   33.0 Crater                 AW82
            Zelinskiy                       28.9S 166.8E   53.0 Crater                 IAU1970
            Zelinskiy Y                     28.5S 166.6E   13.0 Crater                 AW82
            Zeno                            45.2N  72.9E   65.0 Crater         S1878   S1878
            Zeno A                          44.5N  70.0E   44.0 Crater                 NLF?
            Zeno B                          44.0N  71.0E   37.0 Crater                 NLF?
            Zeno D                          45.0N  71.2E   29.0 Crater                 NLF?
            Zeno E                          41.7N  70.8E   18.0 Crater                 NLF?
            Zeno F                          42.4N  80.0E   17.0 Crater                 NLF?
            Zeno G                          43.9N  73.1E   11.0 Crater                 NLF?
            Zeno H                          41.4N  74.4E   17.0 Crater                 NLF?
            Zeno J                          44.2N  76.3E   13.0 Crater                 NLF?
            Zeno K                          42.8N  66.6E   18.0 Crater                 NLF?
            Zeno P                          43.4N  66.1E   11.0 Crater                 NLF?
            Zeno U                          42.5N  68.8E   16.0 Crater                 NLF?
            Zeno V                          43.0N  69.3E   22.0 Crater                 NLF?
            Zeno W                          43.3N  67.6E   10.0 Crater                 NLF?
            Zeno X                          43.6N  76.9E   17.0 Crater                 NLF?
            Zernike                         18.4N 168.2E   48.0 Crater                 IAU1970
            Zernike T                       18.5N 166.9E   17.0 Crater                 AW82
            Zernike W                       19.6N 166.8E   27.0 Crater                 AW82
            Zernike Z                       20.9N 168.0E   30.0 Crater                 AW82
            Zhiritskiy                      24.8S 120.3E   35.0 Crater                 IAU1970
            Zhiritskiy F                    24.9S 121.6E   75.0 Crater                 AW82
            Zhiritskiy Z                    23.2S 120.4E   22.0 Crater                 AW82
            Zhukovskiy                       7.8N 167.0W   81.0 Crater                 IAU1970
            Zhukovskiy Q                     6.2N 168.8W   23.0 Crater                 AW82
            Zhukovskiy T                     7.9N 172.3W   23.0 Crater                 AW82
            Zhukovskiy U                     8.5N 173.2W   29.0 Crater                 AW82
            Zhukovskiy W                     9.8N 170.3W   31.0 Crater                 AW82
            Zhukovskiy X                    10.5N 171.1W   30.0 Crater                 AW82
            Zhukovskiy Z                    10.0N 166.8W   34.0 Crater                 AW82
            Zinner                          26.6N  58.8W    4.0 Crater                 IAU1973
            Z|:ollner                        8.0S  18.9E   47.0 Crater         VL1645  S1878
            Z|:ollner A                      7.1S  21.5E    7.0 Crater                 NLF?
            Z|:ollner D                      8.3S  17.7E   24.0 Crater                 NLF?
            Z|:ollner E                      8.9S  18.3E    6.0 Crater                 NLF?
            Z|:ollner F                      7.5S  21.9E   25.0 Crater                 NLF?
            Z|:ollner G                      7.3S  20.8E   10.0 Crater                 NLF?
            Z|:ollner H                      7.1S  19.2E    8.0 Crater                 NLF?
            Z|:ollner J                      6.2S  20.7E   11.0 Crater                 NLF?
            Z|:ollner K                      6.5S  20.8E    7.0 Crater                 NLF?
            Zsigmondy                       59.7N 104.7W   65.0 Crater                 IAU1976
            Zsigmondy A                     62.8N 102.6W   63.0 Crater                 AW82
            Zsigmondy S                     59.7N 106.7W   64.0 Crater                 AW82
            Zsigmondy Z                     62.1N 104.9W   23.0 Crater                 AW82
            Zucchius                        61.4S  50.3W   64.0 Crater         R1651   R1651
            Zucchius A                      61.8S  56.0W   28.0 Crater                 NLF?
            Zucchius B                      61.8S  54.3W   25.0 Crater                 NLF?
            Zucchius C                      60.8S  45.2W   22.0 Crater                 NLF?
            Zucchius D                      61.4S  58.7W   26.0 Crater                 NLF?
            Zucchius E                      61.3S  60.6W   21.0 Crater                 NLF?
            Zucchius F                      60.1S  56.5W    8.0 Crater                 NLF?
            Zucchius G                      60.5S  57.2W   25.0 Crater                 NLF?
            Zucchius H                      61.0S  59.7W   14.0 Crater                 NLF?
            Zucchius K                      64.3S  58.0W   10.0 Crater                 NLF?
            Zupus                           17.2S  52.3W   38.0 Crater                 NLF
            Zupus A                         17.2S  53.5W    6.0 Crater                 NLF?
            Zupus B                         17.6S  54.3W    6.0 Crater                 NLF?
            Zupus C                         17.3S  55.1W   19.0 Crater                 NLF?
            Zupus D                         19.7S  53.4W   17.0 Crater                 NLF?
            Zupus F                         17.3S  54.0W    4.0 Crater                 NLF?
            Zupus K                         15.7S  52.1W   17.0 Crater                 NLF?
            Zupus S                         17.0S  51.3W   24.0 Crater                 NLF?
            Zupus V                         18.2S  56.3W    4.0 Crater                 NLF?
            Zupus X                         18.9S  54.9W    5.0 Crater                 NLF?
            Zupus Y                         17.4S  49.6W    2.0 Crater                 NLF?
            Zupus Z                         18.2S  50.1W    3.0 Crater                 NLF?
            Zwicky                          15.4S 168.1E  150.0 Crater                 IAU1979
            Zwicky N                        16.1S 167.4E   30.0 Crater                 AW82
            Zwicky R                        18.3S 163.4E   28.0 Crater                 AW82
            Zwicky S                        16.3S 162.6E   44.0 Crater

            */
        }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('targets');
    }
}
