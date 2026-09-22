<?php

namespace App\Filament\Admin\Resources\CustomerResource\Pages;

use App\Filament\Admin\Resources\CustomerResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\HasCustomerMapLocation;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    use HasAdminFormLayout;
    use HasCustomerMapLocation;
    use RedirectsToListAfterCreate;

    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->extractCustomerMap($data);
        $data['sort_order'] = (int) Customer::query()->max('sort_order') + 1;
        $data['password'] = $data['password'] ?? config('customers.default_password');

        return $data;
    }
}
