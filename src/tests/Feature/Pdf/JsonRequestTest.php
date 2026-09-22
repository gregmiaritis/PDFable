<?php

namespace Tests\Feature\Pdf;

use PHPUnit\Framework\Attributes\DataProvider;

class JsonRequestTest extends PdfTestCase
{
    public static function nonJsonRequests(): array
    {
        $html = '<h1>Hi</h1>';
        $url = 'http://93.184.216.34/';
        $base64 = base64_encode($html);

        return [
            'html as form' => ['/api/pdf', 'application/x-www-form-urlencoded', http_build_query(['html' => $html])],
            'html as raw body' => ['/api/pdf', 'text/html', $html],
            'html without content type' => ['/api/pdf', null, json_encode(['html' => $html])],
            'url as form' => ['/api/pdf/url', 'application/x-www-form-urlencoded', http_build_query(['url' => $url])],
            'url as plain text' => ['/api/pdf/url', 'text/plain', json_encode(['url' => $url])],
            'base64 as form' => ['/api/pdf/base64', 'application/x-www-form-urlencoded', http_build_query(['base64' => $base64])],
            'base64 as multipart' => ['/api/pdf/base64', 'multipart/form-data; boundary=x', "--x\r\nContent-Disposition: form-data; name=\"base64\"\r\n\r\n$base64\r\n--x--"],
        ];
    }

    #[DataProvider('nonJsonRequests')]
    public function test_non_json_requests_are_rejected(string $uri, ?string $contentType, string $body): void
    {
        $this->expectGeneratorNotCalled();

        $server = ['HTTP_AUTHORIZATION' => 'Bearer '.self::TOKEN];
        if ($contentType !== null) {
            $server['CONTENT_TYPE'] = $contentType;
        }

        $this->call('POST', $uri, server: $server, content: $body)
            ->assertStatus(415)
            ->assertExactJson(['message' => 'Content-Type must be application/json.']);
    }

    public function test_json_with_charset_is_accepted(): void
    {
        $this->expectGenerator('fromHtml', '<h1>Hi</h1>', []);

        $this->call('POST', '/api/pdf', server: [
            'CONTENT_TYPE' => 'application/json; charset=utf-8',
            'HTTP_AUTHORIZATION' => 'Bearer '.self::TOKEN,
        ], content: json_encode(['html' => '<h1>Hi</h1>']))->assertOk();
    }

    public function test_token_is_checked_before_content_type(): void
    {
        $this->expectGeneratorNotCalled();

        $this->call('POST', '/api/pdf', server: ['CONTENT_TYPE' => 'text/html'], content: '<h1>Hi</h1>')
            ->assertUnauthorized();
    }
}
