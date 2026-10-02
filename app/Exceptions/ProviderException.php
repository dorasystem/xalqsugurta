<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Error from the insurer's API. The code is the provider's error code (e.g. 503),
 * or 503 when the API could not be reached at all.
 */
class ProviderException extends RuntimeException
{
    /** The registry / provider is down, as opposed to "nothing found for this input" */
    public function isUnavailable(): bool
    {
        return $this->getCode() >= 500;
    }
}
