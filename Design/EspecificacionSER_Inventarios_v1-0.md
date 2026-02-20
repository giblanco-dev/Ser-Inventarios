

| CLÍNICA DE MEDICINA ALTERNATIVA Sistema de Gestión de Inventarios — SER |
| :---: |

 

**DOCUMENTO DE ESPECIFICACIÓN**

Sistema de Inventarios — Módulo Base

 

| Versión | 1.0.0 |
| :---- | :---- |
| **Fecha** | Febrero 2026 |
| **Tecnología Backend** | PHP 8.1.25 Nativo |
| **Base de Datos** | MySQL 8.0 |
| **Clasificación** | **Confidencial — Uso interno** |

 

v1.0 — Módulo Base de Inventario

# **Tabla de Contenido**

# **1\. Introducción y Alcance**

Este documento define las especificaciones funcionales, técnicas y de diseño para el desarrollo del Sistema de Gestión de Inventarios (SER Inventarios) de la Clínica de Medicina Alternativa. El objetivo es construir una aplicación web robusta, escalable y mantenible, desarrollada con PHP 8.1.25 nativo y MySQL 8.0, que cubra la gestión completa del ciclo de vida de los insumos clínicos.

 

## **1.1 Objetivos del Sistema**

* Mantener un registro preciso y en tiempo real de las existencias de insumos clínicos, de venta y de soporte.

* Automatizar el cálculo de existencias mediante el registro de movimientos de entrada y salida.

* Proveer alertas de stock crítico y vencimientos próximos.

* Soportar trazabilidad completa de cada movimiento con número de lote y fecha de vencimiento.

* Construir una base de código limpia, reutilizable y preparada para escalar hacia nuevos módulos.

 

## **1.2 Alcance del Módulo Base (v1.0)**

La versión 1.0 cubre los siguientes módulos funcionales:

* Gestión del catálogo de Insumos (alta, baja lógica, actualización).

* Gestión de catálogos auxiliares: Unidades de medida (MedidaInsumo), Tipos de insumo (TipoInsumo) y Conceptos de movimiento (ConceptoMovimiento).

* Gestión de Códigos de Barra por insumo.

* Registro de Movimientos de inventario (Entradas y Salidas) con actualización automática de existencias.

* Dashboard con alertas de stock crítico y vencimientos próximos.

 

| El sistema está diseñado bajo una arquitectura modular que permita incorporar en fases posteriores: compras, ventas directas, reportes avanzados, integración con punto de venta y auditoría por usuario. |
| :---- |

 

# **2\. Identidad Visual y Paleta de Colores**

La paleta se construye a partir de los dos colores base de la clínica, extendida para cubrir todos los estados de la interfaz (éxito, advertencia, error, neutral). La propuesta transmite elegancia clínica, modernidad y confianza.

 

| Muestra | Nombre | Hex | Uso |
| :---- | :---- | :---- | :---- |
|   | **Teal Oscuro** | \#1A4D5E | Encabezados, portadas, secciones principales, textos de énfasis |
|   | **Teal Corporativo** | \#2D83A0 | Color primario de la UI, botones principales, títulos H2 |
|   | **Teal Medio** | \#1B6A85 | Subtítulos H3, elementos secundarios activos |
|   | **Cian Vibrante** | \#00E5FF | Acento decorativo, separadores, bordes de énfasis |
|   | **Cian Legible** | \#00C8E0 | Versión de acento aplicada sobre fondos claros (\#00C8E0) |
|   | **Azul muy Claro** | \#E0F4F8 | Fondos de celdas de encabezado, cajas informativas |
|   | **Blanco Teal** | \#F2FAFB | Fondo alterno de filas en tablas |
|   | **Gris Teal** | \#4A7080 | Texto secundario, pie de página, etiquetas |
|   | **Gris Suave** | \#8FA8B2 | Bordes, separadores, texto deshabilitado |
|   | **Gris Claro** | \#D9E8ED | Bordes de tablas, líneas divisoras |
|   | **Verde Éxito** | \#27AE60 | Badges OK, confirmaciones, existencia normal |
|   | **Ámbar Advertencia** | \#F39C12 | Badge BAJO, alertas de stock próximo al mínimo |
|   | **Rojo Error** | \#E74C3C | Badge CRÍTICO, errores de validación, eliminación |

 

