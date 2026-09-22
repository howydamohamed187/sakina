<?php

namespace App\Filament\Concerns;

use App\Models\ContactType;
use App\Support\ContactTypes;

trait SyncsContactValue
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $kind = $this->contactKind($data);
        $value = $data['value'] ?? null;

        $data['link_value'] = $kind === ContactTypes::LINK ? $value : null;
        $data['account_value'] = $kind === ContactTypes::ACCOUNT ? $value : null;
        $data['phone_value'] = ContactTypes::isPhone($kind) ? $value : null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeContactValue(array $data): array
    {
        $kind = $this->contactKind($data);
        $data['type'] = $kind;

        $data['value'] = match ($kind) {
            ContactTypes::LINK => $data['link_value'] ?? null,
            ContactTypes::ACCOUNT => $data['account_value'] ?? null,
            default => $data['phone_value'] ?? null,
        };

        unset($data['link_value'], $data['account_value'], $data['phone_value']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function contactKind(array $data): ?string
    {
        $typeId = $data['contact_type_id'] ?? null;

        if ($typeId) {
            return ContactType::query()->find($typeId)?->kind;
        }

        return $data['type'] ?? null;
    }
}
