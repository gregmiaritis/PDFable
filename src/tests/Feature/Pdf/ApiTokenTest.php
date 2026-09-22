<?php

namespace Tests\Feature\Pdf;

use App\Services\PdfGenerator;
use PHPUnit\Framework\Attributes\DataProvider;

class ApiTokenTest extends PdfTestCase
{
    public static function endpoints(): array
    {
        return [
            'html' => ['/api/pdf', ['html' => '<h1>Hi</h1>']],
            'url' => ['/api/pdf/url', ['url' => 'http://93.184.216.34/']],
            'base64' => ['/api/pdf/base64', ['base64' => base64_encode('<h1>Hi</h1>')]],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_missing_token_is_rejected(string $uri, array $payload): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson($uri, $payload)
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Missing API token. Send it as "Authorization: Bearer <token>".']);
    }

    #[DataProvider('endpoints')]
    public function test_invalid_token_is_rejected(string $uri, array $payload): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson($uri, $payload, ['Authorization' => 'Bearer wrong'])
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Invalid API token.']);
    }

    #[DataProvider('endpoints')]
    public function test_token_must_be_sent_as_bearer(string $uri, array $payload): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson($uri, $payload, ['Authorization' => self::TOKEN])
            ->assertUnauthorized();
    }

    #[DataProvider('endpoints')]
    public function test_unconfigured_token_fails_closed(string $uri, array $payload): void
    {
        config(['services.pdf_api.token' => null]);
        $this->expectGeneratorNotCalled();

        $this->postJson($uri, $payload, ['Authorization' => 'Bearer '])
            ->assertStatus(500)
            ->assertExactJson(['message' => 'API token is not configured.']);
    }

    #[DataProvider('endpoints')]
    public function test_valid_token_is_accepted(string $uri, array $payload): void
    {
        $this->mock(PdfGenerator::class)->shouldIgnoreMissing(self::FAKE_PDF);

        $this->postJson($uri, $payload, $this->authHeaders())->assertOk();
    }

    public function test_health_endpoint_does_not_require_a_token(): void
    {
        $this->getJson('/api/health')->assertOk()->assertExactJson(['status' => 'ok']);
    }
}
