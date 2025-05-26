<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
           [ 
            'nama_depan' => 'admin',
            'nama_belakang' => 'topan',
            'email' => 'aziswihasto@gmail.com',
            'no_hp' => '087724414526',
            'gender' => 'L',
            'role_type' => 2,
            'password' => Hash::make('aziswihasto@gmail.com'),
            ],
            [ 
            'nama_depan' => 'ali',
            'nama_belakang' => 'topan',
            'email' => 'azis.hibatul2004@gmail.com',
            'no_hp' => '087724414526',
            'gender' => 'L',
            'role_type' => 1,
            'password' => Hash::make('azis.hibatul2004@gmail.com'),
            ],
        ]);
    }
}
