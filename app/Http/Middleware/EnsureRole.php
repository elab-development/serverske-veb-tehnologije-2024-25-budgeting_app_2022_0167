<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Usage: ->middleware('role:admin') ili 'role:admin,user'
     */
    public function handle(Request $request, Closure $next, string $roles)
    {
        $user = $request->user();  

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // dozvoljene uloge (podržava zarez ili |)
        $allowed = preg_split('/[|,]/', $roles);
        $allowed = array_map('trim', $allowed);

        if (!in_array($user->role, $allowed, true)) {
            return response()->json(['message' => 'Forbidden: insufficient role'], 403);
        }

        return $next($request);
    }
}
