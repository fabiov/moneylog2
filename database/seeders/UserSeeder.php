<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Fabio Ventura',
                'email' => 'foravatenubi@gmail.com',
                'password' => Hash::make('P@ss0rd'),
            ],
            [
                'id' => 21,
                'name' => 'Giovanni Ventura',
                'email' => 'giovannivent@gmail.com',
                'password' => Hash::make('P@ss0rd'),
            ],
        ]);
    }
}
