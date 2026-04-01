<?php

namespace App\Libraries;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Sms
{
    protected string $apiEndpoint = 'https://control.msg91.com/api/v5/otp';

    protected string $authKey;

    protected ?string $templateId = null;

    protected array $postParams = [];

    protected array $queryParams = [];

    protected bool $fake = false;

    public function __construct(?string $authKey = null)
    {
        $this->authKey = $authKey ?? config('settings.msg91_auth_key');

        if (empty($this->authKey)) {
            throw new InvalidArgumentException('Auth key is required for SMS service.');
        }
    }

    public static function make(?string $authKey = null): static
    {
        return new static($authKey);
    }

    public function endpoint(string $url): static
    {
        $this->apiEndpoint = $url;

        return $this;
    }

    public function template(string $templateId): static
    {
        $this->templateId = $templateId;

        return $this;
    }

    public function withPostParams(array $params): static
    {
        $this->postParams = $params;

        return $this;
    }

    public function withQueryParams(array $params): static
    {
        $this->queryParams = $params;

        return $this;
    }

    public function send(string $phone): Response
    {
        if (empty($this->templateId)) {
            throw new InvalidArgumentException('Template ID is required to send an SMS.');
        }

        $queryParams = http_build_query([
            'mobile' => $phone,
            'template_id' => $this->templateId,
            'authkey' => $this->authKey,
            ...$this->queryParams,
        ]);

        $url = "{$this->apiEndpoint}?{$queryParams}";

        if ($this->fake) {
            Http::fake(function ($request) {
                return Http::response([
                    'request_id' => md5(Str::random()),
                    'type' => 'success',
                    'fake' => true,
                ], 200);
            });
            $response = Http::post($url, $this->postParams);
        } else {
            $response = Http::post($url, $this->postParams);
        }

        if (! $response->successful()) {
            throw new \RuntimeException("SMS API error: {$response->body()}");
        }

        return $response;
    }
}
