<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ReportMail;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
     * Get the latest EOD report for the authenticated user.
     */
    public function latest(Request $request): JsonResponse
    {
        $user = Auth::user();

        $report = \App\Models\Report::where('user_id', $user->id)
            ->where('type', 'eod')
            ->latest('report_date')
            ->with('user')
            ->first();

        if (!$report) {
            return response()->json([
                'message' => 'No reports available. Reports are generated at the end of each day.'
            ], 404);
        }

        return response()->json([
            'report' => new \App\Http\Resources\EODReportResource($report)
        ]);
    }

    /**
     * Get all reports for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $perPage = $request->get('per_page', 15);
        $type = $request->get('type', null);

        $query = \App\Models\Report::where('user_id', $user->id)
            ->with('user')
            ->orderBy('report_date', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        $reports = $query->paginate($perPage);

        return response()->json([
            'data' => $reports->items(),
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage(),
            'per_page' => $reports->perPage(),
            'total' => $reports->total(),
        ]);
    }

    /**
     * Get reports by date.
     */
    public function getByDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['nullable', 'in:eod,custom']
        ]);

        $user = Auth::user();

        $query = \App\Models\Report::where('user_id', $user->id)
            ->where('report_date', $validated['date'])
            ->with('user');

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        $report = $query->first();

        if (!$report) {
            return response()->json([
                'message' => 'No report found for the specified date.'
            ], 404);
        }

        return response()->json([
            'report' => new \App\Http\Resources\EODReportResource($report)
        ]);
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

    /**
     * Download report as PDF.
     */
    public function downloadPdf(Request $request, $id)
    {
        $validated = $request->validate([
            'highlights' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        $report = \App\Models\Report::where('user_id', $user->id)
            ->where('id', $id)
            ->with('user')
            ->first();

        if (!$report) {
            return response()->json([
                'message' => 'Report not found.'
            ], 404);
        }

        // Prepare data for PDF
        $data = $report->data ?? [];

        // Override highlights and challenges if provided by user
        $highlights = $validated['highlights'] ?? $report->highlights;
        $challenges = $validated['challenges'] ?? $report->challenges;

        $pdfData = [
            'report' => $report,
            'highlights' => $highlights,
            'challenges' => $challenges,
            'outreachSummary' => $data['outreach_summary'] ?? [
                'total_contacted' => 0,
                'contacted_leads' => [],
            ],
            'wonDeals' => $data['won_deals'] ?? [],
            'newLeads' => $data['new_leads'] ?? [],
            'lostDeals' => $data['lost_deals'] ?? [],
            'upcomingTasks' => $data['key_reminders']['upcoming_tasks'] ?? [],
            'overdueTasks' => $data['key_reminders']['overdue_tasks'] ?? [],
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.eod-report-pdf', $pdfData);

        $salespersonName = str_replace(' ', '-', strtolower($report->user->name));
        $filename = 'sales-report-' . $salespersonName . '-' . $report->report_date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Send report via email.
     */
    public function sendEmail(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'recipient' => ['nullable', 'email'],
            'highlights' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        $report = \App\Models\Report::where('user_id', $user->id)
            ->where('id', $id)
            ->with('user')
            ->first();

        if (!$report) {
            return response()->json([
                'message' => 'Report not found.'
            ], 404);
        }

        // Default recipient
        $recipient = $validated['recipient'] ?? 'ombenifaraja@gmail.com';

        // Pass highlights and challenges to the mailable
        $highlights = $validated['highlights'] ?? null;
        $challenges = $validated['challenges'] ?? null;

        try {
            Mail::to($recipient)->send(
                new ReportMail($report, $recipient, $highlights, $challenges)
            );

            return response()->json([
                'message' => 'Report sent successfully to ' . $recipient,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send report: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}
