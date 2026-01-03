<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
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
