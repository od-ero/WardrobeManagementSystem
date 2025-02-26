<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'add-user',  'module' => 'Users', 'display_name' => 'Adding New User'],
            ['name' => 'edit-user',  'module' => 'Users', 'display_name' => 'Update User'],
            ['name' => 'view-user',  'module' => 'Users', 'display_name' => 'View User'],
            ['name' => 'Add-Employee',  'module' => 'User', 'display_name' => 'Creating New User'],
            ['name' => 'list-active-user', 'module' => 'Users', 'display_name' => 'List Active User'],
            ['name' => 'list-deleted-user',  'module' => 'Users', 'display_name' => 'List Deleted User'],
            ['name' => 'destroy-user',  'module' => 'Users', 'display_name' => 'Delete User'],
            ['name' => 'activate-user',  'module' => 'Users', 'display_name' => 'Edit Member Details'],
            ['name' => 'Add-Employee',  'module' => 'User', 'display_name' => 'Creating New User'],
            ['name' => 'system-name', 'module' => 'Users', 'display_name' => 'Changing System Name'],
            ['name' => 'activate-member',  'module' => 'Member', 'display_name' => 'Activate Members'],
            ['name' => 'list-active-members',  'module' => 'Member', 'display_name' => 'List active members'],
            ['name' => 'list-deleted-members', 'module' => 'Member', 'display_name' => 'List Deleted Members'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
