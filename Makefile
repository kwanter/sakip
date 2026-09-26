# SAKIP Docker Makefile
# Convenient commands for Docker operations

.PHONY: help build up down restart logs shell db migrate seed cache clear optimize test-local test-unit-local lint-local gates-local php-version

# Default target
help:
	@echo "SAKIP Docker Commands:"
	@echo ""
	@echo "  make build        - Build Docker images"
	@echo "  make up           - Start all services"
	@echo "  make down         - Stop all services"
	@echo "  make restart      - Restart all services"
	@echo "  make logs         - Show application logs"
	@echo "  make shell        - Open shell in app container"
	@echo "  make artisan      - Run artisan command (CMD='command here')"
	@echo "  make db           - Open MySQL shell"
	@echo "  make migrate      - Run database migrations"
	@echo "  make seed         - Run database seeders"
	@echo "  make fresh        - Fresh install (migrate + seed)"
	@echo "  make cache        - Clear all caches"
	@echo "  make optimize     - Optimize for production"
	@echo "  make test         - Run tests"
	@echo "  make lint         - Run code style check"
	@echo "  make gates-local  - Local quality gates without Docker (pint + test --profile)"
	@echo "  make php-version  - Show the resolved local PHP binary"
	@echo "  make backup       - Backup database"
	@echo "  make restore      - Restore database (FILE=backup.sql)"
	@echo "  make clean        - Remove all containers, volumes, and images"
	@echo "  make ps           - Show running containers"

# Build images
build:
	docker-compose build

# Start services
up:
	docker-compose up -d

# Stop services
down:
	docker-compose down

# Restart services
restart:
	docker-compose restart

# Show logs
logs:
	docker-compose logs -f app

# Open shell
shell:
	docker-compose exec app bash

# Run artisan command
artisan:
	@docker-compose exec app php artisan $(CMD)

# Open MySQL shell
db:
	docker-compose exec mysql mysql -u $(DB_USERNAME) -p$(DB_PASSWORD) $(DB_DATABASE)

# Run migrations
migrate:
	docker-compose exec app php artisan migrate --force

# Run seeders
seed:
	docker-compose exec app php artisan db:seed --force

# Fresh install
fresh: migrate seed

# Clear caches
cache:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# Optimize for production
optimize:
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	docker-compose exec app php artisan optimize

# Run tests
test:
	docker-compose exec app php artisan test

# Run code style check
lint:
	docker-compose exec app ./vendor/bin/pint --test

# Fix code style
fix-style:
	docker-compose exec app ./vendor/bin/pint

# Backup database
backup:
	@docker-compose exec mysql mysqldump -u root -p$(DB_ROOT_PASSWORD) $(DB_DATABASE) > backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "Database backed up to backup_$$(date +%Y%m%d_%H%M%S).sql"

# Restore database
restore:
	@docker-compose exec -T mysql mysql -u root -p$(DB_ROOT_PASSWORD) $(DB_DATABASE) < $(FILE)
	@echo "Database restored from $(FILE)"

# Clean everything
clean:
	docker-compose down -v
	docker system prune -f

# Show running containers
ps:
	docker-compose ps

# Install dependencies
install:
	docker-compose exec app composer install
	docker-compose exec app npm install
	docker-compose exec app npm run build

# Update dependencies
update:
	docker-compose exec app composer update
	docker-compose exec app npm update

# Generate key
key:
	docker-compose exec app php artisan key:generate

# Storage link
storage:
	docker-compose exec app php artisan storage:link

# Queue worker
queue:
	docker-compose exec app php artisan queue:work

# Schedule run
schedule:
	docker-compose exec app php artisan schedule:run

# Monitor queue
queue-monitor:
	docker-compose exec app php artisan queue:monitor

# Telescope install (dev only)
telescope:
	docker-compose exec app php artisan telescope:install
	docker-compose exec app php artisan migrate

# ---------------------------------------------------------------------------
# Local toolchain (no Docker) — resolved PHP binary
# ---------------------------------------------------------------------------
# Non-interactive shells (agent tooling, CI hooks) do not source ~/.zshrc, so `php`
# can be missing from PATH even though the interactive shell resolves it. Every local
# gate below therefore runs through an explicitly resolved binary.
# Recorded in CONSTRAINTS.md §2 (retro 2026-09-26, action A1).
PHP ?= $(shell command -v php 2>/dev/null || echo /opt/homebrew/opt/php@8.3/bin/php)

php-version:
	@$(PHP) -v | head -1

# Local tests (mirrors CONSTRAINTS.md §2); --profile lists the 10 slowest tests (action A3)
test-local:
	$(PHP) artisan config:clear
	$(PHP) artisan test --profile

test-unit-local:
	$(PHP) artisan test --testsuite=Unit

lint-local:
	$(PHP) vendor/bin/pint --test --no-interaction

gates-local: lint-local test-local
