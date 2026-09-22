<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Support\Locales;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['password'] ?? null)) {
            $data['password'] = (string) config('customers.default_password');
        }

        $data['locale'] ??= Locales::default();
        $data['theme'] ??= 'system';

        return $data;
    }
}
