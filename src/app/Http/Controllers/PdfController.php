<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;

class PdfController extends Controller
{
    public function show(Request $request): Response
    {
        // Accept either a raw text/html body or an `html` field (JSON / form).
        $html = $request->isJson() || $request->has('html')
            ? $request->validate(['html' => ['required', 'string']])['html']
            : $request->getContent();

        abort_if(blank($html), 422, 'No HTML provided.');

        $pdf = Browsershot::html($html)
            ->setChromePath('/usr/bin/chromium')
            ->noSandbox()
            ->format('A4')
            ->showBackground()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'Content-Length' => strlen($pdf),
        ]);
    }
}
