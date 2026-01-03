<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->tenant_id) {
            return response()->json([
                'message' => 'Tenant não encontrado para este usuário.'
            ], 403);
        }

        app()->instance('tenant_id', $user->tenant_id);

        return $next($request);
    }
}
