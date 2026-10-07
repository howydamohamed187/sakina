<?php

namespace App\Services;

use App\Models\Customer;
use App\Support\QuestionCategories;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;

class HomeService
{
    public function __construct(
        private readonly PrayerTimesService $prayerTimes,
        private readonly DailyQuestionService $dailyQuestions,
    ) {}

    /**
     * Keys are part of the mobile contract and referenced by layout "data_key".
     *
     * @return array{notifications: array, prayer_times: array, next_prayer: array, quick_actions: array, daily_question: ?array}
     */
    public function data(Authenticatable $user, float $latitude, float $longitude, ?string $timezone = null): array
    {
        $timezone ??= $this->prayerTimes->resolveTimezone($latitude, $longitude);

        $today = $this->prayerTimes->getTodayPrayerTimes(latitude: $latitude, longitude: $longitude, timezone: $timezone);
        $tomorrow = $this->prayerTimes->getTomorrowPrayerTimes(latitude: $latitude, longitude: $longitude, timezone: $timezone);

        return [
            'notifications' => $this->notifications($user),
            'prayer_times' => $this->prayerTimes->toList($today),
            'next_prayer' => $this->prayerTimes->getNextPrayer($today, $tomorrow),
            'quick_actions' => $this->quickActions(),
            'daily_question' => $this->dailyQuestion($user),
        ];
    }

    /**
     * @return array{version: int, sections: list<array<string, mixed>>}
     */
    public function layout(): array
    {
        $sections = collect(config('home.layout.sections', []))
            ->sortBy('order')
            ->values()
            ->map(fn (array $section): array => [
                'key' => $section['key'],
                'title' => __("api.home_sections.{$section['key']}"),
                'component' => $section['component'],
                'order' => (int) $section['order'],
                'visible' => (bool) $section['visible'],
                'data_key' => $section['data_key'],
                'data_type' => $section['data_type'],
                'nullable' => (bool) $section['nullable'],
                'settings' => (object) ($section['settings'] ?? []),
                'fields' => $section['fields'],
            ])
            ->all();

        return [
            'version' => (int) config('home.layout.version', 1),
            'sections' => $sections,
        ];
    }

    /**
     * @return array{unread_count: int}
     */
    private function notifications(Authenticatable $user): array
    {
        return [
            'unread_count' => method_exists($user, 'unreadNotifications')
                ? $user->unreadNotifications()->count()
                : 0,
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    private function quickActions(): array
    {
        return [
            'items' => array_map(fn (array $action): array => [
                'key' => $action['key'],
                'title' => __("api.quick_actions.{$action['key']}"),
                'icon' => $action['icon'],
                'enabled' => (bool) $action['enabled'],
                'action' => $action['action'],
            ], config('home.quick_actions', [])),
        ];
    }

    private function dailyQuestion(Authenticatable $user): ?array
    {
        if (! $user instanceof Customer) {
            return null;
        }

        try {
            $payload = $this->dailyQuestions->todayFor($user);
        } catch (ValidationException) {
            return null;
        }

        $question = $payload['question'];
        $answer = $payload['answer'];

        return [
            'id' => $question->id,
            'body' => $question->body,
            'category' => $question->category,
            'category_label' => QuestionCategories::label($question->category),
            'answered' => $answer ? 1 : 0,
            'is_correct' => $answer ? (int) $answer->is_correct : null,
            ...$this->dailyQuestions->statsFor($user),
        ];
    }
}
