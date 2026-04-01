# Лендинг «Международные партнёры»

Главная с карточками партнёрств, страница карточки, админка (логин: `admin`, пароль: `password`).

---

## Клонировал проект — что делать

Зайди в папку с проектом: если клонировал репозиторий — папка будет `landing`; если проект у тебя в подпапке (например в `landing_sku`), то папка проекта — `yii3`.

| Шаг | Где выполнять | Команда |
|-----|----------------|---------|
| 1. Клонировать | Любая папка (например `Desktop`) | `git clone https://github.com/TimQRB/landing.git` |
| 2. Зайти в проект | — | `cd landing` (после клона) или `cd yii3` (если проект внутри родительской папки) |
| 3. Поставить зависимости PHP | Внутри папки проекта | `composer install` |
| 4. Запустить сайт (Docker) | Внутри папки проекта | `docker compose -f docker/compose.yml -f docker/dev/compose.yml up -d --build` |

После этого открыть в браузере: **http://localhost**. Админка: http://localhost/admin/login (`admin` / `password`).

Остановить: в папке проекта выполнить  
`docker compose -f docker/compose.yml -f docker/dev/compose.yml down`

---

## Запуск (если уже есть клон)

```bash
cd yii3
composer install
docker compose -f docker/compose.yml -f docker/dev/compose.yml up -d --build
```

Сайт: **http://localhost**.

---

## Как запушить изменения (в т.ч. README)

Выполнять из папки проекта (у тебя это `yii3`):

```bash
cd yii3
git add .
git commit -m "описание изменений"
git push origin main
```

Первый раз, если ещё не настроен remote:  
`git remote add origin https://github.com/TimQRB/landing.git`  
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
| **Стили сайта** | `public/css/main.css`, `public/css/simple.css` |
| **Стили админки/формы** | `public/css/admin-form.css` |
| **Логотипы в шапке** | Файлы в `public/uploads/`, вывод через маршрут `/logo/{name}` (`LogoController`) |
| **Загрузка файлов (картинки партнёрств и т.п.)** | Обработка в `AdminController`, сохранение в `public/uploads/`; раздача через `ServeController` или статикой |
| **Параметры приложения, БД** | `config/common/params.php` |
| **Подключение зависимостей (DI)** | `config/common/di/` |

---

## Структура (кратко)

```
yii3/
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
