<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('certificates', 'certificate_number')) {
            Schema::table('certificates', function (Blueprint $table): void {
                $table->string('certificate_number')->nullable()->after('course_id');
            });

            DB::table('certificates')
                ->orderBy('id')
                ->select(['id'])
                ->get()
                ->each(function (object $certificate): void {
                    DB::table('certificates')
                        ->where('id', $certificate->id)
                        ->update(['certificate_number' => 'EDT-' . now()->format('Y') . '-' . Str::upper(Str::random(10))]);
                });

            Schema::table('certificates', function (Blueprint $table): void {
                $table->string('certificate_number')->nullable(false)->change();
                $table->unique('certificate_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('certificates', 'certificate_number')) {
            Schema::table('certificates', function (Blueprint $table): void {
                $table->dropUnique(['certificate_number']);
                $table->dropColumn('certificate_number');
            });
        }
    }
};
