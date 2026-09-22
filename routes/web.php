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

    // Email 6-Digit Code Verification
    Route::get('/verificar-codigo', [AuthController::class, 'showVerifyCode'])->name('verification.code.notice');
    Route::post('/verificar-codigo', [AuthController::class, 'verifyCode'])->name('verification.code.verify')->middleware('throttle:10,1');
    Route::post('/verificar-codigo/reenviar', [AuthController::class, 'resendVerificationCode'])->name('verification.code.resend')->middleware('throttle:3,1');

    // Verified Users Only
    Route::middleware('verified.code')->group(function () {
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
        Route::post('/plan-de-seguridad/sugerencia', [SafetyPlanController::class, 'agregarSugerencia'])->name('safety-plan.sugerencia');

        // Favorites & Completed Resources
        Route::post('/recursos/{resource}/favorito', [ResourceController::class, 'toggleFavorite'])->name('recursos.favorite');
        Route::post('/recursos/{resource}/completar', [ResourceController::class, 'toggleCompleted'])->name('recursos.complete');
        Route::get('/mis-favoritos', [ResourceController::class, 'userFavorites'])->name('favorites.index');

        // Bloques de preguntas del motor clínico. Las URLs son neutras a propósito:
        // el navegador no debe poder deducir qué instrumento hay detrás de cada una.
        Route::post('/preguntas/1', [AssessmentController::class, 'submitWho5'])->name('preguntas.bloque1');
        Route::post('/preguntas/2', [AssessmentController::class, 'submitMdi'])->name('preguntas.bloque2');
        Route::post('/preguntas/3', [AssessmentController::class, 'submitAsq'])->name('preguntas.bloque3');
        Route::post('/preguntas/4', [AssessmentController::class, 'submitRevision'])->name('preguntas.bloque4');
        Route::post('/apoyo/texto', [AssessmentController::class, 'revisarTexto'])->name('apoyo.texto')->middleware('throttle:120,1');
        Route::post('/apoyo/accion',[AssessmentController::class, 'registrarAccionCrisis'])->name('apoyo.accion');
        Route::post('/apoyo/casos/{evento}/cerrar', [AssessmentController::class, 'cerrarCasoCrisis'])->name('apoyo.cerrar');

        // Profile Settings
        Route::get('/perfil', [AuthController::class, 'showProfile'])->name('profile.show');
        Route::put('/perfil', [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::put('/perfil/password', [AuthController::class, 'updatePassword'])->name('profile.password');

        // Professional Healthcare Accreditation (Client Submission)
        Route::post('/perfil/solicitud-profesional', [ProfessionalVerificationController::class, 'store'])->name('profile.verification.store');
    });
});

/*
|--------------------------------------------------------------------------
| Panel compartido: administración y personal clínico acreditado
|--------------------------------------------------------------------------
| Un profesional sólo clínico entra aquí sin ser administrador. Cada acción
| clínica vuelve a exigir la acreditación en su controlador.
*/
Route::middleware(['auth', 'panel'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/instituciones', [\App\Http\Controllers\Admin\InstitucionController::class, 'index'])->name('instituciones.index');
    Route::get('/instituciones/{institucion}', [\App\Http\Controllers\Admin\InstitucionController::class, 'show'])->name('instituciones.show');

    // Cola de atención de casos de crisis (se atiende de forma manual)
    Route::get('/cola-atencion', [\App\Http\Controllers\Admin\ColaAtencionController::class, 'index'])->name('cola.index');
    Route::post('/cola-atencion/{caso}/contacto', [\App\Http\Controllers\Admin\ColaAtencionController::class, 'contacto'])->name('cola.contacto');
    Route::post('/cola-atencion/{caso}/cerrar', [\App\Http\Controllers\Admin\ColaAtencionController::class, 'cerrar'])->name('cola.cerrar');

    // Ficha individual y protocolo
    Route::post('/instituciones/{institucion}/colaboradores/{membresia}/ficha', [\App\Http\Controllers\Admin\ExpedienteController::class, 'ficha'])->name('instituciones.ficha');
    Route::post('/instituciones/{institucion}/colaboradores/{membresia}/escalar', [\App\Http\Controllers\Admin\ExpedienteController::class, 'escalar'])->name('instituciones.ficha.escalar');
    Route::post('/instituciones/{institucion}/colaboradores/{membresia}/resumen', [\App\Http\Controllers\Admin\ExpedienteController::class, 'resumen'])->name('instituciones.ficha.resumen');
    Route::post('/instituciones/{institucion}/colaboradores/{membresia}/casos/{caso}/contacto', [\App\Http\Controllers\Admin\ExpedienteController::class, 'contacto'])->name('instituciones.caso.contacto');
    Route::post('/instituciones/{institucion}/colaboradores/{membresia}/casos/{caso}/cerrar', [\App\Http\Controllers\Admin\ExpedienteController::class, 'cerrar'])->name('instituciones.caso.cerrar');
});

