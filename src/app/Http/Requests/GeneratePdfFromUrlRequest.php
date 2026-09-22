<?php

namespace App\Http\Requests;

use Closure;

class GeneratePdfFromUrlRequest extends PdfRequest
{
    protected function sourceRules(): array
    {
        return [
            'url' => ['bail', 'required', 'string', 'url:http,https', 'max:2048', $this->publicHost(...)],
        ];
    }

    // Block URLs that resolve to private/internal addresses (other containers, localhost, cloud metadata).
    private function publicHost(string $attribute, mixed $value, Closure $fail): void
    {
        $host = trim((string) parse_url($value, PHP_URL_HOST), '[]');
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : gethostbynamel($host);

        if (! $ips) {
            $fail('The url host could not be resolved.');

            return;
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $fail('The url must point to a public address.');

                return;
            }
        }
    }
}
