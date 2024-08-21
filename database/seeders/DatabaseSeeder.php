<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
use Hash;
use Faker\Factory as Faker;
class DatabaseSeeder extends Seeder
{

    public function run()
    {
        $faker=Faker::create();
        for ($i = 1; $i <= 100; $i++) {
            DB::table('users')->insert([
                'email' => "user$i@gmail.com",
                'user_name' =>  "user$i",
                'email_verified_at' => now(),
                'verification_code' => null,
                'password' => Hash::make('password'),
                'number_of_files' => null,
                'role_name' => 'user',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 1; $i <= 100; $i++) {
            DB::table('groups')->insert([
                'owner_id' => rand(1, 100),
                'name' => "Group{$i}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 1; $i <= 100; $i++) {
            DB::table('user_groups')->insert([
                'user_id' => $i,
                'group_id' => rand(1, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 1; $i <= 1000; $i++) {
            DB::table('public_files')->insert([
                'owner_file_id' => rand(1, 100),
                'user_file_id' => rand(1, 100),
                'name' => $faker->word . ".txt",
                'file' => $faker->word . '.txt',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 1; $i <= 1000; $i++) {
            DB::table('files')->insert([
                'owner_file_id' => rand(1, 100),
                'user_file_id' => rand(1, 100),
                'group_id' => rand(1, 100),
                'name' => $faker->word,
                'file' => $faker->word . '.txt',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
