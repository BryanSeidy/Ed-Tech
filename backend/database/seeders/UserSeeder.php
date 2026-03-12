<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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

        // Create instructors
        User::create([
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@elearning.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Marie Martin',
            'email' => 'marie.martin@elearning.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Pierre Bernard',
            'email' => 'pierre.bernard@elearning.com',
            'password' => Hash::make('password123'),
        ]);

        // Create students
        User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice.johnson@student.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Bob Smith',
            'email' => 'bob.smith@student.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Carol Williams',
            'email' => 'carol.williams@student.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'David Brown',
            'email' => 'david.brown@student.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Emma Davis',
            'email' => 'emma.davis@student.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
