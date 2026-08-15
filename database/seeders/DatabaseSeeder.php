<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\CrisisLine;
use App\Models\MoodLog;
use App\Models\Resource;
use App\Models\SafetyPlan;
use App\Models\User;
use App\Models\UserResourceFavorite;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Demo User
        $demoUser = User::create([
            'name' => 'María López',
            'email' => 'demo@atulado.com.mx',
            'password' => Hash::make('password123'),
            'avatar_color' => 'sage',
            'bio' => 'Cuidando de mi salud mental día a día. Aprendiendo a escucharme.',
            'crisis_contact_name' => 'Sofía López (Hermana)',
            'crisis_contact_phone' => '55 9876 5432',
            'is_admin' => true,
        ]);

        // 2. Safety Plan for Demo User
        SafetyPlan::create([
            'user_id' => $demoUser->id,
            'warning_signs' => [
                'Pensamientos recurrentes de no ser suficiente',
                'Aislamiento social y no responder mensajes por días',
                'Tensión física en hombros y mandíbula apretada',
                'Insomnio persistente o despertar con taquicardia',
            ],
            'internal_coping' => [
                'Ejercicio de respiración diafragmática 4-7-8 por 4 minutos',
                'Ducha con agua tibia enfocando la atención en los sentidos',
                'Salir a caminar despacio sin audífonos',
                'Escribir en el diario sin censura por 10 minutos',
            ],
            'distraction_activities' => [
                'Ir al parque de los viveros',
                'Escuchar la playlist acústica de calma',
                'Preparar un té de manzanilla con miel',
                'Cuidar y regar las plantas del balcón',
            ],
            'trusted_contacts' => [
                ['name' => 'Sofía López', 'phone' => '+52 55 9876 5432', 'relationship' => 'Hermana'],
                ['name' => 'Carlos M.', 'phone' => '+52 55 1122 3344', 'relationship' => 'Mejor amigo'],
            ],
            'professional_contacts' => [
                ['name' => 'Dra. Andrea Morales', 'phone' => '+52 55 4433 2211', 'note' => 'Psicoterapeuta DBT (Mar y Jue)'],
                ['name' => 'Línea de la Vida México', 'phone' => '800 290 0024', 'note' => 'Atención gratuita 24h'],
            ],
            'safe_environment_steps' => 'Guardar medicamentos fuera del alcance visible, pedirle a mi hermana que me acompañe en momentos de crisis aguda, mantener el teléfono con carga.',
            'reasons_for_living' => "1. Ver crecer a mi sobrino Mateo.\n2. Terminar mi proyecto de ilustración.\n3. Viajar a la Patagonia con mi mejor amiga.\n4. La sensación de paz después de superar una tormenta.",
        ]);

        // 3. Past 14 Days Mood Logs for Demo User (creating a realistic trend & streak)
        $pastLogs = [
            ['days_ago' => 13, 'score' => 2, 'emotion' => 'Tristeza', 'energy' => 2, 'sleep' => 5, 'tags' => ['Cansancio', 'Insomnio'], 'note' => 'Día difícil en el trabajo, me sentí desconectada.', 'gratitude' => 'El café caliente por la mañana.'],
            ['days_ago' => 12, 'score' => 2, 'emotion' => 'Ansiedad', 'energy' => 2, 'sleep' => 6, 'tags' => ['Trabajo', 'Sobrecarga'], 'note' => 'Mucha prisa y presión por entregas.', 'gratitude' => 'Un mensaje de apoyo de Sofía.'],
            ['days_ago' => 11, 'score' => 3, 'emotion' => 'En equilibrio', 'energy' => 3, 'sleep' => 7, 'tags' => ['Rutina', 'Calma'], 'note' => 'Pude tomarme una pausa para comer con tranquilidad.', 'gratitude' => 'Poder descansar a tiempo.'],
            ['days_ago' => 10, 'score' => 4, 'emotion' => 'Bien', 'energy' => 4, 'sleep' => 8, 'tags' => ['Amigos', 'Paseo'], 'note' => 'Salí a caminar con Carlos por la tarde, me despejó mucho.', 'gratitude' => 'La charla con Carlos y el aire fresco.'],
            ['days_ago' => 9,  'score' => 3, 'emotion' => 'En equilibrio', 'energy' => 3, 'sleep' => 7, 'tags' => ['Trabajo', 'Productividad'], 'note' => 'Día neutro pero productivo.', 'gratitude' => 'Avanzar en mis pendientes sin estrés.'],
            ['days_ago' => 8,  'score' => 2, 'emotion' => 'Estrés', 'energy' => 2, 'sleep' => 5, 'tags' => ['Salud', 'Sobrecarga'], 'note' => 'Dolor de cabeza por la tarde. Usé la técnica 5-4-3-2-1.', 'gratitude' => 'Recordar que las sensaciones pasan.'],
            ['days_ago' => 7,  'score' => 3, 'emotion' => 'Calma', 'energy' => 3, 'sleep' => 8, 'tags' => ['Descanso', 'Autocuidado'], 'note' => 'Dormí 8 horas completas, me siento más regulada.', 'gratitude' => 'Una buena noche de sueño reparador.'],
            ['days_ago' => 6,  'score' => 4, 'emotion' => 'Esperanza', 'energy' => 4, 'sleep' => 7, 'tags' => ['Creatividad', 'Metas'], 'note' => 'Empecé un boceto nuevo, me motivó bastante.', 'gratitude' => 'Tener energía para crear algo propio.'],
            ['days_ago' => 5,  'score' => 4, 'emotion' => 'Bien', 'energy' => 4, 'sleep' => 7, 'tags' => ['Familia', 'Alimentación'], 'note' => 'Comí con mi familia y cocinamos juntos.', 'gratitude' => 'Las risas en la mesa y compartir la comida.'],
            ['days_ago' => 4,  'score' => 3, 'emotion' => 'En equilibrio', 'energy' => 3, 'sleep' => 7, 'tags' => ['Lectura', 'Calma'], 'note' => 'Día tranquilo en casa.', 'gratitude' => 'Un buen libro y silencio.'],
            ['days_ago' => 3,  'score' => 5, 'emotion' => 'Gratitud', 'energy' => 5, 'sleep' => 8, 'tags' => ['Logro', 'Amistad'], 'note' => 'Presenté mi proyecto y me felicitaron. Me sentí valorada.', 'gratitude' => 'El reconocimiento a mi esfuerzo.'],
            ['days_ago' => 2,  'score' => 4, 'emotion' => 'Calma', 'energy' => 4, 'sleep' => 8, 'tags' => ['Paz', 'Caminata'], 'note' => 'Caminata matutina, respiración diafragmática.', 'gratitude' => 'Sentir el sol de la mañana.'],
            ['days_ago' => 1,  'score' => 4, 'emotion' => 'Esperanza', 'energy' => 4, 'sleep' => 7, 'tags' => ['Bienestar', 'Reflexión'], 'note' => 'Siento que he avanzado mucho en mi autocuidado.', 'gratitude' => 'Mi constancia y paciencia conmigo misma.'],
            ['days_ago' => 0,  'score' => 5, 'emotion' => 'Paz y Esperanza', 'energy' => 5, 'sleep' => 8, 'tags' => ['Motivación', 'Amor propio'], 'note' => 'Hoy me siento en armonía y con ganas de seguir creciendo.', 'gratitude' => 'Estar presente aquí y ahora.'],
        ];

        foreach ($pastLogs as $log) {
            MoodLog::create([
                'user_id' => $demoUser->id,
                'score' => $log['score'],
                'primary_emotion' => $log['emotion'],
                'tags' => $log['tags'],
                'journal_entry' => $log['note'],
                'gratitude_note' => $log['gratitude'],
                'energy_level' => $log['energy'],
                'sleep_hours' => $log['sleep'],
                'logged_date' => Carbon::today()->subDays($log['days_ago'])->format('Y-m-d'),
            ]);
        }

        // 4. Seed Resources Library
        $resources = [
            [
                'title' => 'La regla 5-4-3-2-1 para anclar la ansiedad',
                'slug' => 'regla-54321-ansiedad',
                'category' => 'tip',
                'color_theme' => 'sage',
                'estimated_time' => '2 min',
                'summary' => 'Nombra 5 cosas que ves, 4 que tocas, 3 que escuchas, 2 que hueles y 1 que saboreas. Desactiva la respuesta de lucha o huida en 60 segundos.',
                'content' => "### ¿Por qué funciona el Grounding 5-4-3-2-1?
Cuando experimentamos ansiedad intensa, la amígdala cerebral toma el control, enviando señales de alerta biológica innecesarias. Esta técnica obliga a la corteza prefrontal a reenfocarse en datos sensoriales inmediatos del presente.

#### Pasos para practicarlo:
1. **5 Cosas que puedes VER**: Mira a tu alrededor y fíjate en detalles pequeños: una grieta en la pared, el reflejo de la luz, el color de una sombra.
2. **4 Cosas que puedes TOCAR**: Siente la textura de tu ropa, la firmeza de la silla, la temperatura de tus manos, la textura de la mesa.
3. **3 Cosas que puedes ESCUCHAR**: Presta atención a sonidos lejanos: el tráfico suave, el zumbido del refrigerador, el viento.
4. **2 Cosas que puedes OLER**: Respira hondo e identifica aromas: el café, el jabón en tus manos, el aire fresco.
5. **1 Cosa que puedes SABOREAR**: Concéntrate en el sabor residual en tu boca o toma un sorbo de agua fresca.

*Respira profundo. Estás a salvo en el presente.*",
                'is_featured' => true,
                'order_index' => 1,
            ],
            [
                'title' => 'Carta a tu yo de hace 5 años',
                'slug' => 'carta-yo-cinco-anos',
                'category' => 'ejercicio',
                'color_theme' => 'sky',
                'estimated_time' => '10 min',
                'summary' => 'Escríbele sin filtros a tu versión del pasado. Cuéntale las batallas que ganaste, lo que aprendiste y la compasión que mereces.',
                'content' => "### El poder de la narrativa autocompasiva
Mirar hacia atrás con ojos de compasión nos permite integrar vivencias pasadas y reconocer nuestra propia resiliencia acumulada.

#### Guía para escribir tu carta:
1. **Elige un momento específico**: Recuerda dónde estabas hace 5 años y qué te preocupaba.
2. **Reconoce su vulnerabilidad**: Dile lo que no sabía en ese momento y que todo lo que hizo fue con las herramientas que tenía.
3. **Cuéntale qué tormentas superaste**: Detalla los obstáculos que creías imposibles y cómo lograste salir adelante.
4. **Ofrécele perdón y ternura**: Libera culpas del pasado.
5. **Cierra con gratitud**: Agradece a esa versión por no haberse rendido.",
                'is_featured' => true,
                'order_index' => 2,
            ],
            [
                'title' => '¿Estás descansando o solo evitando?',
                'slug' => 'descansando-o-evitando',
                'category' => 'reflexion',
                'color_theme' => 'lav',
                'estimated_time' => '3 min',
                'summary' => 'El descanso genuino restaura y recarga energía. La evasión aplaza los problemas y genera culpa. Aprende a distinguir entre ambos.',
                'content' => "### La diferencia entre descanso y evasión emocional
A menudo confundimos 'hacer scroll en redes sociales por 2 horas' con descansar.

| Descanso Reparador | Evasión Emocional |
| :--- | :--- |
| Se elige de forma consciente | Ocurre por impulso automático |
| Reduce los niveles de cortisol | Deja una sensación de culpa o vacío |
| Al terminar tienes más claridad | Al terminar el problema sigue ahí y más pesado |

#### Pregunta de reflexión diaria:
*¿Esta actividad me está devolviendo la paz o solo está posponiendo lo que necesito sentir o resolver?*",
                'is_featured' => true,
                'order_index' => 3,
            ],
            [
                'title' => 'Registro emocional en 3 líneas',
                'slug' => 'registro-emocional-3-lineas',
                'category' => 'herramienta',
                'color_theme' => 'terra',
                'estimated_time' => '1 min',
                'summary' => 'Al final de tu jornada: (1) Lo que sentí, (2) Lo que lo provocó, (3) Lo que necesitaba. Tres líneas para forjar autoconocimiento real.',
                'content' => "### Micro-diario de autorregulación
No necesitas escribir páginas enteras para beneficiarte del journaling terapéutico.

#### La fórmula de 3 líneas:
- **Línea 1 (Emoción)**: *Hoy sentí frustración e impotencia hacia las 3:00 PM.*
- **Línea 2 (Detonante)**: *Cuando mi jefe cambió los requerimientos a última hora.*
- **Línea 3 (Necesidad no satisfecha)**: *Necesitaba validación de mi tiempo y poner un límite asertivo.*

Al identificar la necesidad detrás de la emoción, dejamos de culparnos y pasamos a la acción constructiva.",
                'is_featured' => true,
                'order_index' => 4,
            ],
            [
                'title' => 'Respiración diafragmática en 2 minutos (4-7-8)',
                'slug' => 'respiracion-diafragmatica-478',
                'category' => 'tip',
                'color_theme' => 'sage',
                'estimated_time' => '2 min',
                'summary' => 'Inhala en 4 segundos, sostén 7 segundos y exhala suavemente en 8 segundos. Activa el nervio vago y estabiliza el ritmo cardíaco.',
                'content' => "### Fisiología de la respiración 4-7-8
Desarrollada por el Dr. Andrew Weil, la respiración 4-7-8 funciona como un tranquilizante natural para el sistema nervioso simpático.

#### Instrucciones:
1. Coloca una mano sobre tu pecho y otra en tu abdomen.
2. Inhala por la nariz en **4 segundos**, asegurándote de que solo suba el abdomen.
3. Sostén el aire con calma durante **7 segundos**.
4. Exhala completamente por la boca con un suave sonido de suspiro durante **8 segundos**.
5. Repite este ciclo de 4 a 6 veces.",
                'is_featured' => true,
                'order_index' => 5,
            ],
            [
                'title' => 'Técnica STOP para momentos de desbordamiento (DBT)',
                'slug' => 'tecnica-stop-dbt',
                'category' => 'herramienta',
                'color_theme' => 'dark',
                'estimated_time' => '1 min',
                'summary' => 'Stop (Para), Take a breath (Respira), Observe (Observa), Proceed (Procede con sabiduría). Cuatro pasos para no reaccionar impulsivamente.',
                'content' => "### Tolerancia al malestar con la técnica STOP
Cuando las emociones alcanzan un nivel 8 o 9 de intensidad, nuestra capacidad de razonar disminuye.

- **S - Stop (Detente)**: Congélate. No hables, no envíes ese mensaje, no tomes una decisión ahora.
- **T - Take a breath (Toma un respiro)**: Haz una respiración profunda sintiendo cómo entra el oxígeno.
- **O - Observe (Observa)**: ¿Qué está pasando adentro de ti? ¿Qué pensamientos surgen? ¿Qué sensaciones corporales sientes?
- **P - Proceed effectively (Procede eficazmente)**: Pregúntate: *¿Qué acción mejorará esta situación en lugar de empeorarla?*",
                'is_featured' => true,
                'order_index' => 6,
            ],
            [
                'title' => 'Tres cosas por las que hoy estar agradecido',
                'slug' => 'tres-cosas-agradecido',
                'category' => 'reflexion',
                'color_theme' => 'amber',
                'estimated_time' => '3 min',
                'summary' => 'La gratitud no niega el dolor; le hace espacio a lo que también está funcionando bien. Practica nombrar lo micro y cotidiano.',
                'content' => "### La ciencia de la gratitud neuronal
El cerebro humano tiene un sesgo de negatividad evolutivo: presta 5 veces más atención a las amenazas que a lo positivo. Practicar gratitud deliberada equilibra los neurotransmisores de serotonina y dopamina.

#### Ejemplos de detalles pequeños:
- El aroma del café por la mañana.
- Una almohada limpia y fresca.
- Un mensaje de buenos días de alguien que te quiere.
- La sensación de quitarte los zapatos al llegar a casa.",
                'is_featured' => false,
                'order_index' => 7,
            ],
            [
                'title' => 'Plan de seguridad digital personal',
                'slug' => 'plan-seguridad-personal',
                'category' => 'ejercicio',
                'color_theme' => 'sky',
                'estimated_time' => '15 min',
                'summary' => 'Construye un documento vivo con tus señales tempranas de alerta, tus estrategias de calma y tus contactos de emergencia.',
                'content' => "### ¿Por qué todos deberíamos tener un Plan de Seguridad?
En momentos de crisis emocional severa, el cerebro no puede planificar con lucidez. Tener un plan previamente redactado disminuye drásticamente el tiempo de respuesta y previene conductas de riesgo.

Puedes acceder y personalizar tu plan directamente en la sección **Plan de Seguridad** de tu panel de usuario.",
                'is_featured' => false,
                'order_index' => 8,
            ],
        ];

        foreach ($resources as $res) {
            $createdRes = Resource::create($res);
            // Add some favorites for demo user
            if (in_array($res['slug'], ['regla-54321-ansiedad', 'respiracion-diafragmatica-478', 'tecnica-stop-dbt'])) {
                UserResourceFavorite::create([
                    'user_id' => $demoUser->id,
                    'resource_id' => $createdRes->id,
                    'is_completed' => true,
                    'completed_at' => Carbon::now()->subDays(2),
                    'personal_note' => 'Me ayudó mucho en el momento de tensión del martes.',
                ]);
            }
        }

        // 5. Seed Magazine Articles
        $articles = [
            [
                'title' => '¿Qué es la Terapia Dialéctico Conductual (DBT) y cómo ayuda en el día a día?',
                'slug' => 'que-es-dbt-bienestar-diario',
                'author_name' => 'Dra. Elena Vázquez',
                'author_role' => 'Especialista en Psicología Clínica',
                'category' => 'DBT y Regulación',
                'read_time' => '5 min',
                'color_tag' => 'sage',
                'excerpt' => 'Descubre cómo los cuatro pilares de DBT —mindfulness, tolerancia al malestar, regulación emocional y efectividad interpersonal— transforman nuestra respuesta al estrés.',
                'content' => "La Terapia Dialéctico Conductual (DBT), desarrollada por la Dra. Marsha Linehan, nació originalmente para tratar la desregulación emocional severa, pero hoy sus herramientas han demostrado ser extraordinariamente útiles para cualquier persona que experimente estrés, ansiedad o relaciones interpersonales complejas.

### Los cuatro pilares fundamentales:
1. **Mindfulness (Atención Plena)**: La capacidad de estar presente en el momento sin juzgar.
2. **Tolerancia al Malestar**: Estrategias para sobrevivir a momentos de crisis sin empeorar la situación (técnicas como TIPP y STOP).
3. **Regulación Emocional**: Identificar, comprender y modular la intensidad de las emociones sin reprimirlas.
4. **Efectividad Interpersonal**: Aprender a pedir lo que necesitamos y decir 'no' mientras preservamos el respeto propio y las relaciones con los demás.

Aprender estas habilidades es como entrenar un músculo: entre más las practiques en momentos de calma, más naturales serán en momentos de tormenta.",
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'La neurociencia del Grounding: cómo tu cuerpo apaga la alarma de ansiedad',
                'slug' => 'neurociencia-del-grounding-ansiedad',
                'author_name' => 'Lic. Roberto Valdés',
                'author_role' => 'Neuropsicólogo',
                'category' => 'Ansiedad y Ciencia',
                'read_time' => '4 min',
                'color_tag' => 'sky',
                'excerpt' => 'Cuando la mente entra en pánico, el cuerpo debe ser el ancla. Conoce el circuito biológico que conecta tus 5 sentidos con el nervio vago.',
                'content' => "Cuando experimentamos un ataque de pánico o un episodio agudo de ansiedad, nuestro sistema nervioso autónomo activa la rama simpática: ritmo cardíaco acelerado, respiración superficial y pensamientos catastróficos.

El cerebro primitivo interpreta que estamos en peligro de muerte frente a un depredador, aunque solo estemos frente a una pantalla de computadora.

### ¿Por qué los sentidos son la llave maestra?
No podemos 'pensar' para salir de un ataque de pánico mediante la lógica pura, porque la corteza prefrontal está temporalmente inhibida por la adrenalina. Sin embargo, los receptores sensoriales (la piel, la vista, el oído) envían señales directas al tronco encefálico.

Al tocar una superficie fría, nombrar 5 colores distintos o escuchar el sonido de un reloj, le enviamos un mensaje biológico inequívoco al cerebro: **'Aquí y ahora, no hay peligro inminente'**.",
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(8),
            ],
            [
                'title' => 'Cómo acompañar a alguien que está atravesando una crisis emocional',
                'slug' => 'como-acompanar-crisis-emocional',
                'author_name' => 'Equipo A tu lado',
                'author_role' => 'Primeros Auxilios Psicológicos',
                'category' => 'Apoyo y Empatía',
                'read_time' => '6 min',
                'color_tag' => 'terra',
                'excerpt' => 'Qué decir, qué evitar y cómo brindar presencia compasiva sin intentar "arreglar" inmediatamente lo que la otra persona siente.',
                'content' => "Ver sufrir a alguien que queremos genera un impulso natural de querer arreglar la situación de inmediato. Sin embargo, frases bienintencionadas como *'todo va a estar bien'*, *'no te pongas así'* o *'hay personas que la pasan peor'* suelen provocar mayor aislamiento y culpa.

### Lo que sí ayuda:
- **Estar presente en silencio**: A veces, sentarse al lado de la persona sin exigirle explicaciones es el mayor acto de amor.
- **Validar su dolor**: *'Entiendo que esto es muy pesado para ti ahora mismo, y está bien sentirse así.'*
- **Ofrecer ayuda concreta**: En lugar de *'cualquier cosa me avisas'*, ofrece: *'¿Quieres que te prepare un té o que te acompañe a dar una vuelta de 5 minutos?'*
- **Tener a la mano las líneas de crisis**: Si notas señales de riesgo, acompáñale con amabilidad a llamar al 800 290 0024.",
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(12),
            ],
            [
                'title' => 'Productividad tóxica vs. Descanso genuino: sanando la culpa de no hacer nada',
                'slug' => 'productividad-toxica-descanso-genuino',
                'author_name' => 'Mtra. Sofía Camargo',
                'author_role' => 'Psicóloga Organizacional',
                'category' => 'Hábitos y Estilo de Vida',
                'read_time' => '4 min',
                'color_tag' => 'lav',
                'excerpt' => '¿Sientes inquietud o culpa cuando intentas tomar una siesta o un fin de semana libre? Identifica cómo desmantelar la necesidad constante de rendimiento.',
                'content' => "Vivimos en una cultura hiperconectada que premia el agotamiento como si fuera una medalla de honor. Muchas personas sienten taquicardia o ansiedad cuando intentan relajarse, un fenómeno conocido como *ansiedad por inactividad*.

### El descanso no es un premio que te ganas: es un requisito biológico
Así como un teléfono no puede funcionar eternamente sin recargar su batería, el sistema nervioso requiere ciclos de reparación celular y reposo mental.

Aprender a descansar sin culpa es un acto de resistencia y salud preventiva.",
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(16),
            ],
        ];

        foreach ($articles as $art) {
            Article::create($art);
        }

        // 6. Seed Crisis Lines Directory
        $crisisLines = [
            [
                'country' => 'México',
                'country_code' => 'MX',
                'phone_number' => '800 290 0024',
                'service_name' => 'Línea de la Vida (CONASAMA / SSA)',
                'hours' => '24 horas / 365 días',
                'cost_type' => 'Gratuita y confidencial a nivel nacional',
                'description' => 'Atención profesional en crisis emocional, prevención del suicidio y adicciones en toda la República Mexicana.',
                'whatsapp_or_chat_url' => 'https://www.gob.mx/salud/conasama',
                'is_featured' => true,
                'order_index' => 1,
            ],
            [
                'country' => 'México (SAPTEL)',
                'country_code' => 'MX',
                'phone_number' => '55 5259 8121',
                'service_name' => 'SAPTEL Cruz Roja Mexicana',
                'hours' => '24 horas',
                'cost_type' => 'Gratuito',
                'description' => 'Sistema de apoyo psicológico telefónico atendido por psicólogos certificados.',
                'is_featured' => true,
                'order_index' => 2,
            ],
            [
                'country' => 'Argentina',
                'country_code' => 'AR',
                'phone_number' => '(011) 5275-1135',
                'service_name' => 'Centro de Asistencia al Suicida (CAS)',
                'hours' => '24 horas',
                'cost_type' => 'Línea gratuita nacional: 135',
                'description' => 'Atención telefónica gratuita a personas en crisis y prevención del suicidio en toda Argentina.',
                'is_featured' => true,
                'order_index' => 3,
            ],
            [
                'country' => 'España',
                'country_code' => 'ES',
                'phone_number' => '024',
                'service_name' => 'Línea 024 — Atención a la conducta suicida',
                'hours' => '24 horas / 365 días',
                'cost_type' => 'Gratuita, accesible y confidencial',
                'description' => 'Servicio público promovido por el Ministerio de Sanidad de España para soporte emocional inmediato.',
                'is_featured' => true,
                'order_index' => 4,
            ],
            [
                'country' => 'Colombia',
                'country_code' => 'CO',
                'phone_number' => '106',
                'service_name' => 'Línea 106 Bogotá / Línea 192 Nacional',
                'hours' => '24 horas',
                'cost_type' => 'Gratuita',
                'description' => 'Canal de escucha y orientación psicológica para jóvenes, adultos y familias.',
                'is_featured' => true,
                'order_index' => 5,
            ],
            [
                'country' => 'Chile',
                'country_code' => 'CL',
                'phone_number' => '*4141',
                'service_name' => 'Línea *4141 No Estás Solo (MINSAL)',
                'hours' => '24 horas',
                'cost_type' => 'Gratuita desde teléfonos móviles',
                'description' => 'Línea ministerial para atención en crisis y prevención del suicidio en Chile.',
                'is_featured' => true,
                'order_index' => 6,
            ],
            [
                'country' => 'Estados Unidos',
                'country_code' => 'US',
                'phone_number' => '988',
                'service_name' => '988 Suicide & Crisis Lifeline (Español)',
                'hours' => '24/7 en español e inglés',
                'cost_type' => 'Gratuita y confidencial',
                'description' => 'Llama o envía un mensaje de texto al 988 para conectarte con profesionales capacitados.',
                'is_featured' => false,
                'order_index' => 7,
            ],
        ];

        foreach ($crisisLines as $line) {
            CrisisLine::create($line);
        }
    }
}
