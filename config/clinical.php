<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Líneas de Crisis y Asistencia 24/7 (Configurables)
    |--------------------------------------------------------------------------
    */
    'crisis_numbers' => [
        'linea_vida' => env('CLINICAL_LINEA_VIDA', '800 290 0024'),
        'linea_vida_nacional' => env('CLINICAL_LINEA_VIDA_NACIONAL', '800 911 2000'),
        'linea_amiga_yucatan' => env('CLINICAL_LINEA_AMIGA_YUCATAN', '800 108 8000'),
        'emergencias' => env('CLINICAL_EMERGENCIAS', '911'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas de Vigilancia Diaria (Capa 0)
    |--------------------------------------------------------------------------
    | Umbrales calibrados para adelantar la aplicación del WHO-5 sin esperar al día 14.
    */
    'surveillance' => [
        'umbral_desviacion' => 1.0,  // Puntos sobre la escala 0-4 (R1)
        'dias_persistencia' => 3,    // Días consecutivos con valor >= 3 (R2)
        'salto_abrupto' => 2,        // Salto de 2 puntos sostenido 2 días (R3)
        'dias_silencio' => 5,        // Días sin registro tras patrón regular (R4)
        'min_historia_r1' => 14,     // Días mínimos para evaluar línea base en R1
    ],

    /*
    |--------------------------------------------------------------------------
    | Parámetros WHO-5 (Capa 1)
    |--------------------------------------------------------------------------
    | Versión 1998 OMS en español.
    | Escala por ítem: 0 a 5. Crudo: 0 a 25. Escala porcentual: Crudo * 4 (0 a 100).
    */
    'who5' => [
        'corte_crudo_mdi' => 12,       // Crudo <= 12 abre MDI (equivalente a < 13)
        'item_alerta_max' => 1,        // Cualquier ítem con valor <= 1 abre MDI
        'caida_significativa' => 10,   // Puntos de caída en escala 0-100 para alerta
        'dias_min_longitudinal' => 14, // Intervalo mínimo válido para comparar
    ],

    /*
    |--------------------------------------------------------------------------
    | Parámetros MDI (Major Depression Inventory - Capa 2)
    |--------------------------------------------------------------------------
    | 12 preguntas que puntúan 10 constructos (max en 8a/8b y 10a/10b).
    | Rango total: 0 a 50.
    */
    'mdi' => [
        'umbral_amarillo_max' => 19, // < 20: Sin depresión
        'umbral_naranja_max' => 29,  // 20-29: Leve / Moderada
        'umbral_rojo_min' => 30,     // >= 30: Grave
    ],

    /*
    |--------------------------------------------------------------------------
    | Parámetros ASQ (Ask Suicide-Screening Questions NIMH - Capa 3)
    |--------------------------------------------------------------------------
    */
    'asq' => [
        'tiempo_contacto_agudo_min' => 5, // < 5 minutos para Rojo Agudo
    ],

    /*
    |--------------------------------------------------------------------------
    | Separación de Planos (Plano Gerencial)
    |--------------------------------------------------------------------------
    */
    'plano_gerencial' => [
        'umbral_minimo_anonimato' => 15, // Mínimo de 15 personas por corte para reportes agregados
    ],

    /*
    |--------------------------------------------------------------------------
    | Lista Versionada de Términos Críticos (Filtro Léxico)
    |--------------------------------------------------------------------------
    | Coincidencia cerrada para levantar bandera al plano clínico sin clasificar solo.
    */
    'terminos_criticos' => [
        'suicidio',
        'suicidarme',
        'matarme',
        'quitarme la vida',
        'deseo morir',
        'desearia estar muerto',
        'ojala no despertara',
        'no quiero vivir',
        'acabar con todo',
        'autolesion',
        'cortarme',
        'ahorcarme',
        'pastillas para dormir todas',
        'no encuentro salida',
        'sin esperanza',
        'nadie me va a extrañar',
        'seria mejor si no existiera',
    ],
];
