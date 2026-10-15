# Graph Report - atulado  (2026-10-14)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 2857 nodes · 5993 edges · 252 communities (86 shown, 166 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 85 edges (avg confidence: 0.85)
- Token cost: 103,813 input · 3,155 output

## Graph Freshness
- Built from commit: `7ec68257`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Motion Library Minified Core
- User Model
- Registration and Mail Classes
- Iconify Icon Web Component
- Server Monitor Service
- Admin Dashboard and Roster
- Animation Timeline Control
- Test Base and Facades
- Garden Game Engine
- Crisis Events and Clinical Engine
- Authentication Controller
- Frontend Package Dependencies
- Care Queue and Garden Controllers
- Institution Management
- Factories and Schema Blueprints
- Animation Measurement and Keyframes
- Analytics Service Closures
- Mood Logs and Silence Detection
- Clinical Record Service
- Puchol Assessment Cycle
- Clinical Summary Service Closures
- Professional and Clinical Migrations
- Visual Element Animation
- Dashboard and Resource Controllers
- Visual Element Variant Tree
- Account Deletion and Storage
- WHO-5 and ASQ Assessments
- Memberships and Collaborators
- Security and Consent Middleware
- Admin User Management
- Institution Form Request
- Departments Model
- Eloquent Model Files
- Roster Excel Uploads
- Motion Value Internals
- Motion Value Subscriptions
- Config and Platform State Tests
- Motion Render State Helpers
- Admin Controllers
- Assessment Submission Controller
- Safety Plan Controller
- Clinical Audit Log
- Population Analytics Metrics
- Resource and Admin Log Migrations
- Verification and Institution Migrations
- SPA Router and Breathing
- Grouped Animation Playback
- Institution Creation Tests
- Clinical Record Controller
- Platform KPIs and Alerts
- Clinical Summary Builder
- Contact Form
- Article Model
- Atulado Panel Mockups
- Layout Projection Reconciliation
- Panel Mockup Charts
- Minified Motion Helpers
- Platform State Tests
- Institution Invitations
- Join-by-Link Flow
- Private Questions Tests
- Projection Tree Building
- LSP Reflection Helper
- Panel Roles and Roster Tests
- Mood Tracker and Routes
- Professional Verification
- Native Animation Wrapper
- Artisan Console Commands
- Institution Controller Closures
- Article Controller
- Institution Indicators Service
- Institution Data Migration
- Indicators and Analytics Tests
- Invite-by-Link Tests
- Sensitive Data Encryption
- Headless Browser Testing
- Server Restart Tests
- Atulado Report Mock Data
- View Transition Builder
- Account Controller and Logging
- Report Mock Data
- Trusted Device Tests
- Impersonation Tests
- Roster Rows and Emergency Contacts
- App Bootstrap
- Compliance Tests
- Institution Detail Views
- Summary Delivery
- Composer Metadata
- Composer Dev Dependencies
- Composer Scripts
- Text Entrance Animations
- App Service Provider
- MDI Assessment and Surveillance
- Institution Migration Tests
- Password Reset Tests
- Composer Config
- Composer Runtime Requirements
- Scientific Articles Schema Update
- Roster Upload Tables
- Puchol Cycle Applications
- Reading Assignments Adoption Goal
- Daily Mood Log Uniqueness
- Preserve Cases On Deletion
- Institution Invite Link Migration
- Institution Model
- Users Table Migration
- Jobs Table Migration
- Garden Model
- Membership Model
- Composer PSR-4 Autoload
- Logging Configuration
- Cache Table Migration
- User Avatar Migration
- Google ID Migration
- Avatar Text Column Migration
- B2B Organization Fields
- Verification Code Migration
- Clinical Permission Migration
- Clinical Audit Reason
- Crisis Event Context
- Institution Archiving Migration
- Lowercase Emails Migration
- Two-Factor And Consent
- Invite Link Permission
- Admin Layout
- App Layout
- Guest Layout
- Admin Dashboard View
- Email Normalization Middleware
- Mood Logs Table
- Safety Plans Table
- Resource Favorites Table
- Articles Table
- Crisis Lines Table
- Institutions Table
- Memberships Table
- Settings Table
- Interactions Table
- Trusted Devices Table
- Gardens Table
- Example Unit Test
- Institutions Index View
- Artisan CLI Entry
- Dev Test Autoload
- Laravel Package Discovery
- Queue Index View
- Consent Page View
- Restart Script
- Deploy Script
- Backup Script
- Console Scheduling
- Analytics Index View
- Institution Record View
- Registration View
- User Dashboard View
- Profile View
- Safety Plan View
- Safety Plan Print
- Privacy Policy Page
- Feelings Check View
- Terms Page
- Invitation Display View

