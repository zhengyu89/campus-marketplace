<?php

declare(strict_types=1);

namespace App\Modules\Offer\Application;

use App\Core\Application\Exception\ApiException;
use App\Modules\Listing\Infrastructure\ListingRepository;
use App\Modules\Offer\Infrastructure\OfferRepository;

final class OfferService
{
    private const STATUSES = ['Pending', 'Accepted', 'Rejected', 'Cancelled'];

    public function __construct(
        private OfferRepository $offerRepository,
        private ListingRepository $listingRepository
    ) {
    }

    public function createOffer(int $listingId, int $buyerId, array $payload): array
    {
        $listing = $this->requireListing($listingId);

        if ((int) $listing['seller_id'] === $buyerId) {
            throw new ApiException('You cannot make an offer on your own listing', 403);
        }

        if ($listing['listing_status'] !== 'Available') {
            throw new ApiException('This listing is not accepting offers', 409);
        }

        if ($this->offerRepository->hasOffer($listingId, $buyerId)) {
            throw new ApiException(
                'You already made an offer for this listing. Edit or delete it instead.',
                409
            );
        }

        $validated = $this->validateOffer($payload);
        $validated['listing_id'] = $listingId;
        $validated['buyer_id'] = $buyerId;

        return $this->toOfferPayload($this->offerRepository->create($validated));
    }

    public function updateOffer(int $offerId, int $buyerId, array $payload): array
    {
        $offer = $this->requireOffer($offerId);
        $this->ensureBuyer($offer, $buyerId);
        $this->ensurePending($offer);

        return $this->toOfferPayload(
            $this->offerRepository->update($offerId, $this->validateOffer($payload))
        );
    }

    public function deleteOffer(int $offerId, int $buyerId): array
    {
        $offer = $this->requireOffer($offerId);
        $this->ensureBuyer($offer, $buyerId);

        if ($offer['offer_status'] === 'Accepted') {
            throw new ApiException('Accepted offers cannot be deleted', 409);
        }

        $this->offerRepository->delete($offerId);

        return [
            'message' => 'Offer deleted successfully',
        ];
    }

    public function getSentOffers(int $buyerId): array
    {
        return array_map(
            [$this, 'toOfferPayload'],
            $this->offerRepository->findByBuyer($buyerId)
        );
    }

    public function getReceivedOffers(int $sellerId): array
    {
        return array_map(
            [$this, 'toOfferPayload'],
            $this->offerRepository->findBySeller($sellerId)
        );
    }

    public function acceptOffer(int $offerId, int $sellerId): array
    {
        $offer = $this->requireOffer($offerId);
        $this->ensureSeller($offer, $sellerId);
        $this->ensurePending($offer);

        return $this->offerRepository->transaction(function () use ($offerId, $offer) {
            $updatedOffer = $this->offerRepository->updateStatus($offerId, 'Accepted');
            $this->offerRepository->rejectOtherPendingOffers((int) $offer['listing_id'], $offerId);
            $this->offerRepository->updateListingStatus((int) $offer['listing_id'], 'Reserved');

            return $this->toOfferPayload($updatedOffer);
        });
    }

    public function rejectOffer(int $offerId, int $sellerId): array
    {
        $offer = $this->requireOffer($offerId);
        $this->ensureSeller($offer, $sellerId);
        $this->ensurePending($offer);

        return $this->toOfferPayload($this->offerRepository->updateStatus($offerId, 'Rejected'));
    }

    public function cancelOffer(int $offerId, int $buyerId): array
    {
        $offer = $this->requireOffer($offerId);
        $this->ensureBuyer($offer, $buyerId);
        $this->ensurePending($offer);

        return $this->toOfferPayload($this->offerRepository->updateStatus($offerId, 'Cancelled'));
    }

    private function validateOffer(array $payload): array
    {
        $priceValue = $payload['offer_price'] ?? $payload['price'] ?? null;
        $price = is_numeric($priceValue) ? (float) $priceValue : null;
        $message = trim((string) ($payload['message'] ?? ''));
        $errors = [];

        if ($price === null || $price <= 0) {
            $errors['offer_price'] = 'Offer price must be greater than zero.';
        } elseif ($price > 99999999.99) {
            $errors['offer_price'] = 'Offer price is too large.';
        }

        if (mb_strlen($message) > 255) {
            $errors['message'] = 'Message must not exceed 255 characters.';
        }

        if ($errors !== []) {
            throw new ApiException('Validation failed', 422, $errors);
        }

        return [
            'offer_price' => number_format((float) $price, 2, '.', ''),
            'message' => $message === '' ? null : $message,
        ];
    }

    private function requireListing(int $listingId): array
    {
        if ($listingId <= 0) {
            throw new ApiException('Listing not found', 404);
        }

        $listing = $this->listingRepository->findById($listingId);

        if ($listing === null) {
            throw new ApiException('Listing not found', 404);
        }

        return $listing;
    }

    private function requireOffer(int $offerId): array
    {
        if ($offerId <= 0) {
            throw new ApiException('Offer not found', 404);
        }

        $offer = $this->offerRepository->findById($offerId);

        if ($offer === null) {
            throw new ApiException('Offer not found', 404);
        }

        return $offer;
    }

    private function ensureSeller(array $offer, int $sellerId): void
    {
        if ((int) $offer['seller_id'] !== $sellerId) {
            throw new ApiException('You do not have permission to manage this offer', 403);
        }
    }

    private function ensureBuyer(array $offer, int $buyerId): void
    {
        if ((int) $offer['buyer_id'] !== $buyerId) {
            throw new ApiException('You do not have permission to modify this offer', 403);
        }
    }

    private function ensurePending(array $offer): void
    {
        if ($offer['offer_status'] !== 'Pending') {
            throw new ApiException('Only pending offers can be changed', 409);
        }
    }

    private function toOfferPayload(array $offer): array
    {
        $status = in_array($offer['offer_status'], self::STATUSES, true)
            ? $offer['offer_status']
            : 'Pending';

        return [
            'offer_id' => (int) $offer['offer_id'],
            'listing_id' => (int) $offer['listing_id'],
            'buyer_id' => (int) $offer['buyer_id'],
            'seller_id' => (int) $offer['seller_id'],
            'offer_price' => (float) $offer['offer_price'],
            'message' => $offer['message'],
            'offer_status' => $status,
            'created_at' => $offer['created_at'],
            'listing' => [
                'listing_id' => (int) $offer['listing_id'],
                'title' => $offer['listing_title'],
                'price' => (float) $offer['listing_price'],
                'image_url' => $offer['listing_image_url'],
                'listing_status' => $offer['listing_status'],
            ],
            'buyer' => [
                'user_id' => (int) $offer['buyer_id'],
                'name' => $offer['buyer_name'],
            ],
            'seller' => [
                'user_id' => (int) $offer['seller_id'],
                'name' => $offer['seller_name'],
            ],
        ];
    }
}
