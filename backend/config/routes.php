<?php

declare(strict_types=1);

use App\Core\Application\Responder\JsonResponder;
use Slim\App;

return function (App $app): void {
    $app->get('/api/health', function ($request, $response) {
        return JsonResponder::success($response, [
            'message' => 'Backend API is running',
        ]);
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
