<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The standard lens belongs to a telescope (a coma corrector that is always
 * in), not to the observer: instruments.lens_id replaces users.stdlens. The
 * standard lens of an observer moves to their standard telescope, unless
 * that telescope already has one.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->foreignId('lens_id')->nullable()->after('finder_id')
                ->constrained('lenses')->nullOnDelete();
        });

        if (Schema::hasColumn('users', 'stdlens')) {
            DB::table('users')
                ->whereNotNull('stdlens')->where('stdlens', '>', 0)
                ->whereNotNull('stdtelescope')->where('stdtelescope', '>', 0)
                ->orderBy('id')
                ->select(['id', 'stdlens', 'stdtelescope'])
                ->chunk(500, function ($users) {
                    foreach ($users as $user) {
                        $ownLens = DB::table('lenses')->where('id', $user->stdlens)->where('user_id', $user->id)->exists();
                        if ($ownLens) {
                            DB::table('instruments')
                                ->where('id', $user->stdtelescope)->where('user_id', $user->id)->whereNull('lens_id')
                                ->update(['lens_id' => $user->stdlens]);
                        }
                    }
                });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('stdlens');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('stdlens')->nullable();
        });

        DB::table('users')->whereNotNull('stdtelescope')->orderBy('id')->select(['id', 'stdtelescope'])
            ->chunk(500, function ($users) {
                foreach ($users as $user) {
                    $lens = DB::table('instruments')->where('id', $user->stdtelescope)->value('lens_id');
                    if ($lens) {
                        DB::table('users')->where('id', $user->id)->update(['stdlens' => $lens]);
                    }
                }
            });

        Schema::table('instruments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lens_id');
        });
    }
};
