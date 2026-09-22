<?php

namespace Tests\Unit;

use App\Services\PdfGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('browser')]
class PdfGeneratorTest extends TestCase
{
    private PdfGenerator $generator;

    protected function setUp(): void
    {
        if (! is_executable('/usr/bin/chromium')) {
            $this->markTestSkipped('Chromium is not installed.');
        }

        $this->generator = new PdfGenerator;
    }

    public function test_it_renders_html_to_a_pdf(): void
    {
        $pdf = $this->generator->fromHtml('<h1>Hello</h1>');

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertSame(1, $this->pageCount($pdf));
    }

    public function test_it_defaults_to_a4(): void
    {
        $this->assertPageSize(595.92, 841.92, $this->generator->fromHtml('<h1>Hi</h1>'));
    }

    public function test_it_uses_the_given_format(): void
    {
        $this->assertPageSize(612, 792, $this->generator->fromHtml('<h1>Hi</h1>', ['format' => 'Letter']));
    }

    public function test_it_uses_a_custom_paper_size_in_mm(): void
    {
        $pdf = $this->generator->fromHtml('<h1>Hi</h1>', ['width' => 100, 'height' => 150]);

        $this->assertPageSize(283.46, 425.2, $pdf);
    }

    public function test_it_uses_a_custom_paper_size_in_inches(): void
    {
        $pdf = $this->generator->fromHtml('<h1>Hi</h1>', ['width' => 4, 'height' => 6, 'unit' => 'in']);

        $this->assertPageSize(288, 432, $pdf);
    }

    public function test_it_renders_landscape(): void
    {
        $pdf = $this->generator->fromHtml('<h1>Hi</h1>', ['format' => 'A5', 'landscape' => true]);

        $this->assertPageSize(595.92, 420, $pdf);
    }

    public function test_it_limits_pages(): void
    {
        $html = '<div style="page-break-after:always">1</div><div style="page-break-after:always">2</div><div>3</div>';

        $this->assertSame(3, $this->pageCount($this->generator->fromHtml($html)));
        $this->assertSame(2, $this->pageCount($this->generator->fromHtml($html, ['pages' => '1, 3'])));
    }

    public function test_it_accepts_margins_scale_and_background_settings(): void
    {
        $pdf = $this->generator->fromHtml('<body style="background:red"><h1>Hi</h1></body>', [
            'margins' => ['top' => 20, 'right' => 10, 'bottom' => 20, 'left' => 10],
            'scale' => 0.5,
            'print_background' => false,
        ]);

        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function test_it_renders_a_url(): void
    {
        $dir = sys_get_temp_dir().'/pdfgen-'.uniqid();
        mkdir($dir);
        file_put_contents("$dir/index.html", '<h1>From URL</h1>');

        $port = random_int(20000, 40000);
        $server = proc_open(
            ['php', '-S', "127.0.0.1:$port", '-t', $dir],
            [1 => ['file', '/dev/null', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );

        try {
            $this->assertStringContainsString('started', (string) fgets($pipes[2]), 'Test server did not start.');

            $pdf = $this->generator->fromUrl("http://127.0.0.1:$port/", ['format' => 'A5']);

            $this->assertStringStartsWith('%PDF-', $pdf);
            $this->assertPageSize(420, 595.92, $pdf);
        } finally {
            fclose($pipes[2]);
            proc_terminate($server);
            proc_close($server);
            unlink("$dir/index.html");
            rmdir($dir);
        }
    }

    private function pageCount(string $pdf): int
    {
        return preg_match_all('#/Type\s*/Page\b(?!s)#', $pdf);
    }

    private function assertPageSize(float $width, float $height, string $pdf): void
    {
        $this->assertMatchesRegularExpression('#/MediaBox\s*\[#', $pdf);
        preg_match('#/MediaBox\s*\[\s*0 0 ([\d.]+) ([\d.]+)\s*\]#', $pdf, $m);

        $this->assertEqualsWithDelta($width, (float) $m[1], 1, 'Page width');
        $this->assertEqualsWithDelta($height, (float) $m[2], 1, 'Page height');
    }
}
