# Журнал поиска и применения документации

Материал для критерия «Использование современных средств поиска, анализа и интерпретации информации и информационных технологий» (4 балла). На экзамене достаточно показать историю браузера/поиска и объяснить, что именно применили.

| Что искали | Источник | Как применено в проекте |
|---|---|---|
| Подготовленные выражения PDO, отключение эмуляции | https://www.php.net/manual/ru/pdo.prepared-statements.php | `app/db.php`: `ATTR_EMULATE_PREPARES => false`; все запросы в `repository.php` |
| Проверка e-mail | https://www.php.net/manual/ru/filter.examples.validation.php | `filter_var($email, FILTER_VALIDATE_EMAIL)` в `validation.php` |
| Хеширование пароля и проверка | https://www.php.net/manual/ru/function.password-hash.php | `password_hash()` при создании админа, `password_verify()` при входе |
| Разбор даты строго по формату | https://www.php.net/manual/ru/datetime.createfromformat.php | `DateTimeImmutable::createFromFormat('!d.m.Y', ...)` + обратная проверка |
| Защита сессии | https://www.php.net/manual/ru/session.security.php | `HttpOnly`, `SameSite=Lax`, `session_regenerate_id(true)` |
| Блокировка строки в транзакции | https://dev.mysql.com/doc/refman/8.0/en/innodb-locking-reads.html | `SELECT ... FOR UPDATE` в `create_booking()` |
| Случайная выборка | https://dev.mysql.com/doc/refman/8.0/en/order-by-optimization.html | `ORDER BY RAND()` для 6 случайных номеров каталога |
| Ограничения CHECK | https://dev.mysql.com/doc/refman/8.0/en/create-table-check-constraints.html | `CHECK (check_out > check_in)` в `schema.sql` |
| Формы и атрибуты полей | https://developer.mozilla.org/ru/docs/Learn/Forms | `inputmode`, `autocomplete`, `aria-describedby` в формах |
| Синтаксис диаграмм | https://plantuml.com/ru/use-case-diagram и https://plantuml.com/ru/class-diagram | `docs/uml-use-case.puml`, `docs/uml-class.puml` |

## Как оформить доказательство на экзамене

1. Держите открытой вкладку с документацией PHP/MySQL.
2. В README или отдельном файле зафиксируйте 5–7 ссылок и одну строку «что применил».
3. Покажите эксперту соответствующий фрагмент кода (например, `ATTR_EMULATE_PREPARES`).
4. Не копируйте код из интернета без понимания: на защите попросят объяснить строку.
