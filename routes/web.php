<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MoodTrackerController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SafetyPlanController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ProfessionalVerificationController;
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

// Magazine Authoring (Requires Authentication and Verified Healthcare Professional Role)
Route::middleware(['auth', 'professional'])->group(function () {
    Route::get('/revista/crear', [ArticleController::class, 'create'])->name('revista.create');
    Route::post('/revista', [ArticleController::class, 'store'])->name('revista.store');
});

// Magazine (Revista & Publicaciones Científicas)
Route::get('/revista', [ArticleController::class, 'index'])->name('revista.index');
Route::get('/revista/{slug}', [ArticleController::class, 'show'])->name('revista.show');

// Emotional Self-check & Tools
Route::get('/sientes', [ToolController::class, 'sientes'])->name('sientes');
Route::get('/herramientas/respiracion', [ToolController::class, 'respiracion'])->name('tools.respiracion');
Route::get('/herramientas/grounding', [ToolController::class, 'grounding'])->name('tools.grounding');
Route::get('/herramientas/stop', [ToolController::class, 'stop'])->name('tools.stop');
Route::get('/crisis', [ToolController::class, 'crisis'])->name('crisis');

// Legal & Compliance (Google Cloud & Public Requirements)
Route::view('/privacidad', 'privacidad')->name('privacidad');
Route::view('/terminos', 'terminos')->name('terminos');
Route::redirect('/privacidad.html', '/privacidad');
Route::redirect('/terminos.html', '/terminos');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:6,1');

    // Google OAuth Authentication
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    // Password Reset
    Route::get('/olvide-contrasena', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/olvide-contrasena', [AuthController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/restablecer-contrasena/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/restablecer-contrasena', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:5,1');
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

    // Clinical Risk Assessment Engine (v1.0)
    Route::post('/assessment/who5', [AssessmentController::class, 'submitWho5'])->name('assessment.who5');
    Route::post('/assessment/mdi', [AssessmentController::class, 'submitMdi'])->name('assessment.mdi');
    Route::post('/assessment/asq', [AssessmentController::class, 'submitAsq'])->name('assessment.asq');
    Route::post('/assessment/crisis/accion', [AssessmentController::class, 'registrarAccionCrisis'])->name('assessment.crisis.action');
    Route::post('/assessment/crisis/{evento}/cerrar', [AssessmentController::class, 'cerrarCasoCrisis'])->name('assessment.crisis.close');

    // Profile Settings
    Route::get('/perfil', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/perfil', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/perfil/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Professional Healthcare Accreditation (Client Submission)
    Route::post('/perfil/solicitud-profesional', [ProfessionalVerificationController::class, 'store'])->name('profile.verification.store');
});

/*
|--------------------------------------------------------------------------
| Admin Portal Routes (Restricted to is_admin = true)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Acreditaciones
    Route::get('/solicitudes-profesionales', [ProfessionalVerificationController::class, 'index'])->name('verifications.index');
    Route::get('/solicitudes-profesionales/{verification}/documento', [ProfessionalVerificationController::class, 'document'])->name('verifications.document');
    Route::post('/solicitud-profesional/{verification}/aprobar', [ProfessionalVerificationController::class, 'approve'])->name('verification.approve');
    Route::post('/solicitud-profesional/{verification}/rechazar', [ProfessionalVerificationController::class, 'reject'])->name('verification.reject');

    // Usuarios
    Route::get('/usuarios', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::post('/usuarios', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
    Route::put('/usuarios/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');

    // Foros & Revista
    Route::get('/foros', [\App\Http\Controllers\Admin\AdminForumController::class, 'index'])->name('forums.index');
});
