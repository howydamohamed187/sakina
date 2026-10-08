<?php

namespace App\Services\Providers;

use RuntimeException;
use Throwable;

/**
 * Raised by external content providers (prayer times, Quran translations/tafsir, gold prices, exchange rates).
 */
class ProviderException extends RuntimeException
{
    public const SERVICE_GOLD_PRICE = 'gold_price';

    public const SERVICE_EXCHANGE_RATE = 'exchange_rate';

    public const TIMEOUT = 'timeout';

    public const FAILED = 'failed';

    public const INVALID_RESPONSE = 'invalid_response';

    public const NOT_CONFIGURED = 'not_configured';

    public function __construct(
        public readonly string $reason,
        string $message = '',
        ?Throwable $previous = null,
        public readonly ?string $service = null,
    ) {
        parent::__construct($message !== '' ? $message : $reason, 0, $previous);
    }

    public function status(): int
    {
        return match ($this->reason) {
            self::TIMEOUT => 504,
            self::NOT_CONFIGURED => 503,
            default => 502,
        };
    }

    public function userMessage(): string
    {
        return __('api.provider_errors.'.$this->reason);
    }
}
