<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      $user = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'phone' => '0700000000',
                'email' => 'admin@gmail.com',
                'first_name' => 'Systerm',
                'last_name' => 'Admin',
                'id_no' => '0000000',
                'description' => 'Seeder admin',
                'special_access' => 1,
                'password' => Hash::make('1212'),
            ]
        );

     
    }

}
