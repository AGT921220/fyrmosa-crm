# Fyrmosa CRM — contexto para agentes

CRM de cotizaciones y pedidos. No es un ERP de ventas, inventario ni facturación.

## Stack

| Pieza | Uso |
| --- | --- |
| PHP | 8.4 FPM en Docker |
| Laravel | 13 |
| Base de datos | MySQL 8.4 (servicio `mysql`). Las pruebas usan SQLite en memoria |
| Colas | Redis y Horizon |
| Tareas | Contenedor `scheduler` con `schedule:work` |
| UI | Blade, Tabler 1.4 y Bootstrap 5 por CDN |
| PDF | `barryvdh/laravel-dompdf` |
| Importes | `brick/math` (`App\Support\Money`), redondeo half up |
| Auth | Sesión de Laravel. Login por nickname |
| Pruebas | PHPUnit. SQLite en memoria |
| Gráficas | Chart.js en el dashboard |

Zona horaria: `America/Mexico_City`. Idioma: español.

## Módulos

- **Clientes.** Catálogo con varios contactos (nombre, apellido, teléfono y correo).
- **Cotizaciones.** Folio, conceptos, estados, seguimiento, versiones, PDF y conversión a pedido. Exigen cliente y un contacto. El formulario muestra la copia del contacto sin editarla.
- **Pedidos.** Alta directa o desde una cotización ganada, entregas parciales, PDF e histórico. Folio `PED-000001`. También exigen cliente y contacto.
- **Dashboard.** Conteos y montos por moneda, con filtros.

## Estados

Cotización: solicitada, cotizada, en negociación, rechazada, ganada, perdida. Rechazada y perdida requieren motivo.

Pedido: borrador, pendiente de confirmación, confirmada, en proceso, parcialmente entregada, completada, cancelada.

## Relaciones

- `Client` 1—N `Contact`, `Quotation` y `Order`.
- `Contact` pertenece a un cliente. La cotización y el pedido apuntan a un contacto y guardan su copia.
- `Quotation` 1—N `QuotationItem`, 1—N `QuotationVersion`, 0—1 `Order`.
- Una versión apunta a `parent_id` y al origen `root_id`.
- `FollowUp`, `ActivityLog` y `Attachment` son polimórficos sobre cotización y pedido (`order`).
- `Order` 1—N ítems, 1—N entregas, y cada entrega 1—N `OrderDeliveryItem`.

## Árbol

```text
app/Enums
app/Exceptions/CrmRuleException.php
app/Features/Shared/Application
app/Features/Quotations/Application
app/Features/Orders/Application
app/Features/Clients/Application/SaveClient.php
app/Features/Access/Application
app/Features/Dashboard/Application/CrmMetrics.php
app/Http/Controllers
app/Http/Requests
app/Models
app/Policies
app/Support/Money.php
app/Support/Folio.php
app/Support/RolePermissions.php
database/seeders/PermissionSeeder.php
app/Console/Commands/ExpireQuotationsCommand.php
database/migrations
resources/views
public/js/line-items.js
public/js/party.js
routes/web.php
routes/console.php
docker-compose.yml
Dockerfile
docker/nginx
docker/php
tests/Feature
tests/Unit/MoneyTest.php
```

## Roles

| Rol | Alcance |
| --- | --- |
| superadmin | Todas las acciones, usuarios y roles. Pasa cualquier permiso |
| admin | Todas las acciones de negocio. No administra usuarios ni roles |
| vendedor | Clientes, cotizaciones y alta de pedidos |
| facturacion | Ver clientes y cotizaciones. Operar pedidos, entregas y su seguimiento |

El panel `/users` y `/roles` asigna el rol y permisos directos. `users.role` guarda el rol principal, también si se creó en el panel. `PermissionSeeder` restaura los cuatro roles de sistema y no borra los demás. El acceso local del dueño es el nickname `superadmin`, con la misma contraseña que `admin`. El seeder no reescribe una contraseña ya guardada. Ese usuario no aparece en `/users` y nadie puede editarlo ni eliminarlo.

## Glosario

| Término | Significado |
| --- | --- |
| Folio | `COT-000001` o `PED-000001`. Una versión posterior usa el mismo consecutivo: `COT-000001-V2` |
| Subtotal | Suma de conceptos después del descuento y antes del impuesto |
| Instantánea | JSON en `quotation_versions` al aprobar o al crear otra versión |
| Conversión | Copia de la cotización ganada a un pedido propio |
| Eliminación | Soft delete de cotización o pedido; solo admin; motivo obligatorio |

## Fuera de alcance

Marketing, leads, inventario, facturación electrónica, contabilidad, nómina y envío real de correo o WhatsApp. El seguimiento solo registra la actividad.
