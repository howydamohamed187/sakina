<?php

namespace App\Support;

class SocialIcons
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'fab fa-facebook' => 'Facebook',
            'fab fa-x-twitter' => 'X',
            'fab fa-instagram' => 'Instagram',
            'fab fa-youtube' => 'YouTube',
            'fab fa-telegram' => 'Telegram',
            'fab fa-linkedin-in' => 'LinkedIn',
            'fab fa-snapchat' => 'Snapchat',
            'fab fa-tiktok' => 'TikTok',
            'fab fa-whatsapp' => 'WhatsApp',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function toSelect(): array
    {
        return collect(self::all())
            ->mapWithKeys(fn (string $label, string $icon): array => [
                $icon => '<span class="inline-flex items-center gap-2"><i class="'.$icon.'"></i> '.$label.'</span>',
            ])
            ->all();
    }
}
