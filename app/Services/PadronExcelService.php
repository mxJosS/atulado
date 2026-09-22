<?php

namespace App\Services;

use App\Models\CargaPadron;
use App\Models\CargaPadronFila;
use App\Models\ContactoEmergencia;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

/**
 * Padrón de personas por Excel: la plantilla que se descarga y la carga que se sube.
 *
 * Las columnas son fijas (COLUMNAS). Cualquier otra columna del archivo se
 * ignora y no se guarda, así una exportación de nómina con CURP o NSS no deja
 * rastro en la base.
 *
 * No hay tope de filas: la carga consulta lo existente en bloque y hashea una
 * sola contraseña por carga en lugar de una por persona.
 */
class PadronExcelService
{
    public const HOJA_PADRON = 'Padrón';

    /** Filas de la plantilla que traen las listas desplegables. No es un límite de carga. */
    private const FILAS_CON_LISTAS = 10000;

    /**
     * clave => [encabezado en la plantilla, obligatoria, ejemplo, ayuda]
     */
    public const COLUMNAS = [
        'nombre_completo' => ['Nombre completo', true, 'Luis Manuel Chan Poot', 'Nombre y apellidos.'],
        'correo' => ['Correo electrónico', true, 'lchan@empresa.com', 'Con este correo la persona entra a A Tu Lado.'],
        'numero_empleado' => ['Número de empleado', true, '1188', 'La clave con que RR. HH. identifica a la persona.'],
        'departamento' => ['Área / Departamento', true, 'Operaciones › Cuadrilla nocturna', 'Elígela de la lista. Debe existir en la estructura de la institución.'],
        'puesto' => ['Puesto', false, 'Oficial albañil', ''],
        'turno' => ['Turno', false, 'Nocturno', 'Matutino, Vespertino, Nocturno o Mixto.'],
        'horario' => ['Horario', false, '22:00-06:00', 'Ventana para contactar a la persona en una crisis.'],
        'fecha_ingreso' => ['Fecha de ingreso', false, '2024-03-11', 'Fecha en que entró a la institución.'],
        'sexo' => ['Sexo', false, 'Hombre', 'Hombre, Mujer o Prefiero no decir. Sólo para estadística anónima.'],
        'anio_nacimiento' => ['Año de nacimiento', false, '1991', 'Sólo el año. Se guarda el rango de edad, nunca la fecha.'],
        'escolaridad' => ['Escolaridad', false, 'Secundaria', ''],
        'lugar_origen' => ['Lugar de origen', false, 'Tekax, Yucatán', ''],
        'tipo_jornada' => ['Tipo de jornada', false, 'Tiempo completo', ''],
        'contacto_emergencia_nombre' => ['Contacto de emergencia: nombre', false, 'Rosa Poot Canché', 'Respaldo: la persona puede corregirlo desde su perfil.'],
        'contacto_emergencia_telefono' => ['Contacto de emergencia: teléfono', false, '9994128803', ''],
        'contacto_emergencia_relacion' => ['Contacto de emergencia: parentesco', false, 'Madre', ''],
        'idioma' => ['Idioma', false, 'Español', 'Español o Maya. Si se deja vacío, Español.'],
    ];

    public const TURNOS = ['Matutino' => 'matutino', 'Vespertino' => 'vespertino', 'Nocturno' => 'nocturno', 'Mixto' => 'mixto'];

    public const SEXOS = ['Hombre' => 'M', 'Mujer' => 'F', 'Prefiero no decir' => 'prefiere_no_decir'];

    public const IDIOMAS = ['Español' => 'es', 'Maya' => 'myn'];

    public const ESCOLARIDADES = ['Sin estudios', 'Primaria', 'Secundaria', 'Preparatoria / Bachillerato', 'Carrera técnica', 'Licenciatura', 'Posgrado'];

    public const JORNADAS = ['Tiempo completo', 'Medio tiempo', 'Por horas', 'Temporal'];

    public const PARENTESCOS = ['Madre', 'Padre', 'Pareja', 'Hijo(a)', 'Hermano(a)', 'Otro familiar', 'Amistad'];

    /* ════════════════════════ Plantilla ════════════════════════ */

