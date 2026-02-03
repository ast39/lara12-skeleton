# Инструменты проверки качества кода

В проекте настроены следующие инструменты:

## Установленные инструменты

- **PHPStan + Larastan** — статический анализ (level 5), знает Laravel (фасады, Eloquent, контракты).
- **PHP CodeSniffer** — проверка стиля кода (PSR-12).
- **Laravel Pint** — форматирование кода (пресет PSR-12).

## Конфигурационные файлы

- `phpstan.neon` — PHPStan + Larastan
- `phpcs.xml` — PHP CodeSniffer
- `pint.json` — Laravel Pint
- `Makefile` — команды для запуска

## Команды Make

```bash
make help           # Список всех команд
make lint           # Проверка стиля (PHPCS)
make lint-fix       # Автоисправление стиля (phpcbf)
make format         # Проверка форматирования (Pint)
make format-fix     # Автоисправление форматирования (Pint)
make static-analysis # PHPStan + Larastan
make check-all      # lint + format + static-analysis
```

## Перед коммитом

**Рекомендуемый порядок:**

1. Автоисправления (если что-то сломалось по стилю):
   ```bash
   make format-fix
   make lint-fix
   ```

2. Полная проверка:
   ```bash
   make check-all
   ```

Если `check-all` прошёл без ошибок — можно коммитить.

**Одна команда вместо трёх:**

```bash
make check-all
```

Она выполняет по очереди: `make lint`, `make format`, `make static-analysis`. Любая ошибка останавливает выполнение.

## Рекомендации

1. Перед каждым коммитом запускайте `make check-all`.
2. Сначала исправляйте стиль: `make format-fix` и при необходимости `make lint-fix`.
3. Ошибки PHPStan исправляйте в коде; игноры в `phpstan.neon` — только для особенностей фреймворка/архитектуры, не для реальных багов.
