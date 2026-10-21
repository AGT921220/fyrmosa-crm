# Decisiones de arquitectura

## ADR-001 — Casos de uso en Features, Eloquent directo

El negocio vive en clases invocables de `app/Features`. El controlador valida y delega. No hay repositorios ni interfaces de persistencia: Eloquent es el acceso a datos de este proyecto.

## ADR-002 — Importes con brick/math

`App\Support\Money` redondea half up con `Brick\Math`. La librería ya llega con Laravel. No se usa `float` para guardar totales. El navegador imita el redondeo solo para mostrar.

## ADR-003 — Monedas separadas

No hay tipo de cambio. Dashboard, listados y PDF muestran MXN y USD por separado.

## ADR-004 — Copia al convertir

El pedido guarda sus conceptos e importes. La cotización puede versionarse después sin cambiar el pedido. `quotation_id` único evita un segundo pedido de la misma cotización, y el caso de uso revisa toda la cadena de versiones.

## ADR-005 — Histórico append-only

Cambios de estado, precios, condiciones, seguimientos, versiones y entregas se agregan a `activity_logs`. Las instantáneas quedan en `quotation_versions`. Esos registros no se editan ni se borran con la operación.

## ADR-006 — Permisos con Spatie

Cada acción tiene un permiso. Los roles de sistema son superadmin, admin, vendedor y facturación. `users.role` es el rol principal, de sistema o creado en el panel. Al guardar el usuario, ese nombre se sincroniza con Spatie y los permisos directos se conservan. `RolePermissions` es el mapa inicial y `PermissionSeeder` lo vuelve a aplicar solo a esos cuatro. Superadmin pasa en `Gate::before`. Admin no recibe `users.*` ni `roles.*`. El acceso es por `nickname`. El seeder crea el nickname `superadmin` con la misma contraseña que `admin` si no existe, y no reescribe una contraseña ya guardada.

## ADR-007 — PDF en servidor

Dompdf genera el PDF desde Blade con CSS embebido. No depende del navegador del usuario.

## ADR-008 — UI Tabler por CDN

El panel usa Tabler y Bootstrap 5. No hay Tailwind de producto ni bundler obligatorio para operar el CRM. El cálculo de conceptos está en `public/js/line-items.js`.

## ADR-010 — Docker para el entorno local

Nginx, PHP-FPM 8.4, Horizon, el scheduler, MySQL 8.4, Redis y phpMyAdmin viven en `docker-compose.yml`. Los nombres de contenedor usan el sufijo `-crm` y el puerto web por defecto es 8090, porque 8080 ya lo usa otro stack de esta máquina. La aplicación habla con los hosts `mysql` y `redis`. En local Nginx sirve HTTP y Laravel no fuerza el esquema. En producción `URL::forceScheme('https')` genera enlaces HTTPS. Las pruebas no entran a esos servicios.

## ADR-011 — Pedidos y contactos

El módulo de órdenes de compra se llama pedidos: tabla `orders`, modelo `Order`, folio `PED`. Un cliente tiene varios contactos. Cotización y pedido exigen `client_id` y `contact_id`. Al guardar se copia el cliente y el contacto; el formulario no deja editar esa copia.

## ADR-009 — Folios con bloqueo

`folio_sequences` se incrementa con `lockForUpdate` dentro de la transacción que crea el documento. Una versión nueva reutiliza el consecutivo y cambia el sufijo.
