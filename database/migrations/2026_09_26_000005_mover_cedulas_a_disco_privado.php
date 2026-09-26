<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Los documentos de cédula estaban en el disco público: cualquiera con la URL
 * /storage/verifications/... podía abrirlos sin sesión. Se mueven al disco
 * privado (storage/app/private), que sólo se sirve desde el controlador.
 */
return new class extends Migration
{
    public function up(): void
    {
        $publico = Storage::disk('public');
        $privado = Storage::disk('local');

        DB::table('professional_verifications')->whereNotNull('document_path')->orderBy('id')
            ->each(function ($v) use ($publico, $privado) {
                $ruta = $v->document_path;
                if (!$publico->exists($ruta)) {
                    return;
                }
                try {
                    $privado->put($ruta, $publico->get($ruta));
                    if ($privado->exists($ruta)) {
                        $publico->delete($ruta);
                    }
                } catch (\Throwable $e) {
                    Log::error("No se pudo mover la cédula {$ruta} al disco privado: " . $e->getMessage());
                }
            });
    }

    public function down(): void
    {
        // Sin reversa: no se vuelven a exponer.
    }
};
