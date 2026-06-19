<?php

declare(strict_types=1);

namespace App\Modules\Listing\Infrastructure;

use App\Core\Application\Exception\ApiException;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;

final class ListingImageStorage
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private readonly string $uploadDirectory;

    public function __construct(?string $uploadDirectory = null)
    {
        $this->uploadDirectory = $uploadDirectory
            ?? dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'public'
                . DIRECTORY_SEPARATOR . 'uploads'
                . DIRECTORY_SEPARATOR . 'listings';
    }

    public function store(UploadedFileInterface $image): string
    {
        $error = $image->getError();

        if ($error !== UPLOAD_ERR_OK) {
            throw new ApiException(
                'Validation failed',
                422,
                ['image' => $this->uploadErrorMessage($error)]
            );
        }

        $size = $image->getSize();

        if ($size === null || $size <= 0) {
            throw new ApiException(
                'Validation failed',
                422,
                ['image' => 'Select a non-empty image file.']
            );
        }

        if ($size > self::MAX_BYTES) {
            throw new ApiException(
                'Validation failed',
                422,
                ['image' => 'Image size must not exceed 5 MB.']
            );
        }

        $stream = $image->getStream();
        $contents = $stream->getContents();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        if ($contents === '') {
            throw new ApiException(
                'Validation failed',
                422,
                ['image' => 'Select a non-empty image file.']
            );
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($contents);
        $extension = self::ALLOWED_MIME_TYPES[$mimeType] ?? null;

        if ($extension === null || @getimagesizefromstring($contents) === false) {
            throw new ApiException(
                'Validation failed',
                422,
                ['image' => 'Image must be a valid JPG, PNG, or WebP file.']
            );
        }

        $this->ensureUploadDirectory();
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $this->uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

        try {
            $image->moveTo($targetPath);
        } catch (Throwable) {
            throw new ApiException('Unable to store the listing image', 500);
        }

        return '/uploads/listings/' . $fileName;
    }

    public function delete(?string $imagePath): void
    {
        if (!$this->isManagedPath($imagePath)) {
            return;
        }

        $fileName = basename((string) $imagePath);
        $absolutePath = $this->uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function isManagedPath(?string $imagePath): bool
    {
        return is_string($imagePath)
            && preg_match(
                '#^/uploads/listings/[a-f0-9]{32}\.(?:jpg|png|webp)$#',
                $imagePath
            ) === 1;
    }

    private function ensureUploadDirectory(): void
    {
        if (is_dir($this->uploadDirectory)) {
            return;
        }

        if (!mkdir($this->uploadDirectory, 0775, true) && !is_dir($this->uploadDirectory)) {
            throw new ApiException('Unable to prepare image storage', 500);
        }
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image size exceeds the server upload limit.',
            UPLOAD_ERR_PARTIAL => 'Image upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Select an image file to upload.',
            default => 'Unable to upload this image.',
        };
    }
}
