.PHONY: help lint lint-fix format format-fix static-analysis check-all swagger test test-feature test-unit serve

# Цвета для вывода
GREEN  := \033[0;32m
YELLOW := \033[0;33m
NC     := \033[0m # No Color

# Порт для локального сервера (можно переопределить: make serve PORT=9001)
PORT ?= 8000
WORK_BRANCH ?= staging

help: ## Показать все доступные команды
	@echo "$(GREEN)Доступные команды:$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'

format: ## Проверка форматирования кода
	@echo "$(GREEN)Проверка форматирования кода с помощью Laravel Pint...$(NC)"
	@./vendor/bin/pint --test || exit 1

format-fix: ## Автоматическое исправление форматирования
	@echo "$(GREEN)Автоматическое исправление форматирования...$(NC)"
	@./vendor/bin/pint

lint: ## Проверка стиля кода
	@echo "$(GREEN)Проверка стиля кода с помощью PHP CodeSniffer...$(NC)"
	@./vendor/bin/phpcs --standard=phpcs.xml app/ routes/ config/ database/ resources/views/ || exit 1
	@./vendor/bin/phpcs --standard=phpcs.xml --exclude=PSR1.Methods.CamelCapsMethodName,Squiz.NamingConventions.ValidFunctionName tests/ || exit 1

lint-fix: ## Автоматическое исправление стиля кода
	@echo "$(GREEN)Автоматическое исправление стиля кода...$(NC)"
	@./vendor/bin/phpcbf --standard=phpcs.xml app/ routes/ config/ database/ resources/views/
	@./vendor/bin/phpcbf --standard=phpcs.xml --exclude=PSR1.Methods.CamelCapsMethodName,Squiz.NamingConventions.ValidFunctionName tests/

static-analysis: ## Статический анализ кода
	@echo "$(GREEN)Запуск статического анализа кода с помощью PHPStan...$(NC)"
	@php -d memory_limit=512M ./vendor/bin/phpstan analyse || exit 1

check-all: format lint static-analysis ## Запуск всех проверок качества кода
	@echo "$(GREEN)Все проверки пройдены успешно!$(NC)"

swagger: ## Перегенерация Swagger/OpenAPI документации (после изменений в OA-атрибутах)
	@echo "$(GREEN)Генерация Swagger документации...$(NC)"
	@php artisan l5-swagger:generate

test: ## Запуск всех тестов (PHPUnit)
	@echo "$(GREEN)Запуск тестов...$(NC)"
	@php artisan test

test-feature: ## Запуск Feature тестов
	@echo "$(GREEN)Запуск Feature тестов...$(NC)"
	@php artisan test --testsuite=Feature

test-unit: ## Запуск Unit тестов
	@echo "$(GREEN)Запуск Unit тестов...$(NC)"
	@php artisan test --testsuite=Unit

serve: ## Запуск локального сервера (php artisan serve --port=$(PORT))
	@echo "$(GREEN)Запуск локального сервера на порту $(PORT)...$(NC)"
	@php artisan serve --port=$(PORT)

branch-up: ## Подтянуть ветку в текущую ветку (по умолчанию staging, или передай b=main)
	$(eval CURRENT_BRANCH := $(shell git branch --show-current))
	$(eval TARGET_BRANCH := $(if $(b),$(b),$(WORK_BRANCH)))
	@echo "📌 Текущая ветка: $(CURRENT_BRANCH)"
	@echo "🔄 Целевая ветка для слияния: $(TARGET_BRANCH)"
	@git checkout $(TARGET_BRANCH) && git pull origin $(TARGET_BRANCH) && git checkout $(CURRENT_BRANCH) && git merge $(TARGET_BRANCH)
	@echo "✅ Готово. $(TARGET_BRANCH) влит в $(CURRENT_BRANCH). Можно коммитить."

commit: ## Быстрый коммит и пуш (передай m="твое сообщение" или будет автогенерация)
	$(eval COMMIT_MSG := $(if $(m),$(m),commit_from_P$(shell date +%s)))
	@echo "📝 Сообщение коммита: $(COMMIT_MSG)"
	@git add .
	@git commit -m "$(COMMIT_MSG)"
	@git push
	@echo "✅ Изменения закоммичены и запушены!"
	
safe-commit: check-all branch-up commit ## Полная проверка перед коммитом и коммит с пушем
	@echo "✅ Ветка $(CURRENT_BRANCH) успешно обновлена, проверки пройдены успешно и коммит с пушем выполнен успешно!" 