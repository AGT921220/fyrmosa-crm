# Fyrmosa CRM

CRM de cotizaciones y pedidos. Laravel 13, Blade y Tabler.

## Requisitos

- Docker y Docker Compose

## Arranque

```bash
docker compose up -d --build
docker compose exec php composer install
docker compose exec php php artisan migrate --seed
```

La aplicación queda en el puerto `PORT_NGINX` (8090 si no lo cambias). phpMyAdmin usa `PORT_PHPMYADMIN` (8091). MySQL en el host usa `PORT_MYSQL` (4310) y Redis `PORT_REDIS` (6379).

El acceso es por nickname. El seeder crea `admin` y `superadmin` con contraseña `admin` si aún no existen. El dueño entra con `superadmin`. No reescribe una contraseña ya guardada.

Horizon atiende la cola y el contenedor `scheduler` ejecuta el programador, incluido el vencimiento diario de cotizaciones.

## Pruebas

```bash
docker compose exec php php artisan test
```

La suite usa SQLite en memoria y no toca MySQL.

## Vencimiento de cotizaciones

```bash
docker compose exec php php artisan crm:expire-quotations
```

El programador lo ejecuta cada día (`routes/console.php`).
