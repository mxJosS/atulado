<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CargaPadron;
use App\Models\Institucion;
use App\Services\PadronExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PadronController extends Controller
{
    public function __construct(private PadronExcelService $padron)
    {
    }

    public function plantilla(Institucion $institucion)
    {
        return $this->descargar(
            $this->padron->plantilla($institucion),
            'plantilla_padron_' . $institucion->slug . '.xlsx'
        );
    }

    public function importar(Request $request, Institucion $institucion)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ], [
            'archivo.required' => 'Selecciona el archivo de Excel con el padrón.',
            'archivo.uploaded' => 'El archivo no se pudo subir. Si es muy grande, divídelo en dos.',
            'archivo.file' => 'El archivo subido no es válido.',
            'archivo.mimes' => 'El padrón debe ser un archivo de Excel (.xlsx). Descarga la plantilla y llénala.',
            'archivo.max' => 'El archivo no debe pasar de 20 MB.',
        ]);

        $archivo = $request->file('archivo');
        $carga = $this->padron->importar(
            $institucion,
            $request->user(),
            $archivo->getRealPath(),
            $archivo->getClientOriginalName()
        );

        $altas = $carga->filas_lista + $carga->filas_advertencia;
        $mensaje = "Padrón procesado: {$altas} " . ($altas === 1 ? 'persona dada de alta' : 'personas dadas de alta');
        if ($carga->filas_duplicado > 0) {
            $mensaje .= " y {$carga->filas_duplicado} " . ($carga->filas_duplicado === 1 ? 'actualizada' : 'actualizadas');
        }
        $mensaje .= '.';

        $respuesta = redirect()
            ->to(route('admin.instituciones.show', $institucion) . '#tab-padron')
            ->with('success', $mensaje);

        if ($carga->filas_error > 0) {
            $respuesta->with('info', "{$carga->filas_error} "
                . ($carga->filas_error === 1 ? 'fila tiene errores y no se dio' : 'filas tienen errores y no se dieron')
                . ' de alta. Revísalas abajo o descárgalas para corregirlas y volver a subirlas.');
        }

        return $respuesta;
    }

    public function problemas(Institucion $institucion, CargaPadron $carga)
    {
        abort_unless($carga->institucion_id === $institucion->id, 404);

        return $this->descargar(
            $this->padron->archivoProblemas($carga),
            'padron_filas_con_error_' . $institucion->slug . '.xlsx'
        );
    }

    private function descargar(Spreadsheet $libro, string $nombre)
    {
        return response()->streamDownload(function () use ($libro) {
            (new Xlsx($libro))->save('php://output');
            $libro->disconnectWorksheets();
        }, Str::ascii($nombre), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store',
        ]);
    }
}
