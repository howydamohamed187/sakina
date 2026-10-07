<?php

namespace App\Services\Mosques;

use RuntimeException;
use Throwable;

class MosqueProviderException extends RuntimeException
{
    public const TIMEOUT = 'timeout';

    public const FAILED = 'failed';

    public const INVALID_RESPONSE = 'invalid_response';

    public const NOT_CONFIGURED = 'not_configured';

    public function __construct(public readonly string $reason, string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message ?: $reason, 0, $previous);
    }

    public function status(): int
    {
        return match ($this->reason) {
            self::TIMEOUT => 504,
            self::NOT_CONFIGURED => 503,
            default => 502,
        };
    }
}
