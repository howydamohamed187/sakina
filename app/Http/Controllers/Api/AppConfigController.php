<?php

namespace App\Http\Controllers\Api;

use App\Settings\GeneralSettings;
use App\Support\StoredSettings;
use Illuminate\Http\JsonResponse;

class AppConfigController extends ApiController
{
    public function show(): JsonResponse
    {
        $general = StoredSettings::general();
        $defaults = GeneralSettings::defaults();
        $names = is_array($general?->app_name) ? $general->app_name : $defaults['app_name'];

        return $this->success([
            'app_name' => StoredSettings::appName(),
            'app_names' => [
                'ar' => $names['ar'] ?? 'سكينة',
                'en' => $names['en'] ?? 'Sakina',
            ],
            'app_logo' => StoredSettings::logoUrl() ?? '',
            'fav_icon' => StoredSettings::faviconUrl() ?? '',
            'app_email' => $general?->app_email,
            'app_whatsapp' => $general?->app_whatsapp,
            'app_address' => $general?->app_address,
            'app_latitude' => $general?->app_latitude,
            'app_longitude' => $general?->app_longitude,
            'applications_links' => $general?->applications_links ?: $defaults['applications_links'],
            'font_family' => StoredSettings::fontFamily(),
            'font_size' => StoredSettings::fontSize(),
        ], __('api.app_config'));
    }
}
