<?php

declare(strict_types=1);

namespace App\Modules\Category\Infrastructure;

use PDO;

final class CategoryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT category_id, category_name, created_at
             FROM categories
             ORDER BY category_name ASC'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $categoryId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT category_id, category_name, created_at
             FROM categories
             WHERE category_id = :category_id'
        );
        $statement->execute(['category_id' => $categoryId]);
        $category = $statement->fetch(PDO::FETCH_ASSOC);

        return $category === false ? null : $category;
    }

    public function findByName(string $categoryName): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT category_id, category_name, created_at
             FROM categories
             WHERE LOWER(category_name) = LOWER(:category_name)'
        );
        $statement->execute(['category_name' => $categoryName]);
        $category = $statement->fetch(PDO::FETCH_ASSOC);

        return $category === false ? null : $category;
    }

    public function create(string $categoryName): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO categories (category_name)
             VALUES (:category_name)'
        );
        $statement->execute(['category_name' => $categoryName]);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(int $categoryId, string $categoryName): ?array
    {
        $statement = $this->pdo->prepare(
            'UPDATE categories
             SET category_name = :category_name
             WHERE category_id = :category_id'
        );
        $statement->execute([
            'category_id' => $categoryId,
            'category_name' => $categoryName,
        ]);

        return $this->findById($categoryId);
    }

    public function delete(int $categoryId): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM categories
             WHERE category_id = :category_id'
        );
        $statement->execute(['category_id' => $categoryId]);

        return $statement->rowCount() > 0;
    }

    public function hasListings(int $categoryId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM listings
             WHERE category_id = :category_id'
        );
        $statement->execute(['category_id' => $categoryId]);

        return (int) $statement->fetchColumn() > 0;
    }
}
