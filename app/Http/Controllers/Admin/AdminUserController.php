<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Membresia;
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
        $query = User::query()->with('membresiaActiva.institucion:id,slug,nombre_corto', 'membresiaActiva.departamento:id,nombre', 'institucionesAsignadas:id');

        if ($request->filled('rol')) {
            if ($request->rol === 'admin') {
                $query->where(function ($q) {
                    $q->where('is_admin', true)->orWhere('role', 'admin');
                });
            } elseif ($request->rol === 'profesional') {
                $query->whereIn('role', ['profesional', 'clinico'])->where('is_admin', false);
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
        $institutions = Institucion::orderBy('nombre_corto')->get(['id', 'nombre_corto']);

        $counts = [
            'todos' => User::count(),
            'admins' => User::where('is_admin', true)->orWhere('role', 'admin')->count(),
            'profesionales' => User::whereIn('role', ['profesional', 'clinico'])->where('is_admin', false)->count(),
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
            'perfil_profesional' => ['nullable', 'in:publica,clinico,ambos'], // sin valor: sólo publica, como antes
            'clinico_admin' => ['nullable', 'boolean'],
            'education_level' => ['required_if:role,profesional', 'nullable', 'in:licenciatura,especialidad,maestria,doctorado'],
            'license_number' => ['required_if:role,profesional', 'nullable', 'string', 'max:50'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'institution' => ['nullable', 'string', 'max:150'],
            'institucion_id' => ['nullable', 'exists:instituciones,id'],
            'instituciones_asignadas' => ['nullable', 'array'],
            'instituciones_asignadas.*' => ['integer', 'exists:instituciones,id'],
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
        $role = $request->role;

        DB::transaction(function () use ($request, $fullName, $role) {
            $user = new User([
                'name' => $fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'avatar_color' => $role === 'admin' ? 'dark' : ($role === 'profesional' ? 'lav' : 'sage'),
                'email_verified_at' => now(),
                'license_number' => $role === 'profesional' ? trim($request->license_number) : null,
                'institution' => $role === 'profesional' ? trim((string) $request->institution) : null,
            ]);
            $user->asignarRol($role, $request->perfil_profesional, $request->boolean('clinico_admin'));
            $user->save();

            $this->vincular($user, $request);

            if ($role === 'profesional') {
                $degrees = [
                    'licenciatura' => 'Licenciatura',
                    'especialidad' => 'Especialista',
                    'maestria' => 'Maestría',
                    'doctorado' => 'Doctorado',
                ];
                $degreeLabel = $degrees[$request->education_level] ?? 'Especialista';
                $specialtyText = !empty($request->specialty) ? trim($request->specialty) : 'Salud Mental & Bienestar';

                $user->update(['professional_title' => $specialtyText . ' · ' . $degreeLabel]);

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
            'perfil_profesional' => ['nullable', 'in:publica,clinico,ambos'], // sin valor: sólo publica, como antes
            'clinico_admin' => ['nullable', 'boolean'],
            'status' => ['required', 'in:activo,pendiente'],
            'password' => ['nullable', 'string', 'min:6'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
            'institucion_id' => ['nullable', 'exists:instituciones,id'],
            'instituciones_asignadas' => ['nullable', 'array'],
            'instituciones_asignadas.*' => ['integer', 'exists:instituciones,id'],
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
            if (strtolower(trim($request->email)) !== strtolower(trim($user->email))) {
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

        $role = $request->role;

        $user->name = trim($request->name);
        $user->email = trim($request->email);
        $user->asignarRol($role, $request->perfil_profesional, $request->boolean('clinico_admin'));

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

        DB::transaction(function () use ($user, $request) {
            $user->save();
            $this->vincular($user, $request);
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario "' . $user->name . '" actualizado exitosamente.');
    }

    /**
     * Un profesional clínico (perfil «clínico» o «ambos») queda asignado a las
     * instituciones que atiende, sin entrar a su padrón. Cualquier otra cuenta
     * se vincula como hasta ahora: al padrón de la institución elegida.
     */
    private function vincular(User $user, Request $request): void
    {
        if ($user->isClinicoAcreditado() && !$user->is_admin) {
            $user->institucionesAsignadas()->sync(array_map('intval', (array) $request->input('instituciones_asignadas', [])));

            return;
        }

        // Deja de ser clínico (o es administración, que ve todo): sin asignaciones.
        $user->institucionesAsignadas()->detach();
        $this->vincularInstitucion($user, $request->filled('institucion_id') ? (int) $request->institucion_id : null);
    }

    /**
     * Deja a la persona en el padrón de la institución elegida (o en ninguno).
     * Cambiar de institución da de baja la membresía anterior en vez de
     * borrarla, para no perder su historia.
     */
    private function vincularInstitucion(User $user, ?int $institucionId): void
    {
        $actual = $user->membresiaActiva()->first();

        if ($actual?->institucion_id === $institucionId) {
            return;
        }

        $actual?->update(['estado' => 'baja', 'baja_en' => now(), 'baja_motivo' => 'Cambio de institución desde administración de usuarios.']);

        if ($institucionId === null) {
            return;
        }

        $existente = Membresia::where('institucion_id', $institucionId)->where('user_id', $user->id)->first();
        $estado = $user->email_verified_at ? 'activo' : 'invitado';

        if ($existente) {
            $existente->update(['estado' => $estado, 'baja_en' => null, 'baja_motivo' => null]);
        } else {
            Membresia::create([
                'institucion_id' => $institucionId,
                'user_id' => $user->id,
                'rol_institucional' => 'colaborador',
                'estado' => $estado,
                'activado_en' => $estado === 'activo' ? now() : null,
            ]);
        }
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