## **2.1 Aplicación de la Paleta en la UI**

Las variables CSS deben declararse como custom properties en :root para facilitar el tema único de la aplicación:

 

| Variable CSS | Valor | Contexto de uso |
| :---- | :---- | :---- |
| \--color-primary | \#2D83A0 | Botones, nav activo |
| \--color-primary-dark | \#1A4D5E | Hover de botón primario |
| \--color-accent | \#00C8E0 | Bordes de foco, chips |
| \--color-bg | \#F2FAFB | Fondo general de la app |
| \--color-surface | \#FFFFFF | Tarjetas, modales, paneles |
| \--color-text | \#1C2B30 | Texto principal |
| \--color-text-muted | \#4A7080 | Texto secundario/ayuda |
| \--color-ok | \#27AE60 | Badge stock OK |
| \--color-warning | \#F39C12 | Badge stock BAJO |
| \--color-danger | \#E74C3C | Badge stock CRÍTICO / error |

 

# **3\. Arquitectura Técnica**

## **3.1 Stack Tecnológico**

| Capa | Tecnología | Justificación |
| :---- | :---- | :---- |
| Backend | PHP 8.1.25 Nativo | Sin dependencia de frameworks; control total |
| Base de datos | MySQL 8.0 | Esquema relacional ya definido con FK/vistas |
| Frontend | HTML5 \+ CSS3 \+ JS Vanilla | Ligero, sin frameworks; extensible |
| Servidor | Apache / Nginx | Standard; con .htaccess para rutas |
| Acceso BD | PDO \+ PreparedStatements | Prevención de SQL Injection; portable |

 

## **3.2 Estructura de Directorios**

La estructura sigue un patrón MVC simplificado sin framework, orientado a la reutilización y escalabilidad:

 

| Ruta | Descripción |
| :---- | :---- |
| /app/ | Núcleo de la aplicación |
| /app/config/ | Configuración (BD, constantes, entorno) |
| /app/core/ | Clases base: Database, Router, Response, Validator |
| /app/models/ | Modelos: InsumoModel, MovimientoModel, etc. |
| /app/controllers/ | Controladores por módulo funcional |
| /app/helpers/ | Funciones reutilizables: fecha, string, flash |
| /app/middleware/ | Auth, CSRF, sanitización de entradas |
| /public/ | Document root expuesto al navegador |
| /public/assets/css/ | Hojas de estilo (variables CSS \+ módulos) |
| /public/assets/js/ | JavaScript por módulo (sin bundle) |
| /views/ | Plantillas PHP: layouts, partials, módulos |
| /views/layouts/ | master.php (base HTML, nav, sidebar) |
| /views/partials/ | Componentes reutilizables: table, form, modal |

 

## **3.3 Patrones de Diseño Aplicados**

* Repository Pattern para modelos: los modelos encapsulan toda la lógica SQL; los controladores nunca ejecutan queries directamente.

* Active Record simplificado: cada modelo expone métodos estandarizados (findAll, findById, create, update, softDelete).

* Front Controller: un único index.php en /public/ maneja todas las peticiones y delega al Router.

* Flash Messages: mensajes de éxito/error almacenados en sesión y consumidos en la siguiente petición.

* CSRF Token: generado por petición para todos los formularios POST/PUT/DELETE.

* PDO Singleton: una única instancia de conexión por ciclo de vida de la petición.

 

# **4\. Esquema de Base de Datos**

El esquema está definido en el archivo schemaserinventarios.sql. A continuación se documenta cada tabla con sus responsabilidades y relaciones clave.

 

