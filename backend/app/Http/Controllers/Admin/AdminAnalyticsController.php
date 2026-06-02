<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $usersByRole = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $roles = collect(['admin', 'instructor', 'student'])
            ->mapWithKeys(fn (string $role): array => [$role => (int) ($usersByRole[$role] ?? 0)]);

        return response()->json([
            'data' => [
                'users_by_role' => $roles,
                'total_users' => (int) $roles->sum(),
                'total_published_courses' => Course::query()
                    ->where(fn ($query) => $query
                        ->where('publication_status', 'published')
                        ->orWhere('is_published', true))
                    ->count(),
                'total_certificates_issued' => Certificate::query()->count(),
                'total_live_sessions_held' => LiveSession::query()->where('end_at', '<', now())->count(),
            ],
        ]);
    }
}
