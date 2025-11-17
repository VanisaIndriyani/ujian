<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class ReportPdf
{
    /**
     * Render a PDF from a Blade view and download it.
     *
     * @param  string  $title
     * @param  string  $view
     * @param  array<string, mixed>  $data
     */
    public function make(string $title, string $view, array $data = []): Response
    {
        $pdf = Pdf::loadView($view, array_merge($data, ['title' => $title]))
            ->setPaper('a4', 'landscape');

        return $pdf->download(Str::slug($title) . '.pdf');
    }
}

