# Sub-Inventario de Enfermería — Plan de Implementación

## Objetivo

Agregar un módulo de **sub-inventario para el área de Enfermería** que permita gestionar entregas y devoluciones de insumos de tipo `INTE` (Insumo Interno / Clínico) desde el inventario global. Enfermería tendrá su propio stock independiente con alertas, historial de movimientos y recibos imprimibles.

## Reglas de Negocio

1. Solo aplica para insumos con `TipoProducto = 'INTE'` de la tabla `insumos`
2. **Entrega** → Descuenta del inventario global (`insumos.Existencia`) y suma al stock de enfermería
3. **Devolución** → Descuenta del stock de enfermería y suma al inventario global
4. No se puede entregar más de lo disponible en el inventario global
5. No se puede devolver más de lo que tiene enfermería
6. Los niveles de mínimo/máximo se toman de la tabla `insumos` (no se duplican)
7. Las alertas de stock bajo/crítico se calculan comparando el stock de enfermería contra los mismos mínimos de `insumos`
8. Se registra quién entrega y quién recibe

---

## Propuestos Cambios

### Base de Datos

#### [NEW] Migración SQL: `database/migration_enfermeria.sql`

Se crearán **3 tablas nuevas** y **1 vista**:

**Tabla `insumos_enfermeria`** — Stock actual de enfermería por insumo:

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `ID` | INT AUTO_INCREMENT PK | Identificador |
| `InsumoID` | INT UNIQUE FK→insumos.ID | Referencia al insumo global |
| `Existencia` | DECIMAL(12,2) DEFAULT 0 | Stock actual en enfermería |
| `Activo` | TINYINT(1) DEFAULT 1 | Estado |
| `CreatedAt` | DATETIME | Fecha de creación |
| `UpdatedAt` | DATETIME | Última actualización |

**Tabla `entregas_enfermeria`** — Cabecera de cada entrega/devolución:

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `ID` | INT AUTO_INCREMENT PK | Folio de entrega |
| `TipoOperacion` | ENUM('ENTREGA','DEVOLUCION') | Dirección del movimiento |
| `FechaEntrega` | DATETIME DEFAULT NOW | Fecha/hora del movimiento |
| `EntregadoPor` | VARCHAR(100) NOT NULL | Persona que entrega |
| `RecibidoPor` | VARCHAR(100) NOT NULL | Persona que recibe |
| `Observaciones` | TEXT NULL | Notas opcionales |
| `CreatedAt` | DATETIME | Fecha de creación |

**Tabla `entregas_enfermeria_detalle`** — Líneas de detalle por entrega:

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `ID` | INT AUTO_INCREMENT PK | Identificador |
| `EntregaID` | INT FK→entregas_enfermeria.ID | Cabecera |
| `InsumoID` | INT FK→insumos.ID | Insumo entregado |
| `Cantidad` | DECIMAL(12,2) NOT NULL | Cantidad entregada/devuelta |
| `CreatedAt` | DATETIME | Fecha de creación |

**Vista `v_stock_enfermeria`** — Stock actual de enfermería con semáforo:

```sql
SELECT ie.ID, ie.InsumoID, i.Nombre, i.MedidaInsumo,
       m.Descripcion AS MedidaDescripcion,
       ie.Existencia,
       i.Minimos, i.Maximos,
       CASE
           WHEN ie.Existencia <= 0           THEN 'CRITICO'
           WHEN ie.Existencia <= i.Minimos   THEN 'BAJO'
           ELSE 'OK'
       END AS EstadoStock
FROM insumos_enfermeria ie
INNER JOIN insumos i ON ie.InsumoID = i.ID
INNER JOIN medidainsumo m ON i.MedidaInsumo = m.ID
WHERE i.TipoProducto = 'INTE' AND i.Activo = 1 AND ie.Activo = 1;
```

---

### Backend — Modelo

#### [NEW] `app/models/EnfermeriaModel.php`

Modelo principal del módulo con los siguientes métodos:

- **`registrarEntrega(array $cabecera, array $detalles): int`** — Transacción atómica que:
  1. Inserta cabecera en `entregas_enfermeria`
  2. Inserta cada línea de detalle
  3. Actualiza `insumos.Existencia` (resta en ENTREGA, suma en DEVOLUCION)
  4. Actualiza `insumos_enfermeria.Existencia` (suma en ENTREGA, resta en DEVOLUCION)
  5. Crea el registro en `insumos_enfermeria` si no existe (upsert)
  6. Valida stock antes de operar (global para entregas, enfermería para devoluciones)

- **`getStockEnfermeria(filters, limit, offset): array`** — Lista el stock actual de enfermería con filtros (búsqueda, estado de stock)
- **`countStockEnfermeria(filters): int`** — Conteo para paginación
- **`getHistorial(filters, limit, offset): array`** — Historial de entregas/devoluciones
- **`countHistorial(filters): int`** — Conteo para paginación
- **`getDetalleEntrega(int $id): array`** — Líneas de detalle de una entrega
- **`getKPIs(): array`** — KPIs del sub-inventario (total insumos, stock crítico, bajo)
- **`getInsumosDisponibles(): array`** — Lista insumos INTE del inventario global con existencia > 0 para el formulario de entrega

---

### Backend — Controlador