## **4.1 Entidades Principales**

| Tabla | Responsabilidad | Relaciones |
| :---- | :---- | :---- |
| insumos | Catálogo maestro de insumos con stock (Existencia) | → medidainsumo, tipoinsumo |
| medidainsumo | Catálogo de unidades de medida (PZA, ML, LT…) | ← insumos.MedidaInsumo |
| tipoinsumo | Catálogo de tipos de producto (INTE, VTA, PAP…) | ← insumos.TipoProducto |
| conceptomovimiento | Conceptos de movimiento con dirección ENTRADA/SALIDA | ← movimientos.ConceptoID |
| movimientos | Cabecera de cada evento de inventario (fecha, concepto) | → conceptomovimiento |
| movimientodetalle | Líneas del movimiento: insumo, cantidad, lote, vencimiento | → movimientos, insumos |
| insumocodigobarra | Códigos de barra (múltiples por insumo, distintos formatos) | → insumos |

 

## **4.2 Vistas SQL Disponibles**

| Vista | Datos que expone |
| :---- | :---- |
| v\_stock\_actual | Stock con estado semafórico (OK / BAJO / CRÍTICO) para todos los insumos activos |
| v\_alertas\_vencimiento | Insumos con lote próximos a vencer según DiasPreviosAviso configurado |
| v\_historial\_movimientos | Historial completo con concepto, insumo, cantidad, lote y usuario |
| v\_busqueda\_codigo\_barra | Búsqueda rápida de insumo a partir de código de barra escaneado |

 

## **4.3 Regla de Negocio — Actualización de Existencias**

| Cada vez que se registra un movimientodetalle, el sistema debe actualizar automáticamente el campo Existencia de la tabla insumos. Si el ConceptoMovimiento tiene TipoOperacion \= ENTRADA, se suma la Cantidad. Si es SALIDA, se resta. Esta lógica se implementa en el modelo MovimientoModel y se ejecuta dentro de una transacción de base de datos para garantizar consistencia. |
| :---- |

 

Pseudocódigo de la transacción:

| BEGIN TRANSACTION; INSERT INTO movimientos (...) VALUES (...); INSERT INTO movimientodetalle (...) VALUES (...); UPDATE insumos SET Existencia \= Existencia ± :cantidad   WHERE ID \= :insumoId; COMMIT;  \-- o ROLLBACK en caso de error |
| :---- |

 

# **5\. Especificación Funcional por Módulo**

## **5.1 Módulo: Catálogos Auxiliares**

Agrupa la gestión de tres tablas de catálogo que comparten la misma estructura de pantallas y comportamiento. Todos los catálogos soportan baja lógica (campo Activo) en lugar de eliminación física.

 

| 5.1.1 — Catálogo de Unidades de Medida (MedidaInsumo) |
| :---- |

 

| Campo | Especificación |
| :---- | :---- |
| ID | Clave alfanumérica ≤ 10 chars. Mayúsculas. Inmutable tras crear. Ej: ML, PZA, LT. |
| Descripcion | Nombre legible de la unidad. ≤ 100 chars. Validación: no vacío. |
| Activo | 1 \= Activo (default). 0 \= Inactivo. No se permite inactivar si hay insumos activos asociados. |

 

Acciones permitidas: Listar con filtro por estado (Activo/Inactivo) | Crear | Editar descripción | Inactivar (con validación de dependencia).

 

| 5.1.2 — Catálogo de Tipos de Insumo (TipoInsumo) |
| :---- |

 

| Campo | Especificación |
| :---- | :---- |
| ID | Clave ≤ 10 chars (INTE, VTA, PAP, LIM, FORM). Inmutable. |
| Descripcion | Nombre de la categoría. ≤ 100 chars. |
| Activo | Misma regla de inactivación que MedidaInsumo. |

 

| 5.1.3 — Catálogo de Conceptos de Movimiento (ConceptoMovimiento) |
| :---- |

 

