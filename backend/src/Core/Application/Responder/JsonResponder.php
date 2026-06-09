<?php

declare(strict_types=1);

namespace App\Core\Application\Responder;

use Psr\Http\Message\ResponseInterface as Response;

final class JsonResponder
{
    public static function success(Response $response, array $data = [], int $status = 200): Response
    {
        $payload = json_encode([
            'success' => true,
            'data' => $data,
        ]);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public static function error(Response $response, string $message, int $status = 400): Response
    {
        $payload = json_encode([
            'success' => false,
            'message' => $message,
        ]);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
