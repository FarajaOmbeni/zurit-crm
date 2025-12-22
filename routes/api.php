<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes (if needed for external integrations)
// Route::middleware('api')->group(function () {
//     // Public endpoints
// });

// Authenticated API routes
// Using 'auth' middleware for session-based authentication (works with Inertia.js)
// Sanctum is available for token-based authentication if needed for external APIs
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard APIs
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview', [DashboardController::class, 'overview'])->name('api.dashboard.overview');
        Route::get('/tasks-due-today', [DashboardController::class, 'tasksDueToday'])->name('api.dashboard.tasks-due-today');
        Route::get('/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');
    });

    // Leads & Pipeline APIs
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('api.leads.index');
        Route::post('/', [LeadController::class, 'store'])->name('api.leads.store');
        Route::post('/import', [LeadController::class, 'import'])->name('api.leads.import');
        Route::get('/export', [LeadController::class, 'export'])->name('api.leads.export');
        Route::get('/kanban', [LeadController::class, 'kanban'])->name('api.leads.kanban');
        Route::get('/pipeline-stats', [LeadController::class, 'pipelineStats'])->name('api.leads.pipeline-stats');
        Route::get('/{id}', [LeadController::class, 'show'])->name('api.leads.show');
        Route::put('/{id}', [LeadController::class, 'update'])->name('api.leads.update');
        Route::patch('/{id}/status', [LeadController::class, 'updateStatus'])->name('api.leads.update-status');
        Route::patch('/{id}/mark-won', [LeadController::class, 'markAsWon'])->name('api.leads.mark-won');
        Route::patch('/{id}/mark-lost', [LeadController::class, 'markAsLost'])->name('api.leads.mark-lost');
        Route::patch('/{id}/reassign', [LeadController::class, 'reassign'])->name('api.leads.reassign');
        Route::get('/{id}/products/{product_id}/notes', [LeadController::class, 'getNotes'])->name('api.leads.notes.get');
        Route::post('/{id}/products/{product_id}/notes', [LeadController::class, 'addNote'])->name('api.leads.notes.add');
        Route::delete('/{id}', [LeadController::class, 'destroy'])->name('api.leads.destroy');
    });

    // Client Database APIs
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('api.clients.index');
        Route::get('/stats', [ClientController::class, 'stats'])->name('api.clients.stats');
        Route::get('/{id}', [ClientController::class, 'show'])->name('api.clients.show');
        Route::put('/{id}', [ClientController::class, 'update'])->name('api.clients.update');
    });

    // Tasks & Follow-ups APIs
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('api.tasks.index');
        Route::post('/', [TaskController::class, 'store'])->name('api.tasks.store');
        Route::get('/upcoming', [TaskController::class, 'upcoming'])->name('api.tasks.upcoming');
        Route::get('/{id}', [TaskController::class, 'show'])->name('api.tasks.show');
        Route::put('/{id}', [TaskController::class, 'update'])->name('api.tasks.update');
        Route::patch('/{id}/complete', [TaskController::class, 'complete'])->name('api.tasks.complete');
        Route::delete('/{id}', [TaskController::class, 'destroy'])->name('api.tasks.destroy');
    });

    // Activities APIs
    Route::prefix('activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('api.activities.index');
        Route::post('/', [ActivityController::class, 'store'])->name('api.activities.store');
        Route::get('/{id}', [ActivityController::class, 'show'])->name('api.activities.show');
        Route::put('/{id}', [ActivityController::class, 'update'])->name('api.activities.update');
        Route::delete('/{id}', [ActivityController::class, 'destroy'])->name('api.activities.destroy');
    });

    // Lead/Client Activities (nested route)
    Route::prefix('leads/{leadId}')->group(function () {
        Route::get('/activities', [ActivityController::class, 'leadActivities'])->name('api.leads.activities');
    });

    // Reports APIs
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('api.reports.index');
        Route::get('/latest', [ReportController::class, 'latest'])->name('api.reports.latest');
        Route::get('/by-date', [ReportController::class, 'getByDate'])->name('api.reports.by-date');
        Route::get('/{id}/download', [ReportController::class, 'downloadPdf'])->name('api.reports.download');
        Route::post('/{id}/send-email', [ReportController::class, 'sendEmail'])->name('api.reports.send-email');
        Route::post('/eod', [ReportController::class, 'generateEod'])->name('api.reports.eod');
        Route::post('/custom', [ReportController::class, 'generateCustom'])->name('api.reports.custom');
    });

    // Products APIs
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('api.products.index');
        Route::post('/', [ProductController::class, 'store'])->name('api.products.store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('api.products.show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');
    });

    // Users - Assignable endpoint (for admin and manager)
    Route::get('/users/assignable', [UserController::class, 'assignable'])->name('api.users.assignable');

    // Users Management APIs (Admin only)
    Route::prefix('users')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('api.users.index');
        Route::post('/', [UserController::class, 'store'])->name('api.users.store');
        Route::get('/{id}', [UserController::class, 'show'])->name('api.users.show');
        Route::put('/{id}', [UserController::class, 'update'])->name('api.users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');
        Route::get('/{id}/team', [UserController::class, 'team'])->name('api.users.team');
    });
});
