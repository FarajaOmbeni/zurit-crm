<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate End of Day Report.
     */
    public function generateEod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'highlights' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $result = $this->reportService->generateEODReport(
            $user,
            $validated['date'],
            $validated['highlights'] ?? null,
            $validated['challenges'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Generate custom report for date range.
     */
    public function generateCustom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'highlights' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $result = $this->reportService->generateCustomReport(
            $user,
            $validated['start_date'],
            $validated['end_date'],
            $validated['highlights'] ?? null,
            $validated['challenges'] ?? null
        );

        return response()->json($result);
    }
}
