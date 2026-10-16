<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraAdmin;
use App\Models\DispositivoConfiable;
use App\Models\Institucion;
use App\Services\EliminacionCuentaService;
use App\Models\Membresia;
use App\Models\ProfessionalVerification;
use App\Models\User;
use App\Support\Suplantacion;
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
            'password' => ['required', 'confirmed', Password::min(8)],
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
            'invitar_por_link' => ['nullable', 'array'],
            'invitar_por_link.*' => ['integer'],
        ], [
            'first_name.required' => 'El o los nombres son obligatorios.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'education_level.required_if' => 'El grado escolar es obligatorio para un profesional.',
            'license_number.required_if' => 'El número de cédula es obligatorio para un profesional.',
        ]);

        $fullName = trim($request->first_name . ' ' . $request->last_name);
        $role = $request->role;

        $creado = DB::transaction(function () use ($request, $fullName, $role) {
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

            return $user;
        });

        $invita = self::invitaPorLink($creado);
        BitacoraAdmin::registrar('usuario_creado', $creado, "Alta de {$creado->email} con permisos: " . self::permisos($creado)
            . ($invita !== 'ninguna' ? "; invita por link en {$invita}" : '') . '.');

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
            'admin_solo_lectura' => ['nullable', 'boolean'],
            'status' => ['required', 'in:activo,pendiente'],
            'cambiar_password' => ['nullable', 'boolean'],
            'password' => ['exclude_unless:cambiar_password,1,true', 'required', 'string', 'min:8'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
            'institucion_id' => ['nullable', 'exists:instituciones,id'],
            'instituciones_asignadas' => ['nullable', 'array'],
            'instituciones_asignadas.*' => ['integer', 'exists:instituciones,id'],
            'invitar_por_link' => ['nullable', 'array'],
            'invitar_por_link.*' => ['integer'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
            'password.required' => 'Escribe la nueva contraseña o desmarca «Cambiar la contraseña».',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
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
        $antes = ['solo_lectura' => (bool) $user->admin_solo_lectura, 'permisos' => self::permisos($user), 'invita' => self::invitaPorLink($user), 'email' => $user->email, 'verificado' => (bool) $user->email_verified_at];

        $user->name = trim($request->name);
        $user->email = trim($request->email);
        $user->asignarRol($role, $request->perfil_profesional, $request->boolean('clinico_admin'));

        // Sólo la cuenta principal decide quién administra en sólo lectura; nunca a sí misma.
        // ponytail: por ahora sólo se guarda; falta bloquear las acciones de escritura.
        if ($currentUser?->isSuperAdmin() && !$user->isSuperAdmin()) {
            $user->admin_solo_lectura = $role === 'admin' && $request->boolean('admin_solo_lectura');
        }

        // Gestión del Estado (Activo vs Pendiente). Es el mismo estado que ve el
        // padrón: «Activo» también activa su lugar en el padrón y «Pendiente»
        // lo regresa a «Sin activar».
        $enPadron = Membresia::where('user_id', $user->id)->whereIn('estado', ['invitado', 'activo'])->exists();
        if ($request->status === 'activo') {
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
        } elseif ($request->status === 'pendiente') {
            // No permitir suspenderse al admin actual. A quien está en un padrón no
            // se le quita la verificación del correo: sólo vuelve a «Sin activar».
            if ($user->id !== Auth::id() && !$enPadron) {
                $user->email_verified_at = null;
            }
        }

        // La contraseña sólo cambia si se pidió expresamente: un valor que llegue
        // sin la casilla (p. ej. el autocompletado del navegador) se ignora.
        if ($request->boolean('cambiar_password') && filled($request->password)) {
            $user->password = Hash::make($request->password);
            DispositivoConfiable::olvidarTodos($user);
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

            if ($request->status === 'activo') {
                Membresia::where('user_id', $user->id)->where('estado', 'invitado')
                    ->update(['estado' => 'activo', 'activado_en' => now()]);
            } elseif ($request->status === 'pendiente' && $user->id !== Auth::id()) {
                Membresia::where('user_id', $user->id)->where('estado', 'activo')
                    ->update(['estado' => 'invitado', 'activado_en' => null]);
            }
        });

        $cambios = array_filter([
            $antes['permisos'] !== self::permisos($user) ? "permisos {$antes['permisos']} → " . self::permisos($user) : null,
            $antes['invita'] !== self::invitaPorLink($user) ? "invita por link en {$antes['invita']} → " . self::invitaPorLink($user) : null,
            $antes['email'] !== $user->email ? "correo {$antes['email']} → {$user->email}" : null,
            $antes['verificado'] !== (bool) $user->email_verified_at ? 'estado → ' . $request->status : null,
            $request->boolean('cambiar_password') ? 'contraseña cambiada por administración' : null,
            $antes['solo_lectura'] !== (bool) $user->admin_solo_lectura ? 'sólo lectura → ' . ($user->admin_solo_lectura ? 'sí' : 'no') : null,
        ]);
        BitacoraAdmin::registrar($cambios === [] ? 'usuario_editado' : 'usuario_permisos', $user,
            "Edición de {$user->email}" . ($cambios ? ': ' . implode('; ', $cambios) : ' sin cambios de permisos') . '.');

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario "' . $user->name . '" actualizado exitosamente.');
    }

    /** Resumen legible de los permisos de una cuenta, para la bitácora. */
    private static function permisos(User $user): string
    {
        return implode('+', array_filter([
            $user->role ?: 'usuario',
            $user->is_admin ? 'admin' : null,
            $user->is_clinico_atulado ? 'clínico' : null,
        ]));
    }

    /** Instituciones donde una cuenta clínica puede invitar por link, para la bitácora. */
    private static function invitaPorLink(User $user): string
    {
        $nombres = $user->institucionesAsignadas()->wherePivot('puede_invitar_por_link', true)
            ->orderBy('nombre_corto')->pluck('nombre_corto')->all();

        return $nombres ? implode(', ', $nombres) : 'ninguna';
    }

    /**
     * Un profesional clínico (perfil «clínico» o «ambos») queda asignado a las
     * instituciones que atiende, sin entrar a su padrón, y en cada una puede
     * tener permiso de invitar por link. Cualquier otra cuenta se vincula como
     * hasta ahora: al padrón de la institución elegida.
     */
    private function vincular(User $user, Request $request): void
    {
        if ($user->isClinicoAcreditado() && !$user->is_admin) {
            // El permiso sólo cuenta en instituciones que atiende.
            $invita = array_map('intval', (array) $request->input('invitar_por_link', []));
            $asignaciones = collect((array) $request->input('instituciones_asignadas', []))
                ->mapWithKeys(fn ($id) => [(int) $id => ['puede_invitar_por_link' => in_array((int) $id, $invita, true)]])
                ->all();
            $user->institucionesAsignadas()->sync($asignaciones);

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

    /**
     * «Entrar como»: la cuenta principal abre la sesión de esta persona para
     * probar la plataforma. Se vuelve con «Volver a mi cuenta» o al cerrar sesión.
     */
    public function entrar(User $user)
    {
        $admin = Auth::user();

        if (!$admin->puedeEntrarComo($user)) {
            return back()->with('error', 'Sólo las cuentas principales de administración pueden entrar a otra cuenta, y no a otra cuenta principal.');
        }

        Suplantacion::iniciar($admin, $user);

        return redirect()->route($user->is_admin ? 'admin.dashboard' : 'dashboard')
            ->with('info', "Entraste como {$user->name}. Para regresar usa «Volver a mi cuenta».");
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
        BitacoraAdmin::registrar('usuario_eliminado', $user, "Eliminación de la cuenta {$user->email} (" . self::permisos($user) . ').');

        // Borra cuenta, sesiones y archivos; casos y bitácora clínica quedan seudonimizados.
        app(EliminacionCuentaService::class)->eliminar($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario "' . $userName . '" eliminado exitosamente.');
    }
}
