<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seeder pour un utilisateur avec le rôle 'supervisor'
        User::create([
            'name' => 'Ibrahima Sarr',
            'email' => 'ibou@gmail.com',
            'password' => Hash::make('passer'),
            'role' => 'supervisor',
        ]);

        // Seeder pour un utilisateur avec le rôle 'admin'
        User::create([
            'name' => 'Massamba Diouf',
            'email' => 'mass@gmail.com',
            'password' => Hash::make('passer'),
            'role' => 'admin',
        ]);

        
    }
}
