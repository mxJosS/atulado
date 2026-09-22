<?php

namespace App\Services;

use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Membresia;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Macro-áreas y áreas de una institución (dos niveles).
 *
 * Los nombres son únicos dentro de la institución sin distinguir mayúsculas
 * ni acentos, porque la carga del padrón por Excel asigna a cada persona
 * su área por nombre: dos áreas iguales harían ambigua la asignación.
 */
class EstructuraInstitucionalService
{
    /**
     * Convierte "Operaciones, Corporativo , ,Logística" en una lista limpia,
     * sin vacíos ni repetidos.
     *
     * @return list<string>
     */
    public static function parsearLista(?string $texto): array
    {
        $vistos = [];
        $resultado = [];

        foreach (explode(',', (string) $texto) as $pieza) {
            $nombre = trim(preg_replace('/\s+/u', ' ', $pieza));

            if ($nombre === '') {
                continue;
            }

            $clave = self::normalizar($nombre);
            if (isset($vistos[$clave])) {
                continue;
            }

            $vistos[$clave] = true;
            $resultado[] = Str::limit($nombre, 150, '');
        }

        return $resultado;
    }

    public static function normalizar(string $nombre): string
    {
        return Str::lower(trim(Str::ascii($nombre)));
    }

    /**
     * @param  list<string>  $nombres
     */
    public function crearMacroAreas(Institucion $institucion, array $nombres): void
    {
        foreach ($nombres as $nombre) {
            $this->crearArea($institucion, $nombre, null);
        }
    }

    /**
     * Personas en padrón (no dadas de baja) por área.
     *
     * @return array<int,int>
     */
    public function personasPorArea(Institucion $institucion): array
    {
        return Membresia::where('institucion_id', $institucion->id)
            ->enPadron()
            ->whereNotNull('departamento_id')
            ->selectRaw('departamento_id, count(*) as total')
            ->groupBy('departamento_id')
            ->pluck('total', 'departamento_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * Estructura lista para mostrar: macro-áreas con sus áreas y cuántas
     * personas tiene cada una (la macro-área suma las de sus áreas).
     *
     * @return list<array{modelo: Departamento, personas: int, hijos: list<array{modelo: Departamento, personas: int}>}>
     */
    public function arbol(Institucion $institucion): array
    {
        $conteos = $this->personasPorArea($institucion);
        $todas = $institucion->departamentos()->get();
        $arbol = [];

        foreach ($todas->whereNull('parent_id') as $macro) {
            $hijos = [];

            foreach ($todas->where('parent_id', $macro->id) as $hijo) {
                $hijos[] = ['modelo' => $hijo, 'personas' => $conteos[$hijo->id] ?? 0];
            }

            $arbol[] = [
                'modelo' => $macro,
                'personas' => ($conteos[$macro->id] ?? 0) + array_sum(array_column($hijos, 'personas')),
                'hijos' => $hijos,
            ];
        }

        return $arbol;
    }

    /**
     * Aplica lo que llega del formulario de edición:
     *   areas[ID][nombre]  → renombrar
     *   areas[ID][quitar]  → quitar (sólo si no tiene personas)
     *   areas[ID][nuevas]  → áreas nuevas dentro de esa macro-área
     *   $nuevasMacro       → macro-áreas nuevas
     *
     * Valida todo antes de escribir nada.
     *
     * @param  array<int|string,array<string,mixed>>  $cambios
     * @param  list<string>  $nuevasMacro
     * @return list<string> avisos para mostrar (lo que no se pudo quitar)
     *
     * @throws ValidationException
     */
    public function aplicarCambios(Institucion $institucion, array $cambios, array $nuevasMacro): array
    {
        $existentes = $institucion->departamentos()->get()->keyBy('id');
        $conteos = $this->personasPorArea($institucion);
        $avisos = [];

        // 1. Qué se quita. Una macro-área cuenta las personas de sus áreas.
        $quitar = [];
        foreach ($existentes as $id => $area) {
            if (!filter_var($cambios[$id]['quitar'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $ids = $area->parent_id === null
                ? $existentes->where('parent_id', $id)->keys()->push($id)->all()
                : [$id];

            $personas = array_sum(array_map(fn ($i) => $conteos[$i] ?? 0, $ids));

            if ($personas > 0) {
                $avisos[] = "No se quitó «{$area->nombre}» porque tiene {$personas} "
                    . ($personas === 1 ? 'persona asignada' : 'personas asignadas')
                    . '. Reasígnalas primero.';

                continue;
            }

            foreach ($ids as $i) {
                $quitar[$i] = true;
            }
        }

        // 2. Nombres finales, para detectar vacíos y repetidos antes de escribir.
        $errores = [];
        $finales = [];

        foreach ($existentes as $id => $area) {
            if (isset($quitar[$id])) {
                continue;
            }

            $nombre = array_key_exists('nombre', $cambios[$id] ?? [])
                ? trim(preg_replace('/\s+/u', ' ', (string) $cambios[$id]['nombre']))
                : $area->nombre;

            if ($nombre === '') {
                $errores["areas.{$id}.nombre"] = "El nombre de «{$area->nombre}» no puede quedar vacío.";

                continue;
            }

            $finales[] = $nombre;
        }

        $nuevasPorMacro = [];
        foreach ($existentes->whereNull('parent_id') as $id => $macro) {
            if (isset($quitar[$id])) {
                continue;
            }

            $nuevas = self::parsearLista($cambios[$id]['nuevas'] ?? null);
            if ($nuevas !== []) {
                $nuevasPorMacro[$id] = $nuevas;
                array_push($finales, ...$nuevas);
            }
        }

        array_push($finales, ...$nuevasMacro);

        $repetidos = collect($finales)
            ->groupBy(fn ($n) => self::normalizar($n))
            ->filter(fn ($grupo) => $grupo->count() > 1)
            ->map(fn ($grupo) => $grupo->first())
            ->values();

        if ($repetidos->isNotEmpty()) {
            $errores['areas'] = 'Hay áreas con el mismo nombre: ' . $repetidos->join(', ')
                . '. Cada área necesita un nombre distinto para poder asignar el padrón.';
        }

        if ($finales === [] && $errores === []) {
            $errores['areas'] = 'La institución necesita al menos una área.';
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        // 3. Escribir. Primero las áreas hijas, para que ninguna quede huérfana.
        $porQuitar = $existentes->only(array_keys($quitar))
            ->sortByDesc(fn ($area) => $area->parent_id !== null);

        foreach ($porQuitar as $area) {
            $area->delete();
        }

        foreach ($existentes as $id => $area) {
            if (isset($quitar[$id]) || !array_key_exists('nombre', $cambios[$id] ?? [])) {
                continue;
            }

            $nombre = trim(preg_replace('/\s+/u', ' ', (string) $cambios[$id]['nombre']));
            if ($nombre !== $area->nombre) {
                $area->update(['nombre' => Str::limit($nombre, 150, '')]);
            }
        }

        foreach ($nuevasPorMacro as $macroId => $nombres) {
            foreach ($nombres as $nombre) {
                $this->crearArea($institucion, $nombre, $macroId);
            }
        }

        $this->crearMacroAreas($institucion, $nuevasMacro);

        return $avisos;
    }

    private function crearArea(Institucion $institucion, string $nombre, ?int $parentId): Departamento
    {
        return Departamento::create([
            'institucion_id' => $institucion->id,
            'parent_id' => $parentId,
            'nombre' => $nombre,
            'clave' => Departamento::generarClave($institucion->id, $nombre),
            'activo' => true,
        ]);
    }
}
