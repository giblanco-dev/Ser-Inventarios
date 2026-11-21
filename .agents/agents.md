# SER Inventarios — Contexto del Proyecto para Agentes IA

> **Última actualización:** Mayo 2026  
> **Estado del proyecto:** En desarrollo activo — v1.0 base completa + módulo Enfermería implementado

---

## 1. Resumen Ejecutivo

**SER Inventarios** es un sistema web de gestión de inventarios para la **Clínica de Medicina Alternativa Ser S.C.** Desarrollado en **PHP 8.1 nativo** (sin frameworks) con **MySQL 8.0**, siguiendo un patrón MVC simplificado. El proyecto se encuentra corriendo sobre un servidor **Apache** local en:

```
c:\ApacheServer\htdocs\Ser-Inventarios\
```

URL local: `http://localhost/Ser-Inventarios/public/`

---

## 2. Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.1.25 Nativo (sin framework) |
| Base de datos | MySQL 8.0, motor InnoDB, charset utf8mb4 |
| Frontend | HTML5 + CSS3 Vanilla + JS Vanilla |
| Servidor | Apache con `.htaccess` para reescritura de URLs |
| Acceso BD | PDO + Prepared Statements (Singleton) |

---

## 3. Arquitectura del Proyecto

### 3.1 Estructura de Directorios

```
Ser-Inventarios/
├── .agents/
│   ├── agents.md                  ← Este archivo
│   └── rules/
│       └── buenaspracticas.md     ← Reglas de desarrollo a seguir SIEMPRE
├── Design/
│   ├── EspecificacionSER_Inventarios_v1-0.md   ← Especificación funcional completa v1.0
│   └── implementation_planSubInventario-Enf.md ← Plan de implementación módulo Enfermería
├── LoadsBD/
│   ├── import_insumos.php         ← Script de carga masiva de insumos desde CSV
│   ├── import_medidas.sql
│   ├── insumos.csv                ← Catálogo de insumos inicial
│   └── medidainsumo.csv
├── app/
│   ├── config/
│   │   └── config.php             ← Constantes globales (DB, BASE_URL, APP_NAME, etc.)
│   ├── core/
│   │   ├── BaseModel.php          ← Clase base con findAll, findById, create, update, softDelete
│   │   ├── Database.php           ← Singleton PDO
│   │   ├── Router.php             ← Front Controller: kebab-case → PascalCase+Controller
│   │   └── Validator.php          ← Validador de formularios (required, min, max, etc.)
│   ├── helpers/
│   │   └── helpers.php            ← Funciones globales: e(), url(), asset(), redirect(), etc.
│   ├── models/
│   │   ├── BaseModel.php
│   │   ├── CatalogoTerapiasModel.php
│   │   ├── ConceptoMovimientoModel.php
│   │   ├── DashboardModel.php
│   │   ├── EnfermeriaModel.php    ← Sub-inventario Enfermería (COMPLETO)
│   │   ├── InsumoCodigoBarraModel.php
│   │   ├── InsumoModel.php
│   │   ├── MedidaInsumoModel.php
│   │   ├── MovimientoModel.php
│   │   └── TipoInsumoModel.php
│   └── controllers/
│       ├── CatalogoTerapiasController.php
│       ├── CodigoBarraController.php
│       ├── ConceptoMovimientoController.php
│       ├── DashboardController.php
│       ├── InsumosController.php
│       ├── InsumosEnfermeriaController.php  ← Sub-inventario Enfermería (COMPLETO)
│       ├── MedidaInsumoController.php
│       ├── MovimientosController.php
│       └── TipoInsumoController.php
├── database/
│   ├── schemaserinventarios.sql   ← Esquema base v1.0 (tablas + vistas + seed data)
│   └── migration_enfermeria.sql   ← Migración para módulo Enfermería (3 tablas + 1 vista)
├── public/
│   ├── .htaccess                  ← Reescritura mod_rewrite → index.php
│   ├── index.php                  ← Entry point de la app
│   └── assets/
│       ├── css/
│       │   └── app.css            ← Hoja de estilos única con variables CSS y todos los estilos
│       └── js/
│           ├── app.js             ← JS global (sidebar, flash, modal de confirmación)
│           ├── enfermeria.js      ← JS módulo Enfermería (COMPLETO)
│           └── movimientos.js     ← JS módulo Movimientos
└── views/
    ├── catalogo_terapias/
    │   ├── form.php
    │   └── index.php
    ├── concepto_movimiento/
    ├── dashboard/
    ├── enfermeria/                ← Todas las vistas del módulo Enfermería (COMPLETO)
    │   ├── create.php             ← Formulario de entrega
    │   ├── devolucion.php         ← Formulario de devolución
    │   ├── historial.php          ← Historial paginado
    │   ├── index.php              ← Dashboard con KPIs
    │   ├── print.php              ← Recibo imprimible (sin layout)
    │   └── show.php               ← Detalle de entrega/devolución
    ├── errors/
    ├── insumos/
    │   ├── codigos_barra.php
    │   ├── form.php
    │   ├── index.php
    │   └── show.php
    ├── layouts/
    │   └── master.php             ← Layout base HTML con sidebar, topbar, flash messages
    ├── medida_insumo/
    ├── movimientos/
    │   ├── create.php
    │   ├── index.php
    │   └── show.php
    ├── partials/
    │   └── _pagination.php
    └── tipo_insumo/
```

