<?php

namespace Tests\Feature\Pdf;

use App\Services\PdfGenerator;
use Mockery\MockInterface;
use Tests\TestCase;

abstract class PdfTestCase extends TestCase
{
    protected const TOKEN = 'test-token';

    protected const FAKE_PDF = '%PDF-1.4 fake';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.pdf_api.token' => self::TOKEN]);
    }

    protected function authHeaders(): array
    {
        return ['Authorization' => 'Bearer '.self::TOKEN];
    }

    protected function expectGenerator(string $method, mixed ...$args): void
    {
        $this->mock(PdfGenerator::class, fn (MockInterface $mock) => $mock
            ->shouldReceive($method)->once()->with(...$args)->andReturn(self::FAKE_PDF));
    }

    protected function expectGeneratorNotCalled(): void
    {
        $this->mock(PdfGenerator::class, fn (MockInterface $mock) => $mock
            ->shouldNotReceive('fromHtml', 'fromUrl'));
    }

    protected function assertPdfResponse($response): void
    {
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="document.pdf"')
            ->assertHeader('Content-Length', (string) strlen(self::FAKE_PDF));

        $this->assertSame(self::FAKE_PDF, $response->getContent());
    }
}
