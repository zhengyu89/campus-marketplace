<?php

declare(strict_types=1);

use App\Core\Infrastructure\Factory\DatabaseFactory;
use App\Core\Application\Responder\JsonResponder;
use App\Modules\Auth\Application\AuthService;
use App\Modules\Auth\Application\JwtService;
use App\Modules\Auth\Application\Middleware\JwtAuthenticationMiddleware;
use App\Modules\Auth\Application\Middleware\RequireRoleMiddleware;
use App\Modules\Auth\Infrastructure\UserRepository;
use App\Modules\Auth\Presentation\AuthController;
use App\Modules\Category\Application\CategoryService;
use App\Modules\Category\Infrastructure\CategoryRepository;
use App\Modules\Category\Presentation\CategoryController;
use App\Modules\Listing\Application\ListingService;
use App\Modules\Listing\Infrastructure\ListingImageStorage;
use App\Modules\Listing\Infrastructure\ListingRepository;
use App\Modules\Listing\Presentation\ListingController;
use App\Modules\Offer\Application\OfferService;
use App\Modules\Offer\Infrastructure\OfferRepository;
use App\Modules\Offer\Presentation\OfferController;
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

    $resolveCategoryDependencies = static function (): array {
        static $dependencies = null;

        if ($dependencies !== null) {
            return $dependencies;
        }

        $pdo = DatabaseFactory::create();
        $categoryRepository = new CategoryRepository($pdo);
        $categoryService = new CategoryService($categoryRepository);

        $dependencies = [
            'categoryController' => new CategoryController($categoryService),
        ];

        return $dependencies;
    };

    $resolveListingDependencies = static function (): array {
        static $dependencies = null;

        if ($dependencies !== null) {
            return $dependencies;
        }

        $pdo = DatabaseFactory::create();
        $listingRepository = new ListingRepository($pdo);
        $categoryRepository = new CategoryRepository($pdo);
        $listingService = new ListingService(
            $listingRepository,
            $categoryRepository,
            new ListingImageStorage()
        );

        $dependencies = [
            'listingController' => new ListingController($listingService),
        ];

        return $dependencies;
    };

    $resolveOfferDependencies = static function (): array {
        static $dependencies = null;

        if ($dependencies !== null) {
            return $dependencies;
        }

        $pdo = DatabaseFactory::create();
        $offerRepository = new OfferRepository($pdo);
        $listingRepository = new ListingRepository($pdo);
        $offerService = new OfferService($offerRepository, $listingRepository);

        $dependencies = [
            'offerController' => new OfferController($offerService),
        ];

        return $dependencies;
    };

    $protectRoute = static function ($route) use ($resolveAuthDependencies) {
        return $route->add(function ($request, $handler) use ($resolveAuthDependencies) {
            return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
        });
    };

    $protectAdminRoute = static function ($route) use ($resolveAuthDependencies) {
        return $route
            ->add(new RequireRoleMiddleware(['admin']))
            ->add(function ($request, $handler) use ($resolveAuthDependencies) {
                return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
            });
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

    $app->get('/api/listings', function ($request, $response) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->index($request, $response);
    });

    $app->get('/api/listings/mine', function ($request, $response) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->mine($request, $response);
    })->add(function ($request, $handler) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
    });

    $app->get('/api/listings/{id}', function ($request, $response, $args) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->show($request, $response, $args);
    });

    $app->post('/api/listings', function ($request, $response) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->store($request, $response);
    })->add(function ($request, $handler) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
    });

    $app->put('/api/listings/{id}', function ($request, $response, $args) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->update($request, $response, $args);
    })->add(function ($request, $handler) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
    });

    $app->post('/api/listings/{id}/image', function ($request, $response, $args) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->uploadImage($request, $response, $args);
    })->add(function ($request, $handler) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
    });

    $app->delete('/api/listings/{id}/image', function ($request, $response, $args) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->removeImage($request, $response, $args);
    })->add(function ($request, $handler) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
    });

    $app->delete('/api/listings/{id}', function ($request, $response, $args) use ($resolveListingDependencies) {
        return $resolveListingDependencies()['listingController']->delete($request, $response, $args);
    })->add(function ($request, $handler) use ($resolveAuthDependencies) {
        return $resolveAuthDependencies()['jwtAuthentication']($request, $handler);
    });

    $protectRoute($app->post('/api/listings/{id}/offers', function ($request, $response, $args) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->store($request, $response, $args);
    }));

    $protectRoute($app->get('/api/offers/mine', function ($request, $response) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->sent($request, $response);
    }));

    $protectRoute($app->get('/api/offers/received', function ($request, $response) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->received($request, $response);
    }));

    $protectRoute($app->put('/api/offers/{id}', function ($request, $response, $args) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->update($request, $response, $args);
    }));

    $protectRoute($app->delete('/api/offers/{id}', function ($request, $response, $args) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->delete($request, $response, $args);
    }));

    $protectRoute($app->post('/api/offers/{id}/accept', function ($request, $response, $args) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->accept($request, $response, $args);
    }));

    $protectRoute($app->post('/api/offers/{id}/reject', function ($request, $response, $args) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->reject($request, $response, $args);
    }));

    $protectRoute($app->post('/api/offers/{id}/cancel', function ($request, $response, $args) use ($resolveOfferDependencies) {
        return $resolveOfferDependencies()['offerController']->cancel($request, $response, $args);
    }));

    $app->get('/api/categories', function ($request, $response) use ($resolveCategoryDependencies) {
        return $resolveCategoryDependencies()['categoryController']->index($request, $response);
    });

    $protectAdminRoute($app->post('/api/categories', function ($request, $response) use ($resolveCategoryDependencies) {
        return $resolveCategoryDependencies()['categoryController']->store($request, $response);
    }));

    $protectAdminRoute($app->put('/api/categories/{id}', function ($request, $response, $args) use ($resolveCategoryDependencies) {
        return $resolveCategoryDependencies()['categoryController']->update($request, $response, $args);
    }));

    $protectAdminRoute($app->delete('/api/categories/{id}', function ($request, $response, $args) use ($resolveCategoryDependencies) {
        return $resolveCategoryDependencies()['categoryController']->delete($request, $response, $args);
    }));
};