| Campo | Especificación |
| :---- | :---- |
| ID | Clave ≤ 15 chars. Ej: COMPRA, VENTA, MERMA, AJUSTE\_ENT. |
| Descripcion | Texto descriptivo del concepto. ≤ 100 chars. |
| TipoOperacion | ENUM: ENTRADA (suma stock) | SALIDA (resta stock). Inmutable tras crear. Mostrar con badge de color. |
| Activo | No se permite inactivar si existe un movimiento registrado con ese concepto. |

 

## **5.2 Módulo: Gestión de Insumos**

Es el módulo central del sistema. Administra el catálogo maestro de insumos y es el punto de referencia de todas las operaciones de inventario.

 

| 5.2.1 — Lista de Insumos |
| :---- |

 

* Tabla paginada con búsqueda en tiempo real (JS) por Nombre.

* Filtros: Tipo de Insumo, Unidad de Medida, Estado (Activo/Inactivo), Estado de Stock (OK/BAJO/CRÍTICO), Caduca (Sí/No).

* Columnas visibles: ID | Nombre | Tipo | Unidad | Existencia | Mín | Máx | Estado Stock | Caduca | Acciones.

* La columna Estado Stock usa badges de color tomados de la vista v\_stock\_actual.

* Acciones por fila: Ver detalle | Editar | Inactivar | Gestionar códigos de barra.

 

| 5.2.2 — Campos del Formulario de Insumo |
| :---- |

 

| Campo DB | Tipo HTML | Validación | Notas |
| :---- | :---- | :---- | :---- |
| Nombre | text | Requerido, ≤ 255 chars | Mayúsculas automáticas |
| TipoProducto | select | Requerido | Solo tipos activos |
| MedidaInsumo | select | Requerido | Solo medidas activas |
| Minimos | number | ≥ 0, entero | Stock mínimo de alerta |
| Maximos | number | ≥ Minimos | Stock máximo deseado |
| Caducidad | checkbox | Boolean | Activa campo DiasPreviosAviso |
| DiasPreviosAviso | number | Requerido si Caducidad=1 | Días de anticipación de alerta |
| Existencia | (solo lectura) | — | Calculado por movimientos |

 

| IMPORTANTE: El campo Existencia en la tabla insumos NUNCA se edita directamente desde el formulario de insumo. Solo se modifica mediante el registro de movimientos de inventario. El formulario de edición mostrará el valor actual como solo lectura. |
| :---- |

 

| 5.2.3 — Gestión de Códigos de Barra (InsumoCodigoBarra) |
| :---- |

 

Accesible desde la acción 'Gestionar Códigos de Barra' en la lista de insumos. Permite asociar múltiples códigos a un mismo insumo con distintos formatos.

* Listar todos los códigos del insumo (activos e inactivos).

* Agregar nuevo código: CodigoBarra (texto único en toda la tabla), TipoCodigo (EAN13/EAN8/UPC\_A/CODE128/CODE39/QR/INTERNO), Descripción opcional.

* Editar descripción y tipo de un código existente.

* Inactivar / Reactivar código.

* Validación en backend: unicidad global de CodigoBarra antes de insertar.

 

## **5.3 Módulo: Registro de Movimientos de Inventario**

Es el módulo que da vida al inventario. Cada movimiento puede afectar uno o varios insumos en una misma operación (ej. una compra de varios artículos). El impacto en existencias es inmediato y transaccional.

 

| 5.3.1 — Flujo de Registro de Movimiento |
| :---- |

 

* El usuario selecciona el Concepto de Movimiento (ej. COMPRA). El sistema muestra el tipo de operación: ENTRADA o SALIDA con badge de color.

* Ingresa referencia externa opcional (folio de factura, número de orden).

* Observaciones generales del movimiento (opcional).

* Agrega uno o más insumos a la lista de detalle mediante búsqueda por nombre o escaneo de código de barra.

