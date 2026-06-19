<?php

declare(strict_types=1);

namespace App\Modules\Offer\Presentation;

use App\Core\Application\Exception\ApiException;
use App\Core\Application\Responder\JsonResponder;
use App\Modules\Offer\Application\OfferService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class OfferController
{
    public function __construct(private OfferService $offerService)
    {
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success(
                $response,
                [
                    'offer' => $this->offerService->createOffer(
                        (int) ($args['id'] ?? 0),
                        $this->getUserId($request),
                        $this->getPayload($request)
                    ),
                ],
                201
            )
        );
    }

    public function sent(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'offers' => $this->offerService->getSentOffers($this->getUserId($request)),
            ])
        );
    }

    public function received(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'offers' => $this->offerService->getReceivedOffers($this->getUserId($request)),
            ])
        );
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'offer' => $this->offerService->updateOffer(
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
                $this->offerService->deleteOffer(
                    (int) ($args['id'] ?? 0),
                    $this->getUserId($request)
                )
            )
        );
    }

    public function accept(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'offer' => $this->offerService->acceptOffer(
                    (int) ($args['id'] ?? 0),
                    $this->getUserId($request)
                ),
            ])
        );
    }

    public function reject(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'offer' => $this->offerService->rejectOffer(
                    (int) ($args['id'] ?? 0),
                    $this->getUserId($request)
                ),
            ])
        );
    }

    public function cancel(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'offer' => $this->offerService->cancelOffer(
                    (int) ($args['id'] ?? 0),
                    $this->getUserId($request)
                ),
            ])
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
