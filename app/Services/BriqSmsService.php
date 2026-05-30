<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class BriqSmsService
{
    private string $apiKey;
    private string $baseUrl;
    private string $senderId;
    private string $bearerToken;

    public function __construct()
    {
        $this->apiKey   = config('services.briq.api_key', '');
        $this->baseUrl  = rtrim(config('services.briq.base_url', 'https://karibu.briq.tz'), '/');
        $this->senderId = config('services.briq.sender_id', 'BRIQ');
        $this->bearerToken = config('services.briq.bearer_token', '');
    }

    /**
     * Send an SMS via BRIQ.
     * Returns ['success' => true] or ['success' => false, 'error' => string].
     */
    public function send(string $phone, string $message): array
    {
        if (empty($this->apiKey)) {
            Log::warning('BRIQ SMS: API key not set in environment.');
            return ['success' => false, 'error' => 'SMS service is not configured.'];
        }

        $result = $this->normalizePhone($phone);

        if ($result['valid'] === false) {
            return ['success' => false, 'error' => $result['error']];
        }

        $normalized = $result['number'];
        $apiRecipient = ltrim($normalized, '+');
        $endpoint = $this->baseUrl . '/v1/message/send-instant';

        Log::info('BRIQ SMS send attempt', [
            'endpoint' => $endpoint,
            'recipient' => $normalized,
            'sender_id' => $this->senderId,
        ]);

        $headers = [
            'X-API-Key'    => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if (!empty($this->bearerToken)) {
            $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->post($endpoint, [
                    'content'    => $message,
                    'recipients' => [$apiRecipient],
                    'sender_id'  => $this->senderId,
                ]);
        } catch (ConnectionException $exception) {
            Log::error('BRIQ SMS connection failed', [
                'endpoint' => $endpoint,
                'recipient' => $normalized,
                'error' => $exception->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Could not connect to the SMS provider.'];
        }

        if (!$response->successful()) {
            // Never log the API key — log only status and sanitised recipient
            Log::error('BRIQ SMS send failed', [
                'http_status' => $response->status(),
                'recipient'   => $normalized,
                'response'    => $this->summarizeResponse($response->json() ?? $response->body()),
            ]);
            return ['success' => false, 'error' => 'SMS delivery failed (HTTP ' . $response->status() . ').'];
        }

        $data = $response->json();

        Log::info('BRIQ SMS accepted', [
            'http_status' => $response->status(),
            'recipient' => $normalized,
            'job_id' => data_get($data, 'job_id') ?? data_get($data, 'data.job_id'),
            'response' => $this->summarizeResponse($data ?? $response->body()),
        ]);

        return ['success' => true, 'data' => $data];
    }

    /**
     * Normalise and validate a Tanzanian mobile number to E.164 (+255XXXXXXXXX).
     *
     * Tanzania mobile numbers (after +255) are exactly 9 digits and begin
     * with 6 or 7.  Accepted input formats:
     *   07XXXXXXXX   – 10 digits, leading zero      (e.g. 0712 345 678)
     *   7XXXXXXXX    – 9 digits, no prefix           (e.g. 712345678)
     *   2557XXXXXXXX – 12 digits, country code only  (e.g. 255712345678)
     *  +2557XXXXXXXX – 12 digits, full E.164          (e.g. +255712345678)
     *
     * Returns ['valid' => true, 'number' => '+255...']
     *      or ['valid' => false, 'error'  => '<reason>']
     */
    private function normalizePhone(string $phone): array
    {
        $digits = preg_replace('/\D/', '', $phone);

        // Resolve to the raw 9-digit subscriber portion
        if (strlen($digits) === 9) {
            $subscriber = $digits;
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $subscriber = substr($digits, 1);
        } elseif (strlen($digits) === 12 && str_starts_with($digits, '255')) {
            $subscriber = substr($digits, 3);
        } else {
            return [
                'valid' => false,
                'error' => 'Phone number must be a valid Tanzanian mobile number '
                         . '(e.g. 0712 345 678 or +255 712 345 678).',
            ];
        }

        // Tanzanian mobile: 9 digits starting with 6 or 7
        if (!preg_match('/^[67]\d{8}$/', $subscriber)) {
            return [
                'valid' => false,
                'error' => 'Phone number is not a valid Tanzanian mobile number. '
                         . 'Numbers must start with 06X or 07X and be 10 digits long.',
            ];
        }

        return ['valid' => true, 'number' => '+255' . $subscriber];
    }

    private function summarizeResponse(mixed $response): mixed
    {
        if (is_string($response)) {
            return str($response)->limit(500)->toString();
        }

        return $response;
    }
}
