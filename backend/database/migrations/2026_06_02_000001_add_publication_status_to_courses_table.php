<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->enum('publication_status', ['pending', 'published', 'archived'])
                ->default('pending')
                ->after('is_published')
                ->index();
        });

        DB::table('courses')
            ->where('is_published', true)
            ->update(['publication_status' => 'published']);
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('publication_status');
        });
    }
};
