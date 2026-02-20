---
trigger: always_on
---

# Documento de Buenas Prácticas

## Desarrollo de Módulo de Inventarios en PHP

---

## 1. Objetivo

Establecer lineamientos técnicos, arquitectónicos y operativos para el desarrollo de un módulo de inventarios en PHP que sea:

* Seguro
* Escalable
* Mantenible
* Performante
* Fácil de auditar

Este documento aplica para cualquier desarrollador asignado al proyecto.

---

# 2. Arquitectura y Organización del Proyecto

## 2.1 Principios Generales

* Aplicar principios SOLID.
* Seguir patrón MVC (o arquitectura limpia si aplica).
* Separar claramente:

  * Lógica de negocio
  * Acceso a datos
  * Presentación
  * Integraciones externas




Principios Funcionales

* Todo movimiento debe generar trazabilidad.
* No permitir modificaciones directas de stock sin registrar movimiento.
* El stock se calcula desde movimientos (modelo auditable).

---

# 3. Base de Datos

## 3.1 Buenas Prácticas

* Usar InnoDB.
* Definir claves foráneas.
* Usar índices en:

  * product_id
  * warehouse_id
  * movement_date
* No usar SELECT * en producción.

## 3.2 Transacciones

Todo proceso que afecte inventario debe usar transacciones:

```php
DB::beginTransaction();
try {
    // lógica
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    throw $e;
}
```

## 3.3 Concurrencia

* Usar bloqueo optimista o pesimista según el caso.
* Evitar condiciones de carrera.
* Considerar:

  * SELECT ... FOR UPDATE

---

# 4. Seguridad

## 4.1 Validación

* Validar siempre en backend.
* No confiar en validaciones frontend.
* Usar DTOs o Form Requests.

## 4.2 Inyección SQL

* Usar consultas preparadas.
* No concatenar variables en queries.

---

# 5. Código y Estándares

## 5.1 Convenciones

* PSR-12.
* Nombres descriptivos.
* Métodos cortos y específicos.



Principios Finales

Un módulo de inventarios:

* Debe ser auditable
* Debe ser consistente
* No debe perder datos
* Debe prevenir inconsistencias
* Debe ser predecible ante concurrencia

