<?php

namespace Tests\Feature;

use App\Services\BriqSmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BriqSmsServiceTest extends TestCase
{
    // ─── helpers ────────────────────────────────────────────────────────────

    private function configureService(string $key = 'test-key'): void
    {
        config([
            'services.briq.api_key'      => $key,
            'services.briq.base_url'     => 'https://karibu.briq.tz',
            'services.briq.sender_id'    => 'BRIQ',
            'services.briq.bearer_token' => '',
        ]);
    }

    private function fakeSuccess(): void
    {
        Http::fake(['karibu.briq.tz/*' => Http::response(['status' => 'success'], 200)]);
    }

    // ─── config ─────────────────────────────────────────────────────────────

    public function test_service_instantiates_with_env_config(): void
    {
        $this->configureService();
        $this->assertInstanceOf(BriqSmsService::class, new BriqSmsService());
    }

    public function test_send_returns_failure_when_api_key_is_empty(): void
    {
        $this->configureService('');
        $result = (new BriqSmsService())->send('+255712345678', 'Hello');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not configured', $result['error']);
    }

    // ─── happy path ──────────────────────────────────────────────────────────

    public function test_send_returns_success_on_200_response(): void
    {
        $this->fakeSuccess();
        $this->configureService();
        $result = (new BriqSmsService())->send('+255712345678', 'Hello');
        $this->assertTrue($result['success']);
    }

    public function test_send_returns_failure_on_http_error(): void
    {
        Http::fake(['karibu.briq.tz/*' => Http::response(['error' => 'unauthorized'], 401)]);
        $this->configureService();
        $result = (new BriqSmsService())->send('+255712345678', 'Hello');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('401', $result['error']);
    }

    // ─── request payload ─────────────────────────────────────────────────────

    public function test_request_uses_correct_endpoint_and_headers(): void
    {
        $this->fakeSuccess();
        $this->configureService();
        (new BriqSmsService())->send('0712345678', 'Test message');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://karibu.briq.tz/v1/message/send-instant'
                && $request->hasHeader('X-API-Key', 'test-key')
                && $request->hasHeader('Content-Type', 'application/json')
                && $request['sender_id']             === 'BRIQ'
                && $request['content']               === 'Test message'
                && $request['recipients'][0]         === '255712345678';
        });
    }

    // ─── phone normalisation ─────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\DataProvider('validTanzanianPhones')]
    public function test_valid_tanzanian_numbers_are_accepted_and_normalised(
        string $input, string $expectedE164
    ): void {
        $this->fakeSuccess();
        $this->configureService();
        (new BriqSmsService())->send($input, 'Hi');

        Http::assertSent(fn($req) =>
            $req['recipients'][0] === ltrim($expectedE164, '+')
        );
    }

    public static function validTanzanianPhones(): array
    {
        return [
            'local 10-digit with leading 0 (Vodacom)'  => ['0742345678', '+255742345678'],
            'local 10-digit with leading 0 (Airtel)'   => ['0782345678', '+255782345678'],
            'local 10-digit with leading 0 (Tigo)'     => ['0712345678', '+255712345678'],
            'local 10-digit with leading 0 (Halotel)'  => ['0622345678', '+255622345678'],
            '9-digit subscriber only (Zantel)'          => ['772345678',  '+255772345678'],
            '12-digit with country code no plus'        => ['255742345678', '+255742345678'],
            'full E.164 format'                         => ['+255742345678', '+255742345678'],
            'number with spaces'                        => ['0712 345 678', '+255712345678'],
            'number with dashes'                        => ['071-234-5678', '+255712345678'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPhones')]
    public function test_invalid_numbers_are_rejected_with_descriptive_error(
        string $input, string $expectedErrorFragment
    ): void {
        $this->configureService();
        $result = (new BriqSmsService())->send($input, 'Hi');

        $this->assertFalse($result['success'], "Expected '{$input}' to be rejected");
        $this->assertNotEmpty($result['error']);
        // Confirm it surfaces as a human-readable message, not a raw exception
        $this->assertStringNotContainsStringIgnoringCase('exception', $result['error']);
        $this->assertStringContainsString($expectedErrorFragment, $result['error']);
    }

    public static function invalidPhones(): array
    {
        return [
            'empty string'                => ['',              'valid Tanzanian'],
            'letters only'                => ['abc',           'valid Tanzanian'],
            'too short (7 digits)'        => ['0712345',       'valid Tanzanian'],
            'too long (11 digits)'        => ['07123456789',   'valid Tanzanian'],
            'wrong country code (+1)'     => ['+1234567890',   'valid Tanzanian'],
            'wrong country code (+44)'    => ['+44712345678',  'valid Tanzanian'],
            'starts with 08 (invalid TZ)' => ['0812345678',    'valid Tanzanian'],
            'starts with 09 (invalid TZ)' => ['0912345678',    'valid Tanzanian'],
            'starts with 05 (invalid TZ)' => ['0512345678',    'valid Tanzanian'],
        ];
    }
}
