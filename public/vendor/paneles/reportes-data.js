/* ════════════════════════════════════════════════════════════════════
   A TU LADO — Catálogo y plantillas de los documentos emitibles
   Cada reporte devuelve un arreglo de hojas. El motor (reporte.html)
   las envuelve con encabezado, pie, folio, paginación y marca de agua.
   ════════════════════════════════════════════════════════════════════ */

/* ── Contexto de instituciones (se alimenta dinámicamente desde el backend) ─ */
const INSTITUCIONES = window.INSTITUCIONES || {};


const PERIODO_DEF = '15/06/2026 – 14/09/2026';
const EMISOR_DEF = 'Said Canul — clínico acreditado A Tu Lado';

/* ── Utilidades de maquetación ─────────────────────────────────── */
const esc = s => String(s).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));

function T(headers, rows, opts = {}) {
  const th = headers.map(h => {
    const t = typeof h === 'string' ? h : h.t;
    const cls = typeof h === 'object' && h.n ? ' class="n"' : '';
    const w = typeof h === 'object' && h.w ? ` style="width:${h.w}"` : '';
    return `<th${cls}${w}>${t}</th>`;
  }).join('');
  const tb = rows.map(r => {
    const cls = r.cls ? ` class="${r.cls}"` : '';
    const cells = (r.c || r).map((c, i) => {
      const isN = typeof headers[i] === 'object' && headers[i].n;
      return `<td${isN ? ' class="n"' : ''}>${c}</td>`;
    }).join('');
    return `<tr${cls}>${cells}</tr>`;
  }).join('');
  return `<table class="doc"${opts.style ? ` style="${opts.style}"` : ''}><thead><tr>${th}</tr></thead><tbody>${tb}</tbody></table>`;
}

function KV(pairs) {
  return `<dl class="kv">${pairs.map(([k, v]) => `<dt>${k}</dt><dd>${v}</dd>`).join('')}</dl>`;
}

function CO(title, body, tone = '') {
  return `<div class="callout ${tone}"><b>${title}</b>${body}</div>`;
}

function STAT(l, v, u, d) {
  return `<div class="statbox"><div class="l">${l}</div><div class="v">${v}${u ? ` <span class="u">${u}</span>` : ''}</div><div class="d">${d || ''}</div></div>`;
}

function DIST(segs) {
  const bars = segs.map(s =>
    `<div class="s${s.dark ? ' dk' : ''}" style="background:${s.color};width:${s.v}%">${s.v >= 8 ? s.label : ''}</div>`).join('');
  const lg = segs.map(s => `<span><i style="background:${s.color}"></i> ${s.name} ${s.v}%</span>`).join('');
  return `<div class="dist">${bars}</div><div class="lg">${lg}</div>`;
}

const SEM = { verde: '#1E8449', amarillo: '#DCAF00', naranja: '#D9660F', rojo: '#B02418', agudo: '#6E140C' };

/* ── Bloques legales reutilizables ─────────────────────────────── */
/* Dos variantes. La institucional no nombra instrumentos ni usa lenguaje que
   pueda leerse como diagnóstico o tratamiento; la clínica sí, porque va a un
   profesional con cédula dentro de un protocolo activo. */
const L_ALCANCE = `<p><b>Alcance.</b> A Tu Lado es una plataforma de acompañamiento emocional. <b>No emite diagnósticos, no da tratamiento y no sustituye la valoración de un profesional de la salud.</b> Lo que hace es entregar información, recursos y acompañamiento y, cuando hace falta, poner a la persona en contacto con el profesional designado.</p>`;

const L_ALCANCE_CLIN = `<p><b>Alcance.</b> A Tu Lado es una plataforma de acompañamiento emocional. <b>No emite diagnósticos ni sustituye la valoración de un profesional de la salud.</b> Los instrumentos citados (WHO-5, MDI, ASQ) son herramientas de tamizaje: sus resultados orientan el contacto, no lo determinan.</p>`;

const L_USO = `<p><b>Uso permitido.</b> La información de este documento no puede utilizarse para evaluaciones de desempeño, decisiones de promoción, sanciones, terminación de la relación laboral, ni para ningún acto que discrimine a una persona. Su uso con esos fines constituye incumplimiento del contrato de servicio.</p>`;

const L_PRIV_INST = `<p><b>Privacidad.</b> A Tu Lado no entrega a la institución información que permita identificar a una persona. Este documento contiene exclusivamente proporciones del total de la organización: sin nombres, sin conteos de personas, sin desglose por área y sin texto escrito por ninguna persona usuaria. Cuando una situación requiere atención individual se activa el protocolo de crisis y el resumen correspondiente se entrega únicamente al profesional designado, bajo contrato de confidencialidad vigente. La institución recibe el aviso de que el protocolo ocurrió y de su cierre, nunca su contenido.</p>`;

const L_CONF = `<p><b>Documento confidencial.</b> Se entrega exclusivamente a la persona designada en el encabezado, en el marco de un protocolo de crisis activo y de un contrato de confidencialidad vigente. Queda prohibida su reproducción o transmisión a terceros no autorizados. Cada apertura del enlace queda asentada en la bitácora de accesos.</p>`;

const L_METODO = `<p><b>Metodología.</b> Los índices de este reporte son propios de A Tu Lado, en escala 0–100. Combinan el bienestar que las personas reportan en la aplicación, cómo se distribuye el acompañamiento en la organización, la constancia de uso y el uso de las herramientas de la app. El índice de respuesta operativa pondera el cumplimiento del tiempo de contacto comprometido, las canalizaciones concluidas con contacto humano verificado y la ausencia de salidas sin contacto. <b>La metodología interna con la que A Tu Lado determina cuánto acompañamiento entrega a cada persona es propia y confidencial</b>, y no forma parte de lo que se comparte con la institución.</p>`;

const L_TRAZA = `<p><b>Trazabilidad.</b> Esta emisión quedó asentada con folio en la bitácora de accesos, que es inmutable y queda a disposición de la autoridad competente.</p>`;

/* ════════════════════════════════════════════════════════════════
   CATÁLOGO
   audiencia: 'institucion' | 'profesional' | 'interno'
   ════════════════════════════════════════════════════════════════ */

