<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('institution')->constrained('institutions')->nullOnDelete();
            $table->string('macro_group')->nullable()->after('institution_id'); // e.g. 'Operaciones y Frente de Obra'
            $table->string('department')->nullable()->after('macro_group'); // e.g. 'Obra — Cuadrilla nocturna'
            $table->string('shift')->nullable()->after('department'); // 'Matutino', 'Nocturno', 'Mixto'
            $table->string('employee_number')->nullable()->after('shift');
            $table->string('position')->nullable()->after('employee_number'); // e.g. 'Oficial albañil'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institution_id');
            $table->dropColumn(['macro_group', 'department', 'shift', 'employee_number', 'position']);
        });
    }
};
