<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = User::create([
            'name' => 'alfi',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin12345'),
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
        $admin->setGuardName('web');
        $admin->assignRole('admin');
        $admin->setGuardName('api');
        $admin->assignRole('admin');

        $user = User::create([
            'name' => 'alfi',
            'email' => 'user@gmail.com',
            'password' => Hash::make('user12345'),
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
        $user->setGuardName('web');
        $user->assignRole('user');
        $user->setGuardName('api');
        $user->assignRole('user');
    }
}
