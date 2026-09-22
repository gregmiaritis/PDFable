<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePdfFromBase64Request;
use App\Http\Requests\GeneratePdfFromUrlRequest;
use App\Http\Requests\GeneratePdfRequest;
use App\Services\PdfGenerator;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function generateByHTML(GeneratePdfRequest $request, PdfGenerator $generator): Response
    {
        $pdf = $generator->fromHtml(
            $request->validated('html'),
            $request->validated('settings', []),
        );

        return $this->pdfResponse($pdf);
    }

    public function generateByUrl(GeneratePdfFromUrlRequest $request, PdfGenerator $generator): Response
    {
        $pdf = $generator->fromUrl(
            $request->validated('url'),
            $request->validated('settings', []),
        );

        return $this->pdfResponse($pdf);
    }

    public function generateByBase64(GeneratePdfFromBase64Request $request, PdfGenerator $generator): Response
    {
        $pdf = $generator->fromHtml(
            $request->html(),
            $request->validated('settings', []),
        );

        return $this->pdfResponse($pdf);
    }

    private function pdfResponse(string $pdf): Response
    {
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'Content-Length' => strlen($pdf),
        ]);
    }
}
