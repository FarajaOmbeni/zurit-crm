<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSelectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pipeline/select-product', [ProductSelectionController::class, 'index'])->name('pipeline.select-product');
    Route::post('/pipeline/select-product', [ProductSelectionController::class, 'select'])->name('pipeline.select-product.post');

    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline');

    Route::get('/leads', function () {
        return Inertia::render('Leads/Index');
    })->name('leads.index');

    Route::get('/clients', function () {
        return Inertia::render('Clients/Index');
    })->name('clients.index');

    Route::get('/reports', function () {
        return Inertia::render('Reports/Index');
    })->name('reports.index');

    Route::get('/tasks', function () {
        return Inertia::render('Tasks/Index');
    })->name('tasks.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/resend-otp', [UserController::class, 'resendOtp'])->name('users.resend-otp');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