### 3.2 Patrones de Diseño Implementados

- **Front Controller**: `public/index.php` → `Router::dispatch()` → `ControladorXxx::accion()`
- **MVC simplificado**: modelos encapsulan SQL, controladores orquestan, vistas solo presentan
- **Repository Pattern**: modelos tienen `findAll()`, `findById()`, `create()`, `update()`, `softDelete()`
- **PDO Singleton**: `Database::getInstance()` — una sola conexión por ciclo de petición
- **Flash Messages**: `flashMessage($type, $msg)` / `getFlash()` — vía sesión PHP
- **CSRF**: token en sesión, `csrfField()` en formularios, `verifyCsrf()` en cada POST

### 3.3 Enrutamiento

El `Router` convierte la URL de forma automática:

| URL | Controlador | Método |
|-----|------------|--------|
| `/` | `DashboardController` | `index()` |
| `/insumos` | `InsumosController` | `index()` |
| `/insumos/create` | `InsumosController` | `create()` |
| `/insumos-enfermeria` | `InsumosEnfermeriaController` | `index()` |
| `/insumos-enfermeria/historial` | `InsumosEnfermeriaController` | `historial()` |
| `/insumos-enfermeria/show/5` | `InsumosEnfermeriaController` | `show('5')` |
| `/catalogo-terapias` | `CatalogoTerapiasController` | `index()` |

- Segmento 1 en **kebab-case** → PascalCase + `Controller`  
- Segmento 2 en **kebab-case** → camelCase (método)  
- Segmento 3 → `$id` (string, convertir a int en el controlador)

---

## 4. Base de Datos

### 4.1 Esquema Principal (`schemaserinventarios.sql`)

Base de datos: `ser_inventario`

| Tabla | Responsabilidad | Relaciones clave |
|-------|----------------|-----------------|
| `medidainsumo` | Unidades de medida (PZA, ML, LT…) | ← `insumos.MedidaInsumo` |
| `tipoinsumo` | Tipos de insumo (INTE, VTA, PAP…) | ← `insumos.TipoProducto` |
| `conceptomovimiento` | Conceptos de movimiento con dirección ENTRADA/SALIDA | ← `movimientos.ConceptoID` |
| `insumos` | Catálogo maestro con `Existencia` (campo calculado) | → medidainsumo, tipoinsumo |
| `insumocodigobarra` | Códigos de barra múltiples por insumo | → insumos |
| `movimientos` | Cabecera de cada evento de inventario | → conceptomovimiento |
| `movimientodetalle` | Líneas del movimiento: insumo, cantidad, lote, vencimiento | → movimientos, insumos |

**Vistas SQL existentes:**

| Vista | Propósito |
|-------|----------|
| `v_stock_actual` | Stock con semáforo OK/BAJO/CRÍTICO de todos los insumos activos |
| `v_alertas_vencimiento` | Lotes próximos a vencer según `DiasPreviosAviso` |
| `v_historial_movimientos` | Historial completo con concepto, insumo, cantidad, lote |
| `v_busqueda_codigo_barra` | Búsqueda rápida por código de barra escaneado |

**Seed data incluida:** medidas (PZA, ML, LT, GR, KG, CJA, PAQ, ROL, FRC, TBO), tipos (INTE, VTA, PAP, LIM, FORM) y conceptos de movimiento (COMPRA, VENTA, MERMA, AJUSTE_ENT, AJUSTE_SAL, etc.)