* Por cada línea de detalle ingresa: Cantidad, y si el insumo tiene Caducidad=1: Número de Lote y Fecha de Vencimiento.

* El usuario revisa el resumen y confirma. El sistema ejecuta la transacción.

 

| 5.3.2 — Validaciones del Movimiento |
| :---- |

 

* Concepto activo y existente.

* Al menos una línea de detalle.

* Cantidad \> 0 (entero positivo) por línea.

* Si el insumo tiene Caducidad=1: NumeroLote y FechaVencimiento son obligatorios.

* Para movimientos de SALIDA: la Existencia resultante no puede quedar negativa. El sistema debe mostrar advertencia antes de confirmar.

 

| 5.3.3 — Historial de Movimientos |
| :---- |

 

* Tabla paginada con datos de la vista v\_historial\_movimientos.

* Filtros: rango de fechas, concepto, tipo de operación (ENTRADA/SALIDA), insumo, usuario.

* Exportación a Excel/CSV (funcionalidad planificada para v1.1).

* Los movimientos registrados son inmutables. No se permite editar ni eliminar. Solo ajustes contables mediante nuevos movimientos con concepto AJUSTE\_ENT o AJUSTE\_SAL.

 

## **5.4 Módulo: Dashboard Principal**

Pantalla de inicio que muestra el estado general del inventario de forma visual e inmediata.

 

| Widget | Descripción |
| :---- | :---- |
| Tarjetas KPI | Total insumos activos | Stock CRÍTICO | Stock BAJO | Vencimientos próximos |
| Tabla Stock Crítico | Top 15 insumos en estado CRÍTICO ordenados por déficit respecto al mínimo |
| Tabla Vencimientos | Lotes próximos a vencer (fuente: v\_alertas\_vencimiento), ordenados por días restantes |
| Actividad Reciente | Últimos 10 movimientos registrados (fecha, concepto, usuario) |

 

# **6\. Estándares de Desarrollo PHP**

## **6.1 Capa de Base de Datos (Database.php)**

* Singleton PDO con atributos: ERRMODE\_EXCEPTION, DEFAULT\_FETCH\_MODE FETCH\_ASSOC, EMULATE\_PREPARES false.

* Charset utf8mb4 forzado en DSN y en SET NAMES.

* Todos los queries usan Prepared Statements con parámetros nombrados (:param).

* Las transacciones se gestionan exclusivamente en los modelos, no en los controladores.

 

## **6.2 Modelos**

* Una clase por tabla principal. Hereda de BaseModel con métodos findAll($filters, $limit, $offset), findById($id), create($data), update($id, $data), softDelete($id).

* Los métodos de consulta devuelven arrays asociativos o null; nunca lanzan HTML de error.

* La lógica de negocio específica va en métodos propios del modelo (ej. MovimientoModel::registrar($cabecera, $detalles)).

 

## **6.3 Controladores**

* Reciben la petición HTTP, validan el método (GET/POST), llaman al modelo y redirigen o renderan la vista.

* No contienen lógica SQL. No contienen HTML.

* Usan un Validator helper para centralizar las reglas de validación.

* Responden JSON cuando el cliente envía cabecera Accept: application/json (para peticiones AJAX).

 

## **6.4 Seguridad**

* CSRF: token en sesión, verificado en cada POST antes de procesar. Token rotado tras cada uso.

* XSS: todo output a HTML pasa por htmlspecialchars($var, ENT\_QUOTES, 'UTF-8').

* SQL Injection: 100% prepared statements; ningún dato de usuario concatenado en queries.

* Sesiones: session\_regenerate\_id(true) tras login; cookie HttpOnly \+ SameSite=Strict.

* Archivos de configuración (.env o config.php) fuera del document root.

 

## **6.5 Funciones Helper Reutilizables**

