# Структура данных страницы детальной информации о проекте

## Единая дизайн-система

Админ-панель и публичная карточка используют общие стили (`public/css/design-system.css`):

- **Цвета**: акцент `#6ba6ba`, фон страницы `#f4f7f9`, карточки `#ffffff`
- **Шрифты**: Roboto / Open Sans
- **Компоненты**: `border-radius: 12px` для карточек и полей
- **Заголовки секций**: fieldset-стиль (линия с текстом посередине)

---

Данные подтягиваются динамически из таблицы `tbl_partnership`. Для полной поддержки всех блоков выполните миграцию:

```bash
mysql -u root -p yii1_db < schema/project.sql
```

## Сущности и поля

### header (шапка проекта)
| Поле в БД | Описание |
|-----------|----------|
| `org_name` | Название проекта |
| `description` | Текстовое описание |
| `file_path` | Путь к фоновому/превью изображению |

### collaboration_areas (направления сотрудничества)
Источники: `cooperation_directions`, `activity_areas`, `interaction_format` (JSON-массивы).

Ключи для `cooperation_directions`: `research`, `education`, `internships`, `joint_projects`, `commercial`, `grants`, `exchange`.

Ключи для `activity_areas`: `it`, `manufacturing`, `energy`, `medicine`, `education`, `agriculture`, `finance`.

Ключи для `interaction_format`: `joint_research`, `contract_research`, `staff_training`, `joint_lab`, `industrial_projects`, `student_internships`.

### subtasks (подзадачи)
Новое поле `subtasks` (JSON): массив строк.

```json
["Подзадача 1", "Подзадача 2", "Подзадача 3"]
```

### goals (цели проекта)
Новое поле `goals` (JSON): массив строк.

```json
["Цель 1", "Цель 2"]
```

### events (встречи и мероприятия)
Новое поле `events` (JSON): массив объектов.

```json
[
  {"date": "12.05.2024", "title": "Конференция по образованию", "location": "Париж, Франция"},
  {"date": "20.06.2024", "title": "Семинар", "location": "Астана, Казахстан"}
]
```

Поддерживаемые ключи: `date`/`date_event`, `title`/`name`, `location`/`city`/`place`.

## Пустые состояния (Empty states)

Секции автоматически скрываются, если соответствующий блок не заполнен в админ-панели:
- Направления сотрудничества — скрыта при пустых `cooperation_directions`, `activity_areas`, `interaction_format`
- Подзадачи — скрыта при пустом `subtasks`
- Цели — скрыта при пустом `goals`
- Встречи — скрыта при пустом `events`

Блок описания отображается всегда (с плейсхолдером «Описание», если пусто).
