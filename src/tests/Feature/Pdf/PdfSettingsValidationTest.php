<?php

namespace Tests\Feature\Pdf;

use PHPUnit\Framework\Attributes\DataProvider;

class PdfSettingsValidationTest extends PdfTestCase
{
    public static function invalidSettings(): array
    {
        return [
            'settings not an array' => ['nope', 'settings'],
            'unknown format' => [['format' => 'B9'], 'settings.format'],
            'format with width/height' => [['format' => 'A4', 'width' => 100, 'height' => 100], 'settings.format'],
            'width without height' => [['width' => 100], 'settings.height'],
            'height without width' => [['height' => 100], 'settings.width'],
            'zero width' => [['width' => 0, 'height' => 100], 'settings.width'],
            'negative height' => [['width' => 100, 'height' => -5], 'settings.height'],
            'non-numeric width' => [['width' => 'wide', 'height' => 100], 'settings.width'],
            'unknown unit' => [['unit' => 'ft'], 'settings.unit'],
            'non-boolean landscape' => [['landscape' => 'yes'], 'settings.landscape'],
            'margins not an array' => [['margins' => '10'], 'settings.margins'],
            'negative margin' => [['margins' => ['top' => -1]], 'settings.margins.top'],
            'non-numeric margin' => [['margins' => ['left' => 'x']], 'settings.margins.left'],
            'scale too small' => [['scale' => 0.05], 'settings.scale'],
            'scale too large' => [['scale' => 3], 'settings.scale'],
            'non-boolean print_background' => [['print_background' => 'maybe'], 'settings.print_background'],
            'invalid pages' => [['pages' => 'abc'], 'settings.pages'],
            'open page range' => [['pages' => '1-'], 'settings.pages'],
        ];
    }

    public static function endpoints(): array
    {
        return [
            'html' => ['/api/pdf', ['html' => '<h1>Hi</h1>']],
            'url' => ['/api/pdf/url', ['url' => 'http://93.184.216.34/']],
            'base64' => ['/api/pdf/base64', ['base64' => base64_encode('<h1>Hi</h1>')]],
        ];
    }

    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(mixed $settings, string $errorKey): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf', ['html' => '<h1>Hi</h1>', 'settings' => $settings], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorKey);
    }

    #[DataProvider('endpoints')]
    public function test_every_endpoint_validates_settings(string $uri, array $payload): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson($uri, [...$payload, 'settings' => ['format' => 'B9', 'scale' => 5]], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['settings.format', 'settings.scale']);
    }

    public function test_all_valid_settings_are_passed_to_the_generator(): void
    {
        $settings = [
            'width' => 100,
            'height' => 150.5,
            'unit' => 'cm',
            'landscape' => true,
            'margins' => ['top' => 10, 'right' => 5, 'bottom' => 10, 'left' => 5],
            'scale' => 0.8,
            'print_background' => false,
            'pages' => '1-3, 5',
        ];
        $this->expectGenerator('fromHtml', '<h1>Hi</h1>', $settings);

        $this->assertPdfResponse(
            $this->postJson('/api/pdf', ['html' => '<h1>Hi</h1>', 'settings' => $settings], $this->authHeaders())
        );
    }

    public function test_format_alone_is_valid(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Hi</h1>', ['format' => 'Letter']);

        $this->postJson('/api/pdf', ['html' => '<h1>Hi</h1>', 'settings' => ['format' => 'Letter']], $this->authHeaders())
            ->assertOk();
    }

    public function test_validation_errors_are_json_without_an_accept_header(): void
    {
        $this->expectGeneratorNotCalled();

        $response = $this->call('POST', '/api/pdf', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.self::TOKEN,
        ], content: json_encode(['html' => '<h1>Hi</h1>', 'settings' => ['unit' => 'ft']]));

        $response->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('message', 'The selected settings.unit is invalid.')
            ->assertJsonValidationErrors('settings.unit');
    }
}