| Función | Propósito |
| :---- | :---- |
| formatDate($date, $fmt) | Formatea fechas MySQL al formato mostrado en la UI (dd/mm/yyyy) |
| flashMessage($type, $msg) | Almacena mensaje de éxito/error en sesión para la siguiente vista |
| getFlash() | Recupera y elimina el flash message de la sesión |
| sanitize($input) | trim \+ strip\_tags \+ htmlspecialchars para entradas de texto |
| paginateQuery($total, $page, $limit) | Calcula offset y metadatos de paginación (página actual, total páginas) |
| stockBadge($existencia, $minimos) | Devuelve clase CSS del badge: ok / bajo / critico |
| redirect($url) | header('Location: ...') \+ exit() |
| csrfField() | Genera y retorna campo hidden HTML con token CSRF |
| verifyCsrf() | Verifica token POST contra sesión; aborta con 403 si inválido |

 

# **7\. Directrices de Experiencia de Usuario (UX)**

## **7.1 Principios Generales**

* Eficiencia clínica: el personal de clínica debe completar un registro de movimiento en menos de 60 segundos.

* Feedback inmediato: toda acción exitosa o fallida muestra un mensaje claro y visible.

* Consistencia: todos los módulos de catálogo comparten la misma estructura de pantallas y patrones de interacción.

* Accesibilidad: contraste mínimo AA (WCAG 2.1). Navegación completa por teclado. Etiquetas en todos los campos de formulario.

 

## **7.2 Componentes de UI Estándar**

| Componente | Comportamiento esperado |
| :---- | :---- |
| DataTable | Paginación servidor. Búsqueda con debounce 300ms. Filas alternas en xlight. Hover en light. |
| Modal de confirmación | Para acciones destructivas (inactivar). Requiere confirmación explícita del usuario. |
| Flash Message | Banner superior auto-cerrable en 4s. Verde éxito, rojo error, ámbar advertencia. |
| Breadcrumb | Contexto de navegación en todas las pantallas secundarias. |
| Sidebar nav | Fijo en desktop, colapsable en mobile. Ítem activo resaltado en color primario. |
| Loading state | Spinner en botones durante peticiones AJAX. Botón deshabilitado para evitar doble envío. |

 

# **8\. Hoja de Ruta y Escalabilidad**

La arquitectura modular y el esquema de base de datos están diseñados para soportar los siguientes módulos en fases posteriores sin requerir refactorizaciones mayores:

 

| Fase | Módulo | Descripción |
| :---- | :---- | :---- |
| **v1.0** | Inventario Base | Catálogos, Insumos, Códigos de Barra, Movimientos, Dashboard |
| v1.1 | Reportes | Reporte de stock, historial exportable a Excel/PDF, alertas por email |
| v1.2 | Proveedores | Catálogo de proveedores vinculado a movimientos de compra |
| v1.3 | Usuarios y Roles | Control de acceso por rol (Admin, Farmacia, Enfermería, Consulta) |
| v2.0 | Punto de Venta | Venta directa a paciente con integración al inventario y facturación |
| v2.1 | API REST | Endpoints JSON para integración con sistemas externos o app móvil |

 

# **9\. Consideraciones Finales y Convenciones**

* Idioma del código: inglés para variables, métodos y clases; español para comentarios de negocio.

* Encoding: UTF-8 en todos los archivos PHP, HTML y en la conexión MySQL (utf8mb4).

* Logging: registrar en archivo de log (no en pantalla) toda excepción PDO y error de validación de negocio en producción.

* Versionado: Git con ramas por módulo (feature/modulo-movimientos) y commits semánticos (feat:, fix:, refactor:).

* Documentación inline: bloques PHPDoc en todas las clases y métodos públicos.

* No se usarán frameworks (Laravel, Symfony, CodeIgniter). El valor diferencial es el código PHP nativo limpio y bien estructurado.

* Las variables de entorno (host BD, credenciales) deben gestionarse con un archivo .env o config.php fuera del document root, nunca hardcodeadas.

 

 

— Fin del Documento de Especificación v1.0 —  
SER Inventarios · Clínica de Medicina Alternativa · Febrero 2026