# Лендинг «Международные партнёры»

Главная с карточками партнёрств, страница карточки, админка (логин: `admin`, пароль: `password`).

---

## Клонировал проект — что делать

Зайди в папку с проектом (например `landing_sku` — как у тебя названа папка после клона).

| Шаг | Где выполнять | Команда |
|-----|----------------|---------|
| 1. Клонировать | Любая папка (например `Desktop`) | `git clone https://github.com/TimQRB/partners.git` |
| 2. Зайти в проект | — | `cd partners` или `cd landing_sku` (как назвали папку) |
| 3. Поставить зависимости PHP | Внутри папки проекта | `composer install` |
| 4. Запустить сайт (Docker) | Внутри папки проекта | `docker compose -f docker/compose.yml -f docker/dev/compose.yml up -d --build` |

После этого открыть в браузере: **http://localhost**. Админка: http://localhost/admin/login (`admin` / `password`).

Остановить: в папке проекта выполнить  
`docker compose -f docker/compose.yml -f docker/dev/compose.yml down`

---

## Запуск (если уже есть клон)

```bash
cd landing_sku
composer install
docker compose -f docker/compose.yml -f docker/dev/compose.yml up -d --build
```

Сайт: **http://localhost**.

---

## Как запушить изменения (в т.ч. README)

Выполнять из папки проекта:

```bash
cd landing_sku
git add .
git commit -m "описание изменений"
git push origin main
```

Первый раз, если ещё не настроен remote:  
`git remote add origin https://github.com/TimQRB/partners.git`  
затем `git push -u origin main`.

---

## Куда что писать

| Нужно сделать | Куда писать |
|---------------|-------------|
| **Новая страница на сайте** | Контроллер в `src/Controller/` (или метод в `LandingController`) → маршрут в `config/common/routes.php` → шаблон в `views/landing/` или `views/` |
| **Новая страница в админке** | Метод в `src/Controller/Admin/AdminController.php` → маршрут в `config/common/routes.php` внутри группы `Group::create('/admin')` → шаблон в `views/admin/` |
| **Новая таблица или поле в БД** | Docker/MySQL: `schema/partnership.sql`. PostgreSQL-схема: `schema/partnership.pgsql.sql` (legacy). Данные — в `src/Model/` |
| **Логика работы с БД** | `src/Model/Partnership.php` или новый класс в `src/Model/` |
| **Вход/выход, сессия** | `src/Service/AuthService.php` |
| **Защита раздела (только для авторизованных)** | В `config/common/routes.php` добавить маршрут в группу с `->middleware(AuthMiddleware::class)` |
| **Общий вид страниц (шапка, футер)** | `views/layout/main.php` |
| **Стили сайта** | `public/css/design-system.css`, `public/css/landing.css` |
| **Стили админки/формы** | `public/css/admin.css`, `public/css/admin-form.css` |
| **Логотипы в шапке** | Файлы в `public/uploads/`, вывод через маршрут `/logo/{name}` (`LogoController`) |
| **Загрузка файлов (картинки партнёрств и т.п.)** | Обработка в `AdminController`, сохранение в `public/uploads/`; раздача через `ServeController` или статикой |
| **Параметры приложения, БД** | `config/common/params.php` |
| **Подключение зависимостей (DI)** | `config/common/di/` |

---

## Структура (кратко)

```
landing_sku/
├── config/common/routes.php   # Все маршруты
├── config/common/params.php   # БД, layout, параметры view
├── schema/partnership.sql        # Схема для MySQL (Docker)
├── schema/partnership.pgsql.sql  # Легаси Postgres
├── public/                    # index.php, css/, js/, uploads/
├── src/
│   ├── Controller/            # Страницы: Landing, Admin, Logo, Serve
│   ├── Model/                 # Partnership, User — работа с БД
│   ├── Service/AuthService.php
│   └── Middleware/AuthMiddleware.php
└── views/
    ├── layout/main.php        # Общий шаблон
    ├── landing/               # Главная, карточка
    └── admin/                 # Логин, список блоков, форма
```

---

## Маршруты

| URL | Описание |
|-----|----------|
| `/` | Главная |
| `/card/{id}` | Карточка партнёрства |
| `/admin/login` | Вход |
| `/admin/partnerships` | Список блоков (нужен вход) |
| `/admin/partnerships/create` | Создать блок |
| `/admin/partnerships/{id}/edit` | Редактировать блок |
