<?php

namespace App\Support;

class WorkingDays
{
    public const DAYS = [
        'saturday',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    /**
     * @return array<string, array{status: bool, day_name: string, from: string, to: string}>
     */
    public static function defaults(): array
    {
        $days = [];

        foreach (self::DAYS as $day) {
            $days[$day] = [
                'status' => true,
                'day_name' => $day,
                'from' => '00:00',
                'to' => '23:59',
            ];
        }

        return $days;
    }
}
