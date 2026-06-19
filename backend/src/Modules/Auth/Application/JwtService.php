<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application;

use App\Core\Application\Exception\ApiException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

final class JwtService
{
    public function __construct(
        private string $secret,
        private int $ttl
    ) {
    }

    public function issueToken(array $user): array
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->ttl;
        $payload = [
            'sub' => (int) $user['user_id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        return [
            'token' => JWT::encode($payload, $this->resolveSecret(), 'HS256'),
            'expires_at' => gmdate(DATE_ATOM, $expiresAt),
        ];
    }

    public function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->resolveSecret(), 'HS256'));

            return (array) $decoded;
        } catch (Throwable $exception) {
            throw new ApiException('Invalid or expired token', 401);
        }
    }

    private function resolveSecret(): string
    {
        return $this->secret !== '' ? $this->secret : 'change_this_secret_key';
    }
}
