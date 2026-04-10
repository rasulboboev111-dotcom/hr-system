<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized access.');
        }

        $roles = $request->user()->role_ids ?? [];
        
        if (!in_array($role, $roles) && !in_array('admin', $roles)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
