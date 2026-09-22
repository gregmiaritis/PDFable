<?php

namespace Tests\Feature\Pdf;

use PHPUnit\Framework\Attributes\DataProvider;

class GeneratePdfFromBase64Test extends PdfTestCase
{
    public function test_it_decodes_base64_html(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Base64 ✓</h1>', []);

        $this->assertPdfResponse($this->postJson('/api/pdf/base64', [
            'base64' => base64_encode('<h1>Base64 ✓</h1>'),
        ], $this->authHeaders()));
    }

    public function test_it_accepts_a_data_uri(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Data URI</h1>', []);

        $this->postJson('/api/pdf/base64', [
            'base64' => 'data:text/html;base64,'.base64_encode('<h1>Data URI</h1>'),
        ], $this->authHeaders())->assertOk();
    }

    public function test_it_trims_whitespace(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Hi</h1>', []);

        $this->postJson('/api/pdf/base64', [
            'base64' => '  '.base64_encode('<h1>Hi</h1>')."\n",
        ], $this->authHeaders())->assertOk();
    }

    public function test_it_passes_settings_to_the_generator(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Hi</h1>', ['width' => 80, 'height' => 120, 'unit' => 'mm']);

        $this->postJson('/api/pdf/base64', [
            'base64' => base64_encode('<h1>Hi</h1>'),
            'settings' => ['width' => 80, 'height' => 120, 'unit' => 'mm'],
        ], $this->authHeaders())->assertOk();
    }

    public function test_base64_is_required(): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf/base64', [], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['base64' => 'The base64 field is required.']);
    }

    public static function invalidBase64(): array
    {
        return [
            'invalid characters' => ['not base64!!'],
            'decodes to whitespace' => [base64_encode('   ')],
            'array' => [[base64_encode('<h1>Hi</h1>')]],
        ];
    }

    #[DataProvider('invalidBase64')]
    public function test_invalid_base64_is_rejected(mixed $value): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf/base64', ['base64' => $value], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('base64');
    }
}
