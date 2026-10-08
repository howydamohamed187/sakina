<?php

namespace App\Notifications;

use App\Support\Locales;

/**
 * General text notification: free title/body per locale (missing locales fall back to the default one).
 */
class SendAdminMessagesNotification extends CustomerPushNotification
{
    /** @var array<string, string> */
    private array $titles;

    /** @var array<string, string> */
    private array $bodies;

    /**
     * @param  array<string, string|null>  $title
     * @param  array<string, string|null>  $body
     */
    public function __construct(array $title, array $body)
    {
        $this->titles = self::complete($title);
        $this->bodies = self::complete($body);
    }

    public function key(): string
    {
        return 'general';
    }

    public function titles(): array
    {
        return $this->titles;
    }

    public function bodies(): array
    {
        return $this->bodies;
    }

    /**
     * @param  array<string, string|null>  $texts
     * @return array<string, string>
     */
    private static function complete(array $texts): array
    {
        $texts = array_filter(array_intersect_key($texts, array_flip(Locales::all())), fn ($text): bool => filled($text));
        $fallback = (string) ($texts[Locales::default()] ?? reset($texts) ?: '');
        $completed = [];

        foreach (Locales::all() as $locale) {
            $completed[$locale] = (string) ($texts[$locale] ?? $fallback);
        }

        return $completed;
    }
}
