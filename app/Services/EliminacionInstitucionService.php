<?php

namespace App\Services;

use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Archivar, restaurar y eliminar para siempre una institución.
 *
 * Archivar no borra nada: la institución sale de la operación y del tablero,
 * pero su información se sigue viendo y se puede restaurar. Sólo una
 * institución ya archivada se puede eliminar definitivamente.
 */
class EliminacionInstitucionService
{
    public function archivar(Institucion $institucion, User $por): void
    {
        $institucion->forceFill(['archivada_en' => now(), 'archivada_por' => $por->id])->save();
    }

    public function restaurar(Institucion $institucion): void
    {
        $institucion->forceFill(['archivada_en' => null, 'archivada_por' => null])->save();
    }

    /**
     * Casos de crisis abiertos de la institución. Mientras haya alguno no se
     * elimina: se perdería un caso Rojo sin contacto verificado.
     */
    public function casosAbiertos(Institucion $institucion): int
    {
        return EventoCrisis::where('institucion_id', $institucion->id)->abiertos()->count();
    }

    /**
     * Cuentas que se borran junto con la institución: las de su padrón que no
     * pertenecen a ninguna otra institución y no son personal de la plataforma
     * (administración, clínicos, profesionales). Esas sólo se desvinculan.
     *
     * @return list<int>
     */
    public function cuentasABorrar(Institucion $institucion): array
    {
        $ids = Membresia::where('institucion_id', $institucion->id)->pluck('user_id')->unique();

        $enOtra = Membresia::whereIn('user_id', $ids)
            ->where('institucion_id', '!=', $institucion->id)
            ->where('estado', '!=', 'baja')
            ->pluck('user_id')->unique();

        return User::whereIn('id', $ids->diff($enOtra))
            ->where(fn ($q) => $q->where('is_admin', false)->orWhereNull('is_admin'))
            ->where(fn ($q) => $q->where('is_clinico_atulado', false)->orWhereNull('is_clinico_atulado'))
            ->where(fn ($q) => $q->whereNotIn('role', ['admin', 'clinico', 'profesional'])->orWhereNull('role'))
            ->get()
            ->reject(fn (User $u) => $u->isSuperAdmin())
            ->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * Borra para siempre la institución, su estructura, su padrón y las
     * cuentas de sus personas con todo su historial clínico. No se puede deshacer.
     *
     * @return array{cuentas:int} cuántas cuentas se borraron
     */
    public function eliminar(Institucion $institucion, User $por): array
    {
        $resultado = DB::transaction(function () use ($institucion) {
            $cuentas = $this->cuentasABorrar($institucion);

            foreach (array_chunk($cuentas, 500) as $bloque) {
                DB::table('sessions')->whereIn('user_id', $bloque)->delete();
                // La base borra en cascada su historial: registros, cuestionarios, casos y bitácora.
                User::whereIn('id', $bloque)->delete();
            }

            // forceDelete: el modelo usa SoftDeletes y aquí el borrado sí es definitivo.
            // En cascada: áreas, membresías restantes, cargas del padrón y asignaciones clínicas.
            $institucion->forceDelete();

            return ['cuentas' => count($cuentas)];
        });

        Log::warning('Institución eliminada definitivamente.', [
            'institucion_id' => $institucion->id,
            'institucion' => $institucion->nombre_corto,
            'por' => $por->id,
            'cuentas_borradas' => $resultado['cuentas'],
        ]);

        return $resultado;
    }
}