### 4.2 Migración Enfermería (`migration_enfermeria.sql`)

Tres tablas adicionales + una vista, **pendientes de aplicar a producción** si no están ya:

| Tabla/Vista | Descripción |
|------------|-------------|
| `insumos_enfermeria` | Stock actual de enfermería por insumo (UNIQUE en `InsumoID`) |
| `entregas_enfermeria` | Cabecera de cada entrega/devolución (TipoOperacion ENUM) |
| `entregas_enfermeria_detalle` | Líneas de detalle por entrega |
| `v_stock_enfermeria` | Stock de enfermería con semáforo (solo insumos INTE activos) |

### 4.3 Módulo de Terapias (tabla adicional, no en schema original)

El módulo `CatalogoTerapias` usa dos tablas que **no están en `schemaserinventarios.sql`** y que deben existir en BD:

- `catalogo_terapias` — cabecera de terapia (`id_terapiaINV` PK, `id_terapiaERP`, `nom_terapia`, `Activo`, `UsuarioCreacion`, `UsuarioActualiza`)
- `detalle_terapias` — insumos por terapia (`id_terapiaINV` FK, `id_terapiaERP`, `idInsumo` FK→insumos, `CantidadInsumo`)

> ⚠️ **Pendiente:** El SQL de creación de estas tablas no está en ningún archivo del proyecto. Si se parte desde cero, se deben crear manualmente o agregar a un nuevo archivo de migración.

### 4.4 Regla de Negocio — Actualización de Existencias

**CRÍTICO:** `insumos.Existencia` **NUNCA** se edita directamente. Solo se modifica mediante:
1. Registro de un `movimientodetalle` (módulo Movimientos)
2. Operaciones de entrega/devolución de Enfermería (`EnfermeriaModel::registrarEntrega()`)

Toda modificación de stock ocurre dentro de una **transacción** con `BEGIN → COMMIT/ROLLBACK`.

---

## 5. Estado de Implementación por Módulo

### ✅ Completamente implementados

| Módulo | Controlador | Modelo | Vistas | Notas |
|--------|------------|--------|--------|-------|
| Dashboard | `DashboardController` | `DashboardModel` | `dashboard/` | KPIs, stock crítico, vencimientos, actividad reciente |
| Insumos | `InsumosController` | `InsumoModel` | `insumos/` | CRUD completo, búsqueda, paginación, baja lógica |
| Códigos de Barra | `CodigoBarraController` | `InsumoCodigoBarraModel` | dentro de `insumos/` | Gestión múltiple por insumo |
| Movimientos | `MovimientosController` | `MovimientoModel` | `movimientos/` | Registro transaccional, historial filtrable |
| Unidades Medida | `MedidaInsumoController` | `MedidaInsumoModel` | `medida_insumo/` | CRUD con baja lógica |
| Tipos de Insumo | `TipoInsumoController` | `TipoInsumoModel` | `tipo_insumo/` | CRUD con baja lógica |
| Conceptos Movimiento | `ConceptoMovimientoController` | `ConceptoMovimientoModel` | `concepto_movimiento/` | CRUD con baja lógica |
| **Enfermería** | `InsumosEnfermeriaController` | `EnfermeriaModel` | `enfermeria/` | Sub-inventario completo con bloqueo pesimista |
| Catálogo Terapias | `CatalogoTerapiasController` | `CatalogoTerapiasModel` | `catalogo_terapias/` | CRUD + detalle de insumos por terapia |

### ❌ Pendientes (hoja de ruta según especificación)

| Módulo | Versión Planificada | Descripción |
|--------|-------------------|-------------|
| Autenticación / Sesiones | v1.3 | Login, roles (Admin, Farmacia, Enfermería, Consulta) |
| Reportes exportables | v1.1 | Stock, historial a Excel/PDF, alertas por email |
| Proveedores | v1.2 | Catálogo vinculado a movimientos de compra |
| Punto de Venta | v2.0 | Venta directa a paciente con integración al inventario |
| API REST | v2.1 | Endpoints JSON para integración externa o app móvil |

---

## 6. Módulo de Enfermería — Detalle Técnico

> Este módulo fue implementado en una fase posterior a la v1.0. Ver especificación completa en `Design/implementation_planSubInventario-Enf.md`.

