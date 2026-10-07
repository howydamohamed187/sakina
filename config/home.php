<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quick Actions
    |--------------------------------------------------------------------------
    |
    | Keys are part of the mobile contract. Titles live in lang/{locale}/api.php
    | under "quick_actions".
    |
    */

    'quick_actions' => [
        [
            'key' => 'qibla',
            'icon' => 'qibla',
            'enabled' => true,
            'action' => ['type' => 'route', 'value' => 'qibla'],
        ],
        [
            'key' => 'nearest_mosque',
            'icon' => 'mosque',
            'enabled' => true,
            'action' => ['type' => 'route', 'value' => 'nearest_mosque'],
        ],
        [
            'key' => 'zakat_calculator',
            'icon' => 'zakat',
            'enabled' => true,
            'action' => ['type' => 'route', 'value' => 'zakat_calculator'],
        ],
        [
            'key' => 'tasbeeh',
            'icon' => 'tasbeeh',
            'enabled' => true,
            'action' => ['type' => 'route', 'value' => 'tasbeeh'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Semantic UI schema for the Home screen. "component" and "data_key" are
    | part of the mobile contract; bump "version" on any breaking change.
    | Section titles live in lang/{locale}/api.php under "home_sections".
    |
    | "fields" lists every column the section reads from home.data[data_key].
    | Paths are relative to data_key; "[]" marks a list item.
    |
    */

    'layout' => [
        'version' => 1,
        'sections' => [
            [
                'key' => 'header_section',
                'component' => 'home_header',
                'order' => 1,
                'visible' => true,
                'data_key' => 'notifications',
                'data_type' => 'object',
                'nullable' => false,
                'settings' => [],
                'fields' => [
                    ['path' => 'unread_count', 'type' => 'integer', 'nullable' => false, 'description' => 'Unread notifications badge count.'],
                ],
            ],
            [
                'key' => 'prayer_times_section',
                'component' => 'prayer_times',
                'order' => 2,
                'visible' => true,
                'data_key' => 'prayer_times',
                'data_type' => 'array',
                'nullable' => false,
                'settings' => ['scrollable' => true],
                'fields' => [
                    ['path' => '[].key', 'type' => 'string', 'nullable' => false, 'description' => 'One of: fajr, sunrise, dhuhr, asr, maghrib, isha.'],
                    ['path' => '[].name', 'type' => 'string', 'nullable' => false, 'description' => 'Localized display name.'],
                    ['path' => '[].time', 'type' => 'string', 'nullable' => false, 'description' => 'Local time, HH:mm (24h).'],
                ],
            ],
            [
                'key' => 'next_prayer_section',
                'component' => 'next_prayer_card',
                'order' => 3,
                'visible' => true,
                'data_key' => 'next_prayer',
                'data_type' => 'object',
                'nullable' => false,
                'settings' => ['countdown_source' => 'datetime'],
                'fields' => [
                    ['path' => 'key', 'type' => 'string', 'nullable' => false, 'description' => 'One of: fajr, dhuhr, asr, maghrib, isha (never sunrise).'],
                    ['path' => 'name', 'type' => 'string', 'nullable' => false, 'description' => 'Localized display name.'],
                    ['path' => 'time', 'type' => 'string', 'nullable' => false, 'description' => 'Local time, HH:mm (24h).'],
                    ['path' => 'datetime', 'type' => 'datetime', 'nullable' => false, 'description' => 'ISO-8601 with offset; source of truth for the countdown.'],
                    ['path' => 'remaining_seconds', 'type' => 'integer', 'nullable' => false, 'description' => 'Seconds left at response time; informational only.'],
                ],
            ],
            [
                'key' => 'quick_actions_section',
                'component' => 'quick_actions_list',
                'order' => 4,
                'visible' => true,
                'data_key' => 'quick_actions',
                'data_type' => 'object',
                'nullable' => false,
                'settings' => ['columns' => 2],
                'fields' => [
                    ['path' => 'items[].key', 'type' => 'string', 'nullable' => false, 'description' => 'One of: qibla, nearest_mosque, zakat_calculator, tasbeeh.'],
                    ['path' => 'items[].title', 'type' => 'string', 'nullable' => false, 'description' => 'Localized title.'],
                    ['path' => 'items[].icon', 'type' => 'string', 'nullable' => false, 'description' => 'Local icon asset key.'],
                    ['path' => 'items[].enabled', 'type' => 'boolean', 'nullable' => false, 'description' => 'Hide or disable the action when false.'],
                    ['path' => 'items[].action.type', 'type' => 'string', 'nullable' => false, 'description' => 'Action type, e.g. route.'],
                    ['path' => 'items[].action.value', 'type' => 'string', 'nullable' => false, 'description' => 'Route name to open.'],
                ],
            ],
            [
                'key' => 'daily_question_section',
                'component' => 'daily_question_card',
                'order' => 5,
                'visible' => true,
                'data_key' => 'daily_question',
                'data_type' => 'object',
                'nullable' => true,
                'settings' => ['details_endpoint' => '/api/v1/daily-question'],
                'fields' => [
                    ['path' => 'id', 'type' => 'integer', 'nullable' => false, 'description' => 'Question id.'],
                    ['path' => 'body', 'type' => 'string', 'nullable' => false, 'description' => 'Question text.'],
                    ['path' => 'category', 'type' => 'string', 'nullable' => true, 'description' => 'Category key.'],
                    ['path' => 'category_label', 'type' => 'string', 'nullable' => true, 'description' => 'Localized category label.'],
                    ['path' => 'answered', 'type' => 'integer', 'nullable' => false, 'description' => '1 if answered today, otherwise 0.'],
                    ['path' => 'is_correct', 'type' => 'integer', 'nullable' => true, 'description' => '1 or 0 after answering, null before.'],
                    ['path' => 'participations_count', 'type' => 'integer', 'nullable' => false, 'description' => 'Questions answered by the user.'],
                    ['path' => 'correct_answers_count', 'type' => 'integer', 'nullable' => false, 'description' => 'Correct answers by the user.'],
                ],
            ],
        ],
    ],

];
