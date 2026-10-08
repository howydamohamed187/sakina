<?php

namespace App\Http\Controllers\Api;

use App\Services\Providers\ProviderException;
use App\Services\Zakat\ZakatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ZakatController extends ApiController
{
    public function __construct(private readonly ZakatService $zakat) {}

    public function nisab(Request $request): JsonResponse
    {
        $data = Validator::make([
            'currency' => $this->upper($request->query('currency')),
            'country' => $this->upper($request->query('country')),
        ], [
            'currency' => ['nullable', 'string', Rule::in(ZakatService::currencies())],
            'country' => ['nullable', 'string', 'size:2', Rule::in(array_keys((array) config('zakat.country_currencies')))],
        ])->validate();

        if (! $this->zakat->isActive()) {
            return $this->error(__('api.zakat.disabled'), [], 503);
        }

        $currency = $this->zakat->resolveCurrency($data['currency'] ?? null, $data['country'] ?? null);

        try {
            $nisab = $this->zakat->nisab($currency);
        } catch (ProviderException $e) {
            return $this->providerError($e);
        }

        return $this->success([
            ...$nisab,
            'currency_symbol' => $this->symbol($currency),
        ], __('api.zakat.nisab_ready'));
    }

    public function calculate(Request $request): JsonResponse
    {
        $data = Validator::make([
            'amount' => $request->input('amount'),
            'currency' => $this->upper($request->input('currency')),
        ], [
            'amount' => ['required', 'numeric', 'min:0', 'max:'.config('zakat.max_amount'), 'regex:/^\d+(\.\d{1,6})?$/'],
            'currency' => ['required', 'string', Rule::in(ZakatService::currencies())],
        ])->validate();

        if (! $this->zakat->isActive()) {
            return $this->error(__('api.zakat.disabled'), [], 503);
        }

        try {
            $result = $this->zakat->calculate($data['amount'], $data['currency']);
        } catch (ProviderException $e) {
            return $this->providerError($e);
        }

        return $this->success([
            ...$result,
            'currency_symbol' => $this->symbol($data['currency']),
        ], __($result['is_zakat_due'] ? 'api.zakat.due' : 'api.zakat.not_due'));
    }

    private function providerError(ProviderException $e): JsonResponse
    {
        $message = $e->service
            ? __('api.zakat.errors.'.$e->service)
            : $e->userMessage();

        return $this->error($message, [], $e->status());
    }

    private function symbol(string $currency): string
    {
        $key = 'api.zakat.currency_symbols.'.$currency;

        return trans()->has($key) ? __($key) : $currency;
    }

    private function upper(mixed $value): mixed
    {
        return is_string($value) && $value !== '' ? strtoupper(trim($value)) : $value;
    }
}