const REPORTES = {

/* ─────────────────────── 1 · EJECUTIVO ─────────────────────── */
ejecutivo: {
  n: 1, nombre: 'Reporte ejecutivo institucional', audiencia: 'institucion',
  prefijo: 'RE', icono: 'fa-chart-pie', color: '#2E5D4B',
  destinatarios: ['contacto'], periodicidad: 'Trimestral',
  resumen: 'IBI y su composición, distribución del acompañamiento en porcentaje, tendencia de 12 semanas, adopción y constancia, uso por sección, hallazgos accionables y recomendaciones.',
  render: c => [
    `${CO('Este documento no contiene información individual',
      'Sin nombres, sin conteos de personas, sin desglose por área y sin texto escrito por nadie. Todos los valores son proporciones del total de la organización.', 'green')}

     <h3 class="sec">El trimestre en una línea</h3>
     <p class="lead">El Índice de Bienestar Institucional subió de <b>58 a 64 puntos</b> y cruzó el umbral de «saludable». La adopción creció 11 puntos y la constancia de uso 4. La proporción de la organización en nivel de canalización se mantuvo estable en 4%, lo que indica que las situaciones se atendieron y salieron, no que se acumularan. El 100% de las canalizaciones recibió contacto humano dentro del compromiso de 30 minutos.</p>

     <h3 class="sec">Indicadores del periodo</h3>
     <div class="cols3" style="margin-bottom:8px">
       ${STAT('Bienestar institucional', '64', '/ 100', '+6 pts vs. trimestre anterior')}
       ${STAT('Adopción', '83', '%', '+11 pts · activó su cuenta')}
       ${STAT('Constancia del registro', '72', '%', '+4 pts · de días esperados')}
     </div>
     ${T([{ t: 'Indicador', w: '30%' }, { t: 'Anterior', n: 1 }, { t: 'Este periodo', n: 1 }, { t: 'Cambio', n: 1 }, 'Lectura'], [
        { c: ['<b>Índice de Bienestar Institucional</b>', '58', '<b>64</b>', '+6', 'Cruzó a rango saludable'], cls: 'hig' },
        ['Adopción (activó cuenta)', '72%', '<b>83%</b>', '+11', 'Efecto de la campaña interna'],
        ['Uso semanal', '54%', '<b>61%</b>', '+7', 'Sostenido'],
        ['Constancia del registro', '68%', '<b>72%</b>', '+4', 'Sostenido'],
        ['Bienestar reportado', '55', '<b>58</b>', '+3', 'Dentro del rango esperado'],
        ['Proporción en acompañamiento estándar', '55%', '<b>62%</b>', '+7', 'Migración desde el nivel ampliado'],
        ['Proporción en canalización', '4%', '<b>4%</b>', '=', 'Flujo, no acumulación'],
        { c: ['Contacto dentro del SLA de 30 min', '100%', '<b>100%</b>', '=', 'Compromiso cumplido'], cls: 'hig' }
     ])}

     <h3 class="sec">Distribución del acompañamiento</h3>
     ${DIST([
        { name: 'Estándar', label: '62% Estándar', v: 62, color: SEM.verde },
        { name: 'Ampliado', label: '22% Ampliado', v: 22, color: SEM.amarillo, dark: true },
        { name: 'Reforzado', label: '12% Reforzado', v: 12, color: SEM.naranja },
        { name: 'Canalización', label: '4%', v: 4, color: SEM.rojo }
     ])}
     ${T(['Nivel de acompañamiento', { t: '% de la organización', n: 1 }, 'Qué entrega A Tu Lado en ese nivel'], [
        ['<span class="tag g">Estándar</span>', '62%', 'El contenido y las herramientas generales de la aplicación.'],
        ['<span class="tag y">Ampliado</span>', '22%', 'Más información, recursos y ejercicios dentro de la aplicación.'],
        ['<span class="tag o">Reforzado</span>', '12%', 'Información y recursos con mayor frecuencia, y acompañamiento más cercano.'],
        ['<span class="tag r">Canalización</span>', '4%', 'Contacto con el profesional designado dentro de los 30 minutos.']
     ])}
     ${CO('Qué describen estos niveles',
       'Describen <b>cuánto acompañamiento entrega A Tu Lado</b> a cada parte de la organización, no el estado de salud de ninguna persona.', 'green')}`,

    `<h3 class="sec">Cómo se movió la organización en 12 semanas</h3>
     <div class="chartbox" id="c-evol"></div>
     <p class="fine">La lectura correcta de este gráfico es la tendencia, no la foto. Una proporción estable en el nivel de canalización significa que las situaciones entran y salen; si se acumularan, la banda crecería.</p>

     <h3 class="sec">Evolución del índice de bienestar</h3>
     <div class="chartbox" id="c-ibi"></div>
     <p class="fine">La mejora sostenida coincide con la campaña de difusión interna de la semana 4 y con el ajuste de rotación de turnos de la semana 7.</p>

     <h3 class="sec">Uso por sección de la aplicación</h3>
     <div class="chartbox" id="c-mod"></div>
     <p class="fine">Que las herramientas de regulación sean la segunda sección más usada indica que las personas no sólo reportan cómo están: también actúan sobre ello.</p>`,

    `<h3 class="sec">Hallazgos y recomendaciones</h3>
     ${T([{ t: 'Hallazgo', w: '34%' }, 'Recomendación', { t: 'A cargo de', w: '18%' }], [
        ['<b>El bienestar percibido de la jornada nocturna es notoriamente menor que el del resto de la organización.</b>',
         'Revisar el esquema de rotación, la duración de turnos consecutivos y la disponibilidad de descansos. Evitar cualquier acción dirigida a personas concretas.', 'Dirección de operación'],
        ['<b>El primer semestre de antigüedad es el grupo más frágil</b>, con menor constancia de uso y menor bienestar percibido que cualquier otro grupo.',
         'Integrar la presentación de A Tu Lado al proceso de inducción, durante la primera semana.', 'Recursos Humanos'],
        ['<b>17% de la plantilla aún no activa su cuenta.</b>',
         'Repetir la campaña de difusión que subió la adopción 11 puntos. El kit de materiales se entrega como anexo.', 'Comunicación interna'],
        ['<b>La constancia cae de forma consistente en fines de semana y periodos vacacionales.</b>',
         'Es esperable y no requiere intervención. Se documenta para que no se lea como deterioro del programa.', '—']
     ])}
     ${CO('Una advertencia sobre cómo leer este reporte',
       'Ninguno de estos hallazgos identifica personas ni permite deducirlas. Están redactados como patrones de la organización precisamente para que la acción recaiga sobre condiciones de trabajo, no sobre individuos.', 'amber')}

     <h3 class="sec">Respuesta del servicio en el periodo</h3>
     ${T(['Indicador de operación', { t: 'Resultado', n: 1 }, 'Comentario'], [
        ['Canalizaciones al profesional designado', '2.7% de la plantilla', 'Proporción sobre el total de la organización'],
        ['Contacto humano dentro de 30 minutos', '100%', 'Compromiso contractual cumplido'],
        ['Canalizaciones concluidas', '91%', 'El resto continúa en seguimiento al corte'],
        ['Conclusiones con contacto humano verificado', '100%', 'La plataforma no da por concluida una canalización por vencimiento de tiempo'],
        ['Salidas sin contacto', '0', 'Regla implementada en la capa de datos'],
        ['Disponibilidad del canal', '92 de 92 días', 'Cobertura continua']
     ])}

     <h3 class="sec">Cumplimiento NOM-035-STPS-2018 en el periodo</h3>
     ${T(['Elemento', 'Cobertura', { t: 'Estado', w: '22%' }], [
        ['Canal de atención para acontecimientos traumáticos severos', 'Botón de ayuda inmediata, líneas de apoyo y contacto con el profesional designado', '<span class="tag g">Cubierto</span>'],
        ['Identificación de personas expuestas', 'Seguimiento continuo de lo que la persona reporta en la aplicación', '<span class="tag g">Cubierto</span>'],
        ['Canalización a atención médica o psicológica', '2.7% de la plantilla canalizada', '<span class="tag g">Cubierto</span>'],
        ['Difusión de la política de riesgo psicosocial', '83% de acuses registrados', '<span class="tag y">En progreso</span>'],
        ['Registros conservados y disponibles', 'Política de retención firmada y exportable', '<span class="tag g">Cubierto</span>'],
        ['Evaluación del entorno organizacional (Guía III)', 'Fuera del alcance del servicio', '<span class="tag">Del patrón</span>']
     ])}

     <div class="legal">${L_ALCANCE}${L_USO}${L_PRIV_INST}${L_METODO}</div>`
  ],
  charts: () => {
    stackedArea(document.getElementById('c-evol'), {
      height: 200, labels: SEMANAS,
      series: [
        { name: 'Estándar', color: SEM.verde, data: [55, 56, 57, 58, 59, 59, 60, 61, 61, 62, 62, 62] },
        { name: 'Ampliado', color: SEM.amarillo, data: [25, 25, 24, 24, 23, 23, 23, 22, 22, 22, 22, 22] },
        { name: 'Reforzado', color: SEM.naranja, data: [16, 15, 15, 14, 14, 14, 13, 13, 13, 12, 12, 12] },
        { name: 'Canalización', color: SEM.rojo, data: [4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4] }
      ]
    });
    lineChart(document.getElementById('c-ibi'), {
      height: 180, labels: SEMANAS, min: 45, max: 75, ticks: 3, refLine: 60, refLabel: 'umbral saludable',
      series: [{ name: 'IBI', color: '#2E5D4B', data: [58, 58, 59, 60, 61, 61, 62, 62, 63, 63, 64, 64] }]
    });
    barChart(document.getElementById('c-mod'), {
      labelW: 170, rowH: 24, unit: '%', max: 45,
      data: [
        { k: 'Registro diario', v: 39, color: '#2A78D6' },
        { k: 'Herramientas', v: 22, color: '#5B4A8A' },
        { k: 'Recursos y artículos', v: 15, color: '#1BAF7A' },
        { k: 'Revista comunitaria', v: 11, color: '#C98500' },
        { k: 'Cuestionarios', v: 8, color: '#D55181' },
        { k: 'Plan de seguridad', v: 4, color: '#8EADA4' },
        { k: 'Canal de crisis', v: 1, color: '#B02418' }
      ]
    });
  }
},

/* ─────────────────────── 2 · NOM-035 ─────────────────────── */
nom035: {
  n: 2, nombre: 'Constancia de cumplimiento NOM-035', audiencia: 'institucion',
  prefijo: 'NOM', icono: 'fa-clipboard-check', color: '#1BAF7A',
  destinatarios: ['contacto'], periodicidad: 'Trimestral o a demanda',
  resumen: 'Evidencia para inspección: canal disponible y días de cobertura, mecanismo de identificación, canalización, difusión y política de retención.',
  render: c => [
    `<p class="lead">A Tu Lado hace constar que <b>${c.inst.nombre}</b> mantuvo durante el periodo <b>${c.periodo}</b> un servicio de acompañamiento emocional con canal de atención permanente, mecanismo de identificación de personas expuestas a acontecimientos traumáticos severos y protocolo de canalización a atención profesional, en los términos que se detallan a continuación.</p>

     <h3 class="sec">1 · Canal de atención (numeral 5.7 y 8.1)</h3>
     ${T(['Elemento', 'Implementación', { t: 'Evidencia', w: '28%' }], [
        ['Disponibilidad del canal', 'Botón de ayuda inmediata accesible desde cualquier pantalla de la aplicación', '92 de 92 días del periodo'],
        ['Líneas de apoyo', 'Línea de la Vida y SAPTEL mostradas al activarse el canal', 'Registro de despliegue por evento'],
        ['Atención humana', 'Profesional designado con cédula verificada y suplencia de guardia', `${c.inst.profesional} · Ced. ${c.inst.cedula}`],
        ['Tiempo de respuesta comprometido', '30 minutos para el primer contacto humano', '100% de cumplimiento en el periodo'],
        ['Bloqueo de salida sin contacto', 'La aplicación no permite abandonar la pantalla de crisis sin ofrecer contacto', 'Regla en capa de datos']
     ])}

     <h3 class="sec">2 · Identificación de personas expuestas (numeral 5.5)</h3>
     <p>La identificación no depende de que la persona pida ayuda ni de que alguien la reporte. A Tu Lado da seguimiento continuo a lo que cada persona registra en la aplicación y, cuando ese seguimiento lo indica, le entrega más información y acompañamiento o la pone en contacto con el profesional designado.</p>
     ${T(['Elemento del servicio', 'Cómo opera', 'Frecuencia'], [
        ['Registro en la aplicación', 'La persona registra de forma voluntaria cómo se siente', 'Diaria'],
        ['Seguimiento continuo', 'A Tu Lado revisa de forma automática y permanente lo registrado', 'Permanente'],
        ['Acompañamiento graduado', 'A Tu Lado amplía la información y los recursos que entrega', 'Según el seguimiento'],
        ['Canalización', 'Contacto con el profesional designado, con tiempo comprometido', 'Cuando el seguimiento lo indica']
     ])}
     ${CO('Sobre la metodología',
       'La metodología con la que A Tu Lado da seguimiento y decide cuánto acompañamiento entregar es <b>propia y confidencial</b>: no se detalla en este documento ni se comparte con la institución. Lo que se acredita aquí, que es lo que la norma exige, es que el mecanismo existe, opera de forma continua y produce canalización efectiva a un profesional con cédula.', 'amber')}

     <h3 class="sec">3 · Canalización (numeral 8.2)</h3>
     ${T(['Indicador', { t: 'Periodo', n: 1 }, 'Observación'], [
        ['Proporción de la plantilla canalizada a atención profesional', '2.7%', 'Sobre el total de la organización'],
        ['Contacto humano dentro del compromiso de 30 min', '100%', 'Sin excepciones en el periodo'],
        ['Canalizaciones concluidas con contacto humano verificado', '100%', 'Ninguna conclusión automática por tiempo'],
        ['Canalizaciones aún en seguimiento al corte', '9% de las activadas', 'Continúan acompañadas']
     ])}
     ${CO('Sobre la ausencia de conteos', 'Esta constancia expresa la canalización en porcentaje y no en número de personas, de forma deliberada: en una organización de tamaño conocido, un conteo permitiría acotar por descarte a quién corresponde un caso. La autoridad puede verificar la operación con la bitácora de accesos, que acredita los eventos sin exponer identidades.')}

     <h3 class="sec">4 · Difusión (numeral 5.2)</h3>
     ${T(['Acción de difusión', 'Cobertura', { t: 'Estado', w: '20%' }], [
        ['Invitación nominal por correo institucional', '100% del padrón', '<span class="tag g">Completo</span>'],
        ['Códigos de acceso impresos para personal sin correo', 'Entregados por área', '<span class="tag g">Completo</span>'],
        ['Acuse de lectura de la política dentro de la aplicación', '83% de acuses', '<span class="tag y">En progreso</span>'],
        ['Materiales de campaña interna (cartel, tríptico, video)', 'Entregados a comunicación interna', '<span class="tag g">Completo</span>']
     ])}

     <h3 class="sec">5 · Registros y retención (numeral 8.4)</h3>
     ${T(['Tipo de registro', 'Retención', 'Al terminar el contrato'], [
        ['Identidad y datos de contacto', 'Vigencia del contrato', 'Se desvincula de la institución'],
        ['Registros y respuestas de la persona', 'Propiedad de la persona usuaria', 'Permanecen en su cuenta personal'],
        ['Eventos de crisis', '5 años', 'Se conservan seudonimizados'],
        ['Bitácora de accesos', '5 años', 'Inmutable'],
        ['Agregados institucionales', 'Vigencia + 1 año', 'Se entregan y se eliminan']
     ])}

     <h3 class="sec">6 · Delimitación de responsabilidades</h3>
     ${CO('Lo que esta constancia NO acredita',
       'A Tu Lado cubre el <b>canal de atención y la canalización</b> que exige la norma. La identificación y análisis de los factores de riesgo psicosocial mediante las Guías de Referencia II y III, la política escrita de riesgo psicosocial, la evaluación del entorno organizacional y las medidas de control derivadas siguen siendo obligación directa del patrón. Este reporte sirve como insumo documental para esos procesos, no los sustituye. A Tu Lado <b>no diagnostica ni da tratamiento</b>.', 'amber')}

     <div class="legal">${L_ALCANCE}${L_PRIV_INST}${L_TRAZA}</div>

     <div class="signs">
       <div><b>${EMISOR_DEF.split(' — ')[0]}</b>A Tu Lado · emite la constancia</div>
       <div><b>${c.inst.contacto.split(' — ')[0]}</b>${c.inst.corto} · recibe</div>
     </div>`
  ]
},

/* ─────────────────────── 3 · ADOPCIÓN ─────────────────────── */
adopcion: {
  n: 3, nombre: 'Reporte de adopción y difusión', audiencia: 'institucion',
  prefijo: 'AD', icono: 'fa-bullhorn', color: '#C98500',
  destinatarios: ['contacto'], periodicidad: 'Mensual',
  resumen: 'Embudo del alta a la activación en porcentaje, velocidad de activación, efecto medido de cada campaña y kit de materiales.',
  render: c => [
    `<p class="lead">Este reporte responde una sola pregunta: <b>¿está llegando el servicio a la gente?</b> Sólo mide uso y difusión. Sirve para decidir dónde poner esfuerzo de comunicación interna.</p>

     <h3 class="sec">Embudo de adopción</h3>
     <div class="chartbox" id="c-emb"></div>
     ${T(['Etapa', { t: '% del padrón', n: 1 }, { t: 'Caída', n: 1 }, 'Interpretación'], [
        ['Invitadas al padrón', '100%', '—', 'Total de personas dadas de alta'],
        { c: ['Activaron su cuenta', '93%', '−7 pts', 'Es la fuga más grande y la más barata de corregir'], cls: 'hiy' },
        ['Registraron su estado al menos una vez', '83%', '−10 pts', 'Primer uso real de la aplicación'],
        ['Registro sostenido durante 7 días', '60%', '−23 pts', 'Umbral donde el hábito se forma'],
        ['Completaron el seguimiento de la app', '73%', '—', 'Cobertura del acompañamiento'],
        ['Usaron alguna herramienta de la app', '54%', '—', 'Indicador de uso activo, no sólo de registro']
     ])}
     ${CO('Dónde está el dinero',
       'Entre «invitadas» y «activaron cuenta» se pierde 7% de la plantilla. Son personas que ya están pagadas dentro del contrato y no reciben el servicio. Recuperarlas no requiere producto: requiere una semana de difusión.', 'amber')}

     <h3 class="sec">Velocidad de activación</h3>
     ${T(['Momento desde la invitación', { t: '% acumulado que activó', n: 1 }], [
        ['Primeras 24 horas', '31%'],
        ['Primera semana', '64%'],
        ['Segunda semana', '81%'],
        ['Primer mes', '90%'],
        ['Después del primer mes', '93%']
     ])}
     <p class="fine">Después del primer mes la activación se vuelve casi plana: quien no entró en cuatro semanas rara vez entra solo. El recordatorio automático a los 7 días es el que más recupera.</p>

     <h3 class="sec">Efecto medido de las campañas</h3>
     ${T(['Campaña', 'Semana', { t: 'Adopción antes', n: 1 }, { t: 'Después', n: 1 }, { t: 'Efecto', n: 1 }], [
        ['Anuncio en junta general + cartel en comedor', 'S4', '72%', '79%', '+7 pts'],
        ['Mensaje del director por canal interno', 'S7', '79%', '81%', '+2 pts'],
        ['Códigos impresos para personal sin correo', 'S9', '81%', '83%', '+2 pts'],
        ['Recordatorio automático a los 7 días', 'continuo', '—', '—', '+4 pts acumulados']
     ])}
     <p class="fine">El cartel en el comedor y el anuncio presencial siguen siendo, por amplio margen, lo más efectivo en organizaciones con trabajo de campo. El canal digital funciona mejor en personal administrativo.</p>

     <h3 class="sec">Constancia de uso a lo largo del periodo</h3>
     <div class="chartbox" id="c-uso"></div>
     <p class="fine">Las caídas coinciden con periodos vacacionales y cierres de obra. Es esperable y no es señal de deterioro del programa.</p>

     <h3 class="sec">Kit de difusión disponible</h3>
     ${T(['Material', 'Formato', 'Uso sugerido'], [
        ['Cartel 60×90 cm', 'PDF para imprenta, 300 dpi', 'Comedor, entrada, vestidores'],
        ['Tríptico informativo', 'PDF carta horizontal', 'Entrega en inducción'],
        ['Video de presentación (78 s)', 'MP4 1920×1080', 'Junta general, pantallas internas'],
        ['Tarjeta con código de acceso', 'PDF para impresión en lote', 'Personal sin correo institucional'],
        ['Texto para canal interno', 'Documento editable', 'Mensaje del director o de RH']
     ])}

     <div class="legal">${L_USO}${L_PRIV_INST}</div>`
  ],
  charts: () => {
    barChart(document.getElementById('c-emb'), {
      labelW: 190, rowH: 24, unit: '%', max: 100,
      data: [
        { k: 'Invitadas al padrón', v: 100, color: '#2E5D4B' },
        { k: 'Activaron cuenta', v: 93, color: '#3D7A5F' },
        { k: 'Registraron una vez', v: 83, color: '#2A78D6' },
        { k: 'Registro sostenido 7 d', v: 60, color: '#5B4A8A' },
        { k: 'Completaron seguimiento', v: 73, color: '#1BAF7A' },
        { k: 'Usaron herramientas', v: 54, color: '#C98500' }
      ]
    });
    lineChart(document.getElementById('c-uso'), {
      height: 175, labels: SEMANAS, min: 40, max: 80, ticks: 4, unit: '%',
      series: [
        { name: 'Uso semanal', color: '#2A78D6', data: [51, 53, 54, 49, 56, 57, 59, 60, 55, 61, 61, 61] },
        { name: 'Constancia', color: '#1BAF7A', data: [63, 65, 64, 58, 67, 69, 70, 71, 64, 72, 72, 72] }
      ]
    });
  }
},

/* ─────────────────────── 4 · CIERRE DE CONTRATO ─────────────────────── */
cierre: {
  n: 4, nombre: 'Acta de cierre de contrato', audiencia: 'institucion',
  prefijo: 'AC', icono: 'fa-flag-checkered', color: '#2A78D6',
  destinatarios: ['contacto', 'direccion'], periodicidad: 'Al término del contrato',
  resumen: 'Resumen del ciclo completo, evolución del índice de punta a punta, cumplimiento de SLA y constancia de desvinculación de datos.',
  render: c => [
    `<p class="lead">Se hace constar la conclusión del contrato de servicio entre <b>A Tu Lado</b> y <b>${c.inst.nombre}</b>, vigente del <b>${c.inst.alta}</b> al <b>${c.inst.vigencia}</b>, y se deja asentado el estado final del programa y el destino de la información.</p>

     <h3 class="sec">1 · El ciclo completo</h3>
     <div class="cols3" style="margin-bottom:8px">
       ${STAT('IBI inicial', '49', '/ 100', 'Al arranque del programa')}
       ${STAT('IBI final', '64', '/ 100', '+15 puntos en el ciclo')}
       ${STAT('Adopción final', '83', '%', 'Desde 0% al inicio')}
     </div>
     <div class="chartbox" id="c-ciclo"></div>
     <p class="fine">La curva completa del contrato. El primer trimestre es de onboarding y difusión; el crecimiento sostenido empieza cuando la adopción rebasa 70%.</p>

     <h3 class="sec">2 · Cumplimiento del servicio</h3>
     ${T(['Compromiso contractual', 'Resultado del ciclo', { t: 'Estado', w: '18%' }], [
        ['Canal de atención disponible de forma continua', 'Sin interrupciones registradas', '<span class="tag g">Cumplido</span>'],
        ['Primer contacto humano en menos de 30 minutos', '100% de los casos', '<span class="tag g">Cumplido</span>'],
        ['Cierre de casos con contacto humano verificado', '100% de los casos cerrados', '<span class="tag g">Cumplido</span>'],
        ['Profesional designado con cédula vigente', 'Sin periodos descubiertos', '<span class="tag g">Cumplido</span>'],
        ['Entrega de reportes trimestrales', 'Todos entregados en fecha', '<span class="tag g">Cumplido</span>'],
        ['Separación de planos de información', '0 accesos institucionales a datos individuales', '<span class="tag g">Cumplido</span>']
     ])}

     <h3 class="sec">3 · Estado final de la organización</h3>
     ${DIST([
        { name: 'Estándar', label: '62% Estándar', v: 62, color: SEM.verde },
        { name: 'Ampliado', label: '22% Ampliado', v: 22, color: SEM.amarillo, dark: true },
        { name: 'Reforzado', label: '12% Reforzado', v: 12, color: SEM.naranja },
        { name: 'Canalización', label: '4%', v: 4, color: SEM.rojo }
     ])}

     <h3 class="sec">4 · Destino de la información</h3>
     ${T(['Tipo de dato', 'Qué ocurre al cierre', { t: 'Fecha', w: '16%' }], [
        ['Identidad y datos de contacto del padrón', 'Se desvinculan de la institución', 'Inmediato'],
        ['Registros y respuestas de cada persona', 'Permanecen en su cuenta personal; siguen siendo suyos', 'Sin cambio'],
        ['Acceso del panel institucional', 'Revocado', 'Inmediato'],
        ['Acceso del profesional designado', 'Revocado', 'Inmediato'],
        ['Eventos de crisis', 'Se conservan seudonimizados por obligación legal', '5 años'],
        ['Bitácora de accesos', 'Se conserva íntegra e inmutable', '5 años'],
        ['Agregados institucionales', 'Se entregan a la institución y se eliminan de la plataforma', 'A 90 días']
     ])}
     ${CO('Sobre las personas usuarias',
       'La conclusión del contrato no cancela la cuenta de nadie. Cada persona recibió el aviso de que su organización dejó de patrocinar el servicio y conserva su cuenta, su historial y su plan de seguridad. La institución no recupera, hereda ni consulta nada de eso en ningún momento.', 'green')}

     <h3 class="sec">5 · Requisitos verificados antes del cierre</h3>
     ${T(['Requisito', { t: 'Estado', w: '22%' }], [
        ['Ningún protocolo de crisis abierto', '<span class="tag g">Verificado</span>'],
        ['Todos los casos del ciclo cerrados con contacto verificado', '<span class="tag g">Verificado</span>'],
        ['Reportes pendientes entregados', '<span class="tag g">Verificado</span>'],
        ['Aviso enviado a cada persona del padrón', '<span class="tag g">Verificado</span>'],
        ['Constancia de eliminación programada', '<span class="tag g">Verificado</span>']
     ])}

     <div class="legal">${L_ALCANCE}${L_USO}${L_PRIV_INST}</div>

     <div class="signs">
       <div><b>${EMISOR_DEF.split(' — ')[0]}</b>A Tu Lado</div>
       <div><b>${c.inst.contacto.split(' — ')[0]}</b>${c.inst.corto}</div>
     </div>`
  ],
  charts: () => {
    lineChart(document.getElementById('c-ciclo'), {
      height: 190, min: 40, max: 90, ticks: 4,
      labels: ['Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'],
      series: [
        { name: 'IBI', color: '#2E5D4B', data: [49, 51, 54, 56, 58, 60, 62, 64] },
        { name: 'Adopción %', color: '#2A78D6', data: [42, 55, 63, 68, 72, 76, 80, 83] }
      ]
    });
  }
},

/* ─────────────────────── 5 · CERTIFICADO ─────────────────────── */
certificado: {
  n: 5, nombre: 'Certificado de organización acompañada', audiencia: 'institucion',
  prefijo: 'CE', icono: 'fa-heart-circle-check', color: '#1E8449',
  destinatarios: ['contacto'], periodicidad: 'Anual',
  resumen: 'Documento de una página, diseñado para publicarse, que declara que la organización mantiene un canal de acompañamiento activo y verificado.',
  sinEncabezado: true,
  render: c => [
    `<div class="cert">
       <svg viewBox="0 0 16 16" width="52" height="52"><rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/><rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/><rect x="2" y="4" width="12" height="2" fill="#5AB56E"/><rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/><rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/><rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/><rect x="4" y="1" width="1" height="1" fill="#C0392B"/><rect x="11" y="3" width="1" height="1" fill="#C0392B"/><rect x="9" y="7" width="1" height="1" fill="#C0392B"/></svg>
       <div class="k" style="margin-top:14px">Organización acompañada</div>
       <h1>A Tu <em style="font-family:var(--font-editorial);font-style:italic">Lado</em></h1>
       <div class="body" style="margin-top:20px">A Tu Lado hace constar que</div>
       <div class="who">${c.inst.nombre}</div>
       <div class="body">
         mantuvo durante el periodo <b>${c.inst.alta} – ${c.inst.vigencia}</b> un canal de acompañamiento emocional permanente para las personas que la integran, con atención humana verificada, profesional de la salud con cédula designado y protocolo de crisis operativo las veinticuatro horas.
       </div>
       <div class="body" style="margin-top:16px">
         Durante el periodo, el <b>100%</b> de las situaciones que activaron el protocolo recibió contacto humano dentro de los treinta minutos comprometidos, y ninguna se cerró sin ese contacto.
       </div>
       <div style="margin-top:26px" class="seal">
         A TU LADO<br><b>${c.folio}</b><br>${c.fecha}
       </div>
       <div class="signs" style="margin-top:30px;max-width:130mm;margin-left:auto;margin-right:auto">
         <div><b>${EMISOR_DEF.split(' — ')[0]}</b>A Tu Lado</div>
         <div><b>${c.inst.profesional}</b>Cédula ${c.inst.cedula} · profesional designado</div>
       </div>
       <p class="fine" style="margin-top:24px;max-width:135mm;margin-left:auto;margin-right:auto">
         Este certificado acredita la existencia y operación del canal de acompañamiento. No constituye una certificación de cumplimiento integral de la NOM-035-STPS-2018, cuya evaluación del entorno organizacional corresponde al patrón. A Tu Lado no diagnostica ni sustituye la atención de un profesional de la salud.
       </p>
     </div>`
  ]
},

/* ─────────────────────── 6 · RESUMEN CLÍNICO ─────────────────────── */
clinico: {
  n: 6, nombre: 'Resumen clínico individual', audiencia: 'profesional',
  prefijo: 'RC', icono: 'fa-user-doctor', color: '#B02418',
  destinatarios: ['profesional', 'guardia'], periodicidad: 'Por caso',
  confidencial: true, requiereProtocolo: true, requierePersona: true,
  resumen: 'Trayectoria del ánimo con reglas disparadas, WHO-5, MDI y ASQ ítem por ítem, texto libre con contexto, plan de seguridad y contactos.',
  render: c => [
    `${CO('Este documento se emite porque hay un protocolo de crisis activo',
      'Fuera de un protocolo activo, la entrega de información individual no está permitida por contrato. El enlace expira en 72 horas.', 'red')}

     <h3 class="sec">1 · Identificación</h3>
     ${KV([
        ['Persona', '<b>Luis Manuel Chan Poot</b>'],
        ['Folio interno', 'COL-0412'],
        ['Organización', `${c.inst.corto} · Obra, cuadrilla nocturna`],
        ['Jornada', 'Nocturna, 22:00–06:00'],
        ['Mejor ventana de contacto', '17:00 – 21:00 (antes de entrar al turno)'],
        ['Caso', 'CR-2026-0117 · abierto 14/09/2026 08:52'],
        ['Clasificación vigente', '<span class="tag r">ROJO AGUDO</span>'],
        ['Tiempo en la plataforma', '6 meses · 172 registros']
     ])}

     <h3 class="sec">2 · Por qué se activó el protocolo</h3>
     <p>La persona venía de una línea base estable, con media de <b>1.4</b> en el registro diario de 30 días. A partir del 10 de septiembre la media móvil de 7 días se desvió de forma sostenida (<b>regla R1</b>), persistió cinco días consecutivos en los valores 3–4 (<b>regla R2</b>) y el 13 de septiembre presentó una caída abrupta a 4 (<b>regla R3</b>).</p>
     <p>El registro del 14 de septiembre incluyó texto libre con marcadores de la categoría <b>ideación</b>, lo que adelantó la aplicación del MDI. El ítem 6 del MDI puntuó 4, lo que encadenó de forma automática el ASQ, cuyo ítem 5 resultó positivo.</p>

     <h3 class="sec">3 · Trayectoria del registro diario — 30 días</h3>
     <div class="chartbox" id="c-animo"></div>
     <p class="fine">Escala invertida: 0 = excelente, 4 = terrible. Línea punteada: línea base de 30 días (1.4). Las reglas R1, R2 y R3 se dispararon el 11, 12 y 13 de septiembre respectivamente.</p>

     <h3 class="sec">4 · Instrumentos aplicados</h3>
     ${T(['Instrumento', { t: 'Fecha', w: '13%' }, { t: 'Resultado', n: 1 }, 'Interpretación', 'Ítems que destacan'], [
        ['<b>WHO-5</b>', '14/09/2026', '24 / 100', 'Muy por debajo del corte de 50', 'Ítems 3 y 4 en 0 (energía y descanso)'],
        ['WHO-5 anterior', '16/08/2026', '55 / 100', 'Dentro de rango', '—'],
        { c: ['<b>MDI</b>', '14/09/2026', '38 / 50', 'Nivel ROJO', '<b>Ítem 6 en 4</b> · ítem 9 (sueño) en 5'], cls: 'hi' },
        { c: ['<b>ASQ</b>', '14/09/2026', 'Positiva aguda', 'Ideación activa presente', '<b>Ítem 5 positivo</b> · ítems 1, 2 y 3 positivos'], cls: 'hi' },
        ['Puchol', '14/09/2026', 'F8 · C7 · E9 · M3', 'Predominio emocional y fisiológico', 'Sueño y apetito alterados']
     ])}`,

    `<h3 class="sec">5 · WHO-5, respuestas íntegras</h3>
     <p class="fine">«En las últimas dos semanas…» · 0 = nunca, 5 = todo el tiempo</p>
     ${T(['Ítem', { t: 'Respuesta', n: 1, w: '22%' }], [
        { c: ['1. Me he sentido alegre y de buen humor', '1 — Alguna vez'], cls: 'hi' },
        { c: ['2. Me he sentido tranquilo y relajado', '1 — Alguna vez'], cls: 'hi' },
        { c: ['3. Me he sentido activo y con energía', '0 — Nunca'], cls: 'hi' },
        { c: ['4. Me he despertado sintiéndome fresco y descansado', '0 — Nunca'], cls: 'hi' },
        ['5. Mi vida diaria ha estado llena de cosas que me interesan', '4 — La mayor parte']
     ])}

     <h3 class="sec">6 · MDI, respuestas íntegras</h3>
     <p class="fine">«¿Con qué frecuencia en las últimas dos semanas…?» · 0 = en ningún momento, 5 = todo el tiempo</p>
     ${T(['Ítem', { t: 'Respuesta', n: 1, w: '14%' }], [
        { c: ['1. Te has sentido decaído, triste', '5'], cls: 'hi' },
        { c: ['2. Has perdido el interés en tus actividades cotidianas', '5'], cls: 'hi' },
        { c: ['3. Te has sentido falto de energía y fuerza', '4'], cls: 'hi' },
        { c: ['4. Has tenido menos confianza en ti mismo', '4'], cls: 'hi' },
        { c: ['5. Has tenido mala conciencia o sentimientos de culpa', '4'], cls: 'hi' },
        { c: ['<b>6. Has sentido que la vida no merece la pena vivirse</b>', '<b>4</b>'], cls: 'hi' },
        { c: ['7. Has tenido dificultad para concentrarte', '4'], cls: 'hi' },
        ['8. Te has sentido muy inquieto (8a) / muy lento (8b)', 'máx 3'],
        { c: ['9. Has tenido problemas para dormir por la noche', '5'], cls: 'hiy' },
        ['10. Has perdido el apetito (10a) / has tenido mucho apetito (10b)', 'máx 3']
     ])}
     ${CO('Encadenamiento automático', 'El ítem 6 en 4 obliga a aplicar el ASQ de inmediato. El motor lo hizo a las 08:51; la respuesta llegó a las 08:52.', 'red')}

     <h3 class="sec">7 · ASQ, respuestas íntegras</h3>
     ${T(['Ask Suicide-Screening Questions (NIMH) · versión en español', { t: 'Respuesta', n: 1, w: '18%' }], [
        ['1. En las últimas semanas, ¿has deseado estar muerto?', '<b>Sí</b>'],
        ['2. En las últimas semanas, ¿has sentido que tú o tu familia estarían mejor si tú estuvieras muerto?', '<b>Sí</b>'],
        ['3. En la última semana, ¿has tenido pensamientos sobre quitarte la vida?', '<b>Sí</b>'],
        ['4. ¿Alguna vez has intentado quitarte la vida?', 'No'],
        { c: ['<b>5. ¿Estás teniendo pensamientos de quitarte la vida ahora mismo?</b>', '<b>Sí</b>'], cls: 'hi' }
     ])}
     <p class="fine">El ítem 5 no exploró método ni planificación: la aplicación derivó de inmediato a la pantalla de crisis y al contacto humano. Esa exploración corresponde al profesional, no a la plataforma.</p>`,

    `<h3 class="sec">8 · Texto escrito por la persona</h3>
     <p class="fine">Proviene exclusivamente del campo de texto libre del registro diario. A Tu Lado no analiza conversaciones privadas ni contenido de otras secciones.</p>
     ${T([{ t: 'Fecha', w: '15%' }, 'Texto', { t: 'Categoría detectada', w: '22%' }], [
        { c: ['14/09 08:47', '«ya no le veo salida a esto, ya lo pensé bien»', '<span class="tag r">Ideación</span>'], cls: 'hi' },
        ['12/09 05:58', '«no dormí otra vez»', '<span class="tag y">Alteración del sueño</span>'],
        ['10/09 06:41', '«todo pesa»', '<span class="tag y">Carga / agotamiento</span>'],
        ['07/09 08:10', '«descansé el domingo»', '—']
     ])}

     <h3 class="sec">9 · Recursos que la persona ya tiene</h3>
     <div class="cols2">
       <div>
         <p><b>Plan de seguridad</b> — actualizado 13/09/2026</p>
         <ul>
           <li><b>Señales que reconoce:</b> dejar de contestar el teléfono, no comer durante el turno.</li>
           <li><b>Lo que le ayuda:</b> caminar al terminar el turno, música, respiración 4-7-8 de la app.</li>
           <li><b>A quién puede llamar:</b> Rosa (madre), Andrés (compañero de cuadrilla).</li>
           <li><b>Entorno más seguro:</b> dejar las herramientas en el almacén, no llevarlas a casa.</li>
         </ul>
       </div>
       <div>
         <p><b>Contactos de emergencia</b></p>
         <ul>
           <li>Rosa Poot Canché · madre · <b>999 412 88 03</b> <span class="tag g">principal</span></li>
           <li>Andrés Cauich Ek · compañero de trabajo · 999 620 41 09</li>
         </ul>
         <p style="margin-top:8px"><b>Al momento del disparo</b></p>
         <p class="fine">Marcó «estoy con alguien» a las 08:53 e identificó a su compañero de cuadrilla. Se le mostraron Línea de la Vida (800 911 2000) y SAPTEL (55 5259 8121).</p>
       </div>
     </div>

     <h3 class="sec">10 · Cronología del caso</h3>
     ${T([{ t: 'Hora', w: '12%' }, 'Evento'], [
        ['08:47', 'Registro diario con texto libre. Filtro léxico detecta categoría ideación.'],
        ['08:49', 'MDI adelantado. Total 38/50, ítem 6 en 4.'],
        ['08:51', 'ASQ encadenado automáticamente.'],
        { c: ['08:52', '<b>ASQ positiva aguda.</b> Caso abierto. Notificación al profesional designado y a la guardia.'], cls: 'hi' },
        ['08:53', 'Pantalla de crisis mostrada. Salida sin contacto bloqueada. La persona marcó «estoy con alguien».'],
        ['09:09', 'Primer contacto telefónico del profesional designado. 17 minutos desde el disparo (SLA: 30 min).'],
        ['09:41', 'Consulta de ficha por clínico acreditado de A Tu Lado. Motivo: revisión de caso rojo agudo abierto.'],
        ['09:44', 'Emisión de este documento.'],
        ['—', '<b>Pendiente:</b> cierre con contacto humano verificado y nota firmada del profesional.']
     ])}

     <h3 class="sec">11 · Qué sigue</h3>
     <p>El caso permanece abierto hasta que el profesional designado registre el contacto humano verificado y la nota de cierre. <b>La plataforma no cierra este caso por vencimiento de tiempo</b> ni por inactividad de la persona. Mientras el caso siga abierto, la persona recibe contenido de contención en lugar de cuestionarios nuevos.</p>

     <div class="legal">${L_ALCANCE_CLIN}${L_CONF}${L_USO}${L_TRAZA}</div>

     <div class="signs">
       <div><b>${c.destinatario}</b>Profesional designado · recibe</div>
       <div><b>${EMISOR_DEF.split(' — ')[0]}</b>Clínico acreditado A Tu Lado · emite</div>
     </div>`
  ],
  charts: () => {
    lineChart(document.getElementById('c-animo'), {
      height: 175, labels: DIAS30, min: 0, max: 4, ticks: 4, refLine: 1.4, refLabel: 'línea base 30 d',
      series: [{ name: 'Registro diario', color: '#2E5D4B',
        data: [1, 1, 2, 1, 1, 2, 1, 1, 1, 2, 1, 1, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 3, 3, 3, 4, 4] }]
    });
  }
},

/* ─────────────────────── 7 · ACTA DE PROTOCOLO ─────────────────────── */
acta: {
  n: 7, nombre: 'Acta de activación y cierre de protocolo', audiencia: 'profesional',
  prefijo: 'AP', icono: 'fa-file-signature', color: '#6E140C',
  destinatarios: ['profesional', 'guardia'], periodicidad: 'Por caso',
  confidencial: true, requierePersona: true,
  resumen: 'Cronología minuto a minuto del caso, acciones tomadas, nota de cierre y firma del profesional que verificó el contacto humano.',
  render: c => [
    `<p class="lead">Acta del protocolo de crisis <b>CR-2026-0117</b>, activado el 14 de septiembre de 2026 a las 08:52 horas en <b>${c.inst.corto}</b>.</p>

     <h3 class="sec">1 · Datos del caso</h3>
     ${KV([
        ['Caso', 'CR-2026-0117'],
        ['Persona', 'Luis Manuel Chan Poot · COL-0412'],
        ['Disparado por', 'ASQ positiva aguda (ítem 5 positivo)'],
        ['Nivel asignado', '<span class="tag r">ROJO AGUDO</span>'],
        ['Profesional responsable', `${c.inst.profesional} · Ced. ${c.inst.cedula}`],
        ['Estado al momento de la emisión', '<span class="tag y">En atención — cierre pendiente</span>']
     ])}

     <h3 class="sec">2 · Cronología verificada</h3>
     ${T([{ t: 'Hora', w: '11%' }, 'Evento', { t: 'Registrado por', w: '26%' }], [
        ['08:47', 'Registro diario con texto libre; filtro léxico detecta ideación', 'Sistema'],
        ['08:49', 'MDI adelantado — total 38/50, ítem 6 en 4', 'Sistema'],
        ['08:51', 'ASQ encadenado automáticamente', 'Sistema'],
        { c: ['08:52', '<b>Apertura del caso.</b> Notificación simultánea a profesional designado y guardia', 'Sistema'], cls: 'hi' },
        ['08:52', 'Despliegue de líneas de ayuda: Línea de la Vida y SAPTEL', 'Sistema'],
        ['08:53', 'Pantalla de crisis; salida sin contacto bloqueada', 'Sistema'],
        ['08:53', '«Estoy con alguien» — identifica a compañero de cuadrilla', 'Persona usuaria'],
        ['08:55', 'Acuse de notificación del profesional designado', c.inst.profesional],
        { c: ['09:09', '<b>Primer contacto humano telefónico.</b> Duración 22 minutos', c.inst.profesional], cls: 'hig' },
        ['09:31', 'Acuerdo de seguimiento: llamada diaria durante 7 días', c.inst.profesional],
        ['09:41', 'Consulta de ficha para apoyo clínico', 'Said Canul (A Tu Lado)'],
        ['09:44', 'Emisión del resumen clínico RC-2026-0117-A', 'Said Canul (A Tu Lado)']
     ])}
     ${CO('Cumplimiento del compromiso de tiempo',
       'Transcurrieron <b>17 minutos</b> entre el disparo del protocolo y el primer contacto humano verificado. El compromiso contractual es de 30 minutos.', 'green')}

     <h3 class="sec">3 · Acciones tomadas</h3>
     ${T(['Acción', 'Detalle', { t: 'Estado', w: '18%' }], [
        ['Contención telefónica inmediata', 'Llamada de 22 minutos con el profesional designado', '<span class="tag g">Realizada</span>'],
        ['Verificación de acompañamiento presencial', 'Compañero de cuadrilla presente durante el evento', '<span class="tag g">Confirmada</span>'],
        ['Activación de la red de apoyo', 'Contacto con Rosa Poot Canché (madre), con consentimiento de la persona', '<span class="tag g">Realizada</span>'],
        ['Revisión del plan de seguridad', 'Repasado y actualizado durante la llamada', '<span class="tag g">Realizada</span>'],
        ['Canalización a atención presencial', 'Cita agendada para 15/09/2026, 18:00 h', '<span class="tag y">Agendada</span>'],
        ['Suspensión temporal de cuestionarios', 'La app entrega contenido de contención mientras el caso siga abierto', '<span class="tag g">Activa</span>'],
        ['Seguimiento diario', 'Llamada diaria durante 7 días', '<span class="tag y">En curso</span>']
     ])}

     <h3 class="sec">4 · Nota de cierre</h3>
     <div style="border:1px solid #DCE8E0;border-radius:5px;padding:10px;min-height:32mm;background:#FAFDFB">
       <p class="fine" style="margin:0">Espacio reservado para la nota del profesional responsable. El caso no puede cerrarse sin que esta sección esté completa y firmada.</p>
       <p class="fine" style="margin-top:6px"><b>Debe incluir:</b> estado de la persona al cierre, verificación de que el contacto humano ocurrió, acuerdos de seguimiento y, si aplica, la canalización a servicio externo.</p>
     </div>

     <h3 class="sec">5 · Verificación de cierre</h3>
     ${T(['Requisito para cerrar el caso', { t: 'Estado', w: '24%' }], [
        ['Contacto humano verificado por profesional con cédula', '<span class="tag g">Cumplido</span>'],
        ['Nota de cierre redactada y firmada', '<span class="tag y">Pendiente</span>'],
        ['Plan de seguridad revisado con la persona', '<span class="tag g">Cumplido</span>'],
        ['Acuerdo de seguimiento establecido', '<span class="tag g">Cumplido</span>'],
        ['Canalización a atención presencial ofrecida', '<span class="tag g">Cumplido</span>']
     ])}
     ${CO('La plataforma no cierra este caso',
       'Mientras la nota de cierre no esté firmada, el caso permanece abierto y visible en la cola de atención. No existe cierre por vencimiento de tiempo, por inactividad de la persona ni por decisión administrativa.', 'red')}

     <h3 class="sec">6 · Qué supo la institución</h3>
     <p>${c.inst.corto} recibió, a las 08:52, el aviso de que se activó un protocolo de crisis en la organización, y recibirá el aviso de su cierre. <b>No recibió ni recibirá</b> el nombre de la persona, el instrumento que disparó el caso, los puntajes, el texto escrito ni el contenido de la llamada.</p>

     <div class="legal">${L_ALCANCE_CLIN}${L_CONF}${L_USO}${L_TRAZA}</div>

     <div class="signs">
       <div><b>${c.inst.profesional}</b>Cédula ${c.inst.cedula} · profesional responsable del caso</div>
       <div><b>${EMISOR_DEF.split(' — ')[0]}</b>A Tu Lado · registro del sistema</div>
     </div>`
  ]
},

/* ─────────────────────── 8 · BITÁCORA DE ALERTAS ─────────────────────── */
bitacora: {
  n: 8, nombre: 'Bitácora de alertas del periodo', audiencia: 'profesional',
  prefijo: 'BA', icono: 'fa-bell', color: '#D9660F',
  destinatarios: ['profesional', 'guardia', 'contacto'], periodicidad: 'Mensual',
  seudonimo: true,
  resumen: 'Todos los eventos naranja y rojo del periodo en clave seudónima: qué regla disparó, tiempo de respuesta, quién atendió y cómo se cerró.',
  render: c => [
    `<p class="lead">Registro de todos los eventos de nivel naranja y rojo del periodo <b>${c.periodo}</b> en <b>${c.inst.corto}</b>. Las personas aparecen con su folio interno; este documento permite revisar la operación sin exponer identidades.</p>

     <h3 class="sec">Resumen del periodo</h3>
     <div class="cols3" style="margin-bottom:8px">
       ${STAT('Eventos registrados', '38', '', '11 rojo · 27 naranja')}
       ${STAT('Mediana de primer contacto', '21', 'min', 'SLA comprometido: 30 min')}
       ${STAT('Cerrados con contacto verificado', '100', '%', 'De los casos ya cerrados')}
     </div>

     <h3 class="sec">Distribución del tiempo de respuesta</h3>
     <div class="chartbox" id="c-sla"></div>
     <p class="fine">Ningún caso rebasó el compromiso de 30 minutos. El peor tiempo registrado fue de 28 minutos, en un evento nocturno de fin de semana.</p>

     <h3 class="sec">Eventos de nivel rojo</h3>
     ${T([{ t: 'Caso', w: '12%' }, { t: 'Folio', w: '9%' }, 'Disparo', { t: 'Fecha', w: '12%' }, { t: '1er contacto', n: 1 }, 'Atendió', 'Cierre'], [
        { c: ['CR-2026-0117', 'COL-0412', 'ASQ positiva aguda', '14/09 08:52', '17 min', 'R. Ancona', '<span class="tag y">En atención</span>'], cls: 'hi' },
        { c: ['CR-2026-0116', 'COL-0188', 'Bandera léxica + MDI 34', '13/09 21:14', '15 min', 'R. Ancona', '<span class="tag y">En atención</span>'], cls: 'hi' },
        ['CR-2026-0115', 'COL-0307', 'R3 caída abrupta', '13/09 07:40', '12 min', 'R. Ancona', '<span class="tag y">En atención</span>'],
        ['CR-2026-0109', 'COL-0094', 'MDI ítem 6 en 3', '05/09 08:41', '19 min', 'R. Ancona', '<span class="tag g">Verificado</span>'],
        ['CR-2026-0103', 'COL-0277', 'ASQ positiva no aguda', '28/08 14:02', '22 min', 'D. Mena', '<span class="tag g">Verificado</span>'],
        ['CR-2026-0098', 'COL-0412', 'R2 persistencia + WHO-5 31', '19/08 06:55', '24 min', 'R. Ancona', '<span class="tag g">Verificado</span>'],
        ['CR-2026-0091', 'COL-0356', 'Bandera léxica de ideación', '11/08 23:18', '28 min', 'D. Mena', '<span class="tag g">Verificado</span>'],
        ['CR-2026-0084', 'COL-0140', 'MDI 36', '02/08 10:30', '14 min', 'R. Ancona', '<span class="tag g">Verificado</span>'],
        ['CR-2026-0079', 'COL-0221', 'R1 + R2 combinadas', '24/07 07:12', '21 min', 'R. Ancona', '<span class="tag g">Verificado</span>'],
        ['CR-2026-0071', 'COL-0188', 'WHO-5 28', '12/07 09:03', '18 min', 'R. Ancona', '<span class="tag g">Verificado</span>'],
        ['CR-2026-0064', 'COL-0505', 'ASQ positiva no aguda', '29/06 16:41', '26 min', 'D. Mena', '<span class="tag g">Verificado</span>']
     ])}
     ${CO('Reincidencias', 'Dos folios aparecen más de una vez en el periodo: COL-0412 (agosto y septiembre) y COL-0188 (julio y septiembre). La reincidencia es el patrón que más conviene revisar con el profesional: sugiere que el acompañamiento actual no está siendo suficiente para esas dos personas.', 'amber')}`,

    `<h3 class="sec">Eventos de nivel naranja</h3>
     <p class="fine">No activan protocolo de crisis. Se listan porque son el insumo del seguimiento preventivo.</p>
     ${T([{ t: 'Folio', w: '11%' }, 'Regla o instrumento que disparó', { t: 'Fecha', w: '12%' }, 'Acción del motor', 'Resultado a 14 días'], [
        ['COL-0221', 'R2 persistencia', '11/09', 'WHO-5 adelantado', '<span class="tag y">Sigue en naranja</span>'],
        ['COL-0356', 'Alteración del sueño sostenida', '08/09', 'Contenido de higiene del sueño', '<span class="tag y">Sigue en naranja</span>'],
        ['COL-0094', 'WHO-5 41', '02/09', 'MDI programado', '<span class="tag g">Bajó a amarillo</span>'],
        ['COL-0511', 'R1 desviación', '29/08', 'Seguimiento reforzado', '<span class="tag g">Bajó a verde</span>'],
        ['COL-0277', 'MDI 24', '21/08', 'Herramientas de regulación sugeridas', '<span class="tag g">Bajó a amarillo</span>'],
        ['COL-0140', 'R4 silencio 14 días', '18/08', 'Mensaje de reenganche', '<span class="tag r">Sin respuesta</span>'],
        ['COL-0028', 'R3 caída abrupta', '11/08', 'WHO-5 adelantado', '<span class="tag g">Bajó a verde</span>'],
        ['COL-0455', 'WHO-5 44', '04/08', 'MDI programado', '<span class="tag g">Bajó a amarillo</span>']
     ])}
     <p class="fine">Se muestran 8 de 27 eventos naranja. El anexo digital contiene la lista completa en CSV.</p>

     <h3 class="sec">Qué disparó los eventos</h3>
     <div class="chartbox" id="c-reglas"></div>
     <p class="fine">El registro diario y sus reglas de vigilancia detectan más que los cuestionarios programados. Es el argumento de fondo del diseño del motor: la señal aparece en lo cotidiano, no en la evaluación periódica.</p>

     <h3 class="sec">Silencio prolongado — lo que no se ve</h3>
     ${CO('El punto ciego del sistema',
       'Nueve personas llevan más de catorce días sin ningún registro. La regla R4 las marca, pero una persona en silencio no genera datos: no sabemos si está bien, si dejó la organización o si dejó de usar la app precisamente porque está mal. Cuatro de las nueve venían de nivel amarillo, y ese es el subconjunto que conviene perseguir primero.', 'amber')}
     ${T([{ t: 'Folio', w: '12%' }, { t: 'Días en silencio', n: 1 }, 'Último nivel conocido', 'Acción tomada'], [
        ['COL-0140', '19', '<span class="tag">Verde</span>', 'Mensaje de reenganche sin respuesta'],
        ['COL-0398', '17', '<span class="tag y">Amarillo</span>', 'Escalado al profesional designado'],
        ['COL-0462', '16', '<span class="tag y">Amarillo</span>', 'Escalado al profesional designado'],
        ['COL-0233', '15', '<span class="tag">Verde</span>', 'Mensaje de reenganche'],
        ['COL-0087', '15', '<span class="tag y">Amarillo</span>', 'Escalado al profesional designado']
     ])}
     <p class="fine">Se muestran 5 de 9 personas en silencio.</p>

     <div class="legal">${L_ALCANCE_CLIN}${L_USO}
       <p><b>Seudonimización.</b> Este documento identifica a las personas mediante su folio interno. La correspondencia entre folio y nombre existe únicamente dentro del plano clínico y se resuelve sólo con un protocolo activo y motivo declarado.</p>
       ${L_TRAZA}</div>`
  ],
  charts: () => {
    columnChart(document.getElementById('c-sla'), {
      height: 175, unit: ' casos',
      data: [
        { k: '0–5 min', v: 6, color: SEM.verde }, { k: '6–10', v: 9, color: SEM.verde },
        { k: '11–15', v: 8, color: SEM.verde }, { k: '16–20', v: 6, color: SEM.verde },
        { k: '21–25', v: 6, color: SEM.amarillo }, { k: '26–30', v: 3, color: SEM.naranja },
        { k: '>30 (SLA)', v: 0, color: SEM.rojo }
      ]
    });
    barChart(document.getElementById('c-reglas'), {
      labelW: 185, rowH: 23, max: 14,
      data: [
        { k: 'R2 persistencia', v: 11, color: '#2A78D6' },
        { k: 'R1 desviación de base', v: 9, color: '#2A78D6' },
        { k: 'Filtro léxico', v: 7, color: '#B02418' },
        { k: 'R3 caída abrupta', v: 5, color: '#2A78D6' },
        { k: 'WHO-5 programado', v: 4, color: '#1BAF7A' },
        { k: 'MDI ítem 6', v: 2, color: '#D9660F' }
      ]
    });
  }
},

/* ─────────────────────── 9 · SEGUIMIENTO CLÍNICO ─────────────────────── */
seguimiento: {
  n: 9, nombre: 'Lista de seguimiento clínico', audiencia: 'profesional',
  prefijo: 'SC', icono: 'fa-list-check', color: '#D55181',
  destinatarios: ['profesional', 'guardia'], periodicidad: 'Semanal',
  confidencial: true,
  resumen: 'Las personas en nivel naranja y rojo ordenadas por prioridad, con semáforo, última actividad, instrumentos pendientes y plan de seguridad.',
  render: c => [
    `<p class="lead">Agenda de trabajo de la semana para <b>${c.destinatario}</b> en <b>${c.inst.corto}</b>. Ordenada por prioridad clínica, no alfabéticamente ni por antigüedad.</p>

     <h3 class="sec">Prioridad 1 — casos abiertos</h3>
     ${T(['Persona', { t: 'Folio', w: '9%' }, 'Nivel', { t: 'WHO-5', n: 1 }, { t: 'MDI', n: 1 }, 'Último contacto', 'Siguiente acción'], [
        { c: ['Luis Manuel Chan Poot', 'COL-0412', '<span class="tag r">Rojo agudo</span>', '24', '38', 'Hoy 09:09', '<b>Llamada de seguimiento diario</b> · cierre pendiente'], cls: 'hi' },
        { c: ['María Isabel Uc Canché', 'COL-0188', '<span class="tag r">Rojo</span>', '28', '34', 'Ayer 21:29', 'ASQ sin responder — insistir'], cls: 'hi' },
        { c: ['José Armando Pech Kuk', 'COL-0307', '<span class="tag r">Rojo</span>', '32', '31', 'Ayer 07:52', 'Segunda llamada de seguimiento'], cls: 'hi' }
     ])}

     <h3 class="sec">Prioridad 2 — nivel naranja con señal activa</h3>
     ${T(['Persona', { t: 'Folio', w: '9%' }, 'Señal', { t: 'WHO-5', n: 1 }, 'Plan de seguridad', 'Siguiente acción'], [
        ['Gabriela Sosa Medina', 'COL-0221', 'R2 persistencia · 11 días', '41', '<span class="tag g">Sí</span>', 'Revisión en 48 h'],
        ['Ricardo Novelo Aguilar', 'COL-0356', 'Alteración del sueño sostenida', '44', '<span class="tag r">No</span>', 'Proponer plan de seguridad'],
        ['Fernando Tuz Canul', 'COL-0140', 'R4 silencio · 19 días', '51', '<span class="tag r">No</span>', 'Contacto directo, no automático']
     ])}
     ${CO('Sobre el silencio',
       'COL-0140 no ha respondido a dos mensajes automáticos. Cuando la regla R4 rebasa quince días en alguien que venía de nivel amarillo o peor, la recomendación es contacto humano directo, no otro recordatorio del sistema.', 'amber')}

     <h3 class="sec">Prioridad 3 — instrumentos vencidos</h3>
     ${T(['Persona', { t: 'Folio', w: '9%' }, 'Instrumento', 'Programado para', { t: 'Días de retraso', n: 1 }], [
        ['Diana Carolina Ek Balam', 'COL-0094', 'WHO-5', '02/09/2026', '12'],
        ['Silvia Poot Chuc', 'COL-0511', 'WHO-5', '04/09/2026', '10'],
        ['Alejandro Herrera Díaz', 'COL-0028', 'WHO-5', '07/09/2026', '7']
     ])}

     <h3 class="sec">Cierres que dependen de una firma</h3>
     ${T(['Caso', { t: 'Folio', w: '10%' }, 'Contacto verificado', 'Nota de cierre', { t: 'Días abierto', n: 1 }], [
        { c: ['CR-2026-0117', 'COL-0412', '<span class="tag g">Sí — 14/09 09:09</span>', '<span class="tag y">Pendiente</span>', '0'], cls: 'hiy' },
        { c: ['CR-2026-0116', 'COL-0188', '<span class="tag g">Sí — 13/09 21:29</span>', '<span class="tag y">Pendiente</span>', '1'], cls: 'hiy' },
        { c: ['CR-2026-0115', 'COL-0307', '<span class="tag g">Sí — 13/09 07:52</span>', '<span class="tag y">Pendiente</span>', '1'], cls: 'hiy' }
     ])}
     <p class="fine">Estos tres casos ya tuvieron contacto humano y sólo esperan la nota firmada. Mientras tanto siguen contando como abiertos en todos los indicadores del servicio.</p>

     <h3 class="sec">Contexto de la institución</h3>
     ${KV([
        ['Padrón activo', `${c.inst.activos} personas`],
        ['En nivel naranja o superior', '54 personas (16% del activo)'],
        ['Con plan de seguridad', '41 de 54 · 76%'],
        ['Área con mayor concentración', 'Obra — cuadrilla nocturna (67% de las alertas con 11% del padrón)'],
        ['Ventana de contacto para turno nocturno', '17:00 – 21:00']
     ])}

     <div class="legal">${L_ALCANCE_CLIN}${L_CONF}${L_USO}${L_TRAZA}</div>`
  ]
},

/* ─────────────────────── 10 · PLATAFORMA ─────────────────────── */
plataforma: {
  n: 10, nombre: 'Reporte de plataforma', audiencia: 'interno',
  prefijo: 'PL', icono: 'fa-layer-group', color: '#0D1410',
  destinatarios: ['direccion_atl'], periodicidad: 'Mensual',
  todasInstituciones: true,
  resumen: 'Comparativo de todas las instituciones: actividad, adopción, arraigo, casos, SLA y señales de riesgo de renovación.',
  render: c => [
    `<p class="lead">Corte de operación de A Tu Lado al <b>${c.fecha}</b>. Documento de uso interno: incluye información comercial y de salud de cuenta que no se comparte con las instituciones.</p>

     <h3 class="sec">Pulso de la plataforma</h3>
     <div class="cols3" style="margin-bottom:8px">
       ${STAT('Instituciones activas', '14', '', '+2 en el trimestre · 2 en onboarding')}
       ${STAT('Cuentas activadas', '3,186', '/ 4,024', '79.2% de adopción global')}
       ${STAT('Índice de arraigo', '0.41', '', 'DAU/MAU · +0.04')}
     </div>
     <div class="cols3" style="margin-bottom:8px">
       ${STAT('Casos rojo abiertos', '7', '', '2 agudos en 5 instituciones')}
       ${STAT('Mediana de primer contacto', '21', 'min', 'SLA 30 min · 100% cumplido')}
       ${STAT('Cierres verificados', '100', '%', '38 de 38 casos del trimestre')}
     </div>

     <h3 class="sec">Comparativo por institución</h3>
     ${T(['Institución', { t: 'Padrón', n: 1 }, { t: 'Adopción', n: 1 }, { t: 'Arraigo', n: 1 }, { t: 'IBI', n: 1 }, { t: 'Casos', n: 1 }, { t: '1er contacto', n: 1 }, { t: 'Renueva', w: '12%' }, 'Salud de cuenta'], [
        { c: ['Constructora Mayab', '412', '83%', '0.47', '64', '3', '17 min', '14/02/27', '<span class="tag g">Sana</span>'], cls: 'hig' },
        { c: ['IT Soporte Cancún', '186', '88%', '0.52', '66', '2', '12 min', '01/12/26', '<span class="tag g">Sana</span>'], cls: 'hig' },
        ['Tecnológico de Mérida', '1,204', '76%', '0.44', '63', '0', '—', '31/07/27', '<span class="tag g">Sana</span>'],
        { c: ['Grupo Peninsular', '738', '71%', '0.29', '59', '1', '24 min', '30/09/26', '<span class="tag r">En riesgo</span>'], cls: 'hi' },
        { c: ['Hotel Xcanatún', '294', '61%', '0.22', '52', '1', '29 min', '15/11/26', '<span class="tag r">En riesgo</span>'], cls: 'hi' },
        ['TRAsystems', '18', '22%', '—', '—', '—', '—', '02/09/27', '<span class="tag y">Onboarding</span>']
     ])}

     <h3 class="sec">Cuentas que requieren intervención comercial</h3>
     ${T(['Cuenta', 'Señal', 'Diagnóstico', 'Acción sugerida'], [
        ['<b>Grupo Peninsular</b>', 'Uso a la baja 3 semanas (−22%); renueva en 16 días',
         'Nunca hicieron la campaña de difusión interna. El arraigo de 0.29 es el más bajo de las cuentas maduras.',
         'Sesión de resultados con dirección + kit de difusión antes de la renovación'],
        ['<b>Hotel Xcanatún</b>', 'Adopción estancada en 61%; 16 personas en silencio R4',
         'Alta rotación de personal y padrón desactualizado. El NDA del profesional vence en 9 días.',
         'Renovar NDA esta semana + carga masiva de padrón actualizado'],
        ['<b>TRAsystems</b>', 'Onboarding detenido a 12 días del alta',
         'Falta designar profesional. Sin eso no puede operar el protocolo.',
         'Bloquear el arranque hasta designarlo; no facturar hasta entonces']
     ])}
     ${CO('Regla que conviene mantener',
       'Una institución sin profesional designado no debe activarse, aunque ya haya firmado. El servicio sin protocolo humano es exactamente lo que la Memoria Técnica promete no ser.', 'red')}`,

    `<h3 class="sec">Evolución del padrón y la adopción</h3>
     <div class="chartbox" id="c-pl"></div>

     <h3 class="sec">Distribución clínica consolidada</h3>
     ${DIST([
        { name: 'Verde', label: '71% Verde', v: 71, color: SEM.verde },
        { name: 'Amarillo', label: '18% Amarillo', v: 18, color: SEM.amarillo, dark: true },
        { name: 'Naranja', label: '8% Naranja', v: 8, color: SEM.naranja },
        { name: 'Rojo', label: '3%', v: 3, color: SEM.rojo }
     ])}
     ${T(['Nivel', { t: 'Personas', n: 1 }, { t: '% del padrón activo', n: 1 }, { t: 'Cambio mensual', n: 1 }], [
        ['Verde', '2,275', '71.4%', '+1.8 pts'],
        ['Amarillo', '580', '18.2%', '−1.1 pts'],
        ['Naranja', '258', '8.1%', '−0.6 pts'],
        ['Rojo', '67', '2.1%', '−0.1 pts'],
        ['Rojo agudo', '6', '0.2%', '=']
     ])}

     <h3 class="sec">Carga operativa del equipo clínico</h3>
     ${T(['Profesional', { t: 'Instituciones', n: 1 }, { t: 'Casos del mes', n: 1 }, { t: 'Mediana contacto', n: 1 }, { t: 'Horas de guardia', n: 1 }, 'Estado'], [
        ['Psic. Roberto Ancona', '1', '6', '17 min', '168', '<span class="tag g">Normal</span>'],
        { c: ['Psic. Daniela Mena (guardia)', '9', '19', '23 min', '210', '<span class="tag y">Sobrecarga</span>'], cls: 'hiy' },
        ['Said Canul (clínico acreditado)', '14', '—', '—', '—', '<span class="tag g">Normal</span>']
     ])}
     ${CO('Cuello de botella',
       'Nueve instituciones dependen de una sola persona de guardia. Es el riesgo operativo más serio de la plataforma ahora mismo: una incapacidad suya deja sin cobertura a la mayoría del padrón. Contratar un segundo profesional de guardia es prioritario sobre cualquier función nueva del producto.', 'red')}

     <h3 class="sec">Integridad del modelo de privacidad</h3>
     ${T(['Control', { t: 'Eventos', n: 1 }, { t: 'Estado', w: '20%' }], [
        ['Accesos institucionales a datos identificados', '0', '<span class="tag g">Íntegro</span>'],
        ['Aperturas de ficha sin motivo declarado', '0', '<span class="tag g">Íntegro</span>'],
        ['Cierres sin contacto humano verificado', '0', '<span class="tag g">Íntegro</span>'],
        ['NDA vencidos con acceso activo', '0', '<span class="tag g">Íntegro</span>'],
        ['NDA por vencer en 30 días', '1', '<span class="tag y">Atender</span>'],
        ['Enlaces confidenciales no abiertos en 72 h', '2', '<span class="tag y">Revisar</span>']
     ])}

     <h3 class="sec">Prioridades del mes</h3>
     <ol>
       <li><b>Contratar segundo profesional de guardia.</b> Riesgo operativo mayor que cualquier deuda técnica pendiente.</li>
       <li><b>Sesión de resultados con Grupo Peninsular</b> antes del 30 de septiembre. Es la renovación más cercana y la más floja.</li>
       <li><b>Renovar NDA de Daniela Mena</b> antes del 23 de septiembre; al vencer pierde acceso de forma automática.</li>
       <li><b>Desbloquear el onboarding de TRAsystems</b> o pausarlo formalmente.</li>
       <li><b>Revisar la calibración del filtro léxico:</b> 9 falsos positivos en el trimestre, todos en la categoría desesperanza.</li>
     </ol>

     <div class="legal">
       <p><b>Uso interno.</b> Este documento contiene información comercial y de salud de cuenta. No se comparte con las instituciones ni con los profesionales designados.</p>
       ${L_ALCANCE_CLIN}
     </div>`
  ],
  charts: () => {
    lineChart(document.getElementById('c-pl'), {
      height: 190, labels: ['Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'], min: 0, max: 100, ticks: 4,
      series: [
        { name: 'Adopción %', color: '#2A78D6', data: [61, 64, 68, 70, 73, 75, 77, 79] },
        { name: 'Arraigo ×100', color: '#5B4A8A', data: [31, 33, 34, 36, 35, 38, 40, 41] }
      ]
    });
  }
},

/* ─────────────────────── 11 · INVESTIGACIÓN ─────────────────────── */
investigacion: {
  n: 11, nombre: 'Informe anonimizado de producto e investigación', audiencia: 'interno',
  prefijo: 'IA', icono: 'fa-flask', color: '#0D1410',
  destinatarios: ['direccion_atl'], periodicidad: 'Semestral',
  todasInstituciones: true,
  resumen: 'Corte del perfil estadístico por puesto, sexo, rango de edad, escolaridad, antigüedad, jornada y origen, sin llave reversible.',
  render: c => [
    `${CO('Origen de los datos de este informe',
      'Proviene íntegramente de la tabla <b>perfil_estadistico</b>, que guarda resultados agregados junto a variables de contexto <b>sin ninguna llave que permita regresar al usuario</b>. No contiene ni puede contener nombres, correos, fechas de nacimiento ni folios internos. Ningún dato de este informe puede reconstruirse hacia una persona.', 'green')}

     <p class="lead">Corte semestral sobre <b>3,186 personas activas</b> en 14 instituciones. El propósito es doble: calibrar el motor clínico con evidencia propia y construir la base de publicación académica del proyecto.</p>

     <h3 class="sec">Variables del perfil estadístico</h3>
     ${T(['Variable', 'Valores', 'Cobertura', 'Para qué se usa'], [
        ['Puesto', 'Texto normalizado a catálogo', '94%', 'Cohortes por tipo de trabajo'],
        ['Sexo', 'M / F / Prefiere no decir', '89%', 'Diferencias de expresión de síntomas'],
        ['Rango de edad', 'Quinquenios, derivado del año de nacimiento', '86%', 'Cohortes etarias'],
        ['Escolaridad', 'Catálogo de 6 niveles', '78%', 'Legibilidad de instrumentos'],
        ['Antigüedad', 'Rangos, derivada de la fecha de ingreso', '91%', 'Vulnerabilidad por etapa'],
        ['Tipo de jornada', 'Matutina / vespertina / nocturna / mixta', '93%', 'Hallazgo principal del semestre'],
        ['Lugar de origen', 'Municipio y estado', '71%', 'Contexto regional'],
        ['Interacción', 'Eventos por módulo, sin contenido', '100%', 'Diseño de producto']
     ])}

     <h3 class="sec">Hallazgo principal — jornada de trabajo</h3>
     <div class="chartbox" id="c-jor"></div>
     ${T(['Jornada', { t: 'WHO-5 medio', n: 1 }, { t: '% bajo el corte de 50', n: 1 }, { t: '% en naranja o rojo', n: 1 }, { t: 'Adherencia', n: 1 }], [
        ['Matutina', '62', '18%', '9%', '76%'],
        ['Vespertina', '57', '24%', '12%', '71%'],
        ['Mixta o rotativa', '53', '31%', '17%', '64%'],
        { c: ['<b>Nocturna</b>', '<b>48</b>', '<b>42%</b>', '<b>26%</b>', '<b>61%</b>'], cls: 'hi' }
     ])}
     ${CO('La diferencia más grande de todo el corte',
       'Catorce puntos de WHO-5 entre jornada matutina y nocturna, con el triple de proporción en niveles de atención. Es una diferencia mayor que la de cualquier otra variable medida —incluidas edad, escolaridad y antigüedad— y se sostiene en las cinco instituciones con personal nocturno.', 'amber')}`,

    `<h3 class="sec">Antigüedad en la organización</h3>
     ${T(['Antigüedad', { t: '% del padrón', n: 1 }, { t: 'WHO-5 medio', n: 1 }, { t: 'Adherencia', n: 1 }, { t: '% naranja o rojo', n: 1 }], [
        { c: ['Menos de 6 meses', '18%', '51', '64%', '19%'], cls: 'hiy' },
        ['6 meses – 2 años', '34%', '57', '70%', '16%'],
        ['2 – 5 años', '29%', '62', '76%', '12%'],
        ['Más de 5 años', '19%', '60', '81%', '13%']
     ])}
     <p class="fine">El primer semestre es el más frágil en las dos dimensiones a la vez: peor bienestar y menor uso. La curva se aplana después de los dos años. Hallazgo replicado en 11 de 14 instituciones.</p>

     <h3 class="sec">Rango de edad</h3>
     ${T(['Rango', { t: '% del padrón', n: 1 }, { t: 'WHO-5 medio', n: 1 }, { t: 'Uso de herramientas', n: 1 }, 'Módulo preferido'], [
        ['18 – 24', '21%', '54', '68%', 'Herramientas de regulación'],
        ['25 – 34', '33%', '57', '61%', 'Registro diario'],
        ['35 – 44', '26%', '60', '49%', 'Registro diario'],
        ['45 – 54', '14%', '61', '38%', 'Recursos y artículos'],
        ['55 o más', '6%', '63', '31%', 'Recursos y artículos']
     ])}
     <p class="fine">El gradiente por edad es suave en bienestar pero fuerte en <b>forma de uso</b>: la gente joven usa herramientas interactivas, la de mayor edad prefiere leer. Es un dato de diseño de producto, no clínico.</p>

     <h3 class="sec">Escolaridad y legibilidad de los instrumentos</h3>
     ${T(['Nivel', { t: '% del padrón', n: 1 }, { t: 'Tasa de abandono del MDI', n: 1 }, { t: 'Tiempo medio de respuesta', n: 1 }], [
        ['Primaria', '9%', '21%', '6.4 min'],
        ['Secundaria', '27%', '14%', '5.1 min'],
        ['Bachillerato / técnico', '34%', '9%', '3.8 min'],
        ['Licenciatura', '26%', '6%', '3.2 min'],
        ['Posgrado', '4%', '5%', '3.0 min']
     ])}
     ${CO('Implicación de producto',
       'La tasa de abandono del MDI se triplica entre posgrado y primaria, y el tiempo de respuesta casi se duplica. El instrumento no está siendo igual de accesible para todo el padrón. Conviene probar una versión con lectura en voz alta y redacción simplificada, validada contra la versión original.', 'amber')}

     <h3 class="sec">Uso por módulo</h3>
     <div class="chartbox" id="c-mod2"></div>

     <h3 class="sec">Uso por hora del día</h3>
     <div class="chartbox" id="c-hor"></div>
     <p class="fine">14% de las sesiones ocurren entre las 22:00 y las 06:00. En la población de jornada nocturna sube a 41%. El uso nocturno correlaciona con puntajes altos en el ítem de sueño del MDI.</p>

     <h3 class="sec">Líneas de investigación abiertas</h3>
     <ol>
       <li><b>Jornada nocturna y bienestar percibido.</b> El hallazgo es lo bastante grande y consistente para sostener un artículo. Requiere control por antigüedad y tipo de industria.</li>
       <li><b>Valor predictivo de las reglas de vigilancia.</b> ¿Cuántas de las alertas R1–R3 anticipan un descenso confirmado por instrumento? Es la validación interna del motor.</li>
       <li><b>Legibilidad diferencial del MDI.</b> Comparar versión estándar contra versión simplificada.</li>
       <li><b>El punto ciego del silencio.</b> Qué ocurre con quienes dejan de registrar: seguimiento con consentimiento explícito.</li>
     </ol>

     <div class="legal">
       <p><b>Uso interno y académico.</b> Los datos de este informe son anónimos por construcción y pueden usarse para publicación científica citando a A Tu Lado como fuente, previa aprobación de dirección.</p>
       <p><b>Sin reversibilidad.</b> La tabla <span style="font-family:var(--font-mono)">perfil_estadistico</span> no contiene llave foránea hacia usuarios. Ningún dato aquí presentado puede atribuirse a una persona, ni siquiera desde dentro de la plataforma.</p>
       ${L_ALCANCE_CLIN}
     </div>`
  ],
  charts: () => {
    barChart(document.getElementById('c-jor'), {
      labelW: 155, rowH: 26, max: 100, unit: ' / 100',
      data: [
        { k: 'Matutina', v: 62, color: '#2A78D6' },
        { k: 'Vespertina', v: 57, color: '#2A78D6' },
        { k: 'Mixta o rotativa', v: 53, color: '#D9660F' },
        { k: 'Nocturna', v: 48, color: '#B02418' }
      ]
    });
    barChart(document.getElementById('c-mod2'), {
      labelW: 175, rowH: 23, max: 45, unit: '%',
      data: [
        { k: 'Registro diario', v: 39, color: '#2A78D6' },
        { k: 'Herramientas', v: 22, color: '#5B4A8A' },
        { k: 'Recursos y artículos', v: 15, color: '#1BAF7A' },
        { k: 'Revista comunitaria', v: 11, color: '#C98500' },
        { k: 'Cuestionarios', v: 8, color: '#D55181' },
        { k: 'Plan de seguridad', v: 4, color: '#8EADA4' },
        { k: 'Canal de crisis', v: 1, color: '#B02418' }
      ]
    });
    columnChart(document.getElementById('c-hor'), {
      height: 165, unit: '%', showValues: false, max: 12,
      data: [
        { k: '00', v: 2.1, color: '#5B4A8A' }, { k: '02', v: 1.4, color: '#5B4A8A' }, { k: '04', v: 1.8, color: '#5B4A8A' },
        { k: '06', v: 9.4, color: '#2E5D4B' }, { k: '08', v: 11.2, color: '#2E5D4B' }, { k: '10', v: 7.6, color: '#2E5D4B' },
        { k: '12', v: 8.1, color: '#2E5D4B' }, { k: '14', v: 6.9, color: '#2E5D4B' }, { k: '16', v: 7.2, color: '#2E5D4B' },
        { k: '18', v: 9.8, color: '#2E5D4B' }, { k: '20', v: 10.1, color: '#2E5D4B' }, { k: '22', v: 5.6, color: '#5B4A8A' }
      ]
    });
  }
},

/* ─────────────────────── 12 · CALIBRACIÓN ─────────────────────── */
calibracion: {
  n: 12, nombre: 'Informe de calibración del motor clínico', audiencia: 'interno',
  prefijo: 'CA', icono: 'fa-gauge-high', color: '#0D1410',
  destinatarios: ['direccion_atl'], periodicidad: 'Trimestral',
  todasInstituciones: true,
  resumen: 'Disparos por regla, cuántos escalaron, falsas alarmas y precisión del filtro léxico por categoría. Permite ajustar umbrales con evidencia.',
  render: c => [
    `<p class="lead">Este informe existe para que los umbrales del motor se ajusten con evidencia y no con intuición. Cubre el trimestre <b>${c.periodo}</b> sobre las 14 instituciones activas.</p>

     <h3 class="sec">Reglas de vigilancia — capa 0</h3>
     ${T(['Regla', 'Qué detecta', { t: 'Disparos', n: 1 }, { t: 'Escalaron', n: 1 }, { t: 'Tasa de escalamiento', n: 1 }, { t: 'Falsas alarmas', n: 1 }], [
        ['<b>R1</b>', 'Desviación de la línea base de 30 días', '412', '96', '23%', '31'],
        { c: ['<b>R2</b>', 'Persistencia ≥ 5 días en valores 3–4', '188', '104', '<b>55%</b>', '9'], cls: 'hig' },
        ['<b>R3</b>', 'Caída abrupta de 2 o más niveles en 48 h', '96', '41', '43%', '14'],
        { c: ['<b>R4</b>', 'Silencio ≥ 14 días consecutivos', '341', '12', '<b>4%</b>', 'n/d'], cls: 'hiy' }
     ])}
     ${CO('R2 es la regla que más rinde',
       'Más de la mitad de sus disparos terminan en un escalamiento confirmado por instrumento, con la menor tasa de falsas alarmas. Es la señal más económica del motor: no requiere cuestionario, sólo constancia de registro.', 'green')}
     ${CO('R4 es la regla que menos sirve como está',
       'Sólo 4% de los silencios termina en escalamiento, pero eso no significa que el resto esté bien: significa que <b>no sabemos</b>. Una persona en silencio no genera datos. Subir el umbral a 21 días reduciría el ruido, pero retrasar el contacto en quien venía de nivel amarillo es justamente lo que no queremos. La propuesta es separar R4 en dos reglas según el último nivel conocido.', 'amber')}

     <h3 class="sec">Propuesta de ajuste de umbrales</h3>
     ${T(['Regla', 'Umbral actual', 'Umbral propuesto', 'Efecto estimado'], [
        ['R1', 'Desviación > 1.0 sobre la base', 'Sin cambio', '—'],
        ['R2', '≥ 5 días consecutivos', 'Sin cambio', 'Es la regla mejor calibrada'],
        ['R3', 'Caída ≥ 2 niveles en 48 h', 'Caída ≥ 2 niveles en 72 h', '+18% de disparos, −4 pts de falsas alarmas'],
        ['R4-a', '14 días (último nivel verde)', '21 días', '−38% de ruido'],
        ['R4-b', '14 días (último nivel amarillo o peor)', '10 días', 'Contacto humano más temprano donde importa']
     ])}

     <h3 class="sec">Instrumentos — capas 1 a 3</h3>
     ${T(['Instrumento', { t: 'Aplicaciones', n: 1 }, { t: 'Tasa de finalización', n: 1 }, { t: 'Tiempo medio', n: 1 }, { t: 'Escalaron al siguiente nivel', n: 1 }], [
        ['WHO-5', '6,104', '94%', '1.8 min', '11%'],
        ['MDI', '1,488', '88%', '4.2 min', '9%'],
        ['ASQ', '212', '97%', '1.1 min', '31% positivas'],
        ['Puchol', '1,402', '91%', '3.4 min', 'n/a']
     ])}
     <p class="fine">La tasa de finalización del MDI (88%) es la más baja del conjunto y la que más varía por escolaridad. Es el instrumento candidato a una versión simplificada.</p>`,

    `<h3 class="sec">Filtro léxico — precisión por categoría</h3>
     ${T(['Categoría', { t: 'Patrones', n: 1 }, { t: 'Detecciones', n: 1 }, { t: 'Verdaderos positivos', n: 1 }, { t: 'Precisión', n: 1 }, 'Acción del motor'], [
        { c: ['Ideación', '11', '24', '23', '<b>96%</b>', 'ASQ inmediata'], cls: 'hig' },
        { c: ['Desesperanza', '18', '61', '43', '<b>70%</b>', 'Adelanta WHO-5'], cls: 'hiy' },
        ['Aislamiento', '14', '48', '39', '81%', 'Suma a vigilancia'],
        ['Alteración del sueño', '9', '116', '109', '94%', 'Suma a Puchol fisiológico'],
        ['Violencia o miedo', '12', '19', '17', '89%', 'Aviso al profesional'],
        ['Consumo de sustancias', '10', '—', '—', '—', 'Desactivada por contrato en 6 instituciones']
     ])}
     ${CO('El problema está en desesperanza',
       '18 de los 61 disparos fueron falsos positivos, y concentran los 9 casos revisados manualmente del trimestre. El patrón culpable es el uso coloquial de expresiones como «ya no puedo más» referidas a carga de trabajo y no a estado de ánimo. Propuesta: exigir coocurrencia con un valor invertido ≥ 3 en el registro del mismo día antes de adelantar el WHO-5.', 'red')}

     <h3 class="sec">Encadenamiento del motor</h3>
     ${T(['Transición', { t: 'Ocurrencias', n: 1 }, { t: '% del origen', n: 1 }, 'Comentario'], [
        ['Registro diario → WHO-5 adelantado', '388', '—', 'Principal vía de entrada al tamizaje'],
        ['WHO-5 < 50 → MDI', '671', '11% de WHO-5', 'Funciona como se diseñó'],
        ['MDI ítem 6 ≥ 3 → ASQ', '134', '9% de MDI', 'Encadenamiento automático, sin excepción'],
        ['Filtro léxico ideación → ASQ', '24', '100% de la categoría', 'Sin intervención humana previa'],
        { c: ['ASQ → protocolo de crisis', '66', '31% de ASQ', 'Todas con contacto humano dentro de SLA'], cls: 'hig' }
     ])}

     <h3 class="sec">Rutas A / B / C</h3>
     ${T(['Ruta', 'Criterio de entrada', { t: 'Personas', n: 1 }, { t: '% del activo', n: 1 }, 'Comportamiento observado'], [
        ['Ruta A', 'Valor invertido 0–1 sostenido', '2,180', '68%', 'Ciclo de 30 días, sin cambios'],
        ['Ruta B', 'Valor invertido 2 o señal de vigilancia', '798', '25%', 'Ciclo de 14 días; 31% vuelve a ruta A en un mes'],
        ['Ruta C', 'Valor invertido 3–4 o instrumento positivo', '208', '7%', 'Seguimiento reforzado; 46% baja a ruta B en un mes']
     ])}
     <p class="fine">El movimiento entre rutas es la validación de que el motor no sólo detecta sino que acompaña: dos de cada tres personas que entran a ruta C salen de ella en menos de dos meses.</p>

     <h3 class="sec">Cambios recomendados para el siguiente trimestre</h3>
     <ol>
       <li><b>Separar R4 en R4-a y R4-b</b> según el último nivel conocido. Es el cambio con mayor impacto clínico.</li>
       <li><b>Exigir coocurrencia en la categoría desesperanza</b> del filtro léxico. Reduce el ruido sin tocar ideación, que está bien calibrada.</li>
       <li><b>Ampliar la ventana de R3 a 72 horas.</b> Gana sensibilidad sin costo apreciable en falsas alarmas.</li>
       <li><b>Probar una versión simplificada del MDI</b> contra la estándar, midiendo finalización y concordancia de puntaje.</li>
       <li><b>No tocar R2 ni el encadenamiento MDI → ASQ.</b> Son las piezas que mejor funcionan; cualquier ajuste ahí exige validación previa con el comité clínico.</li>
     </ol>

     <div class="legal">
       <p><b>Uso interno.</b> Cualquier cambio de umbral debe registrarse con fecha, responsable y justificación, y debe poder revertirse. Los cambios en las capas 2 y 3 requieren revisión del comité clínico antes de desplegarse.</p>
       ${L_ALCANCE_CLIN}
     </div>`
  ]
},

/* ─────────────────────── 13 · ELIMINACIÓN ─────────────────────── */
eliminacion: {
  n: 13, nombre: 'Constancia de eliminación y anonimización', audiencia: 'interno',
  prefijo: 'EL', icono: 'fa-trash-can-arrow-up', color: '#0D1410',
  destinatarios: ['contacto', 'direccion_atl'], periodicidad: 'Al cierre de contrato',
  resumen: 'Qué se desvinculó, qué se conservó seudonimizado por obligación legal y qué se eliminó, con fecha y responsable.',
  render: c => [
    `<p class="lead">A Tu Lado hace constar el tratamiento dado a la información asociada al contrato con <b>${c.inst.nombre}</b>, concluido el <b>${c.inst.vigencia}</b>, en cumplimiento de la política de retención declarada en el aviso de privacidad y en el contrato de servicio.</p>

     <h3 class="sec">1 · Datos desvinculados</h3>
     <p>Los siguientes datos dejaron de estar asociados a la institución. La institución no los conserva, no puede recuperarlos y no puede consultarlos.</p>
     ${T(['Conjunto de datos', { t: 'Registros', n: 1 }, { t: 'Fecha de desvinculación', w: '20%' }, { t: 'Estado', w: '16%' }], [
        ['Membresías institucionales del padrón', '412', c.inst.vigencia, '<span class="tag g">Desvinculado</span>'],
        ['Asignación a departamentos', '412', c.inst.vigencia, '<span class="tag g">Eliminado</span>'],
        ['Datos de puesto, turno y horario', '412', c.inst.vigencia, '<span class="tag g">Eliminado</span>'],
        ['Accesos del panel institucional', '3 cuentas', c.inst.vigencia, '<span class="tag g">Revocado</span>'],
        ['Acceso del profesional designado', '1 cuenta', c.inst.vigencia, '<span class="tag g">Revocado</span>']
     ])}

     <h3 class="sec">2 · Datos que permanecen con la persona</h3>
     ${CO('No se elimina lo que no es nuestro ni de la institución',
       'Los registros diarios, las respuestas a los instrumentos, el plan de seguridad y los contactos de emergencia pertenecen a cada persona usuaria y permanecen en su cuenta personal. La conclusión del contrato no cancela ninguna cuenta ni borra el historial de nadie. Cada persona recibió el aviso correspondiente y puede exportar o eliminar su propia información cuando lo decida.', 'green')}

     <h3 class="sec">3 · Datos conservados por obligación legal</h3>
     ${T(['Conjunto de datos', 'Forma de conservación', { t: 'Plazo', w: '14%' }, 'Fundamento'], [
        ['Eventos de crisis del periodo', 'Seudonimizados, sin llave hacia la institución', '5 años', 'Trazabilidad clínica y defensa ante reclamación'],
        ['Bitácora de accesos', 'Íntegra e inmutable', '5 años', 'Auditoría de tratamiento de datos'],
        ['Emisiones de documentos con folio', 'Registro de folio, destinatario y motivo', '5 años', 'Trazabilidad de entregas'],
        ['Constancias emitidas a la institución', 'Copia del documento firmado', '5 años', 'Respaldo del cumplimiento contractual']
     ])}
     <p class="fine">Estos conjuntos no permiten identificar a las personas de la institución. Su conservación responde a la necesidad de acreditar qué ocurrió y quién lo atendió, no a un interés comercial.</p>

     <h3 class="sec">4 · Datos eliminados</h3>
     ${T(['Conjunto de datos', { t: 'Registros', n: 1 }, { t: 'Fecha de eliminación', w: '20%' }, 'Método'], [
        ['Agregados institucionales del periodo', '—', `90 días posteriores`, 'Eliminación definitiva tras la entrega'],
        ['Archivos de carga de padrón (.xlsx originales)', '4 archivos', c.inst.vigencia, 'Eliminación definitiva'],
        ['Correos institucionales del padrón', '412', c.inst.vigencia, 'Sustituidos por el correo personal si la persona lo registró'],
        ['Números de empleado', '412', c.inst.vigencia, 'Eliminación definitiva']
     ])}

     <h3 class="sec">5 · Datos anónimos que permanecen</h3>
     <p>Los aportes de la institución a la tabla <span style="font-family:var(--font-mono)">perfil_estadistico</span> permanecen de forma indefinida. Esos registros <b>ya eran anónimos desde su creación</b>: no contienen nombre, correo, fecha de nacimiento, folio interno ni ninguna llave que permita regresar a una persona o a la institución de origen. No hay nada que eliminar en ellos porque no identifican a nadie.</p>

     <h3 class="sec">6 · Verificación</h3>
     ${T(['Verificación', { t: 'Resultado', w: '24%' }], [
        ['Ningún protocolo de crisis abierto al momento del cierre', '<span class="tag g">Confirmado</span>'],
        ['Ninguna cuenta institucional con acceso activo', '<span class="tag g">Confirmado</span>'],
        ['Ningún enlace de documento confidencial vigente', '<span class="tag g">Confirmado</span>'],
        ['Aviso enviado a cada persona del padrón', '<span class="tag g">Confirmado</span>'],
        ['Reporte de cierre entregado a la institución', '<span class="tag g">Confirmado</span>']
     ])}

     <div class="legal">
       <p><b>Derechos de las personas.</b> Cada persona usuaria conserva en todo momento el derecho de acceder, rectificar, cancelar u oponerse al tratamiento de su información, directamente desde su cuenta o escribiendo a privacidad@atulado.com.mx.</p>
       ${L_ALCANCE}
     </div>

     <div class="signs">
       <div><b>${EMISOR_DEF.split(' — ')[0]}</b>A Tu Lado · responsable del tratamiento</div>
       <div><b>${c.inst.contacto.split(' — ')[0]}</b>${c.inst.corto} · recibe la constancia</div>
     </div>`
  ]
}

};

/* ── Constantes de apoyo para las gráficas ─────────────────────── */
const SEMANAS = ['S1','S2','S3','S4','S5','S6','S7','S8','S9','S10','S11','S12'];
const DIAS30 = ['15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','01','02','03','04','05','06','07','08','09','10','11','12','13','14'];
