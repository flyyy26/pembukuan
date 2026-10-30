<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Admin = bypass semua
        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->can($permission)) {
            return $next($request);
        }

        abort(403, 'Anda tidak punya akses ke halaman ini.');
    }
}