### Reglas de Negocio
1. Solo aplica para insumos con `TipoProducto = 'INTE'`
2. **Entrega** → descuenta `insumos.Existencia` + suma `insumos_enfermeria.Existencia`
3. **Devolución** → descuenta `insumos_enfermeria.Existencia` + suma `insumos.Existencia`
4. No se puede entregar más de lo disponible en inventario global
5. No se puede devolver más de lo que tiene enfermería
6. Los niveles min/max se toman de `insumos` (no se duplican)
7. Bloqueo pesimista: `SELECT ... FOR UPDATE` antes de validar y actualizar

### Rutas del Módulo
| URL | Acción |
|-----|--------|
| `GET /insumos-enfermeria` | Dashboard con KPIs y stock actual |
| `GET /insumos-enfermeria/historial` | Historial de entregas/devoluciones |
| `GET/POST /insumos-enfermeria/create` | Registrar entrega |
| `GET/POST /insumos-enfermeria/devolucion` | Registrar devolución |
| `GET /insumos-enfermeria/show/{id}` | Detalle de una entrega/devolución |
| `GET /insumos-enfermeria/print/{id}` | Recibo imprimible (sin layout master) |
| `GET /insumos-enfermeria/search-insumos` | AJAX: busca insumos disponibles |

### Recibo Imprimible
- Renderizado en `views/enfermeria/print.php` **sin** el layout `master.php`
- CSS `@media print` integrado en `app.css`
- Incluye firma de "Entregó" y "Recibió"

---

## 7. Módulo de Catálogo de Terapias — Detalle Técnico

> Módulo adicional no contemplado en la especificación v1.0. `catalogo_terapias` es un espejo LOCAL, de solo lectura hacia el ERP, de la tabla `ser.terapias`: el alta/baja de la terapia en sí (nombre, ID, activo/inactivo) se gobierna desde el ERP y llega aquí vía sincronización manual (botón). Este módulo solo gestiona localmente qué insumos INTE consume cada terapia — no existe alta, edición de nombre ni baja manual de terapias.

### Estructura
- **`catalogo_terapias`**: cabecera — campos `id_terapiaINV` (PK, igual a `ser.terapias.id_terapia`), `id_terapiaERP`, `nom_terapia`, `Activo` (reflejado desde `ser.terapias.activo` en cada sync), `UsuarioCreacion`, `UsuarioActualiza`
- **`detalle_terapias`**: detalle — `id_terapiaINV` FK, `id_terapiaERP`, `idInsumo` FK→insumos, `CantidadInsumo`

### Rutas del Módulo
| URL | Acción |
|-----|--------|
| `GET /catalogo-terapias` | Listado de terapias activas |
| `POST /catalogo-terapias/sync` | Sincroniza altas/actualizaciones/bajas desde `ser.terapias` (solo `SELECT`, nunca escribe en el ERP) |
| `GET /catalogo-terapias/edit/{id}` | Formulario para editar los insumos de la terapia (nombre/ID de solo lectura) |
| `POST /catalogo-terapias/update/{id}` | Regenera `detalle_terapias` para la terapia |

> ⚠️ **PENDIENTE CRÍTICO**: Las tablas `catalogo_terapias` y `detalle_terapias` no tienen SQL de creación en el repositorio (viven en `Design/catalogo_terapias.sql`, no en `database/`).

---

## 7bis. Módulo de Catálogo de Sueros — Detalle Técnico

> Mismo patrón que el Módulo de Catálogo de Terapias (sección 7): `catalogo_sueros` es un espejo LOCAL, de solo lectura hacia el ERP, de la tabla `ser.sueros`. Se implementó como tablas separadas (no fusionadas con `catalogo_terapias`) porque 15 de los 16 `id_suero` reales colisionaban con `id_terapiaINV` existentes de terapias no relacionadas.

### Estructura
- **`catalogo_sueros`**: cabecera — `id_suero` (PK, igual a `ser.sueros.id_suero`, sin `AUTO_INCREMENT` porque el ERP asigna el ID), `nom_suero`, `Activo`, `UsuarioCreacion`, `UsuarioActualiza`. No guarda `precio` (solo vive en el ERP).
- **`detalle_sueros`**: detalle — `id_suero` FK, `idInsumo` FK→insumos, `CantidadInsumo`

