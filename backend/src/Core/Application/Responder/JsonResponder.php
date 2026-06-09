<?php

declare(strict_types=1);

namespace App\Core\Application\Responder;

use Psr\Http\Message\ResponseInterface as Response;

final class JsonResponder
{
    public static function success(Response $response, array $data = [], int $status = 200): Response
    {
        return self::respond($response, [
            'success' => true,
            'data' => $data,
        ], $status);
    }

    public static function error(Response $response, string $message, int $status = 400, array $errors = []): Response
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return self::respond($response, $payload, $status);
    }

    private static function respond(Response $response, array $payload, int $status): Response
    {
        $encodedPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if ($encodedPayload === false) {
            $status = 500;
            $encodedPayload = '{"success":false,"message":"Failed to encode JSON response"}';
        }

        $response->getBody()->write($encodedPayload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
