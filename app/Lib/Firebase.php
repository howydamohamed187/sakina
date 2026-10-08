<?php

namespace App\Lib;

use App\Models\Platform;
use App\Settings\ThirdPartySettings;
use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Firebase
{
    private array $headers = [];

    private array $tokens = [];

    private array $moreData = [];

    private string $authorizationKey = '';

    private int $type = 1;

    private string $title = '';

    private string $body = '';

    private bool $isSent = false;

    public function __destruct()
    {
        if (! $this->isSent) {
            $this->do();
        }
    }

    public static function make(): self
    {
        return new self;
    }

    public function do(): void
    {
        $config = $this->resolveFcmConfig();
        $projectName = $config['project_id'] ?? null;

        if (blank($projectName) || ! ($config['is_enabled'] ?? true)) {
            $this->isSent = true;

            return;
        }

        $accessToken = $this->getAccessToken($config);

        if (blank($accessToken)) {
            $this->isSent = true;

            return;
        }

        foreach ($this->getTokens() as $token) {
            $data = $this->getFields();
            $data['message']['token'] = $token;
            $url = "https://fcm.googleapis.com/v1/projects/{$projectName}/messages:send";
            $response = Http::withToken($accessToken)->post($url, $data);
            Log::info('firebase_response', $response->json() ?? []);
        }

        $this->isSent = true;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function getAccessToken(array $config = []): ?string
    {
        $config = $config !== [] ? $config : $this->resolveFcmConfig();
        $relativePath = $config['firebase_file'] ?? null;

        if (blank($relativePath)) {
            return null;
        }

        $credentialsPath = public_path('storage/'.$relativePath);

        if (! is_file($credentialsPath)) {
            $credentialsPath = Storage::disk('local')->path($relativePath);
        }

        if (! is_file($credentialsPath)) {
            return null;
        }

        $client = new Client;
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        return $token['access_token'] ?? null;
    }

    /**
     * @return array{project_id?: string|null, firebase_file?: string|null, is_enabled?: bool, is_test_mode?: bool}
     */
    private function resolveFcmConfig(): array
    {
        $platform = $this->currentPlatform();

        if ($platform instanceof Platform && is_array($platform->fcm_config ?? null) && filled($platform->fcm_config['project_id'] ?? null)) {
            return $platform->fcm_config;
        }

        $settings = app(ThirdPartySettings::class);

        return [
            'project_id' => $settings->project_name,
            'firebase_file' => $settings->firebase_file,
            'is_enabled' => filled($settings->project_name) || filled($settings->firebase_file),
            'is_test_mode' => (bool) $settings->is_test,
        ];
    }

    private function currentPlatform(): ?Platform
    {
        if (! function_exists('current_platform')) {
            return Platform::query()->first();
        }

        $platform = current_platform();

        return $platform instanceof Platform ? $platform : null;
    }

    /**
     * @param  mixed  $authorizationKey
     */
    public function setAuthorizationKey($authorizationKey): Firebase
    {
        $this->authorizationKey = $authorizationKey;

        return $this;
    }

    public function getAuthorizationKey(): string
    {
        return $this->authorizationKey;
    }

    public function setType(int $type): Firebase
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setTitle(string $title): Firebase
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setBody(string $body): Firebase
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setTokens(array $tokens): Firebase
    {
        $this->tokens = $tokens;

        return $this;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setMoreData(array $moreData): Firebase
    {
        $this->moreData = $moreData;

        return $this;
    }

    public function getFields(): array
    {
        $fields = [
            'message' => [
                'notification' => [
                    'title' => $this->getTitle(),
                    'body' => $this->getBody(),
                ],
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'default_vibrate_timings' => true,
                        'default_sound' => true,
                    ],
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                ],
            ],

        ];
        if (count($this->getMoreData())) {
            $fields['message']['data'] = [...$fields['message']['data'] ?? [], ...$this->getMoreData()];
        }

        return $fields;
    }

    public function getMoreData(): array
    {
        return $this->moreData;
    }
}
