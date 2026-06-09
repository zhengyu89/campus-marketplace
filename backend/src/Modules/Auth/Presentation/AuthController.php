<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation;

use App\Core\Application\Exception\ApiException;
use App\Core\Application\Responder\JsonResponder;
use App\Modules\Auth\Application\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class AuthController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $result = $this->authService->register($this->getPayload($request));

            return JsonResponder::success($response, $result, 201);
        } catch (ApiException $exception) {
            return JsonResponder::error(
                $response,
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getErrors()
            );
        } catch (Throwable) {
            return JsonResponder::error($response, 'An unexpected error occurred', 500);
        }
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $result = $this->authService->login($this->getPayload($request));

            return JsonResponder::success($response, $result);
        } catch (ApiException $exception) {
            return JsonResponder::error(
                $response,
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getErrors()
            );
        } catch (Throwable) {
            return JsonResponder::error($response, 'An unexpected error occurred', 500);
        }
    }

    public function me(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('authUser');

        if (!is_array($user)) {
            return JsonResponder::error($response, 'Unauthorized', 401);
        }

        return JsonResponder::success($response, [
            'user' => $user,
        ]);
    }

    private function getPayload(ServerRequestInterface $request): array
    {
        $payload = $request->getParsedBody();

        return is_array($payload) ? $payload : [];
    }
}
