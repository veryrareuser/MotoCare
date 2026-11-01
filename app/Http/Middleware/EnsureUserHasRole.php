<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $allowedRoles = array_filter(array_map(
            fn (string $role): ?UserRole => UserRole::tryFrom($role),
            $roles,
        ));

        if (! in_array($user->role, $allowedRoles, strict: true)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
