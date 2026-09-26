<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - Segundo factor por correo para administración y equipo clínico: el código
 *   se guarda con hash y caduca.
 * - Consentimiento expreso (aviso de privacidad, términos y datos sensibles)
 *   con fecha y versión del aviso, y las finalidades secundarias, que son
 *   opcionales y empiezan desactivadas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dos_pasos_codigo')->nullable()->after('verification_code_expires_at');
            $table->timestamp('dos_pasos_expira_en')->nullable()->after('dos_pasos_codigo');
            $table->timestamp('consentimiento_en')->nullable()->after('dos_pasos_expira_en');
            $table->string('consentimiento_version', 20)->nullable()->after('consentimiento_en');
            $table->boolean('acepta_investigacion')->default(false)->after('consentimiento_version');
            $table->boolean('acepta_boletin')->default(false)->after('acepta_investigacion');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dos_pasos_codigo', 'dos_pasos_expira_en', 'consentimiento_en', 'consentimiento_version', 'acepta_investigacion', 'acepta_boletin']);
        });
    }
};
