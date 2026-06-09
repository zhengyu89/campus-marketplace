<?php

declare(strict_types=1);

use App\Core\Infrastructure\Factory\DatabaseFactory;
use App\Core\Application\Responder\JsonResponder;
use App\Modules\Auth\Application\AuthService;
use App\Modules\Auth\Application\JwtService;
use App\Modules\Auth\Application\Middleware\JwtAuthenticationMiddleware;
use App\Modules\Auth\Infrastructure\UserRepository;
use App\Modules\Auth\Presentation\AuthController;
use Slim\App;

return function (App $app): void {
    $resolveAuthDependencies = static function (): array {
        static $dependencies = null;

        if ($dependencies !== null) {
            return $dependencies;
        }

        $pdo = DatabaseFactory::create();
        $userRepository = new UserRepository($pdo);
        $jwtService = new JwtService(
            (string) ($_ENV['JWT_SECRET'] ?? 'change_this_secret_key'),
            (int) ($_ENV['JWT_TTL'] ?? 86400)
        );
        $authService = new AuthService($userRepository, $jwtService);

        $dependencies = [
            'authController' => new AuthController($authService),
            'jwtAuthentication' => new JwtAuthenticationMiddleware($jwtService, $userRepository),
        ];

        return $dependencies;
    };

    $app->get('/api/health', function ($request, $response) {
        return JsonResponder::success($response, [
            'message' => 'Backend API is running',
        ]);
    });

    $app->post('/api/auth/register', function ($request, $response) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['authController']->register($request, $response);
    });

    $app->post('/api/auth/login', function ($request, $response) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['authController']->login($request, $response);
    });

    $app->get('/api/auth/me', function ($request, $response) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['authController']->me($request, $response);
    })->add(function ($request, $handler) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
    });

    $app->get('/api/listings', function ($request, $response) {
        return JsonResponder::success($response, [
            [
                'listing_id' => 1,
                'title' => 'Sample Marketplace Listing',
                'price' => 10.00,
                'status' => 'Available',
            ],
        ]);
    });
};
