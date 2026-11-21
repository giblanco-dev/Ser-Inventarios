# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Contexto del proyecto

**SER Inventarios** es un sistema de gestión de inventario para la Clínica de Medicina Alternativa Ser S.C. Es **PHP 8.1 nativo puro** (sin Composer, sin frameworks, sin autoloader) con MySQL 8.0, corriendo sobre Apache local en `http://localhost/Ser-Inventarios/public/`.

Antes de trabajar en el proyecto, lee también:
- `.agents/agents.md` — documento detallado de arquitectura, esquema de BD, rutas por módulo y estado de implementación (generado en mayo 2026; falta documentar el módulo de Activos Electrónicos/TI descrito abajo).
- `.agents/rules/buenaspracticas.md` — reglas de desarrollo obligatorias (seguridad, transacciones, PSR-12).

## Comandos

No hay build, linter, formateador ni suite de pruebas configurados (no hay `composer.json`, sin PHPUnit). El "correr" el proyecto es servir el directorio con Apache y visitar `public/`. Para verificar cambios PHP sin servidor:

```
php -l app/controllers/NombreController.php   # chequeo de sintaxis
```

Para cargar datos iniciales de insumos:
```
php LoadsBD/import_insumos.php
```

## Arquitectura

Front Controller manual, sin autoloading: `public/index.php` hace `require_once` explícito de `config.php`, `Database.php`, `Router.php`, `Validator.php`, `BaseModel.php` y `helpers.php`, y cada controlador hace `require_once` de su propio modelo. Al crear un módulo nuevo hay que registrar el `require_once` del modelo en el controlador (no hay autoload por convención de nombres).

**Enrutamiento** (`app/core/Router.php`): la URL `/{controlador}/{accion}/{id?}` se resuelve así — segmento 1 en kebab-case → `PascalCase` + `Controller` (ej. `insumos-enfermeria` → `InsumosEnfermeriaController`), segmento 2 en kebab-case → camelCase (método, default `index`), segmento 3 → `$id` como string (el controlador decide si castea a int). Sin rutas declarativas: cualquier método público de un controlador es alcanzable por URL.

**Capas**: modelos extienden `BaseModel` (`app/core/BaseModel.php`) que da `findAll()`, `count()`, `findById()`, `create()`, `update()`, `softDelete()`, `restore()` genéricos vía PDO con prepared statements con nombre. Los modelos definen `$table` y `$primaryKey` (default `'ID'`) y agregan sus propios métodos para joins/reportes/transacciones — no todo pasa por `BaseModel`. `Database::getInstance()` es un singleton PDO (`ERRMODE_EXCEPTION`, `EMULATE_PREPARES=false`, utf8mb4).

**Vistas**: `renderView($vista, $data)` hace `extract($data)` y luego incluye `views/layouts/master.php`, que a su vez incluye la vista específica como `$content`. Las vistas de impresión (ej. `views/enfermeria/print.php`) se renderizan pasando `layout` distinto o se sirven sin master. Al agregar un módulo hay que añadir su enlace manualmente en `views/layouts/master.php`.

**Seguridad obligatoria en cada POST** (ver `app/helpers/helpers.php`): `verifyCsrf()` al inicio del handler (el token rota tras cada uso exitoso), y toda salida a HTML vía `e()`/`sanitize()`. Varios controladores más antiguos (ej. `CatalogoTerapiasController`) no llaman `verifyCsrf()` — es deuda técnica conocida, no un patrón a copiar.

**Regla de negocio crítica**: `insumos.Existencia` nunca se edita directamente. Solo cambia dentro de una transacción (`beginTransaction/commit/rollBack`) disparada por un `movimientodetalle` (módulo Movimientos) o por una entrega/devolución de Enfermería (`EnfermeriaModel::registrarEntrega()`), usando `SELECT ... FOR UPDATE` para bloqueo pesimista. Cualquier feature nueva que toque stock debe seguir este mismo patrón transaccional.

**Baja lógica, no DELETE**: los catálogos usan `Activo = 0` vía `softDelete()`; nunca borrar filas físicamente en tablas de catálogo.

### Módulo de Activos Electrónicos/TI (no documentado en `.agents/agents.md`)

Agregado en el commit `feature: Inventario de Activos de Cómputo` — gestiona equipos de cómputo con número de serie único, credenciales de acceso (usuario/contraseña, contraseña admin, conexión remota) y baja lógica con comentario obligatorio:
- Controlador/modelo: `InsumosElectronicosController` / `InsumosElectronicosModel`, tablas creadas en `database/migration_electronicos_fix.sql`.
- Catálogos de apoyo con CRUD propio: `PuestoController`/`PuestoModel`, `ResponsableController`/`ResponsableModel`, `TipoDispositivoController`/`TipoDispositivoModel`, `UbicacionController`/`UbicacionModel`.
- `NoSerie` es inmutable tras el alta (se ignora si viene en el POST de edición); la baja exige `comentarios_baja` no vacío.

### Deuda técnica conocida (no la repitas al extender el código)

- Tablas de `catalogo_terapias`/`detalle_terapias` referenciadas en código pero cuyo SQL de creación vive en `Design/catalogo_terapias.sql`, no en `database/`.
- No existe autenticación/sesión de usuario ni control de acceso por rol — ningún controlador verifica sesión activa.
- `CatalogoTerapiasModel` referencia `$_SESSION['username']`, que nunca se define (no hay login).
- `app/config/config.php` está versionado en git con credenciales de BD en texto plano — no repitas ese patrón en archivos nuevos, y no imprimas su contenido en logs o commits.

### Deuda técnica conocida (no la repitas al extender el código)
Este es un modulo de extensión del ERP Ser el cual es un proyecto legacy, mismo que en un futuro se estará renovando bajo un stack y prácticas de desarrollo actualizadas, sin embargo se desea mantener como un modulo externo que en esta futura renovación ya sea integrado en su totalidad a un sistema unificado.
