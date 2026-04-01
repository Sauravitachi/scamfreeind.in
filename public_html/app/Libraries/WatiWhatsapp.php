<?php

namespace App\Libraries;

use App\Enums\ContentType;
use App\Enums\WatiTemplateName;
use App\Models\WhatsappMessageLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class WatiWhatsapp
 *
 * Handles sending WhatsApp messages via WATI API using predefined templates.
 */
class WatiWhatsapp
{
    // protected string $baseUrl = 'https://live-mt-server.wati.io/322386/api/v1/';

    protected string $endpoint = 'sendTemplateMessage';

    protected ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('settings.wati_api_key');
    }

    public function send(
        string $phone,
        WatiTemplateName $templateName,
        array $parameters = [],
        ?Model $recipient = null
    ): array {
        try {
            $payload = $this->buildPayload(
                templateName: $templateName,
                parameters: $parameters,
            );

            $response = Http::withHeaders($this->getHeaders())
                ->post($this->getUrl($phone), $payload);

            if (! $response->successful()) {
                Log::error('Wati Error', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
            }

            $this->log(whatsappNumber: $phone, watiTemplateName: $templateName, payload: $payload, response: $response, recipient: $recipient);

            return (array) $response->json();

        } catch (Throwable $e) {
            Log::error('Wati Exception', ['error' => $e->getMessage()]);

            return $this->errorResponse($e);
        }
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => ContentType::JSON->value,
        ];
    }

    protected function getUrl(string $phone): string
    {
        return "{$this->baseUrl}{$this->endpoint}?whatsappNumber={$phone}";
    }

    protected function buildPayload(
        WatiTemplateName $templateName,
        array $parameters
    ): array {
        return [
            'template_name' => $templateName->value,
            'broadcast_name' => $templateName->broadcastName(),
            'parameters' => $this->transformParameters($parameters),
        ];
    }

    protected function transformParameters(array $parameters): array
    {
        return collect($parameters)
            ->map(fn ($value, $key) => ['name' => $key, 'value' => $value])
            ->values()
            ->toArray();
    }

    protected function errorResponse(Throwable $e): array
    {
        return [
            'error' => true,
            'message' => $e->getMessage(),
        ];
    }

    protected function log(string $whatsappNumber, WatiTemplateName $watiTemplateName, array $payload, Response $response, ?Model $recipient = null): WhatsappMessageLog
    {
        $log = new WhatsappMessageLog([
            'whatsapp_number' => $whatsappNumber,
            'template_name' => $watiTemplateName->value,
            'broadcast_name' => $watiTemplateName->broadcastName(),
            'payload' => $payload,
            'response' => $response->json(),
            'response_status_code' => $response->getStatusCode(),
        ]);

        if ($recipient) {
            $log->recipient()->associate($recipient);
        }

        $log->save();

        return $log;
    }
}
