<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class NmbPaymentService
{
    private string     $baseUrl;
    private string     $clientUsr;
    private string     $clientKey;
    private string     $systemName;
    private string     $systemCode;
    private int        $timeout;
    private string|bool $sslVerify; // true = system CA, string = explicit path

    private const CACHE_KEY     = 'nmb_spg_token';
    private const CACHE_MINUTES = 14; // NMB tokens typically valid 15 min

    public function __construct()
    {
        $this->baseUrl    = rtrim(config('services.nmb.base_url', 'https://xyz.spg.co.tz'), '/');
        $this->clientUsr  = config('services.nmb.client_usr', '');
        $this->clientKey  = config('services.nmb.client_key', '');
        $this->systemName = config('services.nmb.system_name', 'REMIS');
        $this->systemCode = config('services.nmb.system_code', 'SP1001');
        $this->timeout    = (int) config('services.nmb.timeout', 20);

        // Use explicit CA bundle path if configured; otherwise let Guzzle/curl
        // fall back to curl.cainfo in php.ini (the system bundle).
        $bundle           = config('services.nmb.ca_bundle');
        $this->sslVerify  = ($bundle && file_exists($bundle)) ? $bundle : true;
    }

    // ─────────────────────── AUTH ────────────────────────────────

    /**
     * Retrieve a bearer token, cached for 14 min.
     * Returns ['token' => '...'] or ['error' => '...'].
     */
    public function getToken(bool $forceRefresh = false): array
    {
        if (! $forceRefresh && Cache::has(self::CACHE_KEY)) {
            return ['token' => Cache::get(self::CACHE_KEY)];
        }

        if (empty($this->clientUsr) || empty($this->clientKey)) {
            return ['error' => 'NMB SPG credentials are not configured (NMB_SPG_USER / NMB_SPG_KEY missing).'];
        }

        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->withOptions(['verify' => $this->sslVerify])
            ->timeout($this->timeout)
            ->post($this->baseUrl . '/api/v1/login', [
                'client_usr' => $this->clientUsr,
                'client_key' => $this->clientKey,
            ]);
        } catch (ConnectionException $e) {
            Log::error('NMB SPG login: connection failed', ['error' => $e->getMessage(), 'url' => $this->baseUrl]);
            return ['error' => 'Could not connect to NMB payment gateway. Check NMB_SPG_URL in .env.'];
        }

        if (! $response->successful()) {
            Log::error('NMB SPG login: HTTP error', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            return ['error' => 'NMB gateway authentication failed (HTTP ' . $response->status() . '). Check credentials.'];
        }

        $token = $response->json('token');

        if (empty($token)) {
            Log::error('NMB SPG login: no token in response', ['body' => substr($response->body(), 0, 500)]);
            return ['error' => 'NMB gateway returned no token. Verify NMB_SPG_USER and NMB_SPG_KEY.'];
        }

        Cache::put(self::CACHE_KEY, $token, now()->addMinutes(self::CACHE_MINUTES));
        Log::info('NMB SPG: token refreshed');

        return ['token' => $token];
    }

    // ─────────────────────── CONTROL NUMBER ──────────────────────

    /**
     * Generate a NMB control number for a payment.
     * Returns ['control_number' => '...', 'data' => [...]] or ['error' => '...'].
     */
    public function generateControlNumber(Payment $payment, bool $demo = false): array
    {
        $payment->loadMissing(['tenant', 'lease.property', 'lease.unit']);
        $tenant = $payment->tenant;
        $lease  = $payment->lease;

        if (! $tenant || ! $lease) {
            return ['error' => 'Payment is missing tenant or lease information.'];
        }

        $nameParts    = explode(' ', trim($tenant->name), 2);
        $firstName    = $nameParts[0];
        $lastName     = $nameParts[1] ?? $nameParts[0];
        $propertyName = optional($lease->property)->name ?? 'Property';
        $unitNumber   = optional($lease->unit)->unit_number;
        $desc         = 'RENT payment – ' . $propertyName . ($unitNumber ? ' / ' . $unitNumber : '');

        $payload = [
            'systemName'  => $this->systemName,
            'systemCode'  => $this->systemCode,
            'payerID'     => 'TENANT-' . $tenant->id,
            'firstName'   => $firstName,
            'lastName'    => $lastName,
            'email'       => $tenant->email,
            'payerMobile' => $this->normalizeMobile($tenant->phone ?? ''),
            'currency'    => 'TZS',
            'amount'      => (float) $payment->amount,
            'amountType'  => 'EXACT',
            'paymentType' => 'RENT',
            'paymentDesc' => $desc,
        ];

        $endpoint = $demo ? '/api/v1/generatedemoctlno' : '/api/v1/generatectlno';

        return $this->postWithAuth($endpoint, $payload, function ($data) use ($payment) {
            $status        = strtolower($data['status'] ?? '');
            $controlNumber = $data['reference_number'] ?? null;

            if ($status !== 'success' || empty($controlNumber)) {
                Log::error('NMB SPG: unexpected generatectlno response', [
                    'payment_id' => $payment->id,
                    'data'       => $data,
                ]);
                return ['error' => 'NMB gateway returned an unexpected response: ' . ($data['status'] ?? 'unknown')];
            }

            Log::info('NMB SPG: control number generated', [
                'payment_id'     => $payment->id,
                'control_number' => $controlNumber,
            ]);

            return ['control_number' => $controlNumber, 'data' => $data];
        });
    }

    // ─────────────────────── VERIFY ──────────────────────────────

    /**
     * Verify a control number.
     * Returns ['data' => [...]] or ['error' => '...'].
     */
    public function verifyControlNumber(string $controlNumber, bool $demo = false): array
    {
        $endpoint = $demo ? '/api/v1/demoverification' : '/api/v1/verification';

        $payload = [
            'body' => [
                'paymentReference' => $controlNumber,
                'transactionId'    => 'REMIS-' . time(),
            ],
            'reservedFields' => [],
        ];

        return $this->postWithAuth($endpoint, $payload, function ($data) {
            $code = (int) ($data['statusCode'] ?? 0);

            if ($code !== 600) {
                return ['error' => 'Verification error: ' . ($data['message'] ?? 'unknown')];
            }

            return ['data' => $data['body'] ?? $data];
        });
    }

    // ─────────────────────── GET PAYMENT INFO ────────────────────

    /**
     * Fetch full payment info for a control number.
     * Returns ['data' => [...], 'paid' => bool, 'transactions' => [...], 'latest_tx' => [...]]
     *      or ['error' => '...'].
     */
    public function getPaymentInfo(string $controlNumber, bool $demo = false): array
    {
        $endpoint = $demo ? '/api/v1/getdemopayment' : '/api/v1/getpayment';

        return $this->postWithAuth($endpoint, ['paymentReference' => $controlNumber], function ($data) {
            $code = (int) ($data['statusCode'] ?? 0);

            if ($code !== 600) {
                return ['error' => 'Payment info error: ' . ($data['statusDesc'] ?? $data['message'] ?? 'unknown')];
            }

            $inner        = $data['data'] ?? [];
            $paid         = isset($inner['paid']) && $inner['paid'] === true;
            $transactions = $inner['transactions'] ?? [];
            $latestTx     = ! empty($transactions) ? end($transactions) : null;

            return [
                'data'         => $inner,
                'paid'         => $paid,
                'transactions' => $transactions,
                'latest_tx'    => $latestTx,
            ];
        });
    }

    // ─────────────────────── SHARED REQUEST HELPER ───────────────

    /**
     * POST to a protected endpoint with Bearer auth.
     * Retries once with a fresh token on HTTP 401.
     *
     * @param  callable $handler  Receives decoded JSON array, returns result array
     */
    private function postWithAuth(string $endpoint, array $payload, callable $handler): array
    {
        foreach ([false, true] as $retry) {
            $auth = $this->getToken(forceRefresh: $retry);

            if (isset($auth['error'])) {
                return $auth;
            }

            try {
                $response = Http::withHeaders([
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $auth['token'],
                ])
                ->withOptions(['verify' => $this->sslVerify])
                ->timeout($this->timeout)
                ->post($this->baseUrl . $endpoint, $payload);
            } catch (ConnectionException $e) {
                Log::error('NMB SPG: connection failed', [
                    'endpoint' => $endpoint,
                    'error'    => $e->getMessage(),
                ]);
                return ['error' => 'Could not connect to NMB payment gateway. Please try again.'];
            }

            // Token expired — clear cache and retry once
            if ($response->status() === 401 && ! $retry) {
                Cache::forget(self::CACHE_KEY);
                Log::warning('NMB SPG: 401 received, refreshing token and retrying', ['endpoint' => $endpoint]);
                continue;
            }

            if (! $response->successful()) {
                Log::error('NMB SPG: HTTP error', [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'body'     => substr($response->body(), 0, 500),
                ]);
                return ['error' => 'NMB gateway error (HTTP ' . $response->status() . '): ' . $this->extractMessage($response)];
            }

            $data = $response->json();

            if (! is_array($data)) {
                Log::error('NMB SPG: non-JSON response', [
                    'endpoint' => $endpoint,
                    'body'     => substr($response->body(), 0, 500),
                ]);
                return ['error' => 'NMB gateway returned an unexpected (non-JSON) response.'];
            }

            return $handler($data);
        }

        return ['error' => 'NMB gateway authentication failed after token refresh.'];
    }

    // ─────────────────────── HELPERS ─────────────────────────────

    /**
     * Normalise any Tanzanian mobile number to 255XXXXXXXXX (no plus).
     */
    private function normalizeMobile(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 9 && preg_match('/^[67]/', $digits)) {
            return '255' . $digits;
        }
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return '255' . substr($digits, 1);
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '255')) {
            return $digits;
        }
        // Fallback — keeps payerMobile field non-empty for API validation
        return '255000000000';
    }

    private function extractMessage($response): string
    {
        $json = $response->json();
        if (is_array($json)) {
            return $json['message'] ?? $json['error'] ?? substr($response->body(), 0, 200);
        }
        return substr((string) $response->body(), 0, 200);
    }
}
