<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')->with('error', 'El inicio de sesión con Google requiere configurar GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en el archivo .env.');
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'No se pudo iniciar la conexión con Google: ' . $e->getMessage());
        }
    }

    public function handleGoogleCallback(Request $request)
    {
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

        // 2. Obtención de usuario desde Google con tolerancia a navegadores móviles/in-app (stateless)
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            Log::warning('Socialite stateless falló, intentando stateful: ' . $e->getMessage());
            try {
                $googleUser = Socialite::driver('google')->user();
            } catch (\Throwable $e2) {
                Log::error('Fallo definitivo al autenticar con Google: ' . $e2->getMessage(), [
                    'exception' => $e2,
                ]);
                return redirect()->route('login')->with('error', 'Ocurrió un error o se canceló la autenticación con Google. Por favor, intenta de nuevo.');
            }
        }

        if (!$googleUser || empty($googleUser->getEmail())) {
            return redirect()->route('login')->with('error', 'No se pudo obtener la información de tu cuenta de Google.');
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

            Auth::login($user, true);
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

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Si el usuario no ha verificado su correo, reenviar código si venció y dirigir a verificación
            if (!$user->hasVerifiedEmail()) {
                if (!$user->verification_code || now()->gt($user->verification_code_expires_at)) {
                    $code = $user->generateVerificationCode();
                    try {
                        Mail::to($user->email)->send(new EmailVerificationCodeMail($code, $user->name));
                    } catch (\Throwable $e) {
                        Log::error('Error al enviar código de verificación en login: ' . $e->getMessage());
                    }
                }

                return redirect()->route('verification.code.notice')
                    ->with('info', 'Por favor confirma el código de 6 dígitos enviado a tu correo para activar tu cuenta.');
            }

            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', '¡Bienvenido al Panel de Administración, ' . $user->name . '!');
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

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
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
            'password' => ['required', 'confirmed', Password::min(6)],
            'avatar_color' => ['nullable', 'string', 'in:sage,terra,lav,sky,amber,dark'],
        ], [
            'name.required' => 'Por favor escribe tu nombre.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar_color' => $validated['avatar_color'] ?? 'sage',
            'email_verified_at' => null,
        ]);

        $code = $user->generateVerificationCode();

        try {
            Mail::to($user->email)->send(new EmailVerificationCodeMail($code, $user->name));
        } catch (\Throwable $e) {
            Log::error('Error al enviar código de verificación al registrarse: ' . $e->getMessage());
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.code.notice')
            ->with('status', '¡Tu cuenta ha sido creada! Hemos enviado un código de 6 dígitos a tu correo electrónico para verificarla.');
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
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

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

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return back()->with('status', 'Te hemos enviado por correo el enlace para restablecer tu contraseña. Revisa tu bandeja de entrada o spam.');
        }

        $errorMsg = match ($status) {
            \Illuminate\Support\Facades\Password::INVALID_USER => 'No encontramos ninguna cuenta registrada con este correo electrónico.',
            \Illuminate\Support\Facades\Password::RESET_THROTTLED => 'Has realizado demasiados intentos. Por favor, espera unos minutos.',
            default => 'No se pudo enviar el enlace de recuperación en este momento.',
        };

        return back()->withErrors(['email' => $errorMsg])->withInput();
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
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

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
