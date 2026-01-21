# logistics-booking-api-laravel

Бронирование мест в слотах для перевозок охлажденных грузов

## Развертывание (Docker)

1) Скопируйте переменные окружения:
`cp .env.example .env`

2) Поднимите окружение:
`docker compose -f docker/docker-compose.yml up -d --build`

Приложение будет доступно на `http://localhost:8000` (контейнер запускает `php artisan serve`).

### Доступ к MySQL

- Пользователь приложения: `logist` / `logist_pass`

## Миграции и сиды

Запуск миграций:
`docker compose -f docker/docker-compose.yml exec app php artisan migrate`

Сидер наполнит slots:
`docker compose -f docker/docker-compose.yml exec app php artisan db:seed`

## Схема защиты от cache stampede

```mermaid
flowchart TD
    A[Запрос] --> B[cache.get(key)]
    B -->|HIT| C[Вернуть кеш]
    B -->|MISS| D[Попытка lock(key)]
    D -->|lock OK| E[double-check cache]
    E -->|HIT| C
    E -->|MISS| F[read DB]
    F --> G[cache.put]
    G --> H[Вернуть]
    D -->|lock FAIL / timeout| I[cache.get(key)]
    I -->|HIT| C
    I -->|MISS| J[Вернуть пусто/стейл]
```

## Схема защиты от oversale в SlotHolderService

```mermaid
flowchart TD
    A[create(slotId, idempotencyKey)] --> B[Cache.get(idempotencyKey)]
    B -->|Hold найден| C[Вернуть Hold]
    B -->|Есть hold_id| D[Hold::find(hold_id)]
    D -->|Найден| C
    D -->|Не найден| E[slotRepository.get(slotId)]
    B -->|Кеш пуст| E
    E -->|Не найден| F[SlotsExceptions::notFound]
    E -->|Найден| G[Проверка capacity]
    G -->|Превышение| H[SlotsExceptions::conflict]
    G -->|Ок| I[holdRepository.create(slotId)]
    I --> J[Cache.put(idempotencyKey, hold)]
    J --> C
```