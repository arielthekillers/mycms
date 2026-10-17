<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Auth\AuthManager;
use App\Core\Request;
use App\Core\Response;

final class AuthMiddleware
{
    public function __construct(private AuthManager $auth, private string $redirectTo = '/admin/login')
    {
    }

    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        if (!$this->auth->check()) {
            if ($request->isJson()) {
                return $response->json(['message' => 'Unauthenticated.'], 401);
            }

            return $response->redirect($this->redirectTo);
        }

        return $next($request, $response);
    }
}
