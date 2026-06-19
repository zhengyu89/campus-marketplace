<?php

declare(strict_types=1);

namespace App\Core\Application\Exception;

use RuntimeException;

final class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $statusCode = 400,
        private array $errors = []
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
