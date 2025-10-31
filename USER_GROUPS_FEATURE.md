# User Groups for Bank Payments

|KB Описание: В системе добавлены группы пользователей и отдельная таблица привязок пользователя к группе. Это используется для сценариев проведения платежей в банк, где поведение может зависеть от принадлежности пользователя к определенным группам.

## Схема данных
- Таблица `user_groups`:
  - id, name (unique), code (unique), description, is_active, timestamps
- Таблица `user_group_user` (pivot):
  - id, user_id (FK users), user_group_id (FK user_groups), assigned_by (FK users, nullable), comment, timestamps
  - Уникальность пары (user_id, user_group_id)

## Модели
- `App\\Models\\UserGroup` — связь `users()` через pivot с полями `assigned_by`, `comment`.
- `App\\Models\\User` — связь `userGroups()` через pivot.

## Контроллеры
- `App\\Http\\Controllers\\UserGroupController`
  - index — список групп
  - create/store — создание
  - edit/update — редактирование
  - destroy — удаление
  - assignForm/assignStore — назначение пользователей в группы (sync)
  - detachUser — исключение пользователя из группы

## Маршруты (Admin)
Префикс: `/admin`, middleware: `auth`, `role:admin`.
- GET `/user-groups` — список
- GET `/user-groups/create` — форма создания
- POST `/user-groups` — сохранить
- GET `/user-groups/{userGroup}/edit` — форма редактирования
- PUT `/user-groups/{userGroup}` — сохранить изменения
- DELETE `/user-groups/{userGroup}` — удалить
- GET `/user-groups/{userGroup}/assign` — форма назначения пользователей
- POST `/user-groups/{userGroup}/assign` — сохранить состав группы
- DELETE `/user-groups/{userGroup}/users/{user}` — удалить пользователя из группы

## Представления (Blade)
- `resources/views/admin/user-groups/index.blade.php`
- `resources/views/admin/user-groups/create.blade.php`
- `resources/views/admin/user-groups/edit.blade.php`
- `resources/views/admin/user-groups/assign.blade.php`

## Использование в бизнес-логике
- Проверить принадлежность пользователя к группе: `auth()->user()->userGroups()->where('code', 'VIP')->exists()`.
- На этапе проведения платежа в банк можно влиять на комиссии/лимиты/маршруты по признаку группы пользователя.

## Комментарии |KB
Все ключевые изменения помечены комментариями, начинающимися с `|KB`, для удобной навигации по коду.
