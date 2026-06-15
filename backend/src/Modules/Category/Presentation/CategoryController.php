<?php

declare(strict_types=1);

namespace App\Modules\Category\Presentation;

use App\Core\Application\Exception\ApiException;
use App\Core\Application\Responder\JsonResponder;
use App\Modules\Category\Application\CategoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class CategoryController
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponder::success($response, [
            'categories' => $this->categoryService->listCategories(),
        ]);
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success(
                $response,
                ['category' => $this->categoryService->createCategory($this->getPayload($request))],
                201
            )
        );
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->handle(
            $response,
            fn () => JsonResponder::success($response, [
                'category' => $this->categoryService->updateCategory(
                    (int) ($args['id'] ?? 0),
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
                $this->categoryService->deleteCategory((int) ($args['id'] ?? 0))
            )
        );
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
