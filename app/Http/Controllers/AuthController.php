<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Ocurrió un error o se canceló la autenticación con Google. Por favor, intenta de nuevo.');
        }

        if (!$googleUser || empty($googleUser->getEmail())) {
            return redirect()->route('login')->with('error', 'No se pudo obtener la información de tu cuenta de Google.');
        }

        // Buscar si existe usuario por google_id o por correo electrónico
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->google_id = $googleUser->getId();
            if (empty($user->avatar) && $googleUser->getAvatar()) {
                $user->avatar = $googleUser->getAvatar();
            }
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Usuario Google'),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'avatar_color' => 'sage',
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

        return redirect()->intended(route('dashboard'))
            ->with('success', '¡Bienvenido(a) a tu espacio seguro, ' . $user->name . '!');
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
            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', '¡Bienvenido al Panel de Administración, ' . $user->name . '!');
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
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', '¡Tu cuenta ha sido creada exitosamente! Bienvenido a tu espacio seguro.');
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
}
