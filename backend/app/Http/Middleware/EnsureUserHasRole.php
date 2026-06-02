<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== $role) {
            return response()->json([
                'code' => 'forbidden',
                'message' => 'Accès réservé aux administrateurs de la plateforme.',
            ], 403);
        }

        return $next($request);
    }
}