### Rutas del Módulo
| URL | Acción |
|-----|--------|
| `GET /catalogo-sueros` | Listado de sueros activos |
| `POST /catalogo-sueros/sync` | Sincroniza altas/actualizaciones/bajas desde `ser.sueros` (solo `SELECT`) |
| `GET /catalogo-sueros/edit/{id}` | Formulario para editar los insumos del suero (nombre/ID de solo lectura) |
| `POST /catalogo-sueros/update/{id}` | Regenera `detalle_sueros` para el suero |

SQL de creación: `database/migration_sueros.sql`.

---

## 8. Clases y Funciones Clave

### 8.1 Helpers Globales (`app/helpers/helpers.php`)

| Función | Descripción |
|---------|-------------|
| `e($value)` | Escape XSS: `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` |
| `sanitize($input)` | trim + strip_tags + htmlspecialchars |
| `csrfField()` | Genera `<input hidden>` con token CSRF |
| `verifyCsrf()` | Verifica token POST, aborta 403 si inválido |
| `flashMessage($type, $msg)` | Almacena flash en sesión |
| `getFlash()` | Recupera y elimina flash de sesión |
| `redirect($path)` | Redirige a URL relativa a BASE_URL |
| `url($path)` | Genera URL completa desde BASE_URL |
| `asset($path)` | URL a `public/assets/...` |
| `renderView($view, $data)` | Renderiza vista dentro del layout master |
| `jsonResponse($data, $code)` | Envía JSON y termina |
| `paginateQuery($total, $page)` | Calcula offset, totalPages, etc. |
| `stockBadge($existencia, $minimos)` | Retorna 'ok' \| 'bajo' \| 'critico' |
| `stockLabel($badge)` | Retorna etiqueta legible del badge |
| `formatDate($date, $fmt)` | Formatea fecha MySQL → dd/mm/yyyy |
| `formatDateTime($date)` | Formatea fecha+hora MySQL |
| `isPost()` | Verifica si la petición es POST |
| `isAjax()` | Verifica si la petición es AJAX |

### 8.2 BaseModel (`app/core/BaseModel.php`)
Métodos heredados por todos los modelos:
- `findAll($filters, $limit, $offset)` — con filtros básicos y paginación
- `findById($id)` — retorna array o null
- `create($data)` — insert y retorna ID
- `update($id, $data)` — actualiza por ID
- `softDelete($id)` — setea `Activo = 0`

### 8.3 Validator (`app/core/Validator.php`)
Uso estándar:
```php
$v = new Validator($data, ['campo' => 'required'], ['campo' => 'Etiqueta']);
$errors = $v->errors(); // array vacío si es válido
```

---

## 9. Identidad Visual y Paleta de Colores

Las variables CSS están declaradas en `public/assets/css/app.css` como custom properties en `:root`:

| Variable CSS | Hex | Uso |
|-------------|-----|-----|
| `--color-primary` | `#2D83A0` | Botones primarios, nav activo |
| `--color-primary-dark` | `#1A4D5E` | Hover de botón primario, encabezados |
| `--color-accent` | `#00C8E0` | Bordes de foco, chips |
| `--color-bg` | `#F2FAFB` | Fondo general de la app |
| `--color-surface` | `#FFFFFF` | Tarjetas, modales, paneles |
| `--color-text` | `#1C2B30` | Texto principal |
| `--color-text-muted` | `#4A7080` | Texto secundario/ayuda |
| `--color-ok` | `#27AE60` | Badge stock OK |
| `--color-warning` | `#F39C12` | Badge stock BAJO |
| `--color-danger` | `#E74C3C` | Badge CRÍTICO / error |

**Fuente:** Inter (Google Fonts), cargada en el `<head>` del layout master.

---

## 10. Convenciones de Desarrollo

Siguiendo el documento `.agents/rules/buenaspracticas.md`:

### PHP
- **PSR-12** para estilo de código
- Variables y métodos en **inglés**, comentarios de negocio en **español**
- Nombres descriptivos y métodos cortos y específicos
- PHPDoc en todas las clases y métodos públicos
- `utf8mb4` en todos los archivos y conexión MySQL
- Excepciones PDO logueadas con `error_log()`, nunca mostradas al usuario

### Seguridad (OBLIGATORIO en cada implementación)
- **Sin SELECT \*** en consultas de producción
- **100% Prepared Statements** con parámetros nombrados (`:param`) — nunca concatenar variables en queries
- **CSRF**: `verifyCsrf()` al inicio de cada POST
- **XSS**: todo output a HTML via `e()` o `sanitize()`
- Validación **siempre en backend**, nunca solo en frontend
- Transacciones para toda operación que modifique inventario

