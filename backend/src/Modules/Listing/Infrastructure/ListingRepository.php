<?php

declare(strict_types=1);

namespace App\Modules\Listing\Infrastructure;

use PDO;

final class ListingRepository
{
    private const SELECT_COLUMNS = '
        SELECT
            listings.listing_id,
            listings.seller_id,
            listings.category_id,
            listings.title,
            listings.description,
            listings.price,
            listings.image_url,
            listings.condition_status,
            listings.listing_status,
            listings.created_at,
            categories.category_name,
            users.name AS seller_name
        FROM listings
        INNER JOIN categories ON categories.category_id = listings.category_id
        INNER JOIN users ON users.user_id = listings.seller_id
    ';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAll(array $filters): array
    {
        $where = [];
        $parameters = [];

        if (($filters['query'] ?? '') !== '') {
            $where[] = '(
                listings.title LIKE :query_title
                OR listings.description LIKE :query_description
                OR categories.category_name LIKE :query_category
                OR users.name LIKE :query_seller
            )';
            $searchPattern = '%' . $filters['query'] . '%';
            $parameters['query_title'] = $searchPattern;
            $parameters['query_description'] = $searchPattern;
            $parameters['query_category'] = $searchPattern;
            $parameters['query_seller'] = $searchPattern;
        }

        if (($filters['category_id'] ?? null) !== null) {
            $where[] = 'listings.category_id = :category_id';
            $parameters['category_id'] = $filters['category_id'];
        }

        if (($filters['condition_status'] ?? '') !== '') {
            $where[] = 'listings.condition_status = :condition_status';
            $parameters['condition_status'] = $filters['condition_status'];
        }

        if (($filters['listing_status'] ?? '') !== '') {
            $where[] = 'listings.listing_status = :listing_status';
            $parameters['listing_status'] = $filters['listing_status'];
        }

        $orderBy = match ($filters['sort'] ?? 'newest') {
            'oldest' => 'listings.created_at ASC, listings.listing_id ASC',
            'price_asc' => 'listings.price ASC, listings.created_at DESC',
            'price_desc' => 'listings.price DESC, listings.created_at DESC',
            default => 'listings.created_at DESC, listings.listing_id DESC',
        };

        $sql = self::SELECT_COLUMNS;

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY ' . $orderBy;

        if (($filters['limit'] ?? null) !== null) {
            $sql .= ' LIMIT :limit';
        }

        $statement = $this->pdo->prepare($sql);

        foreach ($parameters as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }

        if (($filters['limit'] ?? null) !== null) {
            $statement->bindValue(':limit', $filters['limit'], PDO::PARAM_INT);
        }

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $listingId): ?array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . '
            WHERE listings.listing_id = :listing_id'
        );
        $statement->execute(['listing_id' => $listingId]);
        $listing = $statement->fetch(PDO::FETCH_ASSOC);

        return $listing === false ? null : $listing;
    }

    public function findBySeller(int $sellerId): array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . '
            WHERE listings.seller_id = :seller_id
            ORDER BY listings.created_at DESC, listings.listing_id DESC'
        );
        $statement->execute(['seller_id' => $sellerId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $listing): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO listings (
                seller_id,
                category_id,
                title,
                description,
                price,
                image_url,
                condition_status,
                listing_status
            ) VALUES (
                :seller_id,
                :category_id,
                :title,
                :description,
                :price,
                :image_url,
                :condition_status,
                :listing_status
            )'
        );
        $statement->execute($listing);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(int $listingId, array $listing): array
    {
        $statement = $this->pdo->prepare(
            'UPDATE listings
             SET category_id = :category_id,
                 title = :title,
                 description = :description,
                 price = :price,
                 image_url = :image_url,
                 condition_status = :condition_status,
                 listing_status = :listing_status
             WHERE listing_id = :listing_id'
        );
        $statement->execute([
            ...$listing,
            'listing_id' => $listingId,
        ]);

        return $this->findById($listingId);
    }

    public function updateImage(int $listingId, ?string $imageUrl): array
    {
        $statement = $this->pdo->prepare(
            'UPDATE listings
             SET image_url = :image_url
             WHERE listing_id = :listing_id'
        );
        $statement->execute([
            'listing_id' => $listingId,
            'image_url' => $imageUrl,
        ]);

        return $this->findById($listingId);
    }

    public function delete(int $listingId): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM listings WHERE listing_id = :listing_id'
        );
        $statement->execute(['listing_id' => $listingId]);

        return $statement->rowCount() > 0;
    }

    public function hasOffers(int $listingId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM offers WHERE listing_id = :listing_id'
        );
        $statement->execute(['listing_id' => $listingId]);

        return (int) $statement->fetchColumn() > 0;
    }
}
