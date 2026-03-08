<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {

        Module::create([
            'course_id' => 1,
            'title' => 'Introduction',
            'order' => 1
        ]);

        Module::create([
            'course_id' => 1,
            'title' => 'Installation Laravel',
            'order' => 2
        ]);

    }
}