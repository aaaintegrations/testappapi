<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            "name" => "Admin User",
            "username" => 'adminuser',
            "phone" => "923018364470",
            "email" => "admin@mail.com",
            "password" => Hash::make('password'),
            "avatar" => "default.png",
            "gender" => "male",
            "dob" => date('Y-m-d', strtotime('1993-03-07')),
            "registered_at" => date('Y-m-d', strtotime('2020-03-07')),
            "status" => 1,
            "code" => 234234,
            "role" => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
