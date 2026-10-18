<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Auth\Session;
use App\Core\Request;
use App\Core\Response;

final class CsrfMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true) || $request->uri() === '/api/track') {
            return $next($request, $response);
        }

        $token = $request->header('X-CSRF-TOKEN') ?: $request->input('_token');
        
        if (!$token || !hash_equals(Session::csrfToken(), $token)) {
            // If it's a logout request and CSRF fails (e.g., session expired or double-click),
            // just redirect to login since they are effectively logged out anyway.
            if ($request->uri() === '/admin/logout') {
                return $response->redirect('/admin/login');
            }

            if ($request->isJson()) {
                return $response->json(['message' => 'CSRF token mismatch.'], 403);
            }
            return $response->status(403)->html('CSRF token mismatch.');
        }

        return $next($request, $response);
    }
}
