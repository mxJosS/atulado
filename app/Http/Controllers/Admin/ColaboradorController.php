<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Personas del padrón una por una: lo que antes hacía el panel viejo de
 * «Altas y Estructura». La carga masiva está en PadronController.
 */
class ColaboradorController extends Controller
{
    private function reglas(Institucion $institucion, ?Membresia $membresia = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => [$membresia ? 'nullable' : 'required', 'email', 'max:255'],
            'numero_empleado' => [
                'required', 'string', 'max:255',
                Rule::unique('membresias', 'numero_empleado')
                    ->where('institucion_id', $institucion->id)
                    ->ignore($membresia?->id),
            ],
            'departamento_id' => ['required', Rule::exists('departamentos', 'id')->where('institucion_id', $institucion->id)],
            'puesto' => ['nullable', 'string', 'max:255'],
            'turno' => ['nullable', 'in:matutino,vespertino,nocturno,mixto'],
            'horario' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio: con él la persona entra a A Tu Lado.',
            'correo.email' => 'El correo no es válido.',
            'numero_empleado.required' => 'El número de empleado es obligatorio.',
            'numero_empleado.unique' => 'Ese número de empleado ya lo tiene otra persona del padrón.',
            'departamento_id.required' => 'Elige el área de la persona.',
            'departamento_id.exists' => 'El área elegida no pertenece a esta institución.',
        ];
    }

    private function volver(Institucion $institucion)
    {
        return redirect()->to(route('admin.instituciones.show', $institucion) . '#tab-padron');
    }

    public function store(Request $request, Institucion $institucion)
    {
        $datos = $request->validate($this->reglas($institucion), $this->mensajes());
        $correo = Str::lower(trim($datos['correo']));

        $user = User::where('email', $correo)->first();
        if ($user && Membresia::where('institucion_id', $institucion->id)->where('user_id', $user->id)->exists()) {
            return back()->withInput()->withErrors(['correo' => 'Esa persona ya está en el padrón de esta institución.']);
        }

        DB::transaction(function () use ($user, $correo, $datos, $institucion) {
            $user ??= User::create([
                'name' => trim($datos['nombre']),
                'email' => $correo,
                // Aleatoria: la persona define la suya con la invitación.
                'password' => Hash::make(Str::random(40)),
                'role' => 'usuario',
                'avatar_color' => 'sage',
                'email_verified_at' => now(),
            ]);

            Membresia::create([
                'institucion_id' => $institucion->id,
                'user_id' => $user->id,
                'departamento_id' => $datos['departamento_id'],
                'numero_empleado' => trim($datos['numero_empleado']),
                'puesto' => $datos['puesto'] ?? null,
                'turno' => $datos['turno'] ?? null,
                'horario' => $datos['horario'] ?? null,
                'rol_institucional' => 'colaborador',
                'estado' => 'invitado',
            ]);
        });

        return $this->volver($institucion)
            ->with('success', trim($datos['nombre']) . ' se agregó al padrón. Envíale su invitación desde «Invitaciones».');
    }

    public function update(Request $request, Institucion $institucion, Membresia $membresia)
    {
        abort_unless($membresia->institucion_id === $institucion->id, 404);
        $datos = $request->validate($this->reglas($institucion, $membresia), $this->mensajes());

        $membresia->update([
            'departamento_id' => $datos['departamento_id'],
            'numero_empleado' => trim($datos['numero_empleado']),
            'puesto' => $datos['puesto'] ?? null,
            'turno' => $datos['turno'] ?? null,
            'horario' => $datos['horario'] ?? null,
        ]);

        // El nombre de una cuenta ya activada lo controla la persona.
        if ($membresia->estado !== 'activo') {
            $membresia->user->update(['name' => trim($datos['nombre'])]);
        }

        return $this->volver($institucion)->with('success', 'Datos de ' . $membresia->user->name . ' actualizados.');
    }

    public function baja(Request $request, Institucion $institucion, Membresia $membresia)
    {
        abort_unless($membresia->institucion_id === $institucion->id, 404);
        $datos = $request->validate(['motivo' => ['nullable', 'string', 'max:500']]);

        $membresia->update([
            'estado' => 'baja',
            'baja_en' => now(),
            'baja_motivo' => $datos['motivo'] ?? null,
        ]);

        return $this->volver($institucion)
            ->with('success', $membresia->user->name . ' se dio de baja. Su historial se conserva y se puede reactivar en los próximos 30 días.');
    }

    public function reactivar(Institucion $institucion, Membresia $membresia)
    {
        abort_unless($membresia->institucion_id === $institucion->id, 404);

        if (!$membresia->puedeReactivarse()) {
            return $this->volver($institucion)
                ->with('error', 'Sólo se puede reactivar dentro de los 30 días siguientes a la baja. Agrégala de nuevo al padrón.');
        }

        $membresia->update([
            'estado' => $membresia->activado_en ? 'activo' : 'invitado',
            'baja_en' => null,
            'baja_motivo' => null,
        ]);

        return $this->volver($institucion)->with('success', $membresia->user->name . ' volvió al padrón.');
    }
}