    public function plantilla(Institucion $institucion): Spreadsheet
    {
        $libro = new Spreadsheet();
        $libro->getProperties()->setCreator('A Tu Lado')->setTitle('Padrón ' . $institucion->nombre_corto);

        $padron = $libro->getActiveSheet();
        $padron->setTitle(self::HOJA_PADRON);
        $this->escribirEncabezados($padron);

        $listas = $libro->createSheet();
        $listas->setTitle('Listas');
        $rangos = $this->escribirListas($listas, $institucion);
        $listas->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $this->aplicarListas($padron, $rangos);

        $this->escribirInstrucciones($libro->createSheet(), $institucion);

        $libro->setActiveSheetIndex(0);

        return $libro;
    }

    /**
     * Las filas con error de una carga, en el formato de la plantilla y con
     * una columna «Problema» al final, para corregirlas y volver a subirlas.
     */
    public function archivoProblemas(CargaPadron $carga): Spreadsheet
    {
        $libro = $this->plantilla($carga->institucion);
        $hoja = $libro->getSheetByName(self::HOJA_PADRON);

        $colProblema = Coordinate::stringFromColumnIndex(count(self::COLUMNAS) + 1);
        $hoja->setCellValue($colProblema . '1', 'Problema (esta columna se ignora al subir)');
        $hoja->getStyle($colProblema . '1')->getFont()->setBold(true)->getColor()->setRGB('B02418');
        $hoja->getColumnDimension($colProblema)->setWidth(60);

        $fila = 2;
        foreach ($carga->filas()->where('estado', 'error')->cursor() as $registro) {
            $col = 1;
            foreach (array_keys(self::COLUMNAS) as $clave) {
                $hoja->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($col++) . $fila,
                    (string) ($registro->datos[$clave] ?? ''),
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
            $hoja->setCellValue($colProblema . $fila, implode(' ', $registro->mensajes ?? []));
            $fila++;
        }

        return $libro;
    }

    private function escribirEncabezados(Worksheet $hoja): void
    {
        $col = 1;
        foreach (self::COLUMNAS as $clave => [$titulo, $obligatoria, , $ayuda]) {
            $letra = Coordinate::stringFromColumnIndex($col++);
            $hoja->setCellValue($letra . '1', $titulo . ($obligatoria ? ' *' : ''));
            $hoja->getColumnDimension($letra)->setWidth(max(16, mb_strlen($titulo) + 6));

            $estilo = $hoja->getStyle($letra . '1');
            $estilo->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($obligatoria ? '2E5D4B' : '6E887E');
            $estilo->getAlignment()->setWrapText(true)->setVertical('center');

            if ($ayuda !== '') {
                $hoja->getComment($letra . '1')->getText()->createText($ayuda);
            }

            // Texto para que Excel no se coma los ceros a la izquierda ni convierta a notación científica.
            if (in_array($clave, ['numero_empleado', 'contacto_emergencia_telefono', 'horario'], true)) {
                $hoja->getStyle($letra . ':' . $letra)
                    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            }
            if ($clave === 'fecha_ingreso') {
                $hoja->getStyle($letra . ':' . $letra)
                    ->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            }
        }

        $hoja->getRowDimension(1)->setRowHeight(34);
        $hoja->freezePane('A2');
        $hoja->setAutoFilter('A1:' . Coordinate::stringFromColumnIndex(count(self::COLUMNAS)) . '1');
    }

    /**
     * @return array<string,string> clave de columna => rango de su lista en la hoja Listas
     */
    private function escribirListas(Worksheet $hoja, Institucion $institucion): array
    {
        $listas = [
            'departamento' => $this->nombresDeAreas($institucion),
            'turno' => array_keys(self::TURNOS),
            'sexo' => array_keys(self::SEXOS),
            'escolaridad' => self::ESCOLARIDADES,
            'tipo_jornada' => self::JORNADAS,
            'contacto_emergencia_relacion' => self::PARENTESCOS,
            'idioma' => array_keys(self::IDIOMAS),
        ];

        $rangos = [];
        $col = 1;
        foreach ($listas as $clave => $valores) {
            $letra = Coordinate::stringFromColumnIndex($col++);
            $hoja->setCellValue($letra . '1', self::COLUMNAS[$clave][0]);

            foreach (array_values($valores) as $i => $valor) {
                $hoja->setCellValueExplicit($letra . ($i + 2), $valor, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            if ($valores !== []) {
                $rangos[$clave] = "'Listas'!\${$letra}\$2:\${$letra}\$" . (count($valores) + 1);
            }
        }

        return $rangos;
    }

    /** @param array<string,string> $rangos */
    private function aplicarListas(Worksheet $hoja, array $rangos): void
    {
        $indices = array_flip(array_keys(self::COLUMNAS));

        foreach ($rangos as $clave => $rango) {
            $letra = Coordinate::stringFromColumnIndex($indices[$clave] + 1);
            $celdas = $letra . '2:' . $letra . (self::FILAS_CON_LISTAS + 1);

            $validacion = new DataValidation();
            $validacion->setType(DataValidation::TYPE_LIST)
                // Aviso y no bloqueo: si la estructura creció después de descargar
                // la plantilla, el área nueva se puede escribir a mano.
                ->setErrorStyle(DataValidation::STYLE_WARNING)
                ->setAllowBlank(true)
                ->setShowDropDown(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Valor fuera de la lista')
                ->setError('Elige un valor de la lista desplegable.')
                ->setFormula1($rango)
                ->setSqref($celdas);

            $hoja->setDataValidation($celdas, $validacion);
        }
    }

    private function escribirInstrucciones(Worksheet $hoja, Institucion $institucion): void
    {
        $hoja->setTitle('Instrucciones');
        $hoja->setCellValue('A1', 'Padrón de ' . $institucion->nombre_corto);
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $hoja->setCellValue('A2', 'Llena la hoja «' . self::HOJA_PADRON . '» con una persona por fila, sin límite de filas. '
            . 'Las columnas con * son obligatorias. No cambies los encabezados.');
        $hoja->setCellValue('A3', 'No agregues CURP, RFC, NSS, domicilio ni diagnósticos: A Tu Lado no los necesita y, si vienen, se ignoran.');

        $hoja->fromArray(['Columna', 'Obligatoria', 'Ejemplo', 'Notas'], null, 'A5');
        $hoja->getStyle('A5:D5')->getFont()->setBold(true);

        $fila = 6;
        foreach (self::COLUMNAS as [$titulo, $obligatoria, $ejemplo, $ayuda]) {
            $hoja->fromArray([$titulo, $obligatoria ? 'Sí' : 'No', $ejemplo, $ayuda], null, 'A' . $fila++);
        }

        $hoja->getColumnDimension('A')->setWidth(36);
        $hoja->getColumnDimension('B')->setWidth(12);
        $hoja->getColumnDimension('C')->setWidth(34);
        $hoja->getColumnDimension('D')->setWidth(70);
    }

    /**
     * Macro-áreas sin áreas internas van con su nombre; las áreas internas,
     * como «Macro › Área» para que se entienda de dónde cuelgan.
     *
     * @return list<string>
     */
    private function nombresDeAreas(Institucion $institucion): array
    {
        $todas = $institucion->departamentos()->get();
        $nombres = [];

        foreach ($todas->whereNull('parent_id') as $macro) {
            $nombres[] = $macro->nombre;
            foreach ($todas->where('parent_id', $macro->id) as $hijo) {
                $nombres[] = $macro->nombre . ' › ' . $hijo->nombre;
            }
        }

        return $nombres;
    }

    /* ════════════════════════ Carga ════════════════════════ */

    /**
     * Lee el archivo, valida fila por fila y da de alta lo que está bien.
     * Las filas con error no se dan de alta; las de advertencia sí.
     *
     * @throws ValidationException si el archivo no se puede leer o no trae las columnas obligatorias
     */
    public function importar(Institucion $institucion, User $autor, string $ruta, string $nombreOriginal): CargaPadron
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        [$filas, $ignoradas] = $this->leerArchivo($ruta);

        if ($filas === []) {
            throw ValidationException::withMessages(['archivo' => 'El archivo no trae ninguna persona: la hoja «' . self::HOJA_PADRON . '» está vacía.']);
        }

        $revisadas = $this->revisar($institucion, $filas);

        return DB::transaction(fn () => $this->guardar($institucion, $autor, $nombreOriginal, $revisadas, $ignoradas));
    }

    /**
     * @return array{0: list<array{fila:int, datos:array<string,string>}>, 1: list<string>}
     */
    private function leerArchivo(string $ruta): array
    {
        try {
            $lector = IOFactory::createReaderForFile($ruta);
            $lector->setReadDataOnly(true);
            $lector->setReadEmptyCells(false);
            $libro = $lector->load($ruta);
        } catch (Throwable) {
            throw ValidationException::withMessages(['archivo' => 'No se pudo leer el archivo. Súbelo como Excel (.xlsx), de preferencia sobre la plantilla descargada.']);
        }

        $hoja = $libro->getSheetByName(self::HOJA_PADRON) ?? $libro->getSheet(0);
        $ultimaFila = $hoja->getHighestDataRow();
        $ultimaCol = $hoja->getHighestDataColumn();

        $encabezados = $hoja->rangeToArray("A1:{$ultimaCol}1", null, true, false, false)[0] ?? [];
        [$mapa, $ignoradas] = $this->mapearEncabezados($encabezados);

        $faltan = array_filter(
            array_keys(self::COLUMNAS),
            fn ($clave) => self::COLUMNAS[$clave][1] && !in_array($clave, $mapa, true)
        );
        if ($faltan !== []) {
            $nombres = array_map(fn ($clave) => '«' . self::COLUMNAS[$clave][0] . '»', $faltan);
            throw ValidationException::withMessages([
                'archivo' => 'Al archivo le faltan columnas obligatorias: ' . implode(', ', $nombres)
                    . '. Descarga la plantilla y copia ahí tus datos sin cambiar los encabezados.',
            ]);
        }

        $filas = [];
        for ($numero = 2; $numero <= $ultimaFila; $numero++) {
            $valores = $hoja->rangeToArray("A{$numero}:{$ultimaCol}{$numero}", null, true, false, false)[0];
            $datos = [];

            foreach ($mapa as $indice => $clave) {
                $datos[$clave] = $this->textoDeCelda($valores[$indice] ?? null, $clave);
            }

            if (implode('', $datos) === '') {
                continue; // fila en blanco
            }

            $filas[] = ['fila' => $numero, 'datos' => $datos];
        }

        $libro->disconnectWorksheets();

        return [$filas, $ignoradas];
    }

    /**
     * @param  list<mixed>  $encabezados
     * @return array{0: array<int,string>, 1: list<string>} índice de columna => clave, y columnas ignoradas
     */
    private function mapearEncabezados(array $encabezados): array
    {
        $conocidos = [];
        foreach (self::COLUMNAS as $clave => [$titulo]) {
            $conocidos[$clave] = $clave;
            $conocidos[self::normalizarEncabezado($titulo)] = $clave;
        }

        $mapa = [];
        $ignoradas = [];
        foreach ($encabezados as $indice => $encabezado) {
            $texto = trim((string) $encabezado);
            if ($texto === '') {
                continue;
            }

            $clave = $conocidos[self::normalizarEncabezado($texto)] ?? null;

            if ($clave === null || in_array($clave, $mapa, true)) {
                if (!str_starts_with(self::normalizarEncabezado($texto), 'problema')) {
                    $ignoradas[] = $texto;
                }

                continue;
            }

            $mapa[$indice] = $clave;
        }

        return [$mapa, $ignoradas];
    }

    public static function normalizarEncabezado(string $texto): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', Str::lower(Str::ascii(str_replace('*', '', $texto)))), '_');
    }

    private function textoDeCelda(mixed $valor, string $clave): string
    {
        if ($valor === null) {
            return '';
        }

        if ($clave === 'fecha_ingreso' && is_numeric($valor)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
            } catch (Throwable) {
                return (string) $valor;
            }
        }

        // 1188.0 → "1188"
        if (is_float($valor) && floor($valor) === $valor && abs($valor) < 1e15) {
            return (string) (int) $valor;
        }

        return trim(preg_replace('/\s+/u', ' ', (string) $valor));
    }

    /**
     * Valida sin escribir nada.
     *
     * @param  list<array{fila:int, datos:array<string,string>}>  $filas
     * @return list<array{fila:int, datos:array<string,string>, estado:string, mensajes:list<string>, valores:array<string,mixed>, user_id:?int, membresia:?Membresia}>
     */
    private function revisar(Institucion $institucion, array $filas): array
    {
        $areas = $this->indiceDeAreas($institucion);
        $correos = array_values(array_unique(array_filter(array_map(
            fn ($f) => Str::lower($f['datos']['correo'] ?? ''),
            $filas
        ))));

        $usuarios = collect();
        foreach (array_chunk($correos, 1000) as $bloque) {
            $usuarios = $usuarios->merge(User::whereIn('email', $bloque)->get(['id', 'email']));
        }
        $usuarios = $usuarios->keyBy(fn ($u) => Str::lower($u->email));

        $membresias = Membresia::where('institucion_id', $institucion->id)->get();
        $membresiaPorUsuario = $membresias->keyBy('user_id');
        $duenoDelNumero = $membresias->whereNotNull('numero_empleado')
            ->mapWithKeys(fn ($m) => [Str::lower($m->numero_empleado) => $m]);

        $vistosCorreo = [];
        $vistosNumero = [];
        $revisadas = [];

        foreach ($filas as $fila) {
            $d = $fila['datos'];
            $errores = [];
            $avisos = [];
            $valores = [];

            // Obligatorios
            $nombre = $d['nombre_completo'] ?? '';
            if ($nombre === '') {
                $errores[] = 'Falta el nombre.';
            }

            $correo = Str::lower($d['correo'] ?? '');
            if ($correo === '') {
                $errores[] = 'Falta el correo.';
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El correo «{$d['correo']}» no es válido.";
            } elseif (isset($vistosCorreo[$correo])) {
                $errores[] = "El correo se repite en la fila {$vistosCorreo[$correo]}.";
            } else {
                $vistosCorreo[$correo] = $fila['fila'];
            }

            $numero = $d['numero_empleado'] ?? '';
            if ($numero === '') {
                $errores[] = 'Falta el número de empleado.';
            } elseif (isset($vistosNumero[Str::lower($numero)])) {
                $errores[] = "El número de empleado se repite en la fila {$vistosNumero[Str::lower($numero)]}.";
            } else {
                $vistosNumero[Str::lower($numero)] = $fila['fila'];
            }

            $departamento = $d['departamento'] ?? '';
            if ($departamento === '') {
                $errores[] = 'Falta el área / departamento.';
            } else {
                $partes = preg_split('/\s*[›>]\s*/u', $departamento);
                $areaId = $areas[EstructuraInstitucionalService::normalizar((string) end($partes))] ?? null;
                if ($areaId === null) {
                    $errores[] = "El área «{$departamento}» no existe en la institución. Agrégala en «Editar estructura» o corrige el nombre.";
                } else {
                    $valores['departamento_id'] = $areaId;
                }
            }

            // Persona existente y número de empleado ajeno
            $usuario = $usuarios->get($correo);
            $membresia = $usuario ? $membresiaPorUsuario->get($usuario->id) : null;

            if ($numero !== '' && ($dueno = $duenoDelNumero->get(Str::lower($numero)))
                && $dueno->user_id !== $usuario?->id) {
                $errores[] = "El número de empleado {$numero} ya lo tiene otra persona del padrón ({$dueno->folio}).";
            }

            // Opcionales: un valor que no se entiende se deja vacío y se avisa
            $valores['numero_empleado'] = Str::limit($numero, 255, '');
            $valores['puesto'] = $this->textoOpcional($d, 'puesto');
            $valores['horario'] = $this->textoOpcional($d, 'horario');
            $valores['lugar_origen'] = $this->textoOpcional($d, 'lugar_origen');
            $valores['escolaridad'] = $this->deCatalogoAbierto($d['escolaridad'] ?? '', self::ESCOLARIDADES);
            $valores['tipo_jornada'] = $this->deCatalogoAbierto($d['tipo_jornada'] ?? '', self::JORNADAS);
            $valores['turno'] = $this->deCatalogo($d, 'turno', self::TURNOS + ['Completo' => 'mixto'], $avisos);
            $valores['sexo'] = $this->deCatalogo($d, 'sexo', self::SEXOS + ['Masculino' => 'M', 'H' => 'M', 'M' => 'M', 'Femenino' => 'F', 'F' => 'F'], $avisos);
            $valores['idioma'] = $this->deCatalogo($d, 'idioma', self::IDIOMAS + ['es' => 'es', 'myn' => 'myn', 'Maya yucateco' => 'myn'], $avisos) ?? 'es';
            $valores['fecha_ingreso'] = $this->fecha($d['fecha_ingreso'] ?? '', $avisos);
            $valores['rango_edad'] = $this->rangoEdad($d['anio_nacimiento'] ?? '', $avisos);

            $contactoNombre = $this->textoOpcional($d, 'contacto_emergencia_nombre');
            $contactoTelefono = $this->textoOpcional($d, 'contacto_emergencia_telefono', 50);
            if ($contactoNombre === null && $contactoTelefono === null) {
                $avisos[] = 'Sin contacto de emergencia.';
            } elseif ($contactoNombre === null || $contactoTelefono === null) {
                $avisos[] = 'El contacto de emergencia necesita nombre y teléfono; no se guardó.';
            } else {
                $valores['contacto'] = [
                    'nombre' => $contactoNombre,
                    'telefono' => $contactoTelefono,
                    'relacion' => $this->deCatalogoAbierto($d['contacto_emergencia_relacion'] ?? '', self::PARENTESCOS),
                ];
            }

            if ($membresia?->estaDeBaja()) {
                $avisos[] = 'Está dada de baja en esta institución: se actualizaron sus datos pero no se reactivó.';
            }

            $estado = match (true) {
                $errores !== [] => 'error',
                $membresia !== null => 'duplicado',
                $avisos !== [] => 'advertencia',
                default => 'lista',
            };

            if ($estado === 'duplicado') {
                array_unshift($avisos, 'Ya estaba en el padrón: se actualizaron sus datos.');
            }

            $revisadas[] = [
                'fila' => $fila['fila'],
                'datos' => $d,
                'estado' => $estado,
                'mensajes' => $errores !== [] ? $errores : $avisos,
                'valores' => $valores,
                'nombre' => Str::limit($nombre, 255, ''),
                'correo' => $correo,
                'user_id' => $usuario?->id,
                'membresia' => $membresia,
            ];
        }

        return $revisadas;
    }

    /**
     * @param  list<array<string,mixed>>  $revisadas
     * @param  list<string>  $ignoradas
     */
    private function guardar(Institucion $institucion, User $autor, string $nombreOriginal, array $revisadas, array $ignoradas): CargaPadron
    {
        $conteo = array_count_values(array_column($revisadas, 'estado'));

        $carga = CargaPadron::create([
            'institucion_id' => $institucion->id,
            'user_id' => $autor->id,
            'nombre_original' => Str::limit($nombreOriginal, 250, ''),
            'mapeo' => ['columnas_ignoradas' => $ignoradas],
            'filas_total' => count($revisadas),
            'filas_lista' => $conteo['lista'] ?? 0,
            'filas_advertencia' => $conteo['advertencia'] ?? 0,
            'filas_error' => $conteo['error'] ?? 0,
            'filas_duplicado' => $conteo['duplicado'] ?? 0,
            'estado' => 'ejecutando',
        ]);

        // Una sola contraseña aleatoria por carga: nadie la conoce y cada persona
        // define la suya al entrar. Hashear una por fila es lo que hacía que un
        // padrón de cientos de personas excediera el tiempo de la petición.
        $contrasena = Hash::make(Str::random(40));
        $conContacto = ContactoEmergencia::whereIn('user_id', array_filter(array_column($revisadas, 'user_id')))
            ->distinct()->pluck('user_id')->flip();
        $ahora = now();
        $registrosFilas = [];

        foreach ($revisadas as $r) {
            $membresiaId = null;

            if ($r['estado'] !== 'error') {
                $userId = $r['user_id'] ?? User::create([
                    'name' => $r['nombre'],
                    'email' => $r['correo'],
                    'password' => $contrasena,
                    'role' => 'usuario',
                    'avatar_color' => 'sage',
                    'email_verified_at' => $ahora,
                ])->id;

                $v = $r['valores'];
                $campos = array_filter([
                    'departamento_id' => $v['departamento_id'],
                    'numero_empleado' => $v['numero_empleado'],
                    'puesto' => $v['puesto'],
                    'turno' => $v['turno'],
                    'horario' => $v['horario'],
                    'fecha_ingreso' => $v['fecha_ingreso'],
                    'tipo_jornada' => $v['tipo_jornada'],
                    'sexo' => $v['sexo'],
                    'rango_edad' => $v['rango_edad'],
                    'escolaridad' => $v['escolaridad'],
                    'lugar_origen' => $v['lugar_origen'],
                    'idioma' => $v['idioma'],
                ], fn ($valor) => $valor !== null);

                if ($r['membresia']) {
                    // Actualizar no borra lo que ya estaba si la celda viene vacía.
                    $r['membresia']->update($campos);
                    $membresiaId = $r['membresia']->id;
                } else {
                    $membresiaId = Membresia::create($campos + [
                        'institucion_id' => $institucion->id,
                        'user_id' => $userId,
                        'carga_id' => $carga->id,
                        'rol_institucional' => 'colaborador',
                        'estado' => 'invitado',
                    ])->id;
                }

                // Respaldo: sólo si la persona aún no registró el suyo.
                if (isset($v['contacto']) && !isset($conContacto[$userId])) {
                    ContactoEmergencia::create($v['contacto'] + ['user_id' => $userId, 'es_principal' => true]);
                    $conContacto[$userId] = true;
                }
            }

            $registrosFilas[] = [
                'carga_id' => $carga->id,
                'numero_fila' => $r['fila'],
                'datos' => json_encode(array_intersect_key($r['datos'], self::COLUMNAS), JSON_UNESCAPED_UNICODE),
                'estado' => $r['estado'],
                'mensajes' => json_encode($r['mensajes'], JSON_UNESCAPED_UNICODE),
                'membresia_id' => $membresiaId,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        foreach (array_chunk($registrosFilas, 500) as $bloque) {
            CargaPadronFila::insert($bloque);
        }

        $carga->update(['estado' => 'completada', 'ejecutada_en' => $ahora]);

        return $carga;
    }

    /** @return array<string,int> nombre normalizado => id del departamento */
    private function indiceDeAreas(Institucion $institucion): array
    {
        return $institucion->departamentos()->get(['id', 'nombre'])
            ->mapWithKeys(fn ($a) => [EstructuraInstitucionalService::normalizar($a->nombre) => $a->id])
            ->all();
    }

    private function textoOpcional(array $d, string $clave, int $max = 255): ?string
    {
        $valor = $d[$clave] ?? '';

        return $valor === '' ? null : Str::limit($valor, $max, '');
    }

    /**
     * @param  array<string,string>  $catalogo  texto aceptado => valor guardado
     * @param  list<string>  $avisos
     */
    private function deCatalogo(array $d, string $clave, array $catalogo, array &$avisos): ?string
    {
        $valor = $d[$clave] ?? '';
        if ($valor === '') {
            return null;
        }

        foreach ($catalogo as $texto => $guardado) {
            if (EstructuraInstitucionalService::normalizar($texto) === EstructuraInstitucionalService::normalizar($valor)) {
                return $guardado;
            }
        }

        $opciones = implode(', ', array_unique(array_slice(array_keys($catalogo), 0, 4)));
        $avisos[] = self::COLUMNAS[$clave][0] . " «{$valor}» no se reconoce (usa: {$opciones}); se dejó vacío.";

        return null;
    }

    /** Si coincide con la lista se guarda con su escritura oficial; si no, tal cual. */
    private function deCatalogoAbierto(string $valor, array $lista): ?string
    {
        if ($valor === '') {
            return null;
        }

        foreach ($lista as $opcion) {
            if (EstructuraInstitucionalService::normalizar($opcion) === EstructuraInstitucionalService::normalizar($valor)) {
                return $opcion;
            }
        }

        return Str::limit($valor, 150, '');
    }

    private function fecha(string $valor, array &$avisos): ?string
    {
        if ($valor === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y', 'Y/m/d'] as $formato) {
            try {
                $fecha = Carbon::createFromFormat('!' . $formato, $valor);
                if ($fecha !== false && $fecha->format($formato) === $valor) {
                    return $fecha->toDateString();
                }
            } catch (Throwable) {
                // probar el siguiente formato
            }
        }

        $avisos[] = "La fecha de ingreso «{$valor}» no se entiende (usa AAAA-MM-DD); se dejó vacía.";

        return null;
    }

    private function rangoEdad(string $valor, array &$avisos): ?string
    {
        if ($valor === '') {
            return null;
        }

        $anio = ctype_digit($valor) ? (int) $valor : 0;
        $edad = (int) now()->year - $anio;

        if ($anio < 1900 || $edad < 10 || $edad > 100) {
            $avisos[] = "El año de nacimiento «{$valor}» no es válido; se dejó vacío.";

            return null;
        }

        return match (true) {
            $edad < 18 => '<18',
            $edad <= 24 => '18-24',
            $edad <= 34 => '25-34',
            $edad <= 44 => '35-44',
            $edad <= 54 => '45-54',
            default => '55+',
        };
    }
}
