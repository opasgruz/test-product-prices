install:
	@if [ ! -f .env ]; then cp .env.example .env; fi
	@if [ ! -f src/.env ]; then cp src/.env.example src/.env; fi
	docker compose up -d --build
	docker compose exec app composer install
	docker compose exec app php artisan key:generate

	# Создаем структуру папок внутри storage, если их нет
	docker compose exec app mkdir -p storage/framework/cache/data
	docker compose exec app mkdir -p storage/framework/sessions
	docker compose exec app mkdir -p storage/framework/views
	docker compose exec app mkdir -p storage/app/public/reports

	docker compose exec app php artisan storage:link

	# Выставляем права (используем www:www из вашего конфига)
	docker compose exec --user root app chown -R www:www storage bootstrap/cache
	docker compose exec --user root app chmod -R 775 storage bootstrap/cache

	@make fresh
	docker compose exec app php artisan db:seed --class=TestDataSeeder
	docker compose exec app npm install

	@make down
	@make up
	@echo "Развертывание завершено. Проект доступен на http://localhost:8080/reports"
up:
	docker compose up -d
build:
	docker compose build
laravel-install:
	# Сначала создаем проект (это создаст файлы проекта в текущей папке)
	docker compose exec app composer create-project --prefer-dist laravel/laravel .
	# Теперь, когда composer.json существует, настраиваем конфиг
	docker compose exec app composer config process-timeout 300
create-project:
	mkdir -p src
	rm -rf src/* src/.* 2>/dev/null || true
	@make build
	@make up
	@make laravel-install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan storage:link
	# Убеждаемся, что права выставлены ПРАВИЛЬНО после создания всех файлов
	docker compose exec --user root app chown -R www:www /var/www/html
	docker compose exec app npm install
init:
	docker compose up -d --build
	docker compose exec app composer install
	docker compose exec app cp .env.example .env
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan storage:link
	docker compose exec --user root app chown -R www:www storage bootstrap/cache
	docker compose exec app npm install
	@make fresh
remake:
	@make destroy
	@make init
stop:
	docker compose stop
down:
	docker compose down --remove-orphans
down-v:
	docker compose down --remove-orphans --volumes
restart:
	@make down
	@make up
destroy:
	docker compose down --rmi all --volumes --remove-orphans
ps:
	docker compose ps
logs:
	docker compose logs
logs-watch:
	docker compose logs --follow
log-web:
	docker compose logs web
log-web-watch:
	docker compose logs --follow web
log-app:
	docker compose logs app
log-app-watch:
	docker compose logs --follow app
log-db:
	docker compose logs postgres
log-db-watch:
	docker compose logs --follow postgres
web:
	docker compose exec web bash
app:
	docker compose exec app bash
migrate:
	docker compose exec app php artisan migrate
fresh:
	docker compose exec app php artisan migrate:fresh --seed
seed:
	docker compose exec app php artisan db:seed
dacapo:
	docker compose exec app php artisan dacapo
rollback-test:
	docker compose exec app php artisan migrate:fresh
	docker compose exec app php artisan migrate:refresh
tinker:
	docker compose exec app php artisan tinker
test:
	docker compose exec app php artisan test
optimize:
	docker compose exec app php artisan optimize
optimize-clear:
	docker compose exec app php artisan optimize:clear
cache:
	docker compose exec app composer dump-autoload -o
	@make optimize
	docker compose exec app php artisan event:cache
	docker compose exec app php artisan view:cache
cache-clear:
	docker compose exec app composer clear-cache
	@make optimize-clear
	docker compose exec app php artisan event:clear
dump-autoload:
	docker compose exec app composer dump-autoload
ide-helper:
	docker compose exec app php artisan clear-compiled
	docker compose exec app php artisan ide-helper:generate
	docker compose exec app php artisan ide-helper:meta
	docker compose exec app php artisan ide-helper:models --nowrite
seed-test-data:
	docker compose exec app php artisan seed-test-data
