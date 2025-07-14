<?php

namespace Esanj\Manager\database\Seeders;

use Esanj\Manager\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::truncate();

        $data = [
            //Manager permission
            [
                'key' => 'managers.list',
                'display_name' => 'Manager List'
            ],
            [
                'key' => 'managers.create',
                'display_name' => 'Manager Create'
            ],
            [
                'key' => 'managers.edit',
                'display_name' => 'Manager Edit'
            ],
            [
                'key' => 'managers.delete',
                'display_name' => 'Manager delete'
            ],
        ];


        DB::table('permissions')->insert($data);
    }
}
