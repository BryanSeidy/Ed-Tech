<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        User::create([
            'name' => 'Admin',
            'email' => 'admin@elearning.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Teacher',
            'email' => 'teacher@elearning.com',
            'password' => Hash::make('password'),
            'role' => 'teacher'
        ]);

        User::create([
            'name' => 'Student One',
            'email' => 'student1@elearning.com',
            'password' => Hash::make('password'),
            'role' => 'student'
        ]);

        User::create([
            'name' => 'Student Two',
            'email' => 'student2@elearning.com',
            'password' => Hash::make('password'),
            'role' => 'student'
        ]);

    }
}