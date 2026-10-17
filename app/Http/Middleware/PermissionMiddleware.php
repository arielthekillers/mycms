<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Auth\AuthManager;
use App\Core\Auth\Rbac;
use App\Core\Request;
use App\Core\Response;

/**
 * Usage in routes (assuming a small helper wires these from the container):
 *
 *   $router->post('/admin/agenda', [AgendaController::class, 'store'], [
 *       new AuthMiddleware($auth),
 *       new PermissionMiddleware($rbac, $auth, 'module.agenda.create'),
 *   ]);
 */
final class PermissionMiddleware
{
    public function __construct(
        private Rbac $rbac,
        private AuthManager $auth,
        private string $permissionKey
    ) {
    }

    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $userId = $this->auth->id();

        if ($userId === null || !$this->rbac->can($userId, $this->permissionKey)) {
            if ($request->isJson()) {
                return $response->json(['message' => 'Forbidden.'], 403);
            }

            return $response->status(403)->html('403 Forbidden');
        }

        return $next($request, $response);
    }
}
