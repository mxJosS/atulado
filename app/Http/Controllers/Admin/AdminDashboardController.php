<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\ProfessionalVerification;
use App\Models\User;
use App\Services\PlataformaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminDashboardController extends Controller
{
    /**
     * «Estado de la plataforma»: indicadores agregados, alertas e instituciones.
     */
    public function index(Request $request, PlataformaService $plataforma)
    {
        $datos = $plataforma->resumen((string) $request->query('periodo', '7'));

        $recentVerifications = ProfessionalVerification::with('user')
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', $datos + compact('recentVerifications', 'recentUsers'));
    }

    /**
     * Padrón de las instituciones elegidas en el dashboard, sin ningún dato clínico:
     * institución, nombre, correo, área y estado de la cuenta.
     */
    public function exportarPadron(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('instituciones', '')))
            ->map(fn ($id) => (int) $id)->filter()->unique()->values();

        $instituciones = Institucion::query()
            ->when($ids->isNotEmpty(), fn ($q) => $q->whereIn('id', $ids))
            ->orderBy('nombre_corto')
            ->get(['id', 'nombre_corto']);

        $areas = Departamento::query()
            ->whereIn('institucion_id', $instituciones->pluck('id'))
            ->get(['id', 'nombre', 'parent_id'])
            ->keyBy('id');
        $rutaArea = function (?int $id) use ($areas): string {
            $area = $id ? $areas->get($id) : null;
            if (! $area) {
                return '';
            }
            $padre = $area->parent_id ? $areas->get($area->parent_id) : null;

            return $padre ? $padre->nombre . ' › ' . $area->nombre : $area->nombre;
        };

        $estados = [
            'activo' => 'Cuenta activa',
            'invitado' => 'Sin activar',
            'suspendido' => 'Suspendida',
            'baja' => 'Baja',
        ];

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle('Padrón');
        $encabezados = ['Institución', 'Nombre', 'Correo', 'Área', 'Estado de la cuenta'];
        $hoja->fromArray($encabezados, null, 'A1');
        $hoja->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $hoja->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2E5D4B');
        $hoja->freezePane('A2');

        $fila = 2;
        $nombres = $instituciones->pluck('nombre_corto', 'id');
        Membresia::query()
            ->whereIn('institucion_id', $instituciones->pluck('id'))
            ->with('user:id,name,email')
            ->get(['id', 'institucion_id', 'user_id', 'departamento_id', 'estado'])
            ->sortBy([
                fn ($a, $b) => strcasecmp((string) $nombres[$a->institucion_id], (string) $nombres[$b->institucion_id]),
                fn ($a, $b) => ($a->estado === 'baja') <=> ($b->estado === 'baja'),
                fn ($a, $b) => strcasecmp((string) $a->user?->name, (string) $b->user?->name),
            ])
            ->each(function (Membresia $m) use ($hoja, &$fila, $nombres, $rutaArea, $estados) {
                $valores = [
                    $nombres[$m->institucion_id] ?? '',
                    $m->user?->name ?? '',
                    $m->user?->email ?? '',
                    $rutaArea($m->departamento_id),
                    $estados[$m->estado] ?? $m->estado,
                ];
                foreach ($valores as $col => $valor) {
                    // Como texto: un nombre que empiece con «=» no debe tomarse como fórmula.
                    $hoja->setCellValueExplicit([$col + 1, $fila], (string) $valor, DataType::TYPE_STRING);
                }
                $fila++;
            });

        foreach (range('A', 'E') as $columna) {
            $hoja->getColumnDimension($columna)->setAutoSize(true);
        }

        $nombre = $instituciones->count() === 1
            ? 'padron_' . Str::slug($instituciones->first()->nombre_corto) . '.xlsx'
            : 'padron_instituciones_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($libro) {
            (new Xlsx($libro))->save('php://output');
            $libro->disconnectWorksheets();
        }, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store',
        ]);
    }
}
