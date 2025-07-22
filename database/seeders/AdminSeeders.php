<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [ 
                'nama_depan' => 'Inosustain',
                'nama_belakang' => 'Admin',
                'email' => 'inosustain@gmail.com',
                'no_hp' => '087724414526',
                'gender' => 'L',
                'role_type' => 2,
                'password' => Hash::make('inosustain-prod2025'),
            ],
        ]);
    }
}