#### [NEW] `app/controllers/InsumosEnfermeriaController.php`

| Acción | Método | Ruta URL | Descripción |
|--------|--------|----------|-------------|
| `index` | GET | `/insumos-enfermeria` | Dashboard de enfermería con KPIs y stock actual |
| `historial` | GET | `/insumos-enfermeria/historial` | Historial de entregas y devoluciones |
| `create` | GET/POST | `/insumos-enfermeria/create` | Formulario de nueva entrega |
| `devolucion` | GET/POST | `/insumos-enfermeria/devolucion` | Formulario de nueva devolución |
| `show` | GET | `/insumos-enfermeria/show/{id}` | Detalle de una entrega/devolución |
| `print` | GET | `/insumos-enfermeria/print/{id}` | Recibo imprimible (HTML sin layout) |
| `searchInsumos` | GET (AJAX) | `/insumos-enfermeria/search-insumos` | Búsqueda AJAX de insumos INTE |

---

### Frontend — Vistas

#### [NEW] `views/enfermeria/index.php` — Dashboard de Enfermería
- 3 KPI cards: Total insumos en enfermería, Stock Crítico, Stock Bajo
- Tabla de stock actual con columnas: Insumo, Medida, Existencia (Enfermería), Mín, Máx, Estado
- Filtros: búsqueda por nombre, estado de stock
- Paginación

#### [NEW] `views/enfermeria/create.php` — Registrar Entrega
- Campos: Entregado por, Recibido por, Observaciones
- Líneas de detalle dinámicas (reutilizando patrón de `movimientos.js`):
  - Búsqueda AJAX de insumos INTE
  - Muestra existencia global disponible
  - Validación de cantidades

#### [NEW] `views/enfermeria/devolucion.php` — Registrar Devolución
- Misma estructura que entrega pero:
  - Busca insumos que enfermería tenga en stock (existencia > 0)
  - Valida contra stock de enfermería

#### [NEW] `views/enfermeria/historial.php` — Historial de Entregas/Devoluciones
- Filtros: fecha desde/hasta, tipo (entrega/devolución)
- Tabla con: ID, Fecha, Tipo, Entregó, Recibió, Líneas, Cantidad Total, Acciones

#### [NEW] `views/enfermeria/show.php` — Detalle de Entrega
- Información de cabecera + tabla de líneas de detalle
- Botón "Imprimir Recibo"

#### [NEW] `views/enfermeria/print.php` — Recibo Imprimible
- HTML autónomo (sin layout master), con CSS `@media print`
- Logo de la empresa
- Datos de la entrega: folio, fecha, tipo
- Tabla de insumos entregados
- Espacios para firma: "Entregó" y "Recibió"

---

### Frontend — JavaScript

#### [NEW] `public/assets/js/enfermeria.js`
- Reutiliza la lógica de `movimientos.js` adaptada:
  - Búsqueda AJAX de insumos INTE solamente
  - Agregar/quitar líneas de detalle
  - Mostrar existencia disponible (global para entregas, enfermería para devoluciones)

---

### Frontend — Sidebar

#### [MODIFY] `views/layouts/master.php`
- Agregar nueva sección **"Enfermería"** en el sidebar entre "Inventario" y "Catálogos":

```php
<div class="sidebar-section">Enfermería</div>
<a href="<?= url('insumos-enfermeria') ?>" class="sidebar-link ...">
    <span class="icon">🏥</span> Insumos Enfermería
</a>
```

---

### Frontend — CSS

#### [MODIFY] `public/assets/css/app.css`
- Agregar estilos para el recibo imprimible (`@media print`)
- Agregar badges para tipos de operación (entrega/devolución)

---

## User Review Required

> [!IMPORTANT]
> **Impacto en inventario global:** Cada entrega a enfermería reducirá automáticamente la `Existencia` de la tabla `insumos`. Cada devolución la incrementará. Esto es consistente con la lógica actual del módulo de Movimientos.

> [!NOTE]
> **Recibo imprimible:** Se generará como una página HTML optimizada para impresión con CSS `@media print`. No requiere librerías externas de PDF.

---

## Flujos de Operación

### Flujo de Entrega
```
Admin selecciona insumos INTE con cantidad
  → Sistema valida: ¿Hay suficiente en inventario global?
  → SI: Registra entrega, descuenta de global, suma a enfermería
  → NO: Error "Stock insuficiente para [Nombre]"
```

### Flujo de Devolución
```
Admin selecciona insumos que enfermería tiene en stock
  → Sistema valida: ¿Tiene enfermería suficiente?
  → SI: Registra devolución, descuenta de enfermería, suma a global
  → NO: Error "Stock insuficiente en enfermería para [Nombre]"
```

---

## Verification Plan

### Automated Tests
1. Importar migración SQL y verificar que las tablas y vista se crean correctamente
2. Navegador: Verificar que el sidebar muestra el nuevo menú
3. Navegador: Crear una entrega de insumos y verificar que el stock global baja y el de enfermería sube
4. Navegador: Crear una devolución y verificar el efecto inverso
5. Navegador: Intentar entregar más de lo disponible → debe mostrar error
6. Navegador: Verificar que el recibo imprimible se genera correctamente
7. Navegador: Verificar KPIs y alertas de stock en el dashboard de enfermería
