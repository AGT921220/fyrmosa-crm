include .env

example:
	@echo ${SERVER_PASS};
up:
	@docker compose down;\
	docker-compose up -d
up-dev:
	@docker compose down;\
	docker compose -f docker-compose-dev.yml up -d;
down-dev:
	@docker compose -f docker-compose-dev.yml down;
server-enter:
	@ssh root@${SERVER_IP}

install:
	echo "Docker Exec";\
	docker exec -it php-crm composer install
enter:
	docker exec -it php-crm /bin/bash;
nginx:
	docker exec -it nginx-crm /bin/sh;	
clear:
	@docker exec -it php-crm /bin/bash -c \
	"php artisan optimize:clear && \
	php artisan config:cache && \
	php artisan route:cache && \
	php artisan horizon:terminate && \
	php artisan queue:restart"
ngrok:
	@ngrok http --host-header=rewrite http://localhost:8080;
demo/restart-data:
	@docker exec -it php-crm /bin/bash -c \
	"php artisan migrate:fresh && php artisan permissions:create && php artisan roles:create && php artisan users:create-demo && php artisan drivers:sync-from-chofer-users && php artisan demo:create-data"
# 	@php artisan migrate
# 	@php artisan permissions:create
# 	@php artisan roles:create
# 	@php artisan users:create-demo

import-db:
	@docker exec -i ${DB_HOST} mysql -u user -ppassword -e "DROP DATABASE IF EXISTS ${DB_DATABASE}; CREATE DATABASE ${DB_DATABASE};"
	@docker exec -i ${DB_HOST} mysql -u user -ppassword ${DB_DATABASE} < storage/app/rpbi.sql
	@$(MAKE) update-bulk-passwords

db-export:
	@docker exec -i ${DB_HOST} mysqldump -u ${DB_USERNAME} -p${DB_PASSWORD} --no-tablespaces ${DB_DATABASE} > storage/app/gan.sql

update-bulk-passwords:
	@docker exec -i php-crm php artisan users:update-bulk-passwords --force
