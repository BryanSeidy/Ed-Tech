<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', Rule::in(['admin', 'instructor', 'student'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $users = User::query()
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($validated['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->latest()
            ->paginate((int) ($validated['per_page'] ?? 10))
            ->through(fn (User $user): array => $this->serializeUser($user));

        return response()->json($users);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        $currentAdmin = $request->user();
        $nextRole = $request->validated('role');

        if ($currentAdmin && $currentAdmin->is($user) && $nextRole !== 'admin') {
            return response()->json([
                'code' => 'admin_self_demotion_forbidden',
                'message' => 'Un administrateur ne peut pas retirer son propre rôle admin.',
            ], 422);
        }

        $user->update(['role' => $nextRole]);

        return response()->json(['data' => $this->serializeUser($user->refresh())]);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
