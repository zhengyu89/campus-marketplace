<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application\Middleware;

use App\Core\Application\Exception\ApiException;
use App\Core\Application\Responder\JsonResponder;
use App\Modules\Auth\Application\JwtService;
use App\Modules\Auth\Infrastructure\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class JwtAuthenticationMiddleware
{
    public function __construct(
        private JwtService $jwtService,
        private UserRepository $userRepository
    ) {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorizationHeader = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+(.+)$/i', $authorizationHeader, $matches)) {
            return JsonResponder::error(new Response(), 'Authorization token is required', 401);
        }

        try {
            $tokenPayload = $this->jwtService->decodeToken($matches[1]);
            $userId = (int) ($tokenPayload['sub'] ?? 0);

            if ($userId <= 0) {
                throw new ApiException('Invalid or expired token', 401);
            }

            $user = $this->userRepository->findById($userId);

            if ($user === null) {
                throw new ApiException('Invalid or expired token', 401);
            }

            $request = $request
                ->withAttribute('authTokenPayload', $tokenPayload)
                ->withAttribute('authUser', [
                    'user_id' => (int) $user['user_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ]);

            return $handler->handle($request);
        } catch (ApiException $exception) {
            return JsonResponder::error(
                new Response(),
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getErrors()
            );
        }
    }
}
