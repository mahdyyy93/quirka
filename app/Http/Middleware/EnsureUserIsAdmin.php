<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized access.');
        }

        $user = auth()->user();

        if (
            $user->hasRole('admin') ||
            $user->role === 'admin' ||
            $user->can('products.view') ||
            $user->can('orders.view') ||
            $user->can('customers.view') ||
            $user->can('roles.view')
        ) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
