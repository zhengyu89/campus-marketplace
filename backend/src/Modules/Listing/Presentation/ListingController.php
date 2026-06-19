<?php

declare(strict_types=1);

namespace App\Modules\Listing\Presentation;

use App\Core\Application\Exception\ApiException;
use App\Core\Application\Responder\JsonResponder;
use App\Modules\Listing\Application\ListingService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ListingController
{
    public function __construct(private readonly ListingService $listingService)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'listings' => $this->listingService->listListings($request->getQueryParams()),
            ])
        );
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'listing' => $this->listingService->getListing((int) ($args['id'] ?? 0)),
            ])
        );
    }

    public function mine(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'listings' => $this->listingService->getSellerListings($this->getUserId($request)),
            ])
        );
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success(
                $response,
                [
                    'listing' => $this->listingService->createListing(
                        $this->getUserId($request),
                        $this->getPayload($request)
                    ),
                ],
                201
            )
        );
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'listing' => $this->listingService->updateListing(
                    (int) ($args['id'] ?? 0),
                    $this->getUserId($request),
                    $this->getPayload($request)
                ),
            ])
        );
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success(
                $response,
                $this->listingService->deleteListing(
                    (int) ($args['id'] ?? 0),
                    $this->getUserId($request)
                )
            )
        );
    }

    private function getUserId(ServerRequestInterface $request): int
    {
        $user = $request->getAttribute('authUser');

        if (!is_array($user) || (int) ($user['user_id'] ?? 0) <= 0) {
            throw new ApiException('Unauthorized', 401);
        }

        return (int) $user['user_id'];
    }

    private function getPayload(ServerRequestInterface $request): array
    {
        $payload = $request->getParsedBody();

        return is_array($payload) ? $payload : [];
    }

    private function handle(ResponseInterface $response, callable $callback): ResponseInterface
    {
        try {
            return $callback();
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
}
