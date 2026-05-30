<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'teacher')->update(['role' => 'instructor']);

        if ($this->usesMySql()) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'instructor', 'student') NOT NULL DEFAULT 'student'");
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'instructor')->update(['role' => 'teacher']);

        if ($this->usesMySql()) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student'");
        }
    }

    private function usesMySql(): bool
    {
        return in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
