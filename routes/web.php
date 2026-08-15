<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MoodTrackerController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SafetyPlanController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Resources
Route::get('/recursos', [ResourceController::class, 'index'])->name('recursos.index');
Route::get('/recursos/{slug}', [ResourceController::class, 'show'])->name('recursos.show');

// Magazine (Revista)
Route::get('/revista', [ArticleController::class, 'index'])->name('revista.index');
Route::get('/revista/{slug}', [ArticleController::class, 'show'])->name('revista.show');

// Emotional Self-check & Tools
Route::get('/sientes', [ToolController::class, 'sientes'])->name('sientes');
Route::get('/herramientas/respiracion', [ToolController::class, 'respiracion'])->name('tools.respiracion');
Route::get('/herramientas/grounding', [ToolController::class, 'grounding'])->name('tools.grounding');
Route::get('/herramientas/stop', [ToolController::class, 'stop'])->name('tools.stop');
Route::get('/crisis', [ToolController::class, 'crisis'])->name('crisis');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Personal Space)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Hub
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Mood Tracking & History
    Route::post('/mood/checkin', [MoodTrackerController::class, 'store'])->name('mood.store');
    Route::get('/historial', [MoodTrackerController::class, 'history'])->name('mood.history');
    Route::delete('/mood/{moodLog}', [MoodTrackerController::class, 'destroy'])->name('mood.destroy');

    // Safety Plan
    Route::get('/plan-de-seguridad', [SafetyPlanController::class, 'show'])->name('safety-plan.show');
    Route::put('/plan-de-seguridad', [SafetyPlanController::class, 'update'])->name('safety-plan.update');
    Route::get('/plan-de-seguridad/imprimir', [SafetyPlanController::class, 'printView'])->name('safety-plan.print');

    // Favorites & Completed Resources
    Route::post('/recursos/{resource}/favorito', [ResourceController::class, 'toggleFavorite'])->name('recursos.favorite');
    Route::post('/recursos/{resource}/completar', [ResourceController::class, 'toggleCompleted'])->name('recursos.complete');
    Route::get('/mis-favoritos', [ResourceController::class, 'userFavorites'])->name('favorites.index');

    // Profile Settings
    Route::get('/perfil', function () {
        return view('dashboard.perfil', ['user' => auth()->user()]);
    })->name('profile.show');
    Route::put('/perfil', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/perfil/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});