## God Nodes (most connected - your core abstractions)
1. `User` - 266 edges
2. `Institucion` - 157 edges
3. `Membresia` - 124 edges
4. `EventoCrisis` - 81 edges
5. `TestCase` - 62 edges
6. `ClinicalEngineService` - 46 edges
7. `MoodLog` - 45 edges
8. `Controller` - 42 edges
9. `Departamento` - 35 edges
10. `AuditoriaClinica` - 35 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  database/seeders/NavegadorSeeder.php → app/Models/User.php
- `CambiosPanelInstitucionalTest` --references--> `User`  [EXTRACTED]
  tests/Feature/CambiosPanelInstitucionalTest.php → app/Models/User.php
- `ClinicalEngineTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ClinicalEngineTest.php → app/Models/User.php
- `DispositivoConfiableTest` --references--> `User`  [EXTRACTED]
  tests/Feature/DispositivoConfiableTest.php → app/Models/User.php
- `EstadoPlataformaTest` --references--> `User`  [EXTRACTED]
  tests/Feature/EstadoPlataformaTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (252 total, 166 thin omitted)

### Community 0 - "Motion Library Minified Core"
Cohesion: 0.03
Nodes (66): aa(), Ac(), ao(), ba(), bc(), Bs(), ca(), Ci() (+58 more)

### Community 1 - "User Model"
Cohesion: 0.04
Nodes (7): User, Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Database\Eloquent\Relations\HasOne, AdminDashboardAndUserTest, AppFlowTest, CorreosMinusculasTest, EmailVerificationCodeTest

### Community 2 - "Registration and Mail Classes"
Cohesion: 0.05
Nodes (21): AvisoErrorMail, ContactoCorporativoMail, EmailVerificationCodeMail, InvitacionPadronMail, ResetPasswordMail, ResumenClinicoMail, ResetPasswordNotification, {closure#2}() (+13 more)

### Community 3 - "Iconify Icon Web Component"
Cohesion: 0.06
Nodes (50): a(), At(), attributeChangedCallback(), b(), bt(), _check(), connectedCallback(), constructor() (+42 more)

### Community 4 - "Server Monitor Service"
Cohesion: 0.07
Nodes (4): MonitorServidorService, CambiosPanelInstitucionalTest, InvitacionesTest, MotorYColaTest

### Community 5 - "Admin Dashboard and Roster"
Cohesion: 0.06
Nodes (15): {closure#7}(), PadronController, {closure#1}(), {closure#8}(), PadronExcelService, PhpOffice\PhpSpreadsheet\Cell\Coordinate, PhpOffice\PhpSpreadsheet\Cell\DataType, PhpOffice\PhpSpreadsheet\Cell\DataValidation (+7 more)

### Community 6 - "Animation Timeline Control"
Cohesion: 0.06
Nodes (16): An(), Ce(), De(), Gt(), In(), je, jn(), ke() (+8 more)

### Community 7 - "Test Base and Facades"
Cohesion: 0.07
Nodes (11): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Mail, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Password, AdminUserPasswordTest, ExampleTest (+3 more)

### Community 8 - "Garden Game Engine"
Cohesion: 0.09
Nodes (35): actualizarPildoraNivel(), addPlantXp(), ajustarAlturaArbol(), alturaArbol(), aplicarEstado(), azarConSemilla(), collectSunItem(), createFertilizerFx() (+27 more)

### Community 9 - "Crisis Events and Clinical Engine"
Cohesion: 0.06
Nodes (4): EventoCrisis, ClinicalEngineService, {closure#6}(), ClinicalEngineTest

### Community 10 - "Authentication Controller"
Cohesion: 0.07
Nodes (6): AuthController, {closure#1}(), DispositivoConfiable, Navegador, Illuminate\Support\Facades\Cookie, self

### Community 11 - "Frontend Package Dependencies"
Cohesion: 0.05
Nodes (39): dependencies, iconify-icon, @iconify-icon/react, @iconify/json, @iconify-json/game-icons, rpg-awesome, devDependencies, concurrently (+31 more)

### Community 12 - "Care Queue and Garden Controllers"
Cohesion: 0.07
Nodes (21): ColaAtencionController, JardinController, ProfessionalVerificationController, {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}() (+13 more)

### Community 13 - "Institution Management"
Cohesion: 0.07
Nodes (8): {closure#14}(), InstitucionController, Institucion, BelongsToMany, {closure#4}(), {closure#6}(), EliminacionInstitucionService, Illuminate\Database\Eloquent\SoftDeletes

### Community 14 - "Factories and Schema Blueprints"
Cohesion: 0.06
Nodes (14): {closure#2}(), Blueprint, DepartamentoFactory, static, InstitucionFactory, static, MembresiaFactory, static (+6 more)

### Community 15 - "Animation Measurement and Keyframes"
Cohesion: 0.06
Nodes (11): bn, Ji(), measureInstanceViewportBox(), Tn(), tt(), Vt(), wn(), xi() (+3 more)

### Community 17 - "Mood Logs and Silence Detection"
Cohesion: 0.06
Nodes (10): MoodLog, {closure#10}(), {closure#8}(), {closure#9}(), AdminUserSeeder, DatabaseSeeder, InstitutionSeeder, Illuminate\Database\Seeder (+2 more)

### Community 18 - "Clinical Record Service"
Cohesion: 0.06
Nodes (6): {closure#10}(), {closure#3}(), ExpedienteClinicoService, {closure#9}(), {closure#2}(), Illuminate\Pagination\LengthAwarePaginator

### Community 19 - "Puchol Assessment Cycle"
Cohesion: 0.10
Nodes (5): AplicacionPuchol, {closure#1}(), PucholService, Illuminate\Validation\ValidationException, PucholTest

### Community 21 - "Professional and Clinical Migrations"
Cohesion: 0.08
Nodes (23): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}() (+15 more)

### Community 22 - "Visual Element Animation"
Cohesion: 0.09
Nodes (25): animateVisualElement(), as, be(), e(), eo(), es(), fi(), interpolateProjection() (+17 more)

### Community 23 - "Dashboard and Resource Controllers"
Cohesion: 0.09
Nodes (8): DashboardController, HomeController, ResourceController, ToolController, CrisisLine, Interaccion, Resource, UserResourceFavorite

### Community 24 - "Visual Element Variant Tree"
Cohesion: 0.08
Nodes (13): _a(), di(), getBaseTargetFromProps(), hr(), jr, kr(), mount(), removeValueFromRenderState() (+5 more)

### Community 25 - "Account Deletion and Storage"
Cohesion: 0.07
Nodes (9): {closure#1}(), EliminacionCuentaService, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Storage, Laravel\Socialite\Facades\Socialite, Laravel\Socialite\Two\InvalidStateException, Laravel\Socialite\Two\User, Mockery (+1 more)

### Community 26 - "WHO-5 and ASQ Assessments"
Cohesion: 0.12
Nodes (5): AplicacionAsq, AplicacionWho5, Clasificacion, NavegadorSeeder, ExpedienteClinicoTest

### Community 28 - "Memberships and Collaborators"
Cohesion: 0.11
Nodes (5): ColaboradorController, Membresia, {closure#1}(), {closure#1}(), Illuminate\Database\Eloquent\Builder

### Community 29 - "Security and Consent Middleware"
Cohesion: 0.12
Nodes (10): BloquearEnSuplantacion, CabecerasSeguridad, EnsureConsentimiento, EnsureEmailIsVerified, EnsureUserCanUsePanel, EnsureUserIsAdmin, EnsureUserIsProfessional, Suplantacion (+2 more)

### Community 30 - "Admin User Management"
Cohesion: 0.10
Nodes (6): AdminUserController, {closure#5}(), {closure#6}(), EnlaceController, BitacoraAdmin, Illuminate\Validation\Rule

### Community 31 - "Institution Form Request"
Cohesion: 0.09
Nodes (4): {closure#1}(), GuardarInstitucionRequest, EstructuraInstitucionalService, Illuminate\Validation\Validator

### Community 33 - "Eloquent Model Files"
Cohesion: 0.17
Nodes (3): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 34 - "Roster Excel Uploads"
Cohesion: 0.19
Nodes (4): CargaPadron, Illuminate\Http\UploadedFile, PhpOffice\PhpSpreadsheet\IOFactory, PadronExcelTest

### Community 35 - "Motion Value Internals"
Cohesion: 0.14
Nodes (3): bl(), ii, Rr()

### Community 36 - "Motion Value Subscriptions"
Cohesion: 0.13
Nodes (4): p, pl(), reconcileRemovals(), Ya

### Community 37 - "Config and Platform State Tests"
Cohesion: 0.09
Nodes (3): Illuminate\Support\Carbon, Illuminate\Support\Str, Pdo\Mysql

### Community 38 - "Motion Render State Helpers"
Cohesion: 0.11
Nodes (13): dl(), fr(), ha(), Jl(), mr(), n(), or(), po() (+5 more)

### Community 39 - "Admin Controllers"
Cohesion: 0.11
Nodes (6): AdminDashboardController, AdminForumController, AjusteController, AnaliticaController, Controller, Ajuste

### Community 40 - "Assessment Submission Controller"
Cohesion: 0.12
Nodes (5): AssessmentController, MonitorController, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Process, Illuminate\Support\Facades\Validator

### Community 42 - "Clinical Audit Log"
Cohesion: 0.10
Nodes (5): AuditoriaClinica, {closure#7}(), {closure#8}(), {closure#9}(), RuntimeException

### Community 44 - "Resource and Admin Log Migrations"
Cohesion: 0.10
Nodes (7): {closure#1}(), {closure#1}(), desplazar(), down(), up(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 45 - "Verification and Institution Migrations"
Cohesion: 0.10
Nodes (6): {closure#1}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 46 - "SPA Router and Breathing"
Cohesion: 0.20
Nodes (3): BreathingController, initAllComponents(), SpaRouter

### Community 47 - "Grouped Animation Playback"
Cohesion: 0.15
Nodes (5): Bo(), Hn, nc(), qn(), Vc()

### Community 49 - "Clinical Record Controller"
Cohesion: 0.20
Nodes (4): {closure#1}(), {closure#2}(), ExpedienteController, Illuminate\Support\Facades\URL

### Community 52 - "Contact Form"
Cohesion: 0.13
Nodes (4): ContactoController, EnviarContactoRequest, StoreProfessionalVerificationRequest, Illuminate\Foundation\Http\FormRequest

### Community 54 - "Atulado Panel Mockups"
Cohesion: 0.20
Nodes (13): barChart(), closeModal(), columnChart(), el(), gaugeRing(), lineChart(), NAV_ATL, NAV_INST (+5 more)

### Community 55 - "Layout Projection Reconciliation"
Cohesion: 0.12
Nodes (10): adopt(), collectTargets(), finalize(), getRoot(), Gn(), ll(), reconcileAdditions(), runUpdate() (+2 more)

### Community 56 - "Panel Mockup Charts"
Cohesion: 0.20
Nodes (13): barChart(), closeModal(), columnChart(), el(), gaugeRing(), lineChart(), NAV_ATL, NAV_INST (+5 more)

### Community 58 - "Minified Motion Helpers"
Cohesion: 0.17
Nodes (17): I(), Ae(), c(), Fo(), ge(), he(), Hs(), pn() (+9 more)

### Community 60 - "Institution Invitations"
Cohesion: 0.17
Nodes (4): InvitacionController, InvitacionAceptarController, InvitacionService, Illuminate\Validation\Rules\Password

### Community 63 - "Projection Tree Building"
Cohesion: 0.14
Nodes (13): bi(), br(), build(), ei(), Er(), Ni(), Pi(), Pr() (+5 more)

### Community 64 - "LSP Reflection Helper"
Cohesion: 0.19
Nodes (9): ReflectionClass, ReflectionMethod, ReflectionParameter, ReflectionProperty, {closure#1}(), {closure#2}(), getMethodLine(), getReflectionMethod() (+1 more)

### Community 69 - "Artisan Console Commands"
Cohesion: 0.19
Nodes (5): CrearSuperAdmin, DetectarSilencios, ProbarCorreo, PurgarSeudonimizados, Illuminate\Console\Command

### Community 72 - "Institution Indicators Service"
Cohesion: 0.24
Nodes (4): {closure#14}(), IndicadoresInstitucionService, {closure#28}(), Carbon\Carbon

### Community 73 - "Institution Data Migration"
Cohesion: 0.30
Nodes (11): claveOcupada(), {closure#1}(), crearDepartamentos(), crearInstitucion(), crearMembresias(), departamentoSinAsignar(), insertarDepartamento(), normalizar() (+3 more)

### Community 76 - "Sensitive Data Encryption"
Cohesion: 0.21
Nodes (9): {closure#2}(), {closure#3}(), {closure#5}(), down(), estaCifrado(), transformar(), up(), Illuminate\Contracts\Encryption\DecryptException (+1 more)

### Community 77 - "Headless Browser Testing"
Cohesion: 0.31
Nodes (10): HeadlessChromium\BrowserFactory, HeadlessChromium\Page, Page, abrir(), {closure#2}(), entrar(), erroresJs(), esperar() (+2 more)

### Community 78 - "Server Restart Tests"
Cohesion: 0.20
Nodes (4): Illuminate\Process\PendingProcess, {closure#1}(), {closure#2}(), MonitorReinicioTest

### Community 79 - "Atulado Report Mock Data"
Cohesion: 0.17
Nodes (5): DIAS30, INSTITUCIONES, REPORTES, SEM, SEMANAS

### Community 81 - "Account Controller and Logging"
Cohesion: 0.20
Nodes (4): CuentaController, Illuminate\Database\UniqueConstraintViolationException, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\Log

### Community 82 - "Report Mock Data"
Cohesion: 0.18
Nodes (4): DIAS30, REPORTES, SEM, SEMANAS

### Community 86 - "App Bootstrap"
Cohesion: 0.24
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), Throwable, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware

### Community 88 - "Institution Detail Views"
Cohesion: 0.22
Nodes (8): admin.instituciones.partials.enlace, admin.instituciones.partials.invitaciones, admin.instituciones.partials.padron-personas, admin.instituciones.partials.campos, admin.instituciones.partials.contrato, admin.instituciones.partials.estilos, admin.instituciones.partials.semaforo, admin.partials.barra-semaforo

### Community 90 - "Composer Metadata"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 91 - "Composer Dev Dependencies"
Cohesion: 0.22
Nodes (9): require-dev, chrome-php/chrome, fakerphp/faker, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+1 more)

### Community 92 - "Composer Scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 93 - "Text Entrance Animations"
Cohesion: 0.42
Nodes (8): aparecer(), aparecerPalabras(), encolar(), iniciar(), partirEnPalabras(), prepararAmbiente(), prepararEntradas(), vaciarCola()

### Community 94 - "App Service Provider"
Cohesion: 0.29
Nodes (3): AppServiceProvider, Illuminate\Support\ServiceProvider, Login

### Community 98 - "Composer Config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 99 - "Composer Runtime Requirements"
Cohesion: 0.29
Nodes (7): require, ext-zip, laravel/framework, laravel/socialite, laravel/tinker, php, phpoffice/phpspreadsheet

### Community 101 - "Roster Upload Tables"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 102 - "Puchol Cycle Applications"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 103 - "Reading Assignments Adoption Goal"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 104 - "Daily Mood Log Uniqueness"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 105 - "Preserve Cases On Deletion"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 106 - "Institution Invite Link Migration"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 108 - "Users Table Migration"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 109 - "Jobs Table Migration"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 112 - "Composer PSR-4 Autoload"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 113 - "Logging Configuration"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 127 - "Admin Layout"
Cohesion: 0.40
Nodes (4): partials.iconos, partials.movimiento-cabeza, partials.movimiento-scripts, partials.suplantacion

### Community 128 - "App Layout"
Cohesion: 0.40
Nodes (4): partials.iconos, partials.movimiento-cabeza, partials.movimiento-scripts, partials.suplantacion

### Community 129 - "Guest Layout"
Cohesion: 0.40
Nodes (4): partials.iconos, partials.movimiento-cabeza, partials.movimiento-scripts, partials.suplantacion

### Community 130 - "Admin Dashboard View"
Cohesion: 0.50
Nodes (3): admin.partials.dashboard-listas, admin.instituciones.partials.estilos, admin.partials.barra-semaforo

### Community 144 - "Institutions Index View"
Cohesion: 0.50
Nodes (3): admin.instituciones.partials.campos, admin.instituciones.partials.contrato, admin.instituciones.partials.estilos

### Community 146 - "Dev Test Autoload"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 147 - "Laravel Package Discovery"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **125 isolated node(s):** `iconify-icon`, `@iconify-icon/react`, `@iconify/json`, `@iconify-json/game-icons`, `rpg-awesome` (+120 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 1036 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **166 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Model` to `Registration and Mail Classes`, `Server Monitor Service`, `Admin Dashboard and Roster`, `Test Base and Facades`, `Crisis Events and Clinical Engine`, `Authentication Controller`, `Institution Management`, `Factories and Schema Blueprints`, `Mood Logs and Silence Detection`, `Puchol Assessment Cycle`, `Clinical Summary Service Closures`, `Account Deletion and Storage`, `WHO-5 and ASQ Assessments`, `Platform Service Closures`, `Memberships and Collaborators`, `Security and Consent Middleware`, `Admin User Management`, `Departments Model`, `Eloquent Model Files`, `Roster Excel Uploads`, `Config and Platform State Tests`, `Admin Controllers`, `Safety Plan Controller`, `Clinical Audit Log`, `Institution Creation Tests`, `Platform KPIs and Alerts`, `Clinical Summary Builder`, `Platform State Tests`, `Join-by-Link Flow`, `Private Questions Tests`, `Panel Roles and Roster Tests`, `Professional Verification`, `Artisan Console Commands`, `Indicators and Analytics Tests`, `Invite-by-Link Tests`, `Server Restart Tests`, `Trusted Device Tests`, `Impersonation Tests`, `Roster Rows and Emergency Contacts`, `Compliance Tests`, `Institution Migration Tests`, `Password Reset Tests`?**
  _High betweenness centrality (0.130) - this node is a cross-community bridge._
