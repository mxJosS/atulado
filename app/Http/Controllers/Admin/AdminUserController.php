<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                $query->where('role', 'profesional')->where('is_admin', false);
            } elseif ($request->rol === 'paciente') {
                $query->where('role', '!=', 'profesional')->where('is_admin', false);
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
            'profesionales' => User::where('role', 'profesional')->where('is_admin', false)->count(),
            'pacientes' => User::where('role', '!=', 'profesional')->where('is_admin', false)->count(),
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
            ->with('success', 'Usuario dado de alta exitosamente' . ($role === 'profesional' ? ' y validado como Profesional de la Salud.' : '.'));
    }
}
