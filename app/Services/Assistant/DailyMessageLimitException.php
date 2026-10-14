<?php

namespace App\Services\Assistant;

use RuntimeException;

class DailyMessageLimitException extends RuntimeException
{
    /**
     * @param  array{daily_limit: int, used_messages: int, remaining_messages: int}  $usage
     */
    public function __construct(public readonly array $usage)
    {
        parent::__construct(__('api.assistant.daily_limit_reached', [
            'limit' => $usage['daily_limit'],
        ]));
    }
}
