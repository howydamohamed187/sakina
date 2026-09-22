<?php

namespace App\Lib;

use App\Settings\ThirdPartySettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Firebase
{
    private array $tokens = [];

    private array $moreData = [];

    private string $title = '';

    private string $body = '';

    public static function make(): self
    {
        return new self;
    }

    public function send(): void
    {
        $this->do();
    }

    public function do(): void
    {
        $settings = app(ThirdPartySettings::class);
        $projectName = $settings->project_name;
        $accessToken = $this->getAccessToken();

        if (blank($projectName) || blank($accessToken) || $this->getTokens() === []) {
            return;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectName}/messages:send";

        foreach ($this->getTokens() as $token) {
            if (blank($token)) {
                continue;
            }

            $data = $this->getFields();
            $data['message']['token'] = $token;

            Http::withToken($accessToken)->post($url, $data);
        }
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setTokens(array $tokens): self
    {
        $this->tokens = array_values(array_filter($tokens));

        return $this;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setMoreData(array $moreData): self
    {
        $this->moreData = $moreData;

        return $this;
    }

    public function getMoreData(): array
    {
        return $this->moreData;
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

        if ($this->getMoreData() !== []) {
            $fields['message']['data'] = array_map('strval', $this->getMoreData());
        }

        return $fields;
    }

    private function getAccessToken(): ?string
    {
        $path = $this->credentialsPath();

        if (! $path || ! is_readable($path)) {
            return null;
        }

        try {
            $credentials = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($credentials) || blank($credentials['private_key'] ?? null) || blank($credentials['client_email'] ?? null)) {
            return null;
        }

        $now = time();
        $header = $this->base64UrlEncode((string) json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));
        $claims = $this->base64UrlEncode((string) json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $unsigned = $header.'.'.$claims;

        if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $unsigned.'.'.$this->base64UrlEncode($signature),
        ]);

        $token = $response->json('access_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function credentialsPath(): ?string
    {
        $file = app(ThirdPartySettings::class)->firebase_file;

        if (blank($file)) {
            return null;
        }

        $path = Storage::disk('local')->path($file);

        return is_file($path) ? $path : null;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
