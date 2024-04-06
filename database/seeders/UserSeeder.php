<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = collect(
            [
                [
                    'name' => 'Sadia Ali',
                    'email' => 'alisadia229@gmail.com',
                    'password' => Hash::make('Alisadia@990'),


                ],
                [
                    'name' => 'Fatima',
                    'email' => 'fatima344@gmail.com',
                    'password' => Hash::make('Fatima$455'),


                ],
                [
                    'name' => 'Hamid',
                    'email' => 'alihamid377@gmail.com',
                    'password' => Hash::make('Hamid@987'),


                ],
                [
                    'name' => 'admin',
                    'email' => 'admin455@gmail.com',
                    'password' => Hash::make('Admin$433'),
                    'user_type' => 0,
                    'username' => 'admin23'

                ]
            ]
        );
        // DB::table('users')->insert([
        //     'name' => Str::random(10),
        //     'email' => Str::random(10).'@example.com',
        //     'password' => Hash::make('password'),
        // ]);
        $user->each(function ($user) {
            User::insert($user);
        });

        // User::create();
    }
}
