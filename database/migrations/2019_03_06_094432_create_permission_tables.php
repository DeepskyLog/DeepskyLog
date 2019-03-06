<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedInteger('permission_id');

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type', ]);

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(['permission_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedInteger('role_id');

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type', ]);

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['role_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        // Insert the possible DeepskyLog permissions
        DB::table('permissions')->insert(
            array(
                'name' => 'Administer roles & permissions',
                'guard_name' => 'web'
            )
        );

        DB::table('permissions')->insert(
            array(
                'name' => 'Observer permissions',
                'guard_name' => 'web'
            )
        );

        DB::table('permissions')->insert(
            array(
                'name' => 'Adapt database permissions',
                'guard_name' => 'web'
            )
        );

        // Insert the possible DeepskyLog roles
        DB::table('roles')->insert(
            array(
                'name' => 'admin',
                'guard_name' => 'web'
            )
        );

        DB::table('roles')->insert(
            array(
                'name' => 'observer',
                'guard_name' => 'web'
            )
        );

        DB::table('roles')->insert(
            array(
                'name' => 'database',
                'guard_name' => 'web'
            )
        );

        // Insert the DeepskyLog relations between roles and permissions
        DB::table('role_has_permissions')->insert(
            array(
                'permission_id' => '1',
                'role_id' => '1'
            )
        );

        DB::table('role_has_permissions')->insert(
            array(
                'permission_id' => '2',
                'role_id' => '2'
            )
        );

        DB::table('role_has_permissions')->insert(
            array(
                'permission_id' => '2',
                'role_id' => '3'
            )
        );

        DB::table('role_has_permissions')->insert(
            array(
                'permission_id' => '3',
                'role_id' => '1'
            )
        );

        DB::table('role_has_permissions')->insert(
            array(
                'permission_id' => '3',
                'role_id' => '3'
            )
        );

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
}
