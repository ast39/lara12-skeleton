# Rest API Skeleton

Rest API Skeleton for Laravel

## Требования

- **PHP** ≥ 8.2 (расширения: mbstring, tokenizer, xml, pdo, json, openssl)
- **Composer**
- **Node.js** и **npm** (для фронта/сборки)
- **БД**: PostgreSQL или MySQL
- **Redis** (опционально, для кэша/очередей)

## Развёртывание приложения

### 1. Клонирование и зависимости

```bash
git clone <url-репозитория> skeleton-api
cd skeleton-api
composer install
```

### 2. Окружение

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Отредактируйте `.env`: укажите БД (`DB_*`), при необходимости Redis, S3, L5-Swagger и т.д.

**JWT:** ключ задаётся командой `php artisan jwt:secret` (пишет `JWT_SECRET` в `.env`). Варианты: `--show` — только показать; `--force` — перезаписать без подтверждения. Время жизни токена и окно refresh настраиваются в `.env`: `JWT_TTL` (минуты, по умолчанию 60), `JWT_REFRESH_TTL` (минуты, по умолчанию 20160).

### 3. База данных

```bash
php artisan migrate
```

При необходимости:

```bash
php artisan db:seed
```

### 4. Фронт и сборка (если нужны)

```bash
npm install
npm run build
```

Для разработки с hot-reload:

```bash
npm run dev
```

### 5. Запуск

Локально:

```bash
php artisan serve
# или на порту: php artisan serve --port=8002
```

Через очередь (если используется):

```bash
php artisan queue:work
```

### 6. Дополнительно

**Swagger (OpenAPI):**
- Первый раз или после изменений в OA-атрибутах контроллеров/ресурсов перегенерируйте документацию: `make swagger` или `php artisan l5-swagger:generate`.
- UI доступен по адресу из конфига (например `/api/documentation`).

**Продакшен:**
- Кэш конфига: `php artisan config:cache`, `php artisan route:cache`.

---

## Перед коммитом

Перед каждым коммитом нужно прогнать проверки качества кода.

**Минимум — одна команда:**

```bash
make check-all
```

Она по очереди выполняет:

- `make lint` — проверка стиля (PHP CodeSniffer, PSR-12)
- `make format` — проверка форматирования (Laravel Pint)
- `make static-analysis` — статический анализ (PHPStan + Larastan)

Если что-то падает по стилю/форматированию, сначала автоисправьте:

```bash
make format-fix
make lint-fix
```

Затем снова:

```bash
make check-all
```

Коммитить имеет смысл только когда `make check-all` завершается без ошибок.

Подробнее про инструменты и порядок действий — в [PRE_COMMIT.md](PRE_COMMIT.md).
