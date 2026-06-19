<?php

declare(strict_types=1);

namespace App\Modules\Offer\Infrastructure;

use PDO;

final class OfferRepository
{
    private const SELECT_COLUMNS = '
        SELECT
            offers.offer_id,
            offers.listing_id,
            offers.buyer_id,
            offers.offer_price,
            offers.message,
            offers.offer_status,
            offers.created_at,
            listings.seller_id,
            listings.title AS listing_title,
            listings.price AS listing_price,
            listings.image_url AS listing_image_url,
            listings.listing_status,
            buyers.name AS buyer_name,
            sellers.name AS seller_name
        FROM offers
        INNER JOIN listings ON listings.listing_id = offers.listing_id
        INNER JOIN users buyers ON buyers.user_id = offers.buyer_id
        INNER JOIN users sellers ON sellers.user_id = listings.seller_id
    ';

    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $offerId): ?array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . '
            WHERE offers.offer_id = :offer_id'
        );
        $statement->execute(['offer_id' => $offerId]);
        $offer = $statement->fetch(PDO::FETCH_ASSOC);

        return $offer === false ? null : $offer;
    }

    public function findByListingAndBuyer(int $listingId, int $buyerId): ?array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . '
            WHERE offers.listing_id = :listing_id
              AND offers.buyer_id = :buyer_id'
        );
        $statement->execute([
            'listing_id' => $listingId,
            'buyer_id' => $buyerId,
        ]);
        $offer = $statement->fetch(PDO::FETCH_ASSOC);

        return $offer === false ? null : $offer;
    }

    public function findByBuyer(int $buyerId): array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . '
            WHERE offers.buyer_id = :buyer_id
            ORDER BY offers.created_at DESC, offers.offer_id DESC'
        );
        $statement->execute(['buyer_id' => $buyerId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySeller(int $sellerId): array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_COLUMNS . '
            WHERE listings.seller_id = :seller_id
            ORDER BY offers.created_at DESC, offers.offer_id DESC'
        );
        $statement->execute(['seller_id' => $sellerId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasOffer(int $listingId, int $buyerId): bool
    {
        $statement = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM offers
             WHERE listing_id = :listing_id
               AND buyer_id = :buyer_id"
        );
        $statement->execute([
            'listing_id' => $listingId,
            'buyer_id' => $buyerId,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $offer): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO offers (
                listing_id,
                buyer_id,
                offer_price,
                message
            ) VALUES (
                :listing_id,
                :buyer_id,
                :offer_price,
                :message
            )'
        );
        $statement->execute($offer);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(int $offerId, array $offer): array
    {
        $statement = $this->pdo->prepare(
            'UPDATE offers
             SET offer_price = :offer_price,
                 message = :message
             WHERE offer_id = :offer_id'
        );
        $statement->execute([
            'offer_id' => $offerId,
            'offer_price' => $offer['offer_price'],
            'message' => $offer['message'],
        ]);

        return $this->findById($offerId);
    }

    public function updateStatus(int $offerId, string $status): array
    {
        $statement = $this->pdo->prepare(
            'UPDATE offers
             SET offer_status = :offer_status
             WHERE offer_id = :offer_id'
        );
        $statement->execute([
            'offer_id' => $offerId,
            'offer_status' => $status,
        ]);

        return $this->findById($offerId);
    }

    public function delete(int $offerId): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM offers WHERE offer_id = :offer_id'
        );
        $statement->execute(['offer_id' => $offerId]);

        return $statement->rowCount() > 0;
    }

    public function rejectOtherPendingOffers(int $listingId, int $acceptedOfferId): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE offers
             SET offer_status = 'Rejected'
             WHERE listing_id = :listing_id
               AND offer_id <> :offer_id
               AND offer_status = 'Pending'"
        );
        $statement->execute([
            'listing_id' => $listingId,
            'offer_id' => $acceptedOfferId,
        ]);
    }

    public function updateListingStatus(int $listingId, string $status): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE listings
             SET listing_status = :listing_status
             WHERE listing_id = :listing_id'
        );
        $statement->execute([
            'listing_id' => $listingId,
            'listing_status' => $status,
        ]);
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback();
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