- **Why does `Membresia` connect `Memberships and Collaborators` to `Registration and Mail Classes`, `Server Monitor Service`, `Admin Dashboard and Roster`, `Test Base and Facades`, `Crisis Events and Clinical Engine`, `Care Queue and Garden Controllers`, `Institution Management`, `Factories and Schema Blueprints`, `Analytics Service Closures`, `Mood Logs and Silence Detection`, `Clinical Record Service`, `Puchol Assessment Cycle`, `Clinical Summary Service Closures`, `Account Deletion and Storage`, `WHO-5 and ASQ Assessments`, `Platform Service Closures`, `Admin User Management`, `Institution Form Request`, `Eloquent Model Files`, `Roster Excel Uploads`, `Config and Platform State Tests`, `Clinical Audit Log`, `Population Analytics Metrics`, `Institution Creation Tests`, `Clinical Record Controller`, `Platform KPIs and Alerts`, `Clinical Summary Builder`, `Institution Indicators Closures`, `Platform State Tests`, `Institution Invitations`, `Panel Roles and Roster Tests`, `Institution Indicators Service`, `Indicators and Analytics Tests`, `Invite-by-Link Tests`, `Impersonation Tests`, `Roster Rows and Emergency Contacts`, `Compliance Tests`, `MDI Assessment and Surveillance`, `Institution Migration Tests`, `Membership Model`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Why does `Institucion` connect `Institution Management` to `User Model`, `Server Monitor Service`, `Admin Dashboard and Roster`, `Test Base and Facades`, `Factories and Schema Blueprints`, `Mood Logs and Silence Detection`, `Clinical Record Service`, `Puchol Assessment Cycle`, `Clinical Summary Service Closures`, `Account Deletion and Storage`, `WHO-5 and ASQ Assessments`, `Platform Service Closures`, `Memberships and Collaborators`, `Admin User Management`, `Institution Form Request`, `Departments Model`, `Eloquent Model Files`, `Roster Excel Uploads`, `Config and Platform State Tests`, `Admin Controllers`, `Institution Creation Tests`, `Clinical Record Controller`, `Platform KPIs and Alerts`, `Clinical Summary Builder`, `Institution Indicators Closures`, `Platform State Tests`, `Institution Invitations`, `Join-by-Link Flow`, `Panel Roles and Roster Tests`, `Institution Controller Closures`, `Institution Indicators Service`, `Indicators and Analytics Tests`, `Invite-by-Link Tests`, `Impersonation Tests`, `Roster Rows and Emergency Contacts`, `Compliance Tests`, `Institution Migration Tests`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **What connects `iconify-icon`, `@iconify-icon/react`, `@iconify/json` to the rest of the system?**
  _125 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Motion Library Minified Core` be split into smaller, more focused modules?**
  _Cohesion score 0.03385952871196522 - nodes in this community are weakly interconnected._
- **Should `User Model` be split into smaller, more focused modules?**
  _Cohesion score 0.03715584068430901 - nodes in this community are weakly interconnected._
- **Should `Registration and Mail Classes` be split into smaller, more focused modules?**
  _Cohesion score 0.0539906103286385 - nodes in this community are weakly interconnected._