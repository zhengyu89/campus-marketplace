<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application\Middleware;

use App\Core\Application\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class RequireRoleMiddleware
{
    public function __construct(private readonly array $allowedRoles)
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('authUser');

        if (!is_array($user)) {
            return JsonResponder::error(new Response(), 'Unauthorized', 401);
        }

        if (!in_array($user['role'] ?? null, $this->allowedRoles, true)) {
            return JsonResponder::error(
                new Response(),
                'You do not have permission to access this resource',
                403
            );
        }

        return $handler->handle($request);
    }
}
