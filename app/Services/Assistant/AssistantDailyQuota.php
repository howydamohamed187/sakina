<?php

namespace App\Services\Assistant;

use App\Models\AiDailyMessageQuota;
use App\Models\AiMessage;
use App\Models\Customer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Counts successful customer messages across every conversation for the current
 * app-timezone day, and reserves a slot before the provider is called.
 */
class AssistantDailyQuota
{
    /**
     * @return array{daily_limit: int, used_messages: int, remaining_messages: int}
     */
    public function status(Customer $customer): array
    {
        $limit = $this->limit();
        $used = $this->used($customer);

        return [
            'daily_limit' => $limit,
            'used_messages' => $used,
            'remaining_messages' => max(0, $limit - $used),
        ];
    }

    public function reserve(Customer $customer): void
    {
        DB::transaction(function () use ($customer): void {
            $row = $this->lockedRow($customer);
            $used = $this->used($customer);
            $limit = $this->limit();

            if ($used + $row->reserved >= $limit) {
                throw new DailyMessageLimitException([
                    'daily_limit' => $limit,
                    'used_messages' => $used,
                    'remaining_messages' => max(0, $limit - $used - $row->reserved),
                ]);
            }

            $row->increment('reserved');
        });
    }

    public function commit(Customer $customer): void
    {
        $this->releaseReserved($customer);
    }

    public function release(Customer $customer): void
    {
        $this->releaseReserved($customer);
    }

    public function limit(): int
    {
        return max(1, (int) config('assistant.daily_message_limit', 5));
    }

    private function releaseReserved(Customer $customer): void
    {
        DB::transaction(function () use ($customer): void {
            $row = $this->lockedRow($customer);

            if ($row->reserved > 0) {
                $row->decrement('reserved');
            }
        });
    }

    private function lockedRow(Customer $customer): AiDailyMessageQuota
    {
        $date = $this->today();

        $row = AiDailyMessageQuota::query()
            ->where('customer_id', $customer->id)
            ->whereDate('usage_date', $date)
            ->lockForUpdate()
            ->first();

        if ($row instanceof AiDailyMessageQuota) {
            return $row;
        }

        try {
            AiDailyMessageQuota::query()->create([
                'customer_id' => $customer->id,
                'usage_date' => $date,
                'reserved' => 0,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Another request created today's row first.
        }

        return AiDailyMessageQuota::query()
            ->where('customer_id', $customer->id)
            ->whereDate('usage_date', $date)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function used(Customer $customer): int
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        return AiMessage::query()
            ->where('role', AiMessage::ROLE_USER)
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('conversation', fn ($query) => $query->where('customer_id', $customer->id))
            ->count();
    }

    private function today(): string
    {
        return now()->timezone((string) config('app.timezone'))->toDateString();
    }
}
