<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class LogAdminAction
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($request->user()?->isAdmin()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->method(),
                'resource_type' => $request->path(),
                'resource_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
