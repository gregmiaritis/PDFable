<?php

namespace Tests\Feature\Pdf;

class GeneratePdfFromHtmlTest extends PdfTestCase
{
    public function test_it_generates_a_pdf_from_json_html(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Hi</h1>', []);

        $this->assertPdfResponse(
            $this->postJson('/api/pdf', ['html' => '<h1>Hi</h1>'], $this->authHeaders())
        );
    }

    public function test_it_passes_settings_to_the_generator(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Hi</h1>', ['format' => 'A5', 'landscape' => true]);

        $this->postJson('/api/pdf', [
            'html' => '<h1>Hi</h1>',
            'settings' => ['format' => 'A5', 'landscape' => true],
        ], $this->authHeaders())->assertOk();
    }

    public function test_html_is_required(): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf', [], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['html' => 'The html field is required.']);
    }

    public function test_html_must_be_a_string(): void
    {
        $this->expectGeneratorNotCalled();

        $this->postJson('/api/pdf', ['html' => ['<h1>Hi</h1>']], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('html');
    }
}
