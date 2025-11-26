<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\ReportController;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GenerateDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-daily {--date= : Date to generate report for (YYYY-MM-DD). Defaults to yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily reports for all active users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') 
            ? \Carbon\Carbon::parse($this->option('date'))
            : now()->subDay();

        $this->info("Generating daily reports for {$date->format('Y-m-d')}...");

        // Get all active users
        $users = User::where('is_active', true)->get();

        if ($users->isEmpty()) {
            $this->warn('No active users found.');
            return Command::SUCCESS;
        }

        $generated = 0;
        $failed = 0;

        // Use ReportController logic (will be moved to ReportService later)
        $reportController = new ReportController();

        foreach ($users as $user) {
            try {
                // Check if report already exists for this date
                $existingReport = Report::where('user_id', $user->id)
                    ->where('type', 'daily')
                    ->where('report_date', $date->format('Y-m-d'))
                    ->first();

                if ($existingReport) {
                    $this->warn("Report already exists for {$user->name} on {$date->format('Y-m-d')}");
                    continue;
                }

                // Temporarily set authenticated user for report generation
                Auth::login($user);

                // Generate daily report (using EOD logic for now, can be customized)
                $request = new \Illuminate\Http\Request([
                    'date' => $date->format('Y-m-d'),
                    'highlights' => '',
                    'challenges' => '',
                ]);

                $response = $reportController->generateEod($request);
                $reportData = json_decode($response->getContent(), true);

                // Update report type to 'daily'
                if (isset($reportData['report']['id'])) {
                    Report::where('id', $reportData['report']['id'])->update(['type' => 'daily']);
                }

                $this->info("Generated daily report for {$user->name}");
                $generated++;

                Log::info("Daily report generated", [
                    'user_id' => $user->id,
                    'report_id' => $reportData['report']['id'] ?? null,
                    'date' => $date->format('Y-m-d'),
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to generate report for {$user->name}: {$e->getMessage()}");
                $failed++;

                Log::error("Failed to generate daily report", [
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Auth::logout();

        $this->info("Generated {$generated} report(s), {$failed} failed.");

        return Command::SUCCESS;
    }
}
