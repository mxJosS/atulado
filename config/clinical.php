<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Líneas de Crisis y Asistencia 24/7 (Configurables)
    |--------------------------------------------------------------------------
    */
    'crisis_numbers' => [
        'linea_vida' => env('CLINICAL_LINEA_VIDA', '800 911 2000'),
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
    | Puchol (test breve del estado de ánimo, 22 ítems en 4 bloques)
    |--------------------------------------------------------------------------
    | Se reparte en bloques cortos: uno al día como máximo, sólo en días sin
    | otras preguntas. Resultados informativos; impulsos suicidas > 0 = Rojo.
    */
    'puchol' => [
        'dias_ciclo' => 30,                 // cada cuánto empieza un ciclo nuevo
        'max_ofrecimientos' => 3,           // veces que se ofrece un bloque sin respuesta antes de esperar al siguiente ciclo
        'dias_sin_repetir_adelanto' => 7,   // el MDI no adelanta un ciclo si hubo uno hace menos de esto
        'item_mdi_alto' => 4,               // ítems 3, 8 o 9 del MDI en este valor o más adelantan el ciclo
    ],

    /*
    |--------------------------------------------------------------------------
    | Separación de Planos (Plano Gerencial)
    |--------------------------------------------------------------------------
    */
    'plano_gerencial' => [
        // Mínimo de personas por corte para reportes agregados. Cada institución puede
        // fijar el suyo (instituciones.umbral_anonimato); éste es el valor general.
        // 15 es lo recomendado para anonimato; en el piloto se trabaja desde 1.
        'umbral_minimo_anonimato' => (int) env('ATULADO_UMBRAL_ANONIMATO', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Lista Versionada de Términos Críticos (Filtro Léxico)
    |--------------------------------------------------------------------------
    | Coincidencia ampliada con raíces para levantar bandera al plano clínico sin clasificar solo.
    */
    'terminos_criticos' => [
        'suicid',
        'suicida',
        'suicidio',
        'suicidios',
        'suicidarme',
        'suicidarse',
        'suicidarte',
        'suicidar',
        'matarme',
        'matarse',
        'matarte',
        'mataria',
        'quitarme la vida',
        'quitarse la vida',
        'quitarte la vida',
        'morirme',
        'morirse',
        'deseo morir',
        'quiero morir',
        'desearia estar muerto',
        'desearia estar muerta',
        'ojala no despertara',
        'no quiero vivir',
        'dejar de vivir',
        'acabar con todo',
        'acabar con mi vida',
        'terminar con mi vida',
        'hacerme daño',
        'autolesion',
        'autolesionarme',
        'autolesionarse',
        'autolesiones',
        'cortarme',
        'cortarme las venas',
        'ahorcar',
        'ahorcarme',
        'ahorcarse',
        'pastillas para dormir todas',
        'sobredosis',
        'envenenarme',
        'pistola',
        'arma',
        'dispararme',
        'no encuentro salida',
        'sin salida',
        'sin esperanza',
        'nadie me va a extrañar',
        'seria mejor si no existiera',
        'ya no puedo mas',
        'ya no aguanto',
    ],
];
