<?php

namespace App\Http\Controllers\Api;

use App\Support\Locales;
use App\Support\StoredSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends ApiController
{
    public function show(): JsonResponse
    {
        return $this->success(
            StoredSettings::publicGeneral(app()->getLocale()),
            __('api.app_config')
        );
    }

    
}
