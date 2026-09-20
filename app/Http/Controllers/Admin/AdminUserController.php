<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
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
        $query = User::query()->with('institution');

        if ($request->filled('rol')) {
            if ($request->rol === 'admin') {
                $query->where(function ($q) {
                    $q->where('is_admin', true)->orWhere('role', 'admin');
                });
            } elseif ($request->rol === 'profesional') {
                $query->where('role', 'profesional')->where('is_admin', false);
            } elseif ($request->rol === 'usuario') {
                $query->where(function ($q) {
                    $q->where('role', 'usuario')->orWhereNull('role');
                })->where('is_admin', false);
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
        $institutions = Institution::orderBy('name')->get();

        $counts = [
            'todos' => User::count(),
            'admins' => User::where('is_admin', true)->orWhere('role', 'admin')->count(),
            'profesionales' => User::where('role', 'profesional')->where('is_admin', false)->count(),
            'usuarios' => User::where(function ($q) {
                $q->where('role', 'usuario')->orWhereNull('role');
            })->where('is_admin', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'counts', 'institutions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => ['required', 'in:admin,profesional,usuario'],
            'education_level' => ['required_if:role,profesional', 'nullable', 'in:licenciatura,especialidad,maestria,doctorado'],
            'license_number' => ['required_if:role,profesional', 'nullable', 'string', 'max:50'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'institution' => ['nullable', 'string', 'max:150'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
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
        $institutionId = $request->filled('institution_id') ? (int) $request->institution_id : null;

        DB::transaction(function () use ($request, $fullName, $isAdmin, $role, $institutionId) {
            $user = User::create([
                'name' => $fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $isAdmin,
                'role' => $role,
                'avatar_color' => $isAdmin ? 'dark' : ($role === 'profesional' ? 'lav' : 'sage'),
                'email_verified_at' => now(),
                'institution_id' => $institutionId,
                'license_number' => $role === 'profesional' ? trim($request->license_number) : null,
                'institution' => $role === 'profesional' ? trim($request->institution) : null,
            ]);

            if ($user->institution_id) {
                $inst = Institution::find($user->institution_id);
                if ($inst) {
                    $cnt = $inst->users()->count();
                    $inst->users_count = $cnt;
                    $inst->active_count = $cnt;
                    $inst->save();
                }
            }

            if ($role === 'profesional') {
                $degrees = [
                    'licenciatura' => 'Licenciatura',
                    'especialidad' => 'Especialista',
                    'maestria' => 'Maestría',
                    'doctorado' => 'Doctorado',
                ];
                $degreeLabel = $degrees[$request->education_level] ?? 'Especialista';
                $specialtyText = !empty($request->specialty) ? trim($request->specialty) : 'Salud Mental & Bienestar';
                $computedTitle = $specialtyText . ' · ' . $degreeLabel;

                $user->update(['professional_title' => $computedTitle]);

                ProfessionalVerification::create([
                    'user_id' => $user->id,
                    'full_name' => $fullName,
                    'license_number' => trim($request->license_number),
                    'education_level' => $request->education_level,
                    'document_path' => null,
                    'status' => 'aprobada',
                    'admin_notes' => 'Alta directa y verificación automática autorizada por Dirección/Administración.' . (!empty($request->specialty) ? " Especialidad: {$request->specialty}" : ''),
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
            'role' => ['required', 'in:admin,profesional,usuario'],
            'status' => ['required', 'in:activo,pendiente'],
            'password' => ['nullable', 'string', 'min:6'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'role.required' => 'El rol del usuario es obligatorio.',
            'status.required' => 'El estado de la cuenta es obligatorio.',
        ]);

        $currentUser = Auth::user();

        // 1. Prohibir modificar la cuenta principal si no es el mismo
        if ($user->isSuperAdmin() && !$currentUser?->isSuperAdmin()) {
            return back()->with('error', 'No tienes autorización para modificar la cuenta principal de administración.');
        }

        // 2. Proteger la integridad de la cuenta principal de administración
        if ($user->isSuperAdmin()) {
            if (strtolower(trim($request->email)) !== 'admin@atulado.com.mx') {
                return back()->with('error', 'El correo electrónico de la cuenta principal no puede ser modificado.');
            }
            if ($request->role !== 'admin') {
                return back()->with('error', 'La cuenta principal debe mantener siempre el rol de administrador.');
            }
            if ($request->status !== 'activo') {
                return back()->with('error', 'La cuenta principal no puede ser suspendida.');
            }
        }

        // 3. Prohibir degradarse a sí mismo si es el admin actual
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'No puedes quitarte los permisos de administrador a ti mismo.');
        }

        // 4. Prohibir degradar al único administrador del sistema
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
        } elseif ($role === 'usuario') {
            $user->license_number = null;
            $user->institution = null;
            $user->professional_title = null;
        }

        $oldInstId = $user->institution_id;
        $newInstId = $request->filled('institution_id') ? (int) $request->institution_id : null;
        $user->institution_id = $newInstId;

        $user->save();

        if ($oldInstId !== $newInstId) {
            if ($oldInstId) {
                $oldInst = Institution::find($oldInstId);
                if ($oldInst) {
                    $cnt = $oldInst->users()->count();
                    $oldInst->users_count = $cnt;
                    $oldInst->active_count = $cnt;
                    $oldInst->save();
                }
            }
            if ($newInstId) {
                $newInst = Institution::find($newInstId);
                if ($newInst) {
                    $cnt = $newInst->users()->count();
                    $newInst->users_count = $cnt;
                    $newInst->active_count = $cnt;
                    $newInst->save();
                }
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario "' . $user->name . '" actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        // 1. Prohibir eliminar la cuenta principal bajo cualquier circunstancia
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'La cuenta principal de administración está protegida y no puede ser eliminada.');
        }

        // 2. Prohibir auto-eliminación
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }

        // 3. Solo la cuenta principal de administración puede eliminar a otros administradores
        if (($user->is_admin || $user->role === 'admin') && !$currentUser?->isSuperAdmin()) {
            return back()->with('error', 'Solo la cuenta principal de administración tiene autorización para eliminar a otros administradores.');
        }

        // 4. Prohibir eliminar al único administrador del sistema
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
