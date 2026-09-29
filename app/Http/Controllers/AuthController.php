<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationCodeMail;
use App\Models\Ajuste;
use App\Models\DispositivoConfiable;
use App\Models\User;
use App\Support\Navegador;
use App\Support\Suplantacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function redirectToGoogle()
    {
        if (!Ajuste::googleHabilitado()) {
            return $this->googleDesactivado();
        }

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')->with('error', 'El inicio de sesión con Google requiere configurar GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en el archivo .env.');
        }

        try {
            // Con estado en sesión (protege contra el login CSRF) y PKCE.
            return Socialite::driver('google')->enablePKCE()->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'No se pudo iniciar la conexión con Google: ' . $e->getMessage());
        }
    }

    /** Acceso con Google apagado desde el panel: sólo con correo. */
    private function googleDesactivado()
    {
        return redirect()->route('login')
            ->with('error', 'El acceso con Google no está disponible en este momento. Entra o regístrate con tu correo institucional.');
    }

    public function handleGoogleCallback(Request $request)
    {
        // Si se apagó mientras la persona estaba en la pantalla de Google, tampoco entra por aquí.
        // Excepción: quien llegó por un enlace «Invitar por link» vigente, donde Google siempre está disponible.
        if (!Ajuste::googleHabilitado() && !app(\App\Services\EnlaceInstitucionService::class)->enSesion()) {
            return $this->googleDesactivado();
        }

        // 1. Manejo de errores devueltos directamente por Google en la URL
        if ($request->has('error')) {
            $errorCode = $request->get('error');
            Log::warning('Google OAuth devolvió error en callback', [
                'error' => $errorCode,
                'error_description' => $request->get('error_description'),
            ]);

            if ($errorCode === 'access_denied') {
                return redirect()->route('login')->with('error', 'Google denegó el acceso. Si el proyecto en Google Cloud está en "Modo de prueba", la cuenta debe registrarse en "Usuarios de prueba" o publicar la app.');
            }

            return redirect()->route('login')->with('error', 'La autenticación con Google fue cancelada o rechazada.');
        }

        // 2. Obtención de usuario desde Google. Siempre con estado: el parámetro
        //    «state» tiene que coincidir con el que se guardó en la sesión al salir
        //    hacia Google; si no, alguien podría meter a otra persona en su cuenta.
        try {
            $googleUser = Socialite::driver('google')->enablePKCE()->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('Google OAuth: el parámetro state no coincide con la sesión.');

            return redirect()->route('login')->with('error', 'La sesión con Google expiró o se abrió en otro navegador. Vuelve a presionar «Acceder con Google» desde esta misma ventana.');
        } catch (\Throwable $e) {
            Log::error('Fallo al autenticar con Google: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()->route('login')->with('error', 'Ocurrió un error o se canceló la autenticación con Google. Por favor, intenta de nuevo.');
        }

        if (!$googleUser || empty($googleUser->getEmail())) {
            return redirect()->route('login')->with('error', 'No se pudo obtener la información de tu cuenta de Google.');
        }

        // Sólo correos que Google verificó: si no, cualquiera podría registrar en
        // Google un correo ajeno y entrar a la cuenta de esa persona aquí.
        if (!filter_var($googleUser->user['email_verified'] ?? $googleUser->user['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->with('error', 'Google no ha verificado ese correo. Entra con tu correo y contraseña.');
        }

        // 3. Procesamiento y persistencia en base de datos con captura integral de excepciones
        try {
            $email = strtolower(trim($googleUser->getEmail()));
            $googleId = (string) $googleUser->getId();
            $avatarUrl = $googleUser->getAvatar();

            // Buscar si existe usuario por google_id o por correo electrónico
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            // Administración y equipo clínico entran sólo con contraseña y segundo
            // factor: Google no les pediría el código.
            if ($user && $user->requiereSegundoFactor()) {
                return redirect()->route('login')->with('error', 'Las cuentas de administración y del equipo clínico entran con correo y contraseña, no con Google.');
            }

            if ($user) {
                $user->google_id = $googleId;
                if (empty($user->avatar) && $avatarUrl) {
                    $user->avatar = $avatarUrl;
                }
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                $user->save();
            } else {
                $user = User::create([
                    'name' => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Usuario Google'),
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $avatarUrl,
                    'avatar_color' => 'sage',
                    'role' => 'usuario',
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(32)),
                ]);
            }

            // Sin «recordarme»: la sesión dura lo normal y no ~400 días.
            Auth::login($user);
            $request->session()->regenerate();

            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', '¡Bienvenido(a) al Panel de Administración, ' . $user->name . '!');
            }

            $intended = session()->get('url.intended');
            if ($intended && (str_contains($intended, '/admin') || str_contains($intended, 'solicitudes-profesionales'))) {
                session()->forget('url.intended');
                return redirect()->route('dashboard')
                    ->with('success', '¡Bienvenido(a) a tu espacio seguro, ' . $user->name . '!');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', '¡Bienvenido(a) a tu espacio seguro, ' . $user->name . '!');

        } catch (\Throwable $e) {
            Log::error('Error crítico al procesar usuario de Google: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $googleUser->getEmail() ?? null,
            ]);

            return redirect()->route('login')->with('error', 'Ocurrió un error al procesar tu cuenta de usuario. Por favor, intenta de nuevo o inicia con correo.');
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un formato de correo válido.',
            'password.required' => 'Por favor ingresa tu contraseña.',
        ]);

        $remember = $request->boolean('remember');

        // Administración y equipo clínico: desde un dispositivo nuevo (otra IP,
        // otro navegador u otro equipo) la contraseña correcta no basta. Se manda
        // un código al correo y la sesión se abre hasta escribirlo.
        $cuenta = User::where('email', $credentials['email'])->first();
        if ($cuenta && $cuenta->requiereSegundoFactor() && config('atulado.segundo_factor') && Auth::validate($credentials)) {
            if ($dispositivo = DispositivoConfiable::reconoce($cuenta, $request)) {
                $dispositivo->update(['ultimo_uso_en' => now()]);
                Auth::login($cuenta, $remember);
                $request->session()->regenerate();

                return $this->despuesDeEntrar($cuenta);
            }

            $request->session()->regenerate();
            $request->session()->put('dos_pasos', ['user_id' => $cuenta->id, 'remember' => $remember, 'intentos' => 0]);
            $this->enviarCodigoAcceso($cuenta, $request);

            return redirect()->route('acceso.codigo');
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return $this->despuesDeEntrar(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /* ─────────── Segundo factor por correo ─────────── */

    public const MAX_INTENTOS_CODIGO = 5;

    private function enviarCodigoAcceso(User $user, Request $request): void
    {
        $codigo = (string) random_int(100000, 999999);
        $user->forceFill([
            'dos_pasos_codigo' => Hash::make($codigo),
            'dos_pasos_expira_en' => now()->addMinutes(10),
        ])->save();

        try {
            Mail::to($user->email)->send(new EmailVerificationCodeMail(
                $codigo, $user->name, 'acceso', Navegador::nombre($request->userAgent()), $request->ip(),
            ));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el código de acceso: ' . $e->getMessage());
        }
    }

    private function cuentaEnSegundoFactor(Request $request): ?User
    {
        $pendiente = $request->session()->get('dos_pasos');

        return $pendiente ? User::find($pendiente['user_id'] ?? 0) : null;
    }

    public function showCodigoAcceso(Request $request)
    {
        $user = $this->cuentaEnSegundoFactor($request);
        if (!$user) {
            return redirect()->route('login');
        }

        return view('auth.verify-code', ['modo' => 'acceso', 'correo' => $user->email]);
    }

    public function verificarCodigoAcceso(Request $request)
    {
        $user = $this->cuentaEnSegundoFactor($request);
        if (!$user) {
            return redirect()->route('login')->with('error', 'Tu intento de acceso expiró. Vuelve a escribir tu correo y contraseña.');
        }

        $request->validate(['code' => ['required', 'string', 'size:6']], [
            'code.required' => 'Escribe el código de 6 dígitos.',
            'code.size' => 'El código tiene exactamente 6 dígitos.',
        ]);

        $valido = $user->dos_pasos_codigo
            && $user->dos_pasos_expira_en?->isFuture()
            && Hash::check((string) $request->input('code'), $user->dos_pasos_codigo);

        if (!$valido) {
            $pendiente = $request->session()->get('dos_pasos');
            $pendiente['intentos'] = ($pendiente['intentos'] ?? 0) + 1;

            if ($pendiente['intentos'] >= self::MAX_INTENTOS_CODIGO || !$user->dos_pasos_expira_en?->isFuture()) {
                $user->forceFill(['dos_pasos_codigo' => null, 'dos_pasos_expira_en' => null])->save();
                $request->session()->forget('dos_pasos');
                Log::warning("Segundo factor: acceso cancelado para la cuenta {$user->id} desde {$request->ip()} (código vencido o demasiados errores).");

                return redirect()->route('login')->with('error', 'El código venció o se escribió mal demasiadas veces. Vuelve a entrar con tu contraseña para recibir uno nuevo.');
            }

            $request->session()->put('dos_pasos', $pendiente);

            return back()->withErrors(['code' => 'El código no es correcto. Te quedan ' . (self::MAX_INTENTOS_CODIGO - $pendiente['intentos']) . ' intentos.']);
        }

        $recordar = (bool) ($request->session()->get('dos_pasos.remember') ?? false);
        $user->forceFill(['dos_pasos_codigo' => null, 'dos_pasos_expira_en' => null])->save();
        $request->session()->forget('dos_pasos');

        Auth::login($user, $recordar);
        $request->session()->regenerate();

        // Este dispositivo queda confirmado: desde aquí (misma IP y navegador) ya no se pide código.
        Cookie::queue(Cookie::make(
            DispositivoConfiable::COOKIE,
            DispositivoConfiable::confiar($user, $request),
            60 * 24 * 365, null, null, null, true, false, 'lax',
        ));

        return $this->despuesDeEntrar($user);
    }

    public function reenviarCodigoAcceso(Request $request)
    {
        $user = $this->cuentaEnSegundoFactor($request);
        if (!$user) {
            return redirect()->route('login');
        }

        $this->enviarCodigoAcceso($user, $request);
        $request->session()->put('dos_pasos.intentos', 0);

        return back()->with('status', 'Te enviamos un código nuevo. El anterior ya no sirve.');
    }

    public function cancelarCodigoAcceso(Request $request)
    {
        $request->session()->forget('dos_pasos');

        return redirect()->route('login');
    }

    /** A dónde va cada cuenta al entrar. */
    private function despuesDeEntrar(User $user)
    {
        // Si es administrador, acceso directo sin interrupciones
        if ($user->is_admin) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', '¡Bienvenido al Panel de Administración, ' . $user->name . '!');
        }

        // Si el usuario no ha verificado su correo, reenviar código si venció y dirigir a verificación
        if (!$user->hasVerifiedEmail()) {
            if (!$user->verification_code || now()->gt($user->verification_code_expires_at)) {
                try {
                    $code = $user->generateVerificationCode();
                    Mail::to($user->email)->send(new EmailVerificationCodeMail($code, $user->name));
                } catch (\Throwable $e) {
                    Log::error('Error al enviar código de verificación en login: ' . $e->getMessage());
                }
            }

            return redirect()->route('verification.code.notice')
                ->with('info', 'Por favor confirma el código de 6 dígitos enviado a tu correo para activar tu cuenta.');
        }

        $intended = session()->get('url.intended');
        if ($intended && (str_contains($intended, '/admin') || str_contains($intended, 'solicitudes-profesionales'))) {
            session()->forget('url.intended');
            return redirect()->route('dashboard')
                ->with('success', '¡Bienvenido de vuelta, ' . $user->name . '!');
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', '¡Bienvenido de vuelta, ' . $user->name . '!');
    }

    /**
     * Consentimiento expreso para datos sensibles (LFPDPPP): casillas
     * obligatorias sin marcar de inicio; las finalidades secundarias son
     * opcionales y también empiezan sin marcar.
     */
    public static function reglasConsentimiento(): array
    {
        return [
            'acepto_aviso' => ['accepted'],
            'acepto_sensibles' => ['accepted'],
            'mayor_de_edad' => ['accepted'],
            'acepta_investigacion' => ['nullable', 'boolean'],
            'acepta_boletin' => ['nullable', 'boolean'],
        ];
    }

    public static function mensajesConsentimiento(): array
    {
        return [
            'acepto_aviso.accepted' => 'Para crear tu cuenta necesitas aceptar el aviso de privacidad y los términos.',
            'acepto_sensibles.accepted' => 'Necesitamos tu consentimiento expreso para tratar tus datos de bienestar emocional: sin ellos el servicio no puede funcionar.',
            'mayor_de_edad.accepted' => 'A tu lado es para personas mayores de edad. Si eres menor, entra por la invitación de tu escuela.',
        ];
    }

    /** Pantalla de consentimiento para quien entró sin darlo (Google, alta por administración, cuentas previas). */
    public function showConsentimiento(Request $request)
    {
        if ($request->user()->tieneConsentimiento()) {
            return redirect()->route('dashboard');
        }

        return view('auth.consentimiento');
    }

    public function guardarConsentimiento(Request $request)
    {
        $request->validate(self::reglasConsentimiento(), self::mensajesConsentimiento());
        $request->user()->registrarConsentimiento($request->boolean('acepta_investigacion'), $request->boolean('acepta_boletin'));

        return redirect()->intended(route('dashboard'))->with('success', 'Gracias. Ya puedes usar A tu lado.');
    }

    /** Finalidades secundarias desde el perfil: se pueden cambiar cuando se quiera. */
    public function updatePreferenciasDatos(Request $request)
    {
        $datos = $request->validate([
            'acepta_investigacion' => ['nullable', 'boolean'],
            'acepta_boletin' => ['nullable', 'boolean'],
        ]);

        $request->user()->forceFill([
            'acepta_investigacion' => (bool) ($datos['acepta_investigacion'] ?? false),
            'acepta_boletin' => (bool) ($datos['acepta_boletin'] ?? false),
        ])->save();

        return back()->with('success', 'Tus preferencias sobre el uso de tus datos se guardaron.');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'avatar_color' => ['nullable', 'string', 'in:sage,mint,terra,lav,sky,amber,dark'],
        ] + self::reglasConsentimiento(), [
            'name.required' => 'Por favor escribe tu nombre.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'avatar_color.in' => 'El tono de avatar seleccionado no es válido.',
        ] + self::mensajesConsentimiento());

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
                'avatar_color' => $validated['avatar_color'] ?? 'sage',
                'email_verified_at' => null,
            ]);
            $user->registrarConsentimiento($request->boolean('acepta_investigacion'), $request->boolean('acepta_boletin'));

            $code = $user->generateVerificationCode();

            $mailSent = false;
            try {
                Mail::to($user->email)->send(new EmailVerificationCodeMail($code, $user->name));
                $mailSent = true;
            } catch (\Throwable $e) {
                Log::error('Error al enviar código de verificación al registrarse: ' . $e->getMessage());
            }

            Auth::login($user);
            $request->session()->regenerate();

            $msg = $mailSent
                ? '¡Tu cuenta ha sido creada! Hemos enviado un código de 6 dígitos a tu correo (' . $user->email . ') para activarla.'
                : '¡Tu cuenta ha sido creada! Si no recibiste el código de inmediato, haz clic en "Reenviar código" para solicitar uno nuevo.';

            return redirect()->route('verification.code.notice')->with('status', $msg);

        } catch (\Throwable $e) {
            Log::error('Error crítico al registrar usuario: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors([
                'email' => 'Ocurrió un inconveniente al registrar la cuenta. Por favor verifica tus datos e inténtalo de nuevo.',
            ]);
        }
    }

    public function showVerifyCode()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-code');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Por favor ingresa el código de 6 dígitos.',
            'code.size' => 'El código de verificación debe tener exactamente 6 dígitos.',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        if (!$user->isVerificationCodeValid($request->code)) {
            if ($user->verification_code_expires_at && now()->gt($user->verification_code_expires_at)) {
                return back()->withErrors(['code' => 'El código de verificación ha expirado. Haz clic en "Reenviar código" para obtener uno nuevo.']);
            }

            return back()->withErrors(['code' => 'El código de verificación es incorrecto. Por favor verifica e intenta de nuevo.']);
        }

        $user->markEmailAsVerified();

        if ($user->is_admin) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', '¡Correo verificado con éxito! Bienvenido al Panel de Administración.');
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', '¡Correo verificado con éxito! Bienvenido a tu espacio seguro.');
    }

    public function resendVerificationCode(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $code = $user->generateVerificationCode();

        try {
            Mail::to($user->email)->send(new EmailVerificationCodeMail($code, $user->name));
        } catch (\Throwable $e) {
            Log::error('Error al reenviar código de verificación: ' . $e->getMessage());
            return back()->withErrors(['code' => 'No se pudo enviar el correo en este momento. Por favor intenta en un momento.']);
        }

        return back()->with('status', 'Hemos enviado un nuevo código de 6 dígitos a tu correo electrónico.');
    }

    public function logout(Request $request)
    {
        // Dentro de otra cuenta con «Entrar como», salir regresa a la cuenta principal.
        if (Suplantacion::activa() && Suplantacion::terminar()) {
            return redirect()->route('admin.users.index')->with('success', 'Volviste a tu cuenta.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Has cerrado sesión con tranquilidad. Estamos aquí cuando nos necesites.');
    }

    public function showProfile()
    {
        $user = Auth::user();
        $latestVerification = $user->latestProfessionalVerification;
        return view('dashboard.perfil', compact('user', 'latestVerification'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'remove_avatar' => ['nullable'],
            'bio' => ['nullable', 'string', 'max:300'],
            'crisis_contact_name' => ['nullable', 'string', 'max:100'],
            'crisis_contact_phone' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'El nombre o apodo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un formato de correo válido.',
            'email.unique' => 'Este correo electrónico ya pertenece a otro usuario.',
            'avatar.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'avatar.mimes' => 'La foto de perfil debe ser formato JPG, PNG o WEBP.',
            'avatar.max' => 'La foto de perfil no debe superar los 4MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->bio = $validated['bio'] ?? null;
        $user->crisis_contact_name = $validated['crisis_contact_name'] ?? null;
        $user->crisis_contact_phone = $validated['crisis_contact_phone'] ?? null;
        $user->save();

        return back()->with('success', 'Tu perfil y foto han sido actualizados con éxito.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);
        // Con contraseña nueva, cada dispositivo vuelve a confirmarse con código.
        DispositivoConfiable::olvidarTodos(Auth::user());

        return back()->with('success', 'Tu contraseña ha sido modificada con éxito.');
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            Log::info("Recuperación de contraseña sin envío ({$status}).");
        }

        // La misma respuesta exista o no la cuenta: así no se puede averiguar
        // qué correos están registrados.
        return back()->with('status', 'Si ese correo tiene una cuenta en A tu lado, te enviamos un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada y la carpeta de spam.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();
                DispositivoConfiable::olvidarTodos($user);

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Tu contraseña ha sido restablecida con éxito. Ya puedes iniciar sesión con tu nueva clave.');
        }

        $errorMsg = match ($status) {
            \Illuminate\Support\Facades\Password::INVALID_USER => 'No encontramos ningún usuario con ese correo electrónico.',
            \Illuminate\Support\Facades\Password::INVALID_TOKEN => 'El enlace de recuperación es inválido o ya ha expirado.',
            default => 'Ocurrió un error al restablecer la contraseña.',
        };

        return back()->withErrors(['email' => $errorMsg]);
    }
}
