<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TinVerificationController
 *
 * Server-side proxy for the TRA (Tanzania Revenue Authority) VAT Verification
 * API. The browser sends a TIN number via AJAX; this controller calls the TRA
 * endpoint server-side and returns a normalised JSON response.
 *
 * Endpoint:  GET {TRA_API_BASE_URL}/api/CheckVatStatusFull/{tin}
 *
 * .env keys:
 *   TRA_API_BASE_URL  – Full base URL supplied by TRA, e.g. https://196.x.x.x:8090
 *   TRA_API_KEY       – Bearer token supplied by TRA (leave blank if not required)
 *   TRA_API_TIMEOUT   – HTTP timeout in seconds (default 35 – TRA servers can be slow)
 *   TRA_DEV_MODE      – Set to "true" in local development to return mock data
 *                       without needing a live TRA server connection
 */
class TinVerificationController extends Controller
{
    /**
     * Verify a TIN number against the TRA VAT API.
     *
     * POST /landlord/tenants/verify-tin
     *
     * Request  (JSON): { "tin": "100-200-300" }
     * Success  (200):  { "success": true,  "data": { ... } }
     * Failure  (4xx/503): { "success": false, "message": "..." }
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'tin' => 'required|string|max:50',
        ]);

        $tin     = trim($request->input('tin'));
        $baseUrl = rtrim(config('services.tra.base_url'), '/');
        $apiKey  = config('services.tra.api_key');
        $timeout = max(5, (int) config('services.tra.timeout', 35));
        $devMode = filter_var(config('services.tra.dev_mode'), FILTER_VALIDATE_BOOLEAN);

        // ── Dev / mock mode ──────────────────────────────────────────
        // When TRA_DEV_MODE=true the controller returns realistic fake data so
        // the full add-tenant flow can be tested locally without a TRA server.
        if ($devMode) {
            return $this->mockResponse($tin);
        }

        // ── Guard: API not yet configured ────────────────────────────
        if (empty($baseUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'The TRA verification service is not yet configured on this server. '
                           . 'Use "Enter Manually" to add the tenant, or contact your system administrator to set up the TRA API connection.',
            ], 503);
        }

        // ── Live TRA call ────────────────────────────────────────────
        try {
            $headers = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ];

            // TRA supplies a Bearer token alongside the server address.
            // Add it only when configured — some internal TRA deployments
            // do not require token authentication on the VAT status endpoints.
            if (filled($apiKey)) {
                $headers['Authorization'] = 'Bearer ' . $apiKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout($timeout)
                ->withoutVerifying()    // TRA servers commonly use self-signed certificates
                ->get("{$baseUrl}/api/CheckVatStatusFull/{$tin}");

            // 404 → TIN not registered with TRA
            if ($response->status() === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taxpayer not found. The TIN number is not registered in the TRA system. '
                               . 'Please double-check the number and try again.',
                ], 422);
            }

            // 401 / 403 → bad or missing API key
            if (in_array($response->status(), [401, 403])) {
                Log::error('TRA API authentication failure', [
                    'tin'    => $tin,
                    'status' => $response->status(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication with the TRA service failed. '
                               . 'Please check the TRA_API_KEY in your .env file.',
                ], 503);
            }

            if (!$response->successful()) {
                Log::warning('TRA API non-success response', [
                    'tin'    => $tin,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'The TRA service returned an unexpected response (HTTP '
                               . $response->status() . '). Please try again in a moment.',
                ], 422);
            }

            $data = $response->json();

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The TRA service returned an empty response. The TIN may not exist.',
                ], 422);
            }

            // Normalise camelCase TRA field names to snake_case for the frontend
            return response()->json([
                'success' => true,
                'data'    => [
                    'taxpayer_id'   => $data['taxpayerId']   ?? null,
                    'taxpayer_name' => $data['taxpayerName'] ?? null,
                    'first_name'    => $data['firstName']    ?? null,
                    'middle_name'   => $data['middleName']   ?? null,
                    'last_name'     => $data['lastName']     ?? null,
                    'has_vat'       => $data['hasVAT']       ?? false,
                    'vrn'           => $data['vrn']          ?? null,
                    'trading_name'  => $data['tradingName']  ?? null,
                ],
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('TRA API connection error', ['tin' => $tin, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Could not reach the TRA verification server at the configured address. '
                           . 'This usually means the TRA server IP/port is unreachable from this machine. '
                           . 'Check your network connection or firewall rules, or use "Enter Manually" to continue.',
            ], 503);

        } catch (\Exception $e) {
            Log::error('TRA API unexpected error', ['tin' => $tin, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during TIN verification. Please try again later.',
            ], 503);
        }
    }

    /**
     * Return realistic mock data for local development.
     * Activated by setting TRA_DEV_MODE=true in .env.
     */
    private function mockResponse(string $tin): JsonResponse
    {
        // Simulate a short network delay so the UI loading state is visible
        usleep(600_000); // 0.6 s

        // Return a 404-like "not found" for a specific test TIN so
        // developers can also test the failure path.
        if ($tin === '000-000-000') {
            return response()->json([
                'success' => false,
                'message' => '[DEV MODE] Taxpayer not found. Use any other TIN for a success response.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'taxpayer_id'   => 99999,
                'taxpayer_name' => 'JUMA HASSAN MWALIMU',
                'first_name'    => 'JUMA',
                'middle_name'   => 'HASSAN',
                'last_name'     => 'MWALIMU',
                'has_vat'       => true,
                'vrn'           => '40-' . strtoupper(substr(md5($tin), 0, 6)) . '-A',
                'trading_name'  => 'MWALIMU ENTERPRISES',
            ],
        ]);
    }
}
