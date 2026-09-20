<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\MoodLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentsData = [
            [
                'id' => 'operaciones-y-frente-de-obra',
                'name' => 'Operaciones y Frente de Obra',
                'badge' => 'Estable',
                'badge_color' => '#1E8449',
                'active_count' => 2,
                'who5_avg' => 74,
                'adherence_avg' => 85,
                'alerts_count' => 0,
                'distribution' => ['verde' => 2, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                'departments' => [
                    [
                        'name' => 'Área General - Operaciones y Frente de Obra',
                        'code' => 'operaciones-gral',
                        'active' => 1,
                        'shift' => 'Vespertino',
                        'dist' => ['verde' => 1, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                        'who5' => 72,
                        'adherence' => '80%',
                        'alerts' => 0,
                        'alert_type' => 'verde',
                        'note' => 'Personal técnico de campo y despliegue.',
                    ],
                    [
                        'name' => 'Soporte Técnico en Sitio',
                        'code' => 'soporte-sitio',
                        'active' => 1,
                        'shift' => 'Matutino',
                        'dist' => ['verde' => 1, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                        'who5' => 76,
                        'adherence' => '90%',
                        'alerts' => 0,
                        'alert_type' => 'verde',
                        'note' => 'Atención directa en empresas y hoteles de Cancún.',
                    ],
                ]
            ],
            [
                'id' => 'corporativo-y-direccion',
                'name' => 'Corporativo y Dirección',
                'badge' => 'Estable',
                'badge_color' => '#1E8449',
                'active_count' => 1,
                'who5_avg' => 80,
                'adherence_avg' => 95,
                'alerts_count' => 0,
                'distribution' => ['verde' => 1, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                'departments' => [
                    [
                        'name' => 'Área General - Corporativo y Dirección',
                        'code' => 'corporativo-gral',
                        'active' => 0,
                        'shift' => 'Completo',
                        'dist' => ['verde' => 0, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                        'who5' => 0,
                        'adherence' => '0%',
                        'alerts' => 0,
                        'alert_type' => 'verde',
                        'note' => 'Dirección general y toma de decisiones.',
                    ],
                    [
                        'name' => 'Administración y Finanzas',
                        'code' => 'admin-finanzas',
                        'active' => 1,
                        'shift' => 'Completo',
                        'dist' => ['verde' => 1, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                        'who5' => 80,
                        'adherence' => '95%',
                        'alerts' => 0,
                        'alert_type' => 'verde',
                        'note' => 'Gestión financiera, compras y recursos humanos.',
                    ],
                ]
            ],
            [
                'id' => 'servicios-generales',
                'name' => 'Servicios Generales',
                'badge' => 'Estable',
                'badge_color' => '#1E8449',
                'active_count' => 1,
                'who5_avg' => 70,
                'adherence_avg' => 75,
                'alerts_count' => 0,
                'distribution' => ['verde' => 1, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                'departments' => [
                    [
                        'name' => 'Área General - Servicios Generales',
                        'code' => 'servicios-gral',
                        'active' => 1,
                        'shift' => 'General',
                        'dist' => ['verde' => 1, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                        'who5' => 70,
                        'adherence' => '75%',
                        'alerts' => 0,
                        'alert_type' => 'verde',
                        'note' => 'Logística de materiales y suministros de cómputo.',
                    ],
                ]
            ],
        ];

        // 1. Crear o actualizar la Institución
        $institution = Institution::updateOrCreate(
            ['slug' => 'it-soporte-cancun'],
            [
                'name' => 'IT Soporte Cancún S.A. de C.V.',
                'short_name' => 'IT Soporte Cancún',
                'rfc' => 'ITS210514ABC',
                'category' => 'Tecnología y Soporte',
                'city' => 'Cancún, Quintana Roo',
                'contact_name' => 'Williams Pérez',
                'contact_position' => 'Gerente de Recursos Humanos',
                'contact_email' => 'recursos.humanos@itsoportecancun.com',
                'contact_phone' => '+52 998 840 1234',
                'professional_name' => 'Psic. Sofía Arana Méndez',
                'professional_license' => 'PSI-948201',
                'professional_contract_status' => 'vigente',
                'plan' => 'Corporativo',
                'users_count' => 4,
                'active_count' => 4,
                'adoption_rate' => 100.0,
                'alert_level' => 'verde',
                'critical_count' => 0,
                'renewal_date' => Carbon::now()->addYear(),
                'departments_data' => $departmentsData,
            ]
        );

        // 2. Colaboradores de demostración para el padrón
        $collaboratorsData = [
            [
                'name' => 'Williams Pérez',
                'email' => 'williams.perez@itsoportecancun.com',
                'department' => 'Administración y Finanzas',
                'macro_group' => 'Corporativo y Dirección',
                'shift' => 'Completo',
                'position' => 'Gerente de RRHH',
                'employee_number' => 'EMP-001',
                'score' => 4,
                'emotion' => 'Tranquilo',
            ],
            [
                'name' => 'Carlos Mendoza Silva',
                'email' => 'carlos.mendoza@itsoportecancun.com',
                'department' => 'Soporte Técnico en Sitio',
                'macro_group' => 'Operaciones y Frente de Obra',
                'shift' => 'Matutino',
                'position' => 'Ingeniero de Soporte',
                'employee_number' => 'EMP-002',
                'score' => 4,
                'emotion' => 'Motivado',
            ],
            [
                'name' => 'Valeria Morales Peña',
                'email' => 'valeria.morales@itsoportecancun.com',
                'department' => 'Área General - Operaciones y Frente de Obra',
                'macro_group' => 'Operaciones y Frente de Obra',
                'shift' => 'Vespertino',
                'position' => 'Técnica de Redes',
                'employee_number' => 'EMP-003',
                'score' => 5,
                'emotion' => 'Muy bien',
            ],
            [
                'name' => 'Esteban Rivas Gómez',
                'email' => 'esteban.rivas@itsoportecancun.com',
                'department' => 'Área General - Servicios Generales',
                'macro_group' => 'Servicios Generales',
                'shift' => 'General',
                'position' => 'Auxiliar de Logística',
                'employee_number' => 'EMP-004',
                'score' => 3,
                'emotion' => 'En equilibrio',
            ],
        ];

        foreach ($collaboratorsData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Colaborador_2026'),
                    'institution_id' => $institution->id,
                    'department' => $data['department'],
                    'macro_group' => $data['macro_group'],
                    'shift' => $data['shift'],
                    'position' => $data['position'],
                    'employee_number' => $data['employee_number'],
                    'role' => 'usuario',
                    'avatar_color' => 'sage',
                    'email_verified_at' => Carbon::now(),
                ]
            );

            // Registro emocional para alimentar el semáforo y gráficas
            MoodLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'logged_date' => Carbon::today()->format('Y-m-d'),
                ],
                [
                    'score' => $data['score'],
                    'primary_emotion' => $data['emotion'],
                    'energy_level' => 4,
                    'sleep_hours' => 8,
                ]
            );
        }

        // Actualizar conteos finales
        $totalCount = $institution->users()->count();
        $institution->update([
            'users_count' => $totalCount,
            'active_count' => $totalCount,
        ]);
    }
}
