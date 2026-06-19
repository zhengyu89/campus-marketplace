<?php

declare(strict_types=1);

namespace App\Modules\Listing\Application;

use App\Core\Application\Exception\ApiException;
use App\Modules\Category\Infrastructure\CategoryRepository;
use App\Modules\Listing\Infrastructure\ListingImageStorage;
use App\Modules\Listing\Infrastructure\ListingRepository;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;

final class ListingService
{
    private const CONDITIONS = ['New', 'Like New', 'Used'];
    private const STATUSES = ['Available', 'Reserved', 'Sold'];
    private const SORTS = ['newest', 'oldest', 'price_asc', 'price_desc'];

    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly ListingImageStorage $imageStorage
    ) {
    }

    public function listListings(array $query): array
    {
        $filters = $this->validateFilters($query);

        return array_map(
            [$this, 'toListingPayload'],
            $this->listingRepository->findAll($filters)
        );
    }

    public function getListing(int $listingId): array
    {
        return $this->toListingPayload($this->requireListing($listingId));
    }

    public function getSellerListings(int $sellerId): array
    {
        return array_map(
            [$this, 'toListingPayload'],
            $this->listingRepository->findBySeller($sellerId)
        );
    }

    public function createListing(
        int $sellerId,
        array $payload,
        ?UploadedFileInterface $image = null
    ): array
    {
        $validated = $this->validateListing($payload);
        $validated['image_url'] = null;
        $validated['seller_id'] = $sellerId;

        if ($image !== null) {
            $validated['image_url'] = $this->imageStorage->store($image);
        }

        try {
            return $this->toListingPayload($this->listingRepository->create($validated));
        } catch (Throwable $exception) {
            $this->imageStorage->delete($validated['image_url']);
            throw $exception;
        }
    }

    public function updateListing(int $listingId, int $sellerId, array $payload): array
    {
        $listing = $this->requireListing($listingId);
        $this->ensureOwner($listing, $sellerId);

        $mergedPayload = [
            'category_id' => $payload['category_id'] ?? $listing['category_id'],
            'title' => $payload['title'] ?? $listing['title'],
            'description' => $payload['description'] ?? $listing['description'],
            'price' => $payload['price'] ?? $listing['price'],
            'image_url' => $listing['image_url'],
            'condition_status' => $payload['condition_status'] ?? $listing['condition_status'],
            'listing_status' => $payload['listing_status'] ?? $listing['listing_status'],
        ];

        return $this->toListingPayload(
            $this->listingRepository->update(
                $listingId,
                $this->validateListing($mergedPayload)
            )
        );
    }

    public function replaceListingImage(
        int $listingId,
        int $sellerId,
        UploadedFileInterface $image
    ): array {
        $listing = $this->requireListing($listingId);
        $this->ensureOwner($listing, $sellerId);
        $newImagePath = $this->imageStorage->store($image);

        try {
            $updatedListing = $this->listingRepository->updateImage($listingId, $newImagePath);
        } catch (Throwable $exception) {
            $this->imageStorage->delete($newImagePath);
            throw $exception;
        }

        $this->imageStorage->delete($listing['image_url']);

        return $this->toListingPayload($updatedListing);
    }

    public function removeListingImage(int $listingId, int $sellerId): array
    {
        $listing = $this->requireListing($listingId);
        $this->ensureOwner($listing, $sellerId);
        $updatedListing = $this->listingRepository->updateImage($listingId, null);
        $this->imageStorage->delete($listing['image_url']);

        return $this->toListingPayload($updatedListing);
    }

    public function deleteListing(int $listingId, int $sellerId): array
    {
        $listing = $this->requireListing($listingId);
        $this->ensureOwner($listing, $sellerId);

        if ($this->listingRepository->hasOffers($listingId)) {
            throw new ApiException(
                'Listing cannot be deleted because it has existing offers',
                409
            );
        }

        $this->listingRepository->delete($listingId);
        $this->imageStorage->delete($listing['image_url']);

        return [
            'message' => 'Listing deleted successfully',
        ];
    }

    private function validateFilters(array $query): array
    {
        $search = trim((string) ($query['q'] ?? $query['search'] ?? ''));
        $categoryValue = $query['category_id'] ?? $query['category'] ?? null;
        $categoryId = $categoryValue === null || $categoryValue === ''
            ? null
            : filter_var($categoryValue, FILTER_VALIDATE_INT);
        $condition = trim((string) ($query['condition_status'] ?? $query['condition'] ?? ''));
        $status = trim((string) ($query['listing_status'] ?? $query['status'] ?? ''));
        $sort = trim((string) ($query['sort'] ?? 'newest'));
        $limitValue = $query['limit'] ?? null;
        $limit = $limitValue === null || $limitValue === ''
            ? null
            : filter_var($limitValue, FILTER_VALIDATE_INT);
        $errors = [];

        if (mb_strlen($search) > 150) {
            $errors['q'] = 'Search term must not exceed 150 characters.';
        }

        if ($categoryValue !== null && $categoryValue !== '' && ($categoryId === false || $categoryId <= 0)) {
            $errors['category_id'] = 'Category must be a valid category ID.';
        }

        if ($categoryId !== null && $categoryId !== false && $this->categoryRepository->findById((int) $categoryId) === null) {
            $errors['category_id'] = 'Selected category does not exist.';
        }

        if ($condition !== '' && !in_array($condition, self::CONDITIONS, true)) {
            $errors['condition_status'] = 'Condition must be New, Like New, or Used.';
        }

        if ($status !== '' && !in_array($status, self::STATUSES, true)) {
            $errors['listing_status'] = 'Status must be Available, Reserved, or Sold.';
        }

        if (!in_array($sort, self::SORTS, true)) {
            $errors['sort'] = 'Sort must be newest, oldest, price_asc, or price_desc.';
        }

        if ($limitValue !== null && $limitValue !== '' && ($limit === false || $limit < 1 || $limit > 100)) {
            $errors['limit'] = 'Limit must be between 1 and 100.';
        }

        if ($errors !== []) {
            throw new ApiException('Validation failed', 422, $errors);
        }

        return [
            'query' => $search,
            'category_id' => $categoryId === null ? null : (int) $categoryId,
            'condition_status' => $condition,
            'listing_status' => $status,
            'sort' => $sort,
            'limit' => $limit === null ? null : (int) $limit,
        ];
    }

    private function validateListing(array $payload): array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $priceValue = $payload['price'] ?? null;
        $price = is_numeric($priceValue) ? (float) $priceValue : null;
        $categoryId = filter_var($payload['category_id'] ?? null, FILTER_VALIDATE_INT);
        $imageUrl = trim((string) ($payload['image_url'] ?? ''));
        $condition = trim((string) ($payload['condition_status'] ?? 'Used'));
        $status = trim((string) ($payload['listing_status'] ?? 'Available'));
        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($title) > 150) {
            $errors['title'] = 'Title must not exceed 150 characters.';
        }

        if ($description === '') {
            $errors['description'] = 'Description is required.';
        } elseif (mb_strlen($description) > 5000) {
            $errors['description'] = 'Description must not exceed 5000 characters.';
        }

        if ($price === null || $price <= 0) {
            $errors['price'] = 'Price must be greater than zero.';
        } elseif ($price > 99999999.99) {
            $errors['price'] = 'Price is too large.';
        }

        if ($categoryId === false || $categoryId <= 0) {
            $errors['category_id'] = 'Category is required.';
        } elseif ($this->categoryRepository->findById((int) $categoryId) === null) {
            $errors['category_id'] = 'Selected category does not exist.';
        }

        if (!in_array($condition, self::CONDITIONS, true)) {
            $errors['condition_status'] = 'Condition must be New, Like New, or Used.';
        }

        if (!in_array($status, self::STATUSES, true)) {
            $errors['listing_status'] = 'Status must be Available, Reserved, or Sold.';
        }

        if ($imageUrl !== '' && !$this->isValidStoredImageReference($imageUrl)) {
            $errors['image_url'] = 'Stored image reference is invalid.';
        }

        if ($errors !== []) {
            throw new ApiException('Validation failed', 422, $errors);
        }

        return [
            'category_id' => (int) $categoryId,
            'title' => $title,
            'description' => $description,
            'price' => number_format((float) $price, 2, '.', ''),
            'image_url' => $imageUrl === '' ? null : $imageUrl,
            'condition_status' => $condition,
            'listing_status' => $status,
        ];
    }

    private function isValidStoredImageReference(string $imageUrl): bool
    {
        if (preg_match('#^/uploads/listings/[a-f0-9]{32}\.(?:jpg|png|webp)$#', $imageUrl) === 1) {
            return true;
        }

        $parsedUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);
        $scheme = strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME));

        return $parsedUrl !== false
            && in_array($scheme, ['http', 'https'], true)
            && mb_strlen($imageUrl) <= 2048;
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

    private function ensureOwner(array $listing, int $sellerId): void
    {
        if ((int) $listing['seller_id'] !== $sellerId) {
            throw new ApiException(
                'You do not have permission to modify this listing',
                403
            );
        }
    }

    private function toListingPayload(array $listing): array
    {
        return [
            'listing_id' => (int) $listing['listing_id'],
            'seller_id' => (int) $listing['seller_id'],
            'category_id' => (int) $listing['category_id'],
            'category_name' => $listing['category_name'],
            'title' => $listing['title'],
            'description' => $listing['description'],
            'price' => (float) $listing['price'],
            'image_url' => $listing['image_url'],
            'condition_status' => $listing['condition_status'],
            'listing_status' => $listing['listing_status'],
            'created_at' => $listing['created_at'],
            'seller' => [
                'user_id' => (int) $listing['seller_id'],
                'name' => $listing['seller_name'],
            ],
        ];
    }
}