### Base de Datos
- Motor InnoDB, claves foráneas definidas, índices en `product_id`, `warehouse_id`, `movement_date`
- Baja **lógica** (campo `Activo = 0`), nunca DELETE físico en catálogos
- Bloqueo pesimista (`SELECT ... FOR UPDATE`) para operaciones concurrentes de inventario

### Git
- Ramas: `feature/modulo-nombre`
- Commits semánticos: `feat:`, `fix:`, `refactor:`, `docs:`

---

## 11. Archivos de Configuración

### `app/config/config.php`
Contiene constantes globales. Valores típicos:
```php
define('APP_NAME',        'SER Inventarios');
define('APP_VERSION',     '1.0.0');
define('APP_CLINIC_NAME', 'Consultorio de Medicina Alternativa Ser S.C.');
define('BASE_URL',        'http://localhost/Ser-Inventarios/public/');
define('APP_ROOT',        dirname(__DIR__, 2));  // raíz del proyecto
define('ITEMS_PER_PAGE',  20);

// Conexión BD
define('DB_HOST', 'localhost');
define('DB_NAME', 'ser_inventario');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

---

## 12. Flujo de una Petición HTTP

```
Browser → Apache → public/.htaccess (mod_rewrite)
         → public/index.php
           ├── require config.php
           ├── require helpers.php
           ├── require Database.php, BaseModel.php, Router.php, Validator.php
           ├── session_start()
           └── (new Router())->dispatch()
                ├── parseUri()            → extrae segmentos de la URL
                ├── resolveController()   → kebab-case → PascalCase+Controller
                ├── resolveAction()       → kebab-case → camelCase
                ├── require ControllerFile.php
                └── $controller->$action($id?)
                     ├── [Modelo] → consulta BD via PDO
                     └── renderView('vista/nombre', $data)
                          └── require views/layouts/master.php
                               └── require $content (la vista específica)
```

---

## 13. Pendientes Conocidos / Deuda Técnica

| Item | Prioridad | Descripción |
|------|-----------|-------------|
| SQL tablas Terapias | 🔴 Alta | Crear `migration_terapias.sql` con `catalogo_terapias` y `detalle_terapias` |
| Autenticación | 🔴 Alta | No existe sistema de login ni control de acceso por rol |
| Middleware Auth | 🔴 Alta | No hay verificación de sesión activa en ningún controlador |
| Exportación CSV/Excel | 🟡 Media | Historial de movimientos exportable (planificado v1.1) |
| Proveedores | 🟡 Media | Catálogo de proveedores vinculado a COMPRA (planificado v1.2) |
| Alertas por email | 🟡 Media | Stock crítico y vencimientos por correo (planificado v1.1) |
| CSRF en Terapias | 🟡 Media | El controlador `CatalogoTerapiasController` no usa `verifyCsrf()` |
| `$_SESSION['username']` | 🟡 Media | El modelo de Terapias usa `$_SESSION['username']` que no existe (sin auth) |
| Test de integración BD Enfermería | 🟢 Baja | Verificar que la migración `migration_enfermeria.sql` fue aplicada |

---

## 14. Instrucciones para Retomar el Desarrollo

1. **Leer este archivo** completamente antes de cualquier cambio.
2. **Revisar las reglas** en `.agents/rules/buenaspracticas.md` — son mandatorias.
3. **Verificar estado de BD**: confirmar que ambas migraciones están aplicadas (`schemaserinventarios.sql` + `migration_enfermeria.sql`).
4. **Verificar tablas de Terapias**: si el módulo de terapias falla, las tablas no existen en BD aún.
5. **Para nuevo módulo**: crear controlador en `app/controllers/`, modelo en `app/models/`, vistas en `views/nombre-modulo/`, agregar enlace en `views/layouts/master.php`.
6. **Seguir la convención de rutas**: kebab-case en URL → resolución automática por el Router.
7. **Siempre usar transacciones** para operaciones que modifiquen `insumos.Existencia`.
8. **Nunca editar `Existencia` directamente** desde formularios de insumo — solo mediante movimientos o entregas de enfermería.

---

*Documento generado analizando el código fuente real del proyecto — Mayo 2026*
