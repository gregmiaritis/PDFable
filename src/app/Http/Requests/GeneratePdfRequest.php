<?php

namespace App\Http\Requests;

class GeneratePdfRequest extends PdfRequest
{
    protected function sourceRules(): array
    {
        return [
            'html' => ['required', 'string'],
        ];
    }
}
