<?php

namespace App\Http\Requests;

use App\Services\PdfGenerator;
use Illuminate\Validation\Rule;

abstract class PdfRequest extends ApiFormRequest
{
    /** Rules for the source of the PDF (html, url, ...). */
    abstract protected function sourceRules(): array;

    public function rules(): array
    {
        return [
            ...$this->sourceRules(),

            'settings' => ['sometimes', 'array'],
            'settings.format' => ['sometimes', 'string', Rule::in(PdfGenerator::FORMATS), 'prohibits:settings.width,settings.height'],
            'settings.width' => ['required_with:settings.height', 'numeric', 'gt:0'],
            'settings.height' => ['required_with:settings.width', 'numeric', 'gt:0'],
            'settings.unit' => ['sometimes', 'string', Rule::in(PdfGenerator::UNITS)],
            'settings.landscape' => ['sometimes', 'boolean'],
            'settings.margins' => ['sometimes', 'array'],
            'settings.margins.top' => ['sometimes', 'numeric', 'min:0'],
            'settings.margins.right' => ['sometimes', 'numeric', 'min:0'],
            'settings.margins.bottom' => ['sometimes', 'numeric', 'min:0'],
            'settings.margins.left' => ['sometimes', 'numeric', 'min:0'],
            'settings.scale' => ['sometimes', 'numeric', 'between:0.1,2'],
            'settings.print_background' => ['sometimes', 'boolean'],
            'settings.pages' => ['sometimes', 'string', 'regex:/^\d+(-\d+)?(\s*,\s*\d+(-\d+)?)*$/'],
        ];
    }
}
