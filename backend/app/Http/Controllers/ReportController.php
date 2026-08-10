<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportExportRequest;
use App\Services\ReportService;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
    }

    public function export(ReportExportRequest $request): Response
    {
        [$filename, $csv] = $this->reportService->exportCsv($request->validated());

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
