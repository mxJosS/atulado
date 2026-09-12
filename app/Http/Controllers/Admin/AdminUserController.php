<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('rol')) {
            if ($request->rol === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->rol === 'profesional') {
                $query->where('is_admin', false);
            }
        }

        if ($request->filled('buscar')) {
            $term = $request->buscar;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%$term%")
                  ->orWhere('email', 'like', "%$term%")
                  ->orWhere('license_number', 'like', "%$term%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'todos' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'profesionales' => User::where('is_admin', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'counts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => ['required', 'in:admin,profesional'],
            'education_level' => ['required_if:role,profesional', 'nullable', 'in:licenciatura,especialidad,maestria,doctorado'],
            'license_number' => ['required_if:role,profesional', 'nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
        ], [
            'first_name.required' => 'El o los nombres son obligatorios.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'education_level.required_if' => 'El grado escolar es obligatorio para un profesional.',
            'license_number.required_if' => 'El número de cédula es obligatorio para un profesional.',
        ]);

        $fullName = trim($request->first_name . ' ' . $request->last_name);
        $isAdmin = $request->role === 'admin';
        $role = $request->role;

        DB::transaction(function () use ($request, $fullName, $isAdmin, $role) {
            $user = User::create([
                'name' => $fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $isAdmin,
                'role' => $role,
                'avatar_color' => $isAdmin ? 'dark' : 'sage',
                'email_verified_at' => now(),
                'license_number' => $role === 'profesional' ? trim($request->license_number) : null,
                'institution' => $role === 'profesional' ? trim($request->institution) : null,
            ]);

            if ($role === 'profesional') {
                $degrees = [
                    'licenciatura' => 'Lic. en Psicología / Salud Mental',
                    'especialidad' => 'Especialista en Salud Mental',
                    'maestria' => 'Mtro. en Psicología Clínica',
                    'doctorado' => 'Dr. en Psicología / Neurociencias',
                ];
                $computedTitle = $degrees[$request->education_level] ?? 'Especialista en Salud Mental';
                $user->update(['professional_title' => $computedTitle]);

                ProfessionalVerification::create([
                    'user_id' => $user->id,
                    'full_name' => $fullName,
                    'license_number' => trim($request->license_number),
                    'education_level' => $request->education_level,
                    'document_path' => null,
                    'status' => 'aprobada',
                    'admin_notes' => 'Alta directa y verificación automática autorizada por Dirección/Administración.',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado Exitosamente');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:admin,profesional'],
            'status' => ['required', 'in:activo,pendiente'],
            'password' => ['nullable', 'string', 'min:6'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'role.required' => 'El rol del usuario es obligatorio.',
            'status.required' => 'El estado de la cuenta es obligatorio.',
        ]);

        // 1. Prohibir degradarse a sí mismo si es el admin actual
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'No puedes quitarte los permisos de administrador a ti mismo.');
        }

        // 2. Prohibir degradar al único administrador del sistema
        if ($user->is_admin && $request->role !== 'admin' && User::where('is_admin', true)->where('id', '!=', $user->id)->count() === 0) {
            return back()->with('error', 'No es posible cambiar el rol al único administrador del sistema.');
        }

        $isAdmin = $request->role === 'admin';
        $role = $request->role;

        $user->name = trim($request->name);
        $user->email = trim($request->email);
        $user->is_admin = $isAdmin;
        $user->role = $role;

        // Gestión del Estado (Activo vs Pendiente)
        if ($request->status === 'activo') {
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
        } elseif ($request->status === 'pendiente') {
            // No permitir suspenderse al admin actual
            if ($user->id !== Auth::id()) {
                $user->email_verified_at = null;
            }
        }

        // Cambio de contraseña opcional
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        // Campos específicos de profesional
        if ($role === 'profesional') {
            $user->license_number = trim($request->license_number ?? '');
            $user->institution = trim($request->institution ?? '');
            if (empty($user->professional_title)) {
                $user->professional_title = 'Especialista en Salud Mental';
            }
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario "' . $user->name . '" actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        // 1. Prohibir auto-eliminación
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }

        // 2. Prohibir eliminar al único administrador del sistema
        if ($user->is_admin && User::where('is_admin', true)->where('id', '!=', $user->id)->count() === 0) {
            return back()->with('error', 'No es posible eliminar al único administrador del sistema.');
        }

        $userName = $user->name;

        DB::transaction(function () use ($user) {
            // Purgar sesiones activas del usuario
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario "' . $userName . '" eliminado exitosamente.');
    }
}
