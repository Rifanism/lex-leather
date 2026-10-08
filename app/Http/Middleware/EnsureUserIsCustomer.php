<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inverse of EnsureUserIsAdmin: guards the storefront routes.
 *
 * Admins manage the shop from /admin — they do not browse, cart or buy from
 * it, so those routes are a hard 403 rather than a hidden link. Guests still
 * pass, because the catalog is public.
 */
class EnsureUserIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->isAdmin(), 403);

        return $next($request);
    }
}
