<?php

namespace App\Exceptions;

/**
 * Error answer of the travel API (website/travel/*): `result` is the API's own code
 * (docs/API.md → Travel, error codes), `data` the whole answer — on codes 40, 51, 52, 59
 * it still carries the order and contract fields. The exception code is the HTTP status
 * (503 when the API could not be reached), so isUnavailable() keeps its meaning.
 */
final class TravelApiException extends ProviderException
{
    public function __construct(
        string $message,
        int $httpStatus,
        public readonly ?int $result = null,
        public readonly array $data = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
