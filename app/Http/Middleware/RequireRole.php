<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (! $user || ! $user->role || ! in_array($user->role->slug, $roles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
