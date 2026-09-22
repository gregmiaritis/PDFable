<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;

class PdfGenerator
{
    public const FORMATS = ['Letter', 'Legal', 'Tabloid', 'Ledger', 'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6'];

    public const UNITS = ['mm', 'cm', 'in', 'px'];

    public function fromHtml(string $html, array $settings = []): string
    {
        return $this->render(Browsershot::html($html), $settings);
    }

    public function fromUrl(string $url, array $settings = []): string
    {
        return $this->render(Browsershot::url($url), $settings);
    }

    /**
     * Apply the page settings and render to PDF bytes.
     *
     * @param  array{
     *     format?: string,
     *     width?: float, height?: float, unit?: string,
     *     landscape?: bool,
     *     margins?: array{top?: float, right?: float, bottom?: float, left?: float},
     *     scale?: float,
     *     print_background?: bool,
     *     pages?: string,
     * }  $settings
     */
    private function render(Browsershot $browsershot, array $settings): string
    {
        $unit = $settings['unit'] ?? 'mm';

        $browsershot
            ->setChromePath('/usr/bin/chromium')
            ->noSandbox();

        if (isset($settings['width'], $settings['height'])) {
            $browsershot->paperSize($settings['width'], $settings['height'], $unit);
        } else {
            $browsershot->format($settings['format'] ?? 'A4');
        }

        if (isset($settings['margins'])) {
            $m = $settings['margins'];
            $browsershot->margins($m['top'] ?? 0, $m['right'] ?? 0, $m['bottom'] ?? 0, $m['left'] ?? 0, $unit);
        }

        if ($settings['landscape'] ?? false) {
            $browsershot->landscape();
        }

        if (isset($settings['scale'])) {
            $browsershot->scale($settings['scale']);
        }

        if ($settings['print_background'] ?? true) {
            $browsershot->showBackground();
        }

        if (isset($settings['pages'])) {
            $browsershot->pages($settings['pages']);
        }

        return $browsershot->pdf();
    }
}
