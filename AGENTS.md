# Fyrmosa CRM

Reglas cortas para trabajar en este repositorio. El código de este proyecto es la fuente de verdad.

1. Stack: PHP 8.4 en Docker (FPM), Laravel 13, Blade, Tabler (CDN), Bootstrap 5, MySQL 8.4 y Redis. Colas con Horizon. PDF con Dompdf. Importes con `brick/math`. Las pruebas usan SQLite en memoria.
2. Dominio: cotizaciones y pedidos. No hay marketing, inventario, facturación ni contabilidad.
3. No inventes módulos, columnas ni estados. Si no está en el código o en la migración, no existe.
4. Código reciente que cumple estas reglas gana sobre un patrón genérico de Laravel. El esqueleto inicial (welcome, Boost) no es plantilla.
5. No acomplejes. El camino más corto que cumpla el requisito. Sin repositorios, puertos ni capas “por si acaso”.
6. Evita `else`. Usa retorno temprano, `match` y guard clauses.
7. Una clase de `app/Features/{Modulo}/Application` es un caso de uso. No la infles con otro caso.
8. El controlador no calcula importes ni cambia estados. Valida con FormRequest y llama al caso de uso.
9. La persistencia es Eloquent directo. No hay Repository Pattern ni API Resources.
10. Los importes se calculan en el servidor con `App\Support\Money`. El JavaScript de la captura solo muestra el mismo resultado.
11. No sumes MXN y USD. Las métricas van separadas por moneda.
12. Una cotización aprobada, rechazada, vencida o cancelada no se edita. Los cambios comerciales de una enviada se hacen con una versión nueva.
13. El pedido copia importes y conceptos. Un cambio posterior en la cotización no lo altera.
14. Solo un pedido por cadena de versiones. El índice único de `quotation_id` refuerza esa regla.
15. No registres entregas en un pedido cancelado ni cantidades mayores a lo pendiente.
16. El histórico (`activity_logs`, `quotation_versions`, entregas) no se borra al modificar la operación.
17. Los folios salen de `folio_sequences` con `lockForUpdate` dentro de una transacción.
18. Los permisos viven en Spatie (`spatie/laravel-permission`). Cada acción tiene un caso en `Permission`. Los roles de sistema son `superadmin`, `admin`, `vendedor` y `facturacion`. `users.role` guarda el rol principal, de sistema o creado en el panel; al guardar el usuario se sincroniza ese rol de Spatie y se conservan los permisos directos. Superadmin pasa siempre (`Gate::before`). Admin tiene el resto de acciones, sin usuarios ni roles. El panel de accesos exige `users.*` o `roles.*`. `PermissionSeeder` restaura los cuatro roles de sistema y no borra roles creados en el panel. El acceso local del dueño es el nickname `superadmin`, con la misma contraseña que `admin`. El seeder crea esos usuarios si no existen y no reescribe una contraseña ya guardada.
19. Autoriza en FormRequest o Policy. No confíes solo en ocultar el botón.
20. Estados en enums: `QuotationStatus` y `OrderStatus`. No compares strings sueltos si ya tienes el enum.
21. Busca el caso de uso equivalente antes de crear otro: cotización para el flujo comercial, pedido para entregas.
22. Cliente y contacto son obligatorios al crear una cotización o un pedido. El documento guarda una copia de nombre, razón social, contacto, correo y teléfono. El formulario solo muestra esa copia.
23. Seguimientos, adjuntos e histórico son polimórficos (`follow_ups`, `attachments`, `activity_logs`).
24. Vistas en `resources/views`, layout `layouts/app.blade.php`, estilos Tabler. No introduzcas Tailwind, React, Vue ni Livewire en el panel.
25. El listado es una tabla Blade con filtros y paginación Bootstrap. No hace falta DataTables.
26. Pruebas en `tests/Feature` con `RefreshDatabase` y SQLite en memoria. Dentro de Docker: `docker compose exec php php artisan test`.
27. El vencimiento automático es `php artisan crm:expire-quotations`, programado a diario en `routes/console.php`. El contenedor `scheduler` lo dispara con `schedule:work`. Horizon procesa la cola Redis.
28. Actualiza `AGENTS.md`, `.cursor/rules` y `docs/AI_*` solo si cambia arquitectura, dominio, permisos, colas o el contrato de un módulo.
29. No pongas secretos ni valores de `.env` en la documentación.
30. No corras `migrate:fresh` salvo que se pida. No borres datos de histórico para “limpiar”.

## Dónde vive qué

| Qué | Dónde |
| --- | --- |
| Casos de uso | `app/Features/{Quotations,Orders,Clients,Dashboard,Access,Shared}/Application` |
| HTTP | `app/Http/Controllers` |
| Validación | `app/Http/Requests` |
| Permisos | `app/Policies`, `app/Enums/Permission.php`, `RolePermissions`, `database/seeders/PermissionSeeder.php` |
| Modelos | `app/Models` |
| Migraciones | `database/migrations` |
| Vistas | `resources/views/{quotations,orders,dashboard,clients,users,roles}` |
| Cálculo en pantalla | `public/js/line-items.js` y `public/js/party.js` |
| Comando de vencimiento | `app/Console/Commands/ExpireQuotationsCommand.php` |
| Docker | `docker-compose.yml`, `Dockerfile`, `docker/nginx`, `docker/php` |
