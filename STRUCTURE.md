# Структура проекта yii3 — лендинг «Международные партнёры»

В проекте только базовый Yii3 и функционал лендинга (заявки на сотрудничество). Всё пишем внутри папки **yii3**.

---

## 1. Исходный код — `src/`

| Назначение | Путь | Namespace |
|------------|------|-----------|
| **Публичный контроллер** | `src/Controller/LandingController.php` | `App\Controller` |
| **Админка** | `src/Controller/Admin/AdminController.php` | `App\Controller\Admin` |
| **Модели** | `src/Model/*.php` | `App\Model` — `Partnership.php`, `User.php` |
| **Сервисы** | `src/Service/AuthService.php` | логин/логаут |
| **Middleware** | `src/Middleware/AuthMiddleware.php` | защита `/admin` |

---

## 2. Шаблоны — `views/`

| Что | Путь | Вызов |
|-----|------|--------|
| **Главная, карточка** | `views/landing/index.php`, `views/landing/card.php` | `'landing/index'`, `'landing/card'` |
| **Админка** | `views/admin/*.php`, `views/admin/partnerships/*.php` | `'admin/login'`, `'admin/dashboard'`, `'admin/partnerships/index'`, `'admin/partnerships/form'` |
| **Layout** | `views/layout/main.php` | задаётся в params как `layout/main` |

---

## 3. Маршруты и конфиг — `config/`

| Файл | Назначение |
|------|------------|
| **config/common/routes.php** | Маршруты: `/`, `/card/{id}`, `/admin/login`, `/admin/*` (partnerships). Группа `/admin` с `AuthMiddleware`. |
| **config/common/params.php** | Параметры приложения, БД, `viewPath`, `layout`. |
| **config/common/di/*.php** | DI: БД, сервисы. |

---

## 4. База данных

- Параметры в **config/common/params.php**: `db.host`, `db.name`, `db.user`, `db.password`, `db.tablePrefix`.
- Схема: **schema/partnership.sql** — таблица `tbl_partnership` (заявки на сотрудничество).
- Для входа в админку используется таблица пользователей (например `tbl_user`), если настроена.

---

## 5. Публичные файлы

- **public/** — `index.php`, статика: `public/css/main.css`, `public/js/main.js`, `public/uploads/` (логотипы, загруженные файлы).

---

## 6. Кратко: куда что писать

| Нужно… | Пишем в… |
|--------|----------|
| Новая страница сайта | `LandingController` или новый контроллер + маршрут в `routes.php` + шаблон в `views/landing/` или `views/` |
| Раздел админки | `AdminController` + маршруты в группе `/admin` + шаблоны в `views/admin/` |
| Работа с БД | `src/Model/` (статические методы) |
| Вход/сессия | `AuthService.php` |
| Защита раздела | `AuthMiddleware` в группе маршрутов |
