<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The OAuth tokens of connected_accounts are encrypted by the model's casts
 * from now on. An encrypted token is about twice as long as the plain one, so
 * the columns become text, and the tokens already stored are encrypted.
 */
return new class extends Migration
{
    private const COLUMNS = ['token', 'secret', 'refresh_token'];

    public function up(): void
    {
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->text('token')->change();
            $table->text('secret')->nullable()->change();
            $table->text('refresh_token')->nullable()->change();
        });

        $this->convert(function (string $value): string {
            try {
                Crypt::decryptString($value);

                return $value; // already encrypted
            } catch (DecryptException) {
                return Crypt::encryptString($value);
            }
        });
    }

    public function down(): void
    {
        $this->convert(function (string $value): string {
            try {
                return Crypt::decryptString($value);
            } catch (DecryptException) {
                return $value;
            }
        });

        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->string('token', 1000)->change();
            $table->string('secret')->nullable()->change();
            $table->string('refresh_token', 1000)->nullable()->change();
        });
    }

    private function convert(callable $transform): void
    {
        DB::table('connected_accounts')->select(array_merge(['id'], self::COLUMNS))->orderBy('id')
            ->chunkById(500, function ($rows) use ($transform) {
                foreach ($rows as $row) {
                    $changes = [];
                    foreach (self::COLUMNS as $column) {
                        $value = $row->{$column};
                        if (is_string($value) && $value !== '') {
                            $new = $transform($value);
                            if ($new !== $value) {
                                $changes[$column] = $new;
                            }
                        }
                    }
                    if ($changes !== []) {
                        DB::table('connected_accounts')->where('id', $row->id)->update($changes);
                    }
                }
            });
    }
};
