<?php

declare(strict_types=1);

namespace App\Modules\Category\Application;

use App\Core\Application\Exception\ApiException;
use App\Modules\Category\Infrastructure\CategoryRepository;

final class CategoryService
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    public function listCategories(): array
    {
        return array_map([$this, 'toCategoryPayload'], $this->categoryRepository->findAll());
    }

    public function createCategory(array $payload): array
    {
        $categoryName = $this->validateCategoryName($payload);

        if ($this->categoryRepository->findByName($categoryName) !== null) {
            throw new ApiException(
                'A category with this name already exists',
                409,
                ['category_name' => 'Category name must be unique.']
            );
        }

        return $this->toCategoryPayload($this->categoryRepository->create($categoryName));
    }

    public function updateCategory(int $categoryId, array $payload): array
    {
        $category = $this->categoryRepository->findById($categoryId);

        if ($category === null) {
            throw new ApiException('Category not found', 404);
        }

        $categoryName = $this->validateCategoryName($payload);
        $existingCategory = $this->categoryRepository->findByName($categoryName);

        if (
            $existingCategory !== null
            && (int) $existingCategory['category_id'] !== (int) $category['category_id']
        ) {
            throw new ApiException(
                'A category with this name already exists',
                409,
                ['category_name' => 'Category name must be unique.']
            );
        }

        return $this->toCategoryPayload($this->categoryRepository->update($categoryId, $categoryName));
    }

    public function deleteCategory(int $categoryId): array
    {
        if ($this->categoryRepository->findById($categoryId) === null) {
            throw new ApiException('Category not found', 404);
        }

        if ($this->categoryRepository->hasListings($categoryId)) {
            throw new ApiException(
                'Category cannot be deleted because listings are using it',
                409
            );
        }

        $this->categoryRepository->delete($categoryId);

        return [
            'message' => 'Category deleted successfully',
        ];
    }

    private function validateCategoryName(array $payload): string
    {
        $categoryName = trim((string) ($payload['category_name'] ?? $payload['name'] ?? ''));
        $errors = [];

        if ($categoryName === '') {
            $errors['category_name'] = 'Category name is required.';
        } elseif (mb_strlen($categoryName) > 100) {
            $errors['category_name'] = 'Category name must not exceed 100 characters.';
        }

        if ($errors !== []) {
            throw new ApiException('Validation failed', 422, $errors);
        }

        return $categoryName;
    }

    private function toCategoryPayload(?array $category): array
    {
        if ($category === null) {
            throw new ApiException('Category not found', 404);
        }

        return [
            'category_id' => (int) $category['category_id'],
            'category_name' => $category['category_name'],
            'created_at' => $category['created_at'],
        ];
    }
}
