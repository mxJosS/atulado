<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Cuenta activa» ahora significa que la persona ya entró a la app. Antes sólo
 * se activaba al aceptar la invitación, así que quien entró por otra vía
 * (recuperar contraseña, Google) seguía «Sin activar» en el padrón.
 *
 * Se activan las membresías de quien ya dejó huella de uso: registro diario,
 * cuestionario o sesión abierta. La fecha de activación es su primer registro.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pendientes = DB::table('membresias')->where('estado', 'invitado')->get(['id', 'user_id']);

        foreach ($pendientes as $m) {
            $primerRegistro = DB::table('mood_logs')->where('user_id', $m->user_id)->min('created_at');

            $entro = $primerRegistro !== null
                || DB::table('aplicaciones_who5')->where('user_id', $m->user_id)->exists()
                || DB::table('aplicaciones_mdi')->where('user_id', $m->user_id)->exists()
                || DB::table('sessions')->where('user_id', $m->user_id)->exists();

            if ($entro) {
                DB::table('membresias')->where('id', $m->id)->update([
                    'estado' => 'activo',
                    'activado_en' => $primerRegistro ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Sin reversa: no se sabe cuáles estaban «Sin activar» por error.
    }
};
