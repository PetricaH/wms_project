<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Fix: use Auth::check() or just check if user is not null
        if (!Auth::user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = Auth::user();

        if (!$this->hasPermission($user, $permission)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if the user has the given permission.
     *
     * @param \App\Models\User $user
     * @param string $permission
     * @return bool
     */
    private function hasPermission($user, $permission): bool
    {
        // Check if the user has any role that has the given permission
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $perm) {
                if ($perm->slug === $permission) {
                    return true;
                }
            }
        }
        
        return false;
    }
}