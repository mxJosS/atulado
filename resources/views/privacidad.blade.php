<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aviso de Privacidad — A tu lado</title>
<meta name="description" content="Aviso de privacidad integral de A tu lado: qué datos recabamos, para qué los usamos, con quién los compartimos y cómo ejercer tus derechos ARCO.">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url('/privacidad') }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="A tu lado">
<meta property="og:title" content="Aviso de Privacidad — A tu lado">
<meta property="og:description" content="Aviso de privacidad integral de A tu lado: qué datos recabamos, para qué los usamos, con quién los compartimos y cómo ejercer tus derechos ARCO.">
<meta property="og:url" content="{{ url('/privacidad') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600..700;1,9..144,400&family=IBM+Plex+Mono:wght@400;500;600&family=Instrument+Serif:ital@0;1&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg-primary:#080C0A; --bg-canvas:#F8FAF9; --bg-surface:#FFFFFF; --bg-subtle:#EEF4F0;
  --border-light:#DCE8E0; --border-subtle:#C2D6CA;
  --sage-base:#2E5D4B; --sage-medium:#3D7A5F; --mint-accent:#A8E6C0; --sage-pale:#D4EDE2;
  --text-pale-mint:#C8DDD1; --text-light-gray:#8EADA4; --text-medium-gray:#556860; --text-near-black:#1A2620;
  --font-display:'Fraunces',Georgia,serif; --font-editorial:'Instrument Serif',Georgia,serif;
  --font-body:'Manrope',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
  --font-mono:'IBM Plex Mono',ui-monospace,monospace;
  --radius-sm:10px; --radius-md:16px; --radius-full:9999px;
  --transition-fast:.18s cubic-bezier(.2,.8,.2,1);
}
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{
  margin:0; min-height:100vh; display:flex; flex-direction:column;
  background:var(--bg-canvas); color:var(--text-near-black);
  font-family:var(--font-body); font-size:16px; line-height:1.6;
  -webkit-font-smoothing:antialiased;
}
a{color:inherit}
em,.editorial-italic{font-family:var(--font-editorial);font-style:italic;font-weight:400;letter-spacing:0}

/* ── Navbar ── */
.site-navbar{
  position:sticky; top:0; z-index:100; background:#080C0A;
  border-bottom:1px solid rgba(255,255,255,.08); padding:.85rem 1.5rem;
}
.nav-container{
  max-width:1200px; margin:0 auto; display:flex; align-items:center;
  justify-content:space-between; gap:1.5rem;
}
.nav-brand{
  display:flex; align-items:center; gap:.65rem; font-family:var(--font-display);
  font-size:1.35rem; font-weight:700; color:#fff; white-space:nowrap;
  flex-shrink:0; text-decoration:none;
}
.nav-menu{display:flex; align-items:center; gap:.15rem; list-style:none; margin:0; padding:0; flex-wrap:wrap}
.nav-link{
  font-size:.86rem; font-weight:500; color:#C8DDD1; padding:.45rem .85rem;
  border-radius:var(--radius-sm); transition:all var(--transition-fast);
  white-space:nowrap; text-decoration:none; display:inline-block;
}
.nav-link:hover{color:#fff; background:rgba(255,255,255,.08)}
.nav-actions{display:flex; align-items:center; gap:.75rem; flex-shrink:0}
.nav-crisis-pill{
  display:inline-flex; align-items:center; gap:7px; padding:.42rem .9rem;
  border-radius:var(--radius-full); background:rgba(169,50,38,.18); color:#FFA59C;
  font-family:var(--font-mono); font-size:.7rem; font-weight:600; letter-spacing:.05em;
  text-decoration:none; white-space:nowrap; transition:all var(--transition-fast);
}
.nav-crisis-pill:hover{background:rgba(169,50,38,.32); color:#fff}
.nav-btn-access{
  display:inline-flex; align-items:center; padding:.45rem 1rem; border-radius:var(--radius-sm);
  background:var(--mint-accent); color:#14382A; font-size:.84rem; font-weight:700;
  text-decoration:none; white-space:nowrap; transition:all var(--transition-fast);
}
.nav-btn-access:hover{background:#8FD8AC}
@media(max-width:1080px){.nav-menu{display:none}}
@media(max-width:560px){
  .site-navbar{padding:.75rem 1rem}
  .nav-brand{font-size:1.15rem}
  .nav-crisis-pill span{display:none}
}

/* ── Contenido ── */
main{flex:1}
.container-narrow{width:100%; max-width:860px; margin:0 auto; padding:0 clamp(1.25rem,3.5vw,2.5rem)}
.pagina{padding-top:3.2rem; padding-bottom:5rem}
.volver{
  display:inline-block; font-family:var(--font-mono); font-size:.78rem;
  color:var(--sage-base); text-decoration:underline; margin-bottom:1.2rem;
}
h1.titulo{
  font-family:var(--font-display); font-size:clamp(1.7rem,4.5vw,2.1rem); font-weight:600;
  letter-spacing:-.03em; margin:0 0 .5rem; line-height:1.15;
}
p.entrada{color:var(--text-medium-gray); font-size:1rem; margin:0}
.meta{
  font-family:var(--font-mono); font-size:.78rem; color:var(--text-medium-gray);
  border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light);
  padding:.8rem 0; margin:1.6rem 0 0; display:flex; gap:.5rem 1.2rem; flex-wrap:wrap;
}
.legal{max-width:78ch}
.legal h2{
  font-family:var(--font-display); font-size:1.28rem; font-weight:600;
  letter-spacing:-.02em; margin:2.6rem 0 .8rem; scroll-margin-top:90px; line-height:1.3;
}
.legal h3{font-size:1rem; font-weight:700; margin:1.6rem 0 .5rem; color:var(--text-near-black)}
.legal p,.legal li{font-size:.95rem; line-height:1.75; color:#33413B}
.legal ul,.legal ol{padding-left:1.3rem; margin:.6rem 0 1rem}
.legal li{margin-bottom:.45rem}
.legal a{color:var(--sage-base)}
.legal table{
  width:100%; border-collapse:collapse; font-size:.88rem; border:1px solid var(--border-light);
  border-radius:var(--radius-sm); overflow:hidden; margin:1rem 0 1.4rem;
}
.legal th{
  text-align:left; padding:.6rem .8rem; background:var(--bg-subtle);
  border-bottom:1px solid var(--border-subtle); font-family:var(--font-mono);
  font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:var(--text-medium-gray);
}
.legal td{padding:.6rem .8rem; border-bottom:1px solid var(--bg-subtle); vertical-align:top}
.legal tr:last-child td{border-bottom:none}
.legal td .rol{display:block; color:var(--text-medium-gray); font-size:.86em; font-weight:400}
.destacado{
  border-left:3px solid var(--sage-base); background:#F3F8F5;
  padding:.9rem 1.1rem; border-radius:0 var(--radius-sm) var(--radius-sm) 0; margin:1.2rem 0;
}
.destacado.alerta{border-left-color:#C0392B; background:#FCEEEC}
.destacado p{margin:0 0 .6rem}
.destacado p:last-child{margin-bottom:0}
.pend{
  background:#FBF2D4; border:1px dashed #D4AC0D; color:#6B5400;
  padding:0 .35rem; border-radius:4px; font-family:var(--font-mono); font-size:.84em;
}
.indice{
  background:var(--bg-subtle); border:1px solid var(--border-light);
  border-radius:var(--radius-md); padding:1.1rem 1.3rem; margin:2rem 0;
}
.indice ol{columns:2; column-gap:2rem; margin:0; padding-left:1.1rem}
.indice li{font-size:.86rem; margin-bottom:.3rem; break-inside:avoid}
.indice a{color:var(--sage-base); text-decoration:none}
.indice a:hover{text-decoration:underline}
.cruzado{margin-top:2.5rem; font-size:.86rem; color:var(--text-medium-gray)}
@media(max-width:640px){
  .indice ol{columns:1}
  .legal table{font-size:.82rem}
  .legal th,.legal td{padding:.5rem .55rem}
}

/* ── Pie ── */
.site-footer{
  background:#080C0A; color:#fff; border-top:1px solid rgba(255,255,255,.08);
  padding:4.5rem clamp(1rem,3vw,2rem) 2.5rem; margin-top:auto;
}
.footer-grid{
  max-width:1200px; margin:0 auto 3.5rem; display:grid;
  grid-template-columns:2fr repeat(3,1fr); gap:3rem;
}
.footer-brand{
  display:flex; align-items:center; gap:.65rem; font-family:var(--font-display);
  font-size:1.35rem; font-weight:700; color:#fff; margin-bottom:.85rem;
}
.footer-desc{color:#8EADA4; font-size:.88rem; max-width:320px; line-height:1.7; margin-bottom:1.25rem}
.footer-crisis{
  display:inline-flex; align-items:center; gap:8px; padding:.5rem 1rem;
  border-radius:var(--radius-full); background:rgba(169,50,38,.12); color:#FFA59C;
  font-family:var(--font-mono); font-size:.72rem; font-weight:600; letter-spacing:.06em;
  text-decoration:none;
}
.footer-col-title{
  font-family:var(--font-mono); font-size:.74rem; letter-spacing:.12em; text-transform:uppercase;
  color:var(--mint-accent); margin:0 0 1.25rem; font-weight:600;
}
.footer-links{list-style:none; display:flex; flex-direction:column; gap:.75rem; margin:0; padding:0}
.footer-links a{
  color:#8EADA4; font-size:.88rem; transition:all var(--transition-fast);
  display:inline-flex; align-items:center; gap:.4rem; text-decoration:none;
}
.footer-links a:hover{color:#fff; transform:translateX(3px)}
.footer-links .ch{color:var(--mint-accent); font-size:.9rem; line-height:1}
.footer-bottom{
  max-width:1200px; margin:0 auto; padding-top:2rem;
  border-top:1px solid rgba(255,255,255,.08); color:#8EADA4; font-size:.82rem;
  display:flex; flex-direction:column; align-items:center; gap:.75rem; text-align:center;
}
.footer-bottom p{margin:0}
.footer-legales{display:flex; gap:1.1rem; flex-wrap:wrap; justify-content:center}
.footer-legales a{color:var(--mint-accent); text-decoration:none; font-size:.82rem}
.footer-legales a:hover{text-decoration:underline}
.footer-legales .sep{opacity:.35}
.footer-nota{font-size:.76rem; opacity:.75; max-width:620px}
@media(max-width:900px){.footer-grid{grid-template-columns:1fr 1fr; gap:2.5rem}}
@media(max-width:560px){.footer-grid{grid-template-columns:1fr; gap:2rem}.site-footer{padding-top:3rem}}

@media print{
  .site-navbar,.site-footer,.volver{display:none}
  body{background:#fff}
  .pagina{padding-top:0}
  .legal table,.destacado{break-inside:avoid}
}
</style>
</head>
<body>

<nav class="site-navbar">
  <div class="nav-container">
    <a href="{{ route('home') }}" class="nav-brand"><svg viewBox="0 0 16 16" width="28" height="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/><rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/><rect x="2" y="4" width="12" height="2" fill="#5AB56E"/><rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/><rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/><rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/><rect x="4" y="1" width="1" height="1" fill="#C0392B"/><rect x="11" y="3" width="1" height="1" fill="#C0392B"/><rect x="9" y="7" width="1" height="1" fill="#C0392B"/></svg><span>a tu <em class="editorial-italic" style="color:#A8E6C0">lado</em></span></a>
    <ul class="nav-menu">
      <li><a href="{{ route('home') }}" class="nav-link">Inicio</a></li>
      <li><a href="{{ route('sientes') }}" class="nav-link">&iquest;C&oacute;mo te sientes?</a></li>
      <li><a href="{{ route('tools.respiracion') }}" class="nav-link">Respira Conmigo</a></li>
      <li><a href="{{ route('recursos.index') }}" class="nav-link">Recursos</a></li>
      <li><a href="{{ route('revista.index') }}" class="nav-link">Revista</a></li>
      <li><a href="{{ route('crisis') }}" class="nav-link">L&iacute;neas de Ayuda</a></li>
    </ul>
    <div class="nav-actions">
      <a href="tel:8002900024" class="nav-crisis-pill" title="Llamar a la l&iacute;nea de crisis 24/7"><svg viewBox="0 0 16 16" width="11" height="11" fill="currentColor" aria-hidden="true"><path d="M3.1 1.4a1.2 1.2 0 0 1 1.7.2l1.3 1.7a1.2 1.2 0 0 1-.1 1.6l-.8.8c.6 1.3 1.8 2.5 3.1 3.1l.8-.8a1.2 1.2 0 0 1 1.6-.1l1.7 1.3a1.2 1.2 0 0 1 .2 1.7l-.8 1c-.5.6-1.3.8-2 .6C6.5 11.3 4.7 9.5 3.4 6.2c-.3-.8 0-1.6.6-2.1l-.9-2.7z"/></svg><span>CRISIS: 800 290 0024</span></a>
      @auth
        <a href="{{ route('dashboard') }}" class="nav-btn-access">Mi Espacio</a>
      @else
        <a href="{{ route('login') }}" class="nav-btn-access">Acceder</a>
      @endauth
    </div>
  </div>
</nav>

<main>
  <div class="container-narrow pagina">
    <a href="{{ route('home') }}" class="volver">&larr; Inicio</a>
    <h1 class="titulo">Aviso de Privacidad</h1>
    <p class="entrada">Aviso de privacidad integral de <strong>A tu lado</strong>, conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares y su Reglamento.</p>
    <div class="meta">
      <span>Última actualización: 15 de septiembre de 2026</span>
      <span>·</span><span>Versión 1.0</span>
      <span>·</span><span>Vigente desde su publicación</span>
    </div>
    <div class="legal">

      <div class="indice">
        <ol>
          <li><a href="#responsable">Quién es responsable de tus datos</a></li>
          <li><a href="#datos">Qué datos recabamos</a></li>
          <li><a href="#google">Datos que obtenemos de Google</a></li>
          <li><a href="#finalidades">Para qué usamos tus datos</a></li>
          <li><a href="#uso-limitado">Uso limitado de los datos de Google</a></li>
          <li><a href="#institucion">Si usas A tu lado por tu empresa o escuela</a></li>
          <li><a href="#transferencias">Con quién compartimos información</a></li>
          <li><a href="#conservacion">Cuánto tiempo conservamos tus datos</a></li>
          <li><a href="#seguridad">Cómo protegemos tu información</a></li>
          <li><a href="#arco">Tus derechos ARCO</a></li>
          <li><a href="#revocacion">Revocar tu consentimiento</a></li>
          <li><a href="#cookies">Cookies y tecnologías similares</a></li>
          <li><a href="#menores">Menores de edad</a></li>
          <li><a href="#cambios">Cambios a este aviso</a></li>
          <li><a href="#contacto">Contacto</a></li>
        </ol>
      </div>

      <div class="destacado alerta">
        <p><strong>Antes de todo lo legal, lo importante:</strong> A tu lado es un servicio de
        acompañamiento emocional. <strong>No emite diagnósticos, no da tratamiento y no sustituye la
        atención de un profesional de la salud.</strong> Si estás en una situación de emergencia, llama
        a la <strong>Línea de la Vida al 800 911 2000</strong> o al <strong>911</strong>.</p>
      </div>

      <h2 id="responsable">1 · Quién es responsable de tus datos</h2>
      <p><span class="pend">A TU LADO</span>, en adelante «A tu lado»,
      con domicilio en <span class="pend">v. Tecnológico km. 4.5 S/N, Colonia Plan de Ayala, C.P. 97118, Mérida, Yucatán.</span>, Mérida, Yucatán, México,
      es responsable del tratamiento de tus datos personales y del resguardo de este aviso de privacidad.</p>
      <p>Puedes contactarnos en cualquier momento en
      <a href="mailto:privacidad@atulado.com.mx">privacidad@atulado.com.mx</a> o a través de
      <a href="{{ route('home') }}">atulado.com.mx</a>.</p>

      <h2 id="datos">2 · Qué datos recabamos</h2>

      <h3>Datos de identificación y contacto</h3>
      <p>Nombre, correo electrónico, fotografía de perfil si decides ponerla, y los contactos de
      emergencia que tú mismo registres (nombre, teléfono y parentesco de esa persona).</p>

      <h3>Datos personales sensibles</h3>
      <p>La naturaleza del servicio implica que registres información sobre tu estado emocional: tu
      registro diario de ánimo, el texto que escribas en él, tus respuestas a los cuestionarios de la
      plataforma y tu plan de seguridad personal. La ley clasifica esta información como
      <strong>datos personales sensibles</strong>.</p>
      <div class="destacado">
        <p><strong>Consentimiento expreso.</strong> Al crear tu cuenta y marcar la casilla
        correspondiente otorgas tu consentimiento expreso para que A tu lado trate estos datos sensibles
        con las finalidades que se describen en este aviso. Sin ese consentimiento no podemos prestarte
        el servicio, porque el servicio consiste precisamente en acompañarte a partir de lo que tú
        registras.</p>
      </div>

      <h3>Datos de uso de la plataforma</h3>
      <p>Fecha y hora de tus sesiones, qué secciones de la aplicación usas, desde qué tipo de
      dispositivo y tu dirección IP. Esta información nos sirve para operar el servicio, detectar fallas
      y protegerlo de accesos indebidos.</p>

      <h3>Datos que <em>no</em> te pedimos</h3>
      <p>A tu lado <strong>no solicita ni almacena</strong> tu CURP, tu RFC, tu número de seguridad
      social, tu domicilio particular, datos de tarjetas bancarias ni documentos oficiales de identidad.
      Si alguien te los pide a nombre de A tu lado, no los entregues y repórtalo a
      <a href="mailto:privacidad@atulado.com.mx">privacidad@atulado.com.mx</a>.</p>

      <h2 id="google">3 · Datos que obtenemos de Google</h2>
      <p>A tu lado te permite crear tu cuenta e iniciar sesión con tu cuenta de Google. Si eliges esa
      opción, Google nos comparte únicamente lo siguiente:</p>
      <table>
        <thead><tr><th>Dato</th><th>Para qué lo usamos</th></tr></thead>
        <tbody>
          <tr><td>Tu dirección de correo electrónico</td><td>Identificar tu cuenta, permitirte iniciar sesión y enviarte avisos del servicio.</td></tr>
          <tr><td>Tu nombre</td><td>Personalizar la aplicación y dirigirnos a ti por tu nombre.</td></tr>
          <tr><td>Tu fotografía de perfil</td><td>Mostrarla como avatar dentro de la aplicación. Puedes cambiarla o quitarla cuando quieras.</td></tr>
          <tr><td>Tu identificador único de Google</td><td>Vincular de forma segura tu cuenta de Google con tu cuenta de A tu lado.</td></tr>
        </tbody>
      </table>
      <div class="destacado">
        <p><strong>Lo que no hacemos con tu cuenta de Google.</strong> A tu lado no accede a tu correo,
        ni a tu agenda, ni a tus contactos, ni a tus archivos de Google Drive, ni a ningún otro servicio
        de Google. Nunca conocemos tu contraseña de Google: la autenticación ocurre por completo del lado
        de Google. Puedes revocar el acceso en cualquier momento desde
        <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">la página de
        permisos de tu cuenta de Google</a>.</p>
      </div>

      <h2 id="finalidades">4 · Para qué usamos tus datos</h2>

      <h3>Finalidades necesarias para prestarte el servicio</h3>
      <ul>
        <li>Crear tu cuenta, autenticarte y mantener tu sesión activa.</li>
        <li>Guardar tu registro diario, tus respuestas y tu plan de seguridad, y mostrártelos a ti.</li>
        <li>Darte seguimiento dentro de la aplicación y entregarte información, recursos y herramientas.</li>
        <li>Detectar situaciones que requieran contacto humano y activar el protocolo correspondiente.</li>
        <li>Responderte cuando nos escribas y notificarte cambios importantes del servicio.</li>
        <li>Cumplir con obligaciones legales y atender requerimientos de autoridad competente.</li>
      </ul>

      <h3>Finalidades secundarias</h3>
      <p>Las siguientes no son necesarias para prestarte el servicio y <strong>puedes oponerte a ellas
      sin perder el acceso</strong>, escribiéndonos a
      <a href="mailto:privacidad@atulado.com.mx">privacidad@atulado.com.mx</a>:</p>
      <ul>
        <li>Mejorar la plataforma mediante análisis estadísticos <strong>anonimizados</strong>.</li>
        <li>Fines de investigación académica, siempre con información anonimizada.</li>
        <li>Enviarte contenido informativo de salud emocional por correo.</li>
      </ul>
      <div class="destacado">
        <p><strong>Qué significa «anonimizado» aquí.</strong> Para estos fines usamos una tabla separada
        que guarda únicamente resultados agregados junto a variables de contexto (puesto, sexo, rango de
        edad, escolaridad, antigüedad, tipo de jornada, lugar de origen y tipo de interacción).
        <strong>Esa tabla no contiene tu nombre, tu correo, tu fecha de nacimiento ni ninguna llave que
        permita regresar hasta ti</strong>, ni siquiera desde dentro de A tu lado. No es información
        disociable: es información que nació sin identificador.</p>
      </div>

      <h2 id="uso-limitado">5 · Uso limitado de los datos de Google</h2>
      <p>El uso que A tu lado hace de la información recibida de las API de Google se apega a la
      <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener">Política
      de Datos de Usuario de los Servicios de API de Google</a>, incluidos sus requisitos de uso
      limitado. En concreto:</p>
      <ul>
        <li>Usamos los datos recibidos de Google <strong>únicamente</strong> para ofrecerte y mejorar las
        funciones visibles de la aplicación que dependen de ellos.</li>
        <li><strong>No transferimos</strong> esos datos a terceros, salvo cuando sea necesario para
        prestar el servicio, por obligación legal, por requerimiento de autoridad, o con tu
        consentimiento expreso.</li>
        <li><strong>No usamos</strong> esos datos para publicidad, ni para perfilamiento comercial, ni
        los vendemos bajo ninguna circunstancia.</li>
        <li><strong>Ninguna persona</strong> puede leer esos datos, salvo que tú lo autorices
        expresamente, que sea necesario por motivos de seguridad —por ejemplo, investigar un abuso—,
        para cumplir la ley, o que se trate de información agregada y anonimizada para operar el
        servicio.</li>
      </ul>

      <h2 id="institucion">6 · Si usas A tu lado por tu empresa o escuela</h2>
      <p>Cuando tu organización contrata A tu lado, la relación con tus datos no cambia: siguen siendo
      tuyos y tu organización no tiene acceso a ellos. En concreto:</p>
      <table>
        <thead><tr><th>Quién</th><th>Qué puede ver</th></tr></thead>
        <tbody>
          <tr>
            <td><strong>Tu empresa o escuela</strong><span class="rol">Recursos Humanos, Dirección</span></td>
            <td>Únicamente <strong>porcentajes del total de la organización</strong>. Sin nombres, sin
            conteos de personas, sin desglose por área y sin nada de lo que tú escribas. No pueden saber
            quién registró qué, ni deducirlo por descarte.</td>
          </tr>
          <tr>
            <td><strong>El profesional designado</strong><span class="rol">Con cédula profesional</span></td>
            <td>Tu información individual, <strong>únicamente cuando se activa el protocolo de
            crisis</strong> y para poder contactarte. Es una persona designada en conjunto por tu
            organización y por A tu lado, que firmó un contrato de confidencialidad.</td>
          </tr>
          <tr>
            <td><strong>El equipo acreditado de A tu lado</strong></td>
            <td>Tu información individual, con un motivo declarado que queda asentado en una bitácora que
            no se puede editar ni borrar, para poder determinar la mejor forma de acompañarte.</td>
          </tr>
        </tbody>
      </table>
      <div class="destacado alerta">
        <p><strong>Prohibición expresa.</strong> El contrato con tu organización prohíbe usar cualquier
        información proveniente de A tu lado para evaluaciones de desempeño, decisiones de promoción,
        sanciones, terminación de la relación laboral o escolar, o cualquier acto que te discrimine. Si
        sospechas que esto ocurrió, escríbenos a
        <a href="mailto:privacidad@atulado.com.mx">privacidad@atulado.com.mx</a>.</p>
      </div>
      <p>Si dejas la organización, tu cuenta no se cancela: se desvincula. Tu historial sigue siendo tuyo
      y puedes seguir usando A tu lado por tu cuenta.</p>

      <h2 id="transferencias">7 · Con quién compartimos información</h2>
      <p><strong>A tu lado no vende, renta ni comercializa tus datos personales.</strong> Sólo los
      compartimos en estos casos:</p>
      <table>
        <thead><tr><th>Con quién</th><th>Qué se comparte y por qué</th><th>¿Requiere tu consentimiento?</th></tr></thead>
        <tbody>
          <tr><td>Profesional de la salud designado</td><td>Tu resumen individual, sólo con un protocolo de crisis activo, para poder contactarte.</td><td>Sí, otorgado al aceptar este aviso</td></tr>
          <tr><td>Proveedores de infraestructura</td><td>Alojamiento, base de datos y envío de correo. Actúan como encargados: procesan por cuenta nuestra y no pueden usar tu información para fines propios.</td><td>No lo requiere la ley</td></tr>
          <tr><td>Autoridad competente</td><td>Lo que exija una orden fundada y motivada.</td><td>No lo requiere la ley</td></tr>
        </tbody>
      </table>

      <h2 id="conservacion">8 · Cuánto tiempo conservamos tus datos</h2>
      <table>
        <thead><tr><th>Información</th><th>Conservación</th></tr></thead>
        <tbody>
          <tr><td>Datos de tu cuenta y tu historial</td><td>Mientras tu cuenta exista. Al eliminarla, se borran.</td></tr>
          <tr><td>Registros de eventos de crisis</td><td>5 años, en forma seudonimizada, por trazabilidad.</td></tr>
          <tr><td>Bitácora de accesos</td><td>5 años. Es inmutable por diseño.</td></tr>
          <tr><td>Registros técnicos y de seguridad</td><td>12 meses.</td></tr>
          <tr><td>Información anonimizada</td><td>Indefinida. Ya no te identifica, por lo que no hay nada que eliminar.</td></tr>
        </tbody>
      </table>
      <p>Puedes eliminar tu cuenta y tu información cuando quieras desde tu perfil, o escribiéndonos. La
      eliminación es efectiva en un plazo máximo de 30 días naturales.</p>

      <h2 id="seguridad">9 · Cómo protegemos tu información</h2>
      <ul>
        <li>Todo el tráfico viaja cifrado con HTTPS.</li>
        <li>Las contraseñas se almacenan con funciones de hash irreversibles: nadie en A tu lado puede leerlas.</li>
        <li>El acceso a información individual exige un motivo declarado y queda asentado en una bitácora inmutable.</li>
        <li>La separación entre lo que ve una organización y lo que ve el plano clínico está implementada
        en la capa de datos, no sólo en la pantalla.</li>
        <li>Los accesos del profesional designado caducan de forma automática al vencer su contrato de
        confidencialidad.</li>
      </ul>
      <p>Ningún sistema es infalible. Si detectamos una vulneración que afecte de forma significativa tus
      derechos, te lo informaremos sin demora y te diremos qué hacer.</p>

      <h2 id="arco">10 · Tus derechos ARCO</h2>
      <p>Tienes derecho a <strong>acceder</strong> a tus datos, a <strong>rectificarlos</strong> si son
      inexactos, a <strong>cancelarlos</strong> y a <strong>oponerte</strong> a que se usen para fines
      determinados.</p>
      <p>Para ejercerlos, envía un correo a
      <a href="mailto:privacidad@atulado.com.mx">privacidad@atulado.com.mx</a> con:</p>
      <ol>
        <li>Tu nombre y un correo de contacto para responderte.</li>
        <li>Copia de una identificación oficial, para acreditar que eres tú. La usamos sólo para
        verificar tu identidad y no la conservamos.</li>
        <li>La descripción clara de qué derecho quieres ejercer y sobre qué datos.</li>
      </ol>
      <p>Responderemos en un plazo máximo de <strong>20 días hábiles</strong> y, si procede, lo haremos
      efectivo dentro de los <strong>15 días hábiles</strong> siguientes. El trámite es gratuito.</p>
      <p>Si consideras que tu derecho no fue atendido, puedes acudir al Instituto Nacional de
      Transparencia, Acceso a la Información y Protección de Datos Personales.</p>

      <h2 id="revocacion">11 · Revocar tu consentimiento</h2>
      <p>Puedes revocar en cualquier momento el consentimiento que nos diste para tratar tus datos, con
      el mismo procedimiento del punto anterior. Ten en cuenta que revocar el consentimiento sobre los
      datos sensibles implica dar de baja tu cuenta, porque sin ellos el servicio no puede operar.</p>
      <p>También puedes desvincular tu cuenta de Google en cualquier momento desde
      <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">los permisos de
      tu cuenta de Google</a>, sin que eso elimine tu cuenta de A tu lado.</p>

      <h2 id="cookies">12 · Cookies y tecnologías similares</h2>
      <p>Usamos cookies estrictamente necesarias para mantener tu sesión iniciada y proteger los
      formularios contra falsificación de peticiones. No usamos cookies de publicidad ni vendemos
      información a redes publicitarias. Puedes bloquearlas desde tu navegador, pero entonces no podrás
      iniciar sesión.</p>

      <h2 id="menores">13 · Menores de edad</h2>
      <p>A tu lado está dirigido a personas mayores de edad. Cuando una institución educativa contrata el
      servicio para su comunidad estudiantil, la institución es responsable de recabar el consentimiento
      del padre, madre o tutor de quienes no hayan alcanzado la mayoría de edad, y de acreditarlo ante
      A tu lado. Si detectamos una cuenta creada por un menor sin ese consentimiento, la daremos de baja
      y eliminaremos su información.</p>

      <h2 id="cambios">14 · Cambios a este aviso</h2>
      <p>Podemos actualizar este aviso cuando cambie la normativa, el servicio o nuestras prácticas. La
      versión vigente siempre estará publicada en
      <a href="{{ route('privacidad') }}">atulado.com.mx/privacidad</a> con su fecha de
      actualización. Si el cambio es sustancial, te avisaremos por correo y dentro de la aplicación antes
      de que entre en vigor.</p>

      <h2 id="contacto">15 · Contacto</h2>
      <p>Para cualquier duda sobre este aviso o sobre el tratamiento de tus datos:<br>
      <strong>Correo:</strong> <a href="mailto:privacidad@atulado.com.mx">privacidad@atulado.com.mx</a><br>
      <strong>Sitio:</strong> <a href="{{ route('home') }}">atulado.com.mx</a><br>
      <strong>Domicilio:</strong> <a class="pend">v. Tecnológico km. 4.5 S/N, Colonia Plan de Ayala, C.P. 97118, Mérida, Yucatán.</a>, Mérida, Yucatán, México</p>

      <div class="destacado alerta" style="margin-top:2.5rem">
        <p><strong>Recordatorio.</strong> A tu lado no diagnostica, no da tratamiento y no sustituye la
        atención de un profesional de la salud. Si estás en riesgo, llama a la
        <strong>Línea de la Vida: 800 911 2000</strong> (24 horas) o al <strong>911</strong>.</p>
      </div>

      <p class="cruzado"><a href="{{ route('terminos') }}">Consulta también las Condiciones del Servicio →</a></p>

    </div>
  </div>
</main>

<footer class="site-footer">
  <div class="footer-grid">
    <div>
      <div class="footer-brand"><svg viewBox="0 0 16 16" width="26" height="26" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/><rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/><rect x="2" y="4" width="12" height="2" fill="#5AB56E"/><rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/><rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/><rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/><rect x="4" y="1" width="1" height="1" fill="#C0392B"/><rect x="11" y="3" width="1" height="1" fill="#C0392B"/><rect x="9" y="7" width="1" height="1" fill="#C0392B"/></svg><span>a tu <em class="editorial-italic" style="color:#A8E6C0">lado</em></span></div>
      <p class="footer-desc">Un espacio seguro y confidencial de acompa&ntilde;amiento emocional, herramientas de regulaci&oacute;n y prevenci&oacute;n en crisis para M&eacute;xico y Latinoam&eacute;rica.</p>
      <a href="tel:8002900024" class="footer-crisis"><svg viewBox="0 0 16 16" width="11" height="11" fill="currentColor" aria-hidden="true"><path d="M3.1 1.4a1.2 1.2 0 0 1 1.7.2l1.3 1.7a1.2 1.2 0 0 1-.1 1.6l-.8.8c.6 1.3 1.8 2.5 3.1 3.1l.8-.8a1.2 1.2 0 0 1 1.6-.1l1.7 1.3a1.2 1.2 0 0 1 .2 1.7l-.8 1c-.5.6-1.3.8-2 .6C6.5 11.3 4.7 9.5 3.4 6.2c-.3-.8 0-1.6.6-2.1l-.9-2.7z"/></svg><span>L&iacute;nea 24h: 800 290 0024</span></a>
    </div>
    <div>
      <h4 class="footer-col-title">Explorar</h4>
      <ul class="footer-links">
        <li><a href="{{ route('home') }}"><span class="ch">&rsaquo;</span> Inicio</a></li>
        <li><a href="{{ route('sientes') }}"><span class="ch">&rsaquo;</span> &iquest;C&oacute;mo te sientes hoy?</a></li>
        <li><a href="{{ route('recursos.index') }}"><span class="ch">&rsaquo;</span> Biblioteca de Recursos</a></li>
        <li><a href="{{ route('revista.index') }}"><span class="ch">&rsaquo;</span> Revista de Salud Mental</a></li>
      </ul>
    </div>
    <div>
      <h4 class="footer-col-title">Herramientas</h4>
      <ul class="footer-links">
        <li><a href="{{ route('tools.respiracion') }}"><span class="ch">&rsaquo;</span> Respira Conmigo (4-7-8)</a></li>
        <li><a href="{{ route('tools.grounding') }}"><span class="ch">&rsaquo;</span> Grounding 5-4-3-2-1</a></li>
        <li><a href="{{ route('tools.stop') }}"><span class="ch">&rsaquo;</span> T&eacute;cnica STOP</a></li>
        <li><a href="{{ route('crisis') }}"><span class="ch">&rsaquo;</span> L&iacute;neas de Crisis 24h</a></li>
      </ul>
    </div>
    <div>
      <h4 class="footer-col-title">Legal</h4>
      <ul class="footer-links">
        <li><a href="{{ route('privacidad') }}"><span class="ch">&rsaquo;</span> Aviso de Privacidad</a></li>
        <li><a href="{{ route('terminos') }}"><span class="ch">&rsaquo;</span> Condiciones del Servicio</a></li>
        <li><a href="mailto:privacidad@atulado.com.mx"><span class="ch">&rsaquo;</span> Contacto de privacidad</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; {{ date('Y') }} A tu lado &middot; Plataforma de Bienestar y Salud Mental</p>
    <p class="footer-legales">
      <a href="{{ route('privacidad') }}">Aviso de Privacidad</a><span class="sep">&middot;</span>
      <a href="{{ route('terminos') }}">Condiciones del Servicio</a><span class="sep">&middot;</span>
      <a href="mailto:privacidad@atulado.com.mx">privacidad@atulado.com.mx</a>
    </p>
    <p class="footer-nota">*Este servicio es de acompa&ntilde;amiento emocional: no diagnostica, no da tratamiento y no sustituye la atenci&oacute;n de un profesional de la salud. En una emergencia, comun&iacute;cate de inmediato a la L&iacute;nea de la Vida: 800 911 2000.</p>
  </div>
</footer>

</body>
</html>
