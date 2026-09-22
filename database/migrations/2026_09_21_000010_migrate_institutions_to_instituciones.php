<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Puente de datos del esquema viejo al nuevo.
     *
     * Reglas que se respetan aquí:
     *  - Es puramente aditiva: NUNCA escribe en `institutions` ni en `users`.
     *  - Usa el facade DB, no Eloquent: una migración debe ser una foto fija
     *    del esquema, y un modelo puede cambiar bajo sus pies.
     *  - Es idempotente: el Procfile corre `migrate --force` en cada arranque.
     *  - Absorbe los DOS formatos de departments_data que conviven hoy
     *    (unos grupos traen la clave 'name', otros 'macro_group').
     */
    public function up(): void
    {
        if (!Schema::hasTable('institutions')) {
            return;
        }

        // Ya se migró: no volver a hacerlo en el siguiente arranque.
        if (DB::table('instituciones')->exists()) {
            return;
        }

        $viejas = DB::table('institutions')->orderBy('id')->get();

        if ($viejas->isEmpty()) {
            return;
        }

        $rfcVistos = [];

        // Todo o nada: si una institución falla a medias, el siguiente arranque
        // vería `instituciones` con datos y ya no reintentaría el puente.
        DB::transaction(function () use ($viejas, &$rfcVistos) {
            foreach ($viejas as $vieja) {
                $institucionId = $this->crearInstitucion($vieja, $rfcVistos);
                $mapaDepartamentos = $this->crearDepartamentos($vieja, $institucionId);
                $this->crearMembresias($vieja, $institucionId, $mapaDepartamentos);
            }
        });

        Log::info('Puente institutions → instituciones completado', [
            'instituciones' => $viejas->count(),
        ]);
    }

    /**
     * El down sólo vacía las tablas nuevas. La tabla vieja nunca se tocó,
     * así que revertir el código deja el sistema exactamente como estaba.
     */
    public function down(): void
    {
        DB::table('cargas_padron_filas')->delete();
        DB::table('cargas_padron')->delete();
        DB::table('membresias')->delete();
        DB::table('departamentos')->delete();
        DB::table('instituciones')->delete();
    }

    private function crearInstitucion(object $vieja, array &$rfcVistos): int
    {
        $nombreCorto = $vieja->short_name ?: $vieja->name;
        $tieneUsuarios = DB::table('users')->where('institution_id', $vieja->id)->exists();

        // El RFC viejo admitía 20 caracteres y repetidos; el nuevo es único y de 13.
        // Lo que no cumple se anula en vez de romper la migración.
        $rfc = $this->normalizarRfc($vieja->rfc ?? null, $rfcVistos, $vieja->id);

        return (int) DB::table('instituciones')->insertGetId([
            'slug' => $vieja->slug,
            'razon_social' => $vieja->name,
            'nombre_corto' => Str::limit($nombreCorto, 77, '...'),
            'rfc' => $rfc,
            'sector' => $vieja->category ?? null,
            'tipo' => 'empresa',
            'ciudad' => $vieja->city ?? null,
            'estado_republica' => null,
            'zona_horaria' => 'America/Merida',
            'padron_estimado' => (int) ($vieja->users_count ?? 0),
            'contacto_nombre' => $vieja->contact_name ?? null,
            'contacto_puesto' => $vieja->contact_position ?? null,
            'contacto_email' => $vieja->contact_email ?? null,
            'contacto_telefono' => $vieja->contact_phone ?? null,
            'profesional_nombre' => $vieja->professional_name ?? null,
            'profesional_cedula' => isset($vieja->professional_license)
                ? Str::limit((string) $vieja->professional_license, 30, '')
                : null,
            'plan' => Str::limit((string) ($vieja->plan ?: 'Institucional Anual'), 100, ''),
            'vigencia_inicio' => $vieja->created_at ? substr((string) $vieja->created_at, 0, 10) : null,
            'vigencia_fin' => $vieja->renewal_date ?? null,
            'estado' => $tieneUsuarios ? 'activa' : 'onboarding',
            'etiqueta_nivel_1' => 'departamento',
            'aporta_perfil_estadistico' => true,
            'guardia_nocturna' => false,
            'redondear_porcentajes' => true,
            'color' => '#2E5D4B',
            'iniciales' => Str::upper(Str::substr($nombreCorto, 0, 2)),
            'created_at' => $vieja->created_at ?? now(),
            'updated_at' => now(),
        ]);
    }

    private function normalizarRfc(?string $rfc, array &$vistos, int $institucionId): ?string
    {
        $rfc = Str::upper(trim((string) $rfc));

        if ($rfc === '' || strlen($rfc) > 13 || strlen($rfc) < 12) {
            return null;
        }

        if (isset($vistos[$rfc])) {
            Log::warning("RFC duplicado al migrar; se deja vacío en la institución {$institucionId}", ['rfc' => $rfc]);

            return null;
        }

        $vistos[$rfc] = true;

        return $rfc;
    }

    /**
     * departments_data es un JSON de macro-grupos, cada uno con sus áreas.
     * El macro-grupo entra como departamento padre y cada área como hija,
     * así no se pierde la jerarquía que hoy vive en el JSON.
     *
     * @return array<string,int> nombre normalizado del área → id del departamento
     */
    private function crearDepartamentos(object $vieja, int $institucionId): array
    {
        $grupos = json_decode((string) ($vieja->departments_data ?? '[]'), true);
        $mapa = [];
        $clavesUsadas = [];

        if (!is_array($grupos)) {
            $grupos = [];
        }

        foreach ($grupos as $grupo) {
            if (!is_array($grupo)) {
                continue;
            }

            // Aquí muere la incompatibilidad de esquemas: unos traen 'name', otros 'macro_group'.
            $nombreGrupo = $grupo['name'] ?? $grupo['macro_group'] ?? 'General';

            $padreId = $this->insertarDepartamento(
                $institucionId,
                $nombreGrupo,
                null,
                $clavesUsadas,
                $grupo['shift'] ?? null
            );
            $mapa[$this->normalizar($nombreGrupo)] = $padreId;

            foreach ($grupo['departments'] ?? [] as $area) {
                if (!is_array($area) || empty($area['name'])) {
                    continue;
                }

                $hijoId = $this->insertarDepartamento(
                    $institucionId,
                    $area['name'],
                    $padreId,
                    $clavesUsadas,
                    $area['shift'] ?? null
                );
                $mapa[$this->normalizar($area['name'])] = $hijoId;
            }
        }

        // Toda institución necesita al menos un destino para las personas sin área.
        if ($mapa === []) {
            $mapa['__sin_asignar__'] = $this->insertarDepartamento(
                $institucionId,
                'Sin asignar',
                null,
                $clavesUsadas,
                null
            );
        }

        return $mapa;
    }

    private function insertarDepartamento(
        int $institucionId,
        string $nombre,
        ?int $parentId,
        array &$clavesUsadas,
        ?string $turno
    ): int {
        $base = Str::slug($nombre) ?: 'area';
        $clave = $base;
        $n = 2;

        // Se comprueba contra la base además del array en memoria: un área
        // puede haberse insertado en otra pasada de esta misma migración.
        while (isset($clavesUsadas[$clave]) || $this->claveOcupada($institucionId, $clave)) {
            $clave = $base . '-' . $n++;
        }
        $clavesUsadas[$clave] = true;

        return (int) DB::table('departamentos')->insertGetId([
            'institucion_id' => $institucionId,
            'parent_id' => $parentId,
            'nombre' => Str::limit($nombre, 250, ''),
            'clave' => Str::limit($clave, 250, ''),
            'turno_predominante' => $this->normalizarTurno($turno),
            'personas_esperadas' => 0,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Cada usuario vinculado a la institución vieja se convierte en membresía.
     * El usuario en sí no se toca: conserva su cuenta, su historial y su id.
     */
    private function crearMembresias(object $vieja, int $institucionId, array $mapaDepartamentos): void
    {
        $usuarios = DB::table('users')
            ->where('institution_id', $vieja->id)
            ->orderBy('id')
            ->get();

        $numerosUsados = [];

        foreach ($usuarios as $usuario) {
            $departamentoId = $mapaDepartamentos[$this->normalizar((string) ($usuario->department ?? ''))]
                ?? $mapaDepartamentos[$this->normalizar((string) ($usuario->macro_group ?? ''))]
                ?? $this->departamentoSinAsignar($institucionId, $mapaDepartamentos);

            DB::table('membresias')->insert([
                'institucion_id' => $institucionId,
                'user_id' => $usuario->id,
                'departamento_id' => $departamentoId,
                'numero_empleado' => $this->numeroUnico($usuario->employee_number ?? null, $numerosUsados),
                'puesto' => $usuario->position ?? null,
                'turno' => $this->normalizarTurno($usuario->shift ?? null),
                'idioma' => 'es',
                'rol_institucional' => 'colaborador',
                // Ya tienen cuenta y han podido entrar: nacen activos, no invitados.
                'estado' => 'activo',
                'activado_en' => $usuario->created_at ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function claveOcupada(int $institucionId, string $clave): bool
    {
        return DB::table('departamentos')
            ->where('institucion_id', $institucionId)
            ->where('clave', $clave)
            ->exists();
    }

    private function departamentoSinAsignar(int $institucionId, array &$mapa): int
    {
        if (!isset($mapa['__sin_asignar__'])) {
            $claves = [];
            $mapa['__sin_asignar__'] = $this->insertarDepartamento(
                $institucionId,
                'Sin asignar',
                null,
                $claves,
                null
            );
        }

        return $mapa['__sin_asignar__'];
    }

    private function numeroUnico(?string $numero, array &$usados): ?string
    {
        $numero = trim((string) $numero);

        if ($numero === '') {
            return null;
        }

        $candidato = $numero;
        $n = 2;

        while (isset($usados[$candidato])) {
            $candidato = $numero . '-' . $n++;
        }
        $usados[$candidato] = true;

        return $candidato;
    }

    private function normalizarTurno(?string $turno): ?string
    {
        $t = $this->normalizar((string) $turno);

        return match (true) {
            $t === '' => null,
            str_contains($t, 'matutino') => 'matutino',
            str_contains($t, 'vespertino') => 'vespertino',
            str_contains($t, 'nocturno') => 'nocturno',
            str_contains($t, 'mixto') => 'mixto',
            default => null,
        };
    }

    private function normalizar(string $valor): string
    {
        return Str::lower(trim(Str::ascii($valor)));
    }
};
