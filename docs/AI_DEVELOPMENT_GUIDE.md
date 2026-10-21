# Guía de desarrollo

Recetas de este repositorio. Las clases citadas son las de referencia.

## Cotización

1. FormRequest `StoreQuotationRequest` / `UpdateQuotationRequest` con `QuotationRules`.
2. Caso de uso `CreateQuotation` o `UpdateQuotation`.
3. `CalculateDocumentTotals` recalcula líneas. No guardes el total que manda el navegador.
4. Vista `resources/views/quotations/_form.blade.php`.
5. Prueba en `tests/Feature/QuotationTest.php`.

## Estado

1. `ChangeQuotationStatusRequest` elige el permiso según el estado destino.
2. `ChangeQuotationStatus` rechaza transiciones fuera de `QuotationStatus::transitions()`.
3. Rechazo y cancelación exigen comentario.
4. Aprobar guarda instantánea con `SnapshotQuotation`.

## Versión

`CreateQuotationVersion` copia la cotización, conserva el `sequence_number`, sube `version_number` y deja la nueva en borrador. No crea versión si la cadena ya tiene un borrador.

## Conversión

`ConvertQuotationToOrder` exige estado aprobada, bloquea si otra versión de la cadena ya tiene pedido y copia importes guardados, sin recalcular.

## Cliente y contactos

`SaveClient` guarda el cliente y sincroniza la lista `contacts`. No elimina un contacto que ya está en una cotización o un pedido.

## Pedido y entrega

1. Alta directa: `CreateOrder` (borrador).
2. Estado: `ChangeOrderStatus`.
3. Entrega: `RegisterDelivery`. Compara la cantidad contra `quantity - quantity_delivered`.
4. Referencia de prueba: `tests/Feature/OrderTest.php`.

## Permiso nuevo

1. Caso en `App\Enums\Permission`, con `label()` y grupo.
2. Fila en `RolePermissions` para los roles de sistema que deban traerlo, y `php artisan db:seed --class=PermissionSeeder`.
3. Método en la policy o `authorize()` del FormRequest. El controlador no decide el permiso por su cuenta si ya lo cubre el request.
4. El panel de superadmin lista el enum; no inventes un permiso que ninguna acción consulte.
5. Actualiza `.cursor/rules/04-permissions.mdc` si el mapa cambió.

## Usuarios y roles

1. Usuario: `StoreUserRequest` / `UpdateUserRequest` y `SaveUser`. El rol principal queda en `users.role`.
2. Rol: `StoreRoleRequest` / `UpdateRoleRequest` y `SaveRole`. Un rol de sistema no cambia de nombre.
3. `DeleteUser` y `DeleteRole` rechazan borrar al último superadmin o un rol de sistema.
4. Vistas en `resources/views/users` y `resources/views/roles`. El nav solo muestra el enlace con permiso.
5. Prueba en `tests/Feature/AccessTest.php`.

## Listado

`Quotation::filtered()` y `Order::filtered()` aplican búsqueda, filtros y orden. El controlador pagina 15. La vista es una tabla Blade.

## PDF

`QuotationController::pdf` y `OrderController::pdf`. `?download=1` descarga. Sin ese parámetro, el navegador lo muestra.

## Seguimiento

`AddFollowUp` crea el registro, actualiza `next_follow_up_at` del documento y escribe en `activity_logs`.

## Adjunto

`StoreAttachment` guarda el archivo en el disco `local` y registra la actividad. La descarga pasa por `AttachmentController` y exige permiso de ver el documento padre.

## Dashboard

`DashboardController` valida filtros y llama a `CrmMetrics`. No mezcles monedas en una suma nueva.

## Prueba

```bash
docker compose exec php php artisan test
```

`phpunit.xml` fuerza SQLite en memoria. No hace falta MySQL para la suite.