/*
|--------------------------------------------------------------------------
| Sólo administración (is_admin = true)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/exportar-padron', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'exportarPadron'])->name('dashboard.exportar-padron');

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

    // Instituciones: alta y edición
    Route::post('/instituciones', [\App\Http\Controllers\Admin\InstitucionController::class, 'store'])->name('instituciones.store');
    Route::put('/instituciones/{institucion}', [\App\Http\Controllers\Admin\InstitucionController::class, 'update'])->name('instituciones.update');

    // Padrón por Excel
    Route::get('/instituciones/{institucion}/padron/plantilla', [\App\Http\Controllers\Admin\PadronController::class, 'plantilla'])->name('instituciones.padron.plantilla');
    Route::post('/instituciones/{institucion}/padron', [\App\Http\Controllers\Admin\PadronController::class, 'importar'])->name('instituciones.padron.importar');
    Route::get('/instituciones/{institucion}/padron/{carga}/problemas', [\App\Http\Controllers\Admin\PadronController::class, 'problemas'])->name('instituciones.padron.problemas');

    // Padrón: altas individuales, edición, baja y reactivación
    Route::post('/instituciones/{institucion}/personas', [\App\Http\Controllers\Admin\ColaboradorController::class, 'store'])->name('instituciones.personas.store');
    Route::put('/instituciones/{institucion}/personas/{membresia}', [\App\Http\Controllers\Admin\ColaboradorController::class, 'update'])->name('instituciones.personas.update');
    Route::post('/instituciones/{institucion}/personas/{membresia}/baja', [\App\Http\Controllers\Admin\ColaboradorController::class, 'baja'])->name('instituciones.personas.baja');
    Route::post('/instituciones/{institucion}/personas/{membresia}/reactivar', [\App\Http\Controllers\Admin\ColaboradorController::class, 'reactivar'])->name('instituciones.personas.reactivar');

    // Invitaciones por correo
    Route::post('/instituciones/{institucion}/invitaciones', [\App\Http\Controllers\Admin\InvitacionController::class, 'enviar'])->name('instituciones.invitaciones.enviar');

    // El panel viejo de «Altas y Estructura» se integró a cada institución.
    Route::redirect('/altas-estructura', '/admin/instituciones')->name('structure.index');
});

/*
|--------------------------------------------------------------------------
| Enlaces firmados que llegan por correo
|--------------------------------------------------------------------------
*/
Route::middleware('signed')->group(function () {
    Route::get('/invitacion/{membresia}', [\App\Http\Controllers\InvitacionAceptarController::class, 'mostrar'])->name('invitacion.mostrar');
    Route::post('/invitacion/{membresia}', [\App\Http\Controllers\InvitacionAceptarController::class, 'aceptar'])->name('invitacion.aceptar')->middleware('throttle:10,1');
    Route::get('/resumen-clinico/{entrega}', [\App\Http\Controllers\ResumenEntregaController::class, 'ver'])->name('resumen.ver');
});
