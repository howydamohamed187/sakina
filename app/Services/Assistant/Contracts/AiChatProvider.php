<?php

namespace App\Services\Assistant\Contracts;

use App\Services\Providers\ProviderException;

interface AiChatProvider
{
    public function name(): string;

    public function model(): string;

    /**
     * @param  array<int, array{role: 'system'|'user'|'assistant', content: string}>  $messages
     *
     * @throws ProviderException
     */
    public function chat(array $messages): string;
}
