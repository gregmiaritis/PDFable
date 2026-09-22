<?php

namespace Tests\Feature\Pdf;

use PHPUnit\Framework\Attributes\DataProvider;

class GeneratePdfFromUrlTest extends PdfTestCase
{
    // Public IP literals are used so the tests don't depend on DNS.
    public function test_it_generates_a_pdf_from_a_public_url(): void
    {
        $this->expectGenerator('fromUrl', 'https://93.184.216.34/page?x=1', []);

        $this->assertPdfResponse(
            $this->postJson('/api/pdf/url', ['url' => 'https://93.184.216.34/page?x=1'], $this->authHeaders())
        );
    }

    public function test_it_passes_settings_to_the_generator(): void
    {
        $this->expectGenerator('fromUrl', 'http://93.184.216.34/', ['format' => 'A5']);

        $this->postJson('/api/pdf/url', [
            'url' => 'http://93.184.216.34/',
            'settings' => ['format' => 'A5'],
        ], $this->authHeaders())->assertOk();
    }

    public function test_url_is_required(): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf/url', [], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['url' => 'The url field is required.']);
    }

    public static function invalidUrls(): array
    {
        return [
            'not a url' => ['not a url'],
            'file scheme' => ['file:///etc/passwd'],
            'ftp scheme' => ['ftp://93.184.216.34/file'],
            'javascript scheme' => ['javascript:alert(1)'],
            'too long' => ['https://93.184.216.34/'.str_repeat('a', 2048)],
        ];
    }

    #[DataProvider('invalidUrls')]
    public function test_invalid_urls_are_rejected(string $url): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf/url', ['url' => $url], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('url');
    }

    public static function internalUrls(): array
    {
        return [
            'localhost name' => ['http://localhost/'],
            'loopback' => ['http://127.0.0.1/'],
            'private 10/8' => ['http://10.0.0.1/'],
            'private 172.16/12' => ['http://172.16.0.1/'],
            'private 192.168/16' => ['http://192.168.1.1/'],
            'cloud metadata' => ['http://169.254.169.254/latest/meta-data'],
            'unspecified' => ['http://0.0.0.0/'],
            'ipv6 loopback' => ['http://[::1]/'],
        ];
    }

    #[DataProvider('internalUrls')]
    public function test_internal_addresses_are_rejected(string $url): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf/url', ['url' => $url], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['url' => 'The url must point to a public address.']);
    }

    public function test_unresolvable_hosts_are_rejected(): void
    {
        $this->expectGeneratorNotCalled();

        // The .invalid TLD is reserved and never resolves (RFC 2606).
        $this->postJson('/api/pdf/url', ['url' => 'http://does-not-exist.invalid/'], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['url' => 'The url host could not be resolved.']);
    }
}
