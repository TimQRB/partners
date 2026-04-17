# Лендинг «Международные партнёры»

Yii 3 приложение для витрины партнёрств: публичная часть (главная и карточка), формы подачи, админка и API.

## Быстрый старт

### 1) Клонирование и установка зависимостей

```bash
git clone https://github.com/TimQRB/partners.git
cd partners
composer install
```

### 2) Запуск в Docker

```bash
docker compose -f docker/compose.yml -f docker/dev/compose.yml up -d --build
```

Открыть в браузере:

- Сайт: `http://localhost`
- Вход в админку: `http://localhost/admin/login`
- Логин/пароль по умолчанию: `admin` / `password`

Остановка контейнеров:

```bash
docker compose -f docker/compose.yml -f docker/dev/compose.yml down
```

## Технологии

- `PHP 8.2+`
- `Yii 3`
- `MySQL 8.4` (в Docker)
- `FrankenPHP + Caddy` (в контейнере приложения)

## Полезные команды

```bash
# Запуск тестов
composer test

# Локальный запуск встроенного сервера (без Docker, если окружение подготовлено)
composer serve
```

## Где что менять

| Задача | Файлы/папки |
|---|---|
| Публичные страницы | `src/Controller/LandingController.php`, шаблоны `views/landing/` |
| Админка | `src/Controller/Admin/AdminController.php`, шаблоны `views/admin/` |
| Роутинг | `config/common/routes.php` |
| Работа с БД | `src/Model/` |
| Авторизация | `src/Service/AuthService.php`, `src/Middleware/AuthMiddleware.php` |
| Параметры и DI | `config/common/params.php`, `config/common/di/` |
| Стили | `public/css/` |
| SQL-схемы | `schema/partnership.sql`, `schema/partnership.pgsql.sql` (legacy) |

## Ключевые маршруты

### Публичные

- `GET /` — главная
- `GET /card/{id}` — карточка партнёрства
- `GET /partnerships/create` — форма подачи
- `POST /partnerships/create` — отправка формы
- `GET /logo/{name}` — выдача логотипа

### Админка

- `GET /admin/login` — вход
- `GET /admin/dashboard` — дашборд (требует авторизацию)
- `GET /admin/partnerships` — список заявок/партнёрств
- `GET/POST /admin/partnerships/create` — создание
- `GET/POST /admin/partnerships/{id}/edit` — редактирование
- `POST /admin/partnerships/{id}/approve` — подтверждение
- `POST /admin/partnerships/{id}/priority` — изменение приоритета
- `POST /admin/partnerships/{id}/delete` — удаление

### API

- `GET /api/partnerships` — список
- `GET /api/partnerships/{id}` — просмотр

## Структура проекта

```text
partners/
├── config/common/routes.php      # Роуты приложения
├── config/common/params.php      # Параметры приложения
├── docker/                       # Docker-конфигурация
├── public/                       # Веб-корень: css/js/uploads
├── schema/                       # SQL-схемы
├── src/
│   ├── Controller/
│   ├── Middleware/
│   ├── Model/
│   └── Service/
└── views/                        # Шаблоны страниц
```



привет максим