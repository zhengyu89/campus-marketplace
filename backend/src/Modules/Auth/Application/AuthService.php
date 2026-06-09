<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application;

use App\Core\Application\Exception\ApiException;
use App\Modules\Auth\Infrastructure\UserRepository;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly JwtService $jwtService
    ) {
    }

    public function register(array $payload): array
    {
        $validated = $this->validateRegistration($payload);

        if ($this->userRepository->findByEmail($validated['email']) !== null) {
            throw new ApiException(
                'An account with this email already exists',
                409,
                ['email' => 'This email is already registered.']
            );
        }

        $this->userRepository->create(
            $validated['name'],
            $validated['email'],
            password_hash($validated['password'], PASSWORD_DEFAULT)
        );

        return [
            'message' => 'Account created successfully',
        ];
    }

    public function login(array $payload): array
    {
        $validated = $this->validateLogin($payload);
        $user = $this->userRepository->findByEmail($validated['email']);

        if ($user === null || !password_verify($validated['password'], $user['password_hash'])) {
            throw new ApiException('Invalid email or password', 401);
        }

        $issuedToken = $this->jwtService->issueToken($user);

        return [
            'token' => $issuedToken['token'],
            'expires_at' => $issuedToken['expires_at'],
            'user' => $this->toPublicUser($user),
        ];
    }

    public function toPublicUser(array $user): array
    {
        return [
            'user_id' => (int) $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }

    private function validateRegistration(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $password = (string) ($payload['password'] ?? '');
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Name must not exceed 100 characters.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address.';
        } elseif (mb_strlen($email) > 150) {
            $errors['email'] = 'Email must not exceed 150 characters.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (mb_strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($errors !== []) {
            throw new ApiException('Validation failed', 422, $errors);
        }

        return [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ];
    }

    private function validateLogin(array $payload): array
    {
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $password = (string) ($payload['password'] ?? '');
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($errors !== []) {
            throw new ApiException('Validation failed', 422, $errors);
        }

        return [
            'email' => $email,
            'password' => $password,
        ];
    }
}
