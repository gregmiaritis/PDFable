<?php

namespace App\Http\Requests;

use Closure;

class GeneratePdfFromBase64Request extends PdfRequest
{
    // Allow a data URI prefix, e.g. "data:text/html;base64,PGgxPi4uLg==".
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('base64'))) {
            $this->merge(['base64' => preg_replace('/^data:[^;,]*;base64,/', '', trim($this->input('base64')))]);
        }
    }

    protected function sourceRules(): array
    {
        return [
            'base64' => ['bail', 'required', 'string', $this->decodesToHtml(...)],
        ];
    }

    public function html(): string
    {
        return base64_decode($this->validated('base64'), true);
    }

    private function decodesToHtml(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false || blank($decoded)) {
            $fail('The base64 field must be valid base64-encoded HTML.');
        }
    }
}
