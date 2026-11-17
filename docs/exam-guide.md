# Как выполнять ДЭ 09.02.09 (КИМ 09.02.09-2-2027, инвариант)

Практическая шпаргалка по всем четырём заданиям: что рисовать, что писать, в каком порядке и за сколько времени. Код — из этого проекта, пути указаны относительно `hotel-sweet-dreams/`.

## 1. Карта экзамена

| Задание | Что сдаём | Время | Основные баллы |
|---|---|---|---|
| 1 | Диаграмма прецедентов + диаграмма классов | 0:45 | Проектирование — 10 |
| 2 | Прототипы главной, каталога и брони | 0:45 | Интерфейсы — 10 |
| 3 | Сайт: БД, каталог, фильтры, форма, валидация | 1:00 | Серверный код — 41, интеграция — 7 |
| 4 | Календарь занятости + админ-панель | 1:00 | Серверный код, отладка — 2 |
| — | Git, диаграммы, README | в процессе | VCS — 1, поиск — 4 |

Максимум — 75 баллов (ДЭ БУ). 41 балл из 75 — это серверный код, поэтому задание 3 нельзя оставлять «на потом».

## 2. Тайминг на 3 ч 30 мин

| Время | Действие |
|---|---|
| 0:00–0:10 | Скопировать шаблон, создать БД, `git init`, первый коммит |
| 0:10–0:55 | Задание 1: две диаграммы, экспорт PNG/SVG, коммит |
| 0:55–1:40 | Задание 2: три экрана + состояния, экспорт, коммит |
| 1:40–2:40 | Задание 3: схема БД, каталог, фильтры, форма, валидация |
| 2:40–3:20 | Задание 4: календарь, вход админа, список, одобрение/удаление |
| 3:20–3:30 | Сквозной тест, скриншоты, финальный коммит |

Если время горит: сначала сделайте работающий «скелет» (БД + вывод номеров + форма + сохранение), потом красоту. Эксперт начисляет баллы за функции, а не за количество градиентов.

## 3. Задание 1 — диаграммы (45 минут)

### 3.1. Диаграмма прецедентов

Порядок:

1. Выпишите акторов: **Гость**, **Администратор**.
2. Выпишите действия гостя: смотреть каталог, фильтровать, бронировать.
3. Выпишите действия администратора: войти, смотреть заявки, одобрить, отклонить, удалить, управлять номерами.
4. Свяжите действия с акторами.
5. Покажите `<<include>>` для обязательных шагов (бронь включает заполнение формы и проверку дат) и `<<extend>>` для необязательных (фильтр расширяет каталог, одобрение расширяет список заявок).
6. Не рисуйте экраны и кнопки — прецедент это цель пользователя, а не страница.

Готовый исходник: `docs/uml-use-case.puml`. Откройте [plantuml.com](https://plantuml.com/ru/), вставьте код, экспортируйте PNG. В draw.io: **Arrange → Insert → Advanced → PlantUML**.

Типичные ошибки:

- актор «База данных» — это не актор;
- стрелка `include` направлена от включающего к включаемому;
- `extend` направлен от расширяющего к базовому;
- слишком много прецедентов «Открыть страницу» — это не цели.

### 3.2. Диаграмма классов

Минимальный набор сущностей:

- `Category` — категория номера;
- `Room` — номер;
- `Amenity` — удобство;
- `Booking` — заявка;
- `Admin` — администратор;
- `BookingStatus` — перечисление статусов;
- `BookingService` — операции проверки и создания брони.

Обязательно покажите:

- атрибуты с типами (`price : decimal`, `checkIn : date`);
- методы (`isAvailable()`, `approve()`, `validate()`);
- кратности (`Category 1 — 0..* Room`, `Room 1 — 0..* Booking`, `Room * — * Amenity`);
- связь `Booking --> BookingStatus`;
- примечание с инвариантами: `checkOut > checkIn`, нет пересечения одобренных броней.

Готовый исходник: `docs/uml-class.puml`.

## 4. Задание 2 — прототипы (45 минут)

Нужны три основных экрана и их состояния.

### Главная

- шапка: название «Сладкие сны», слоган, переход в каталог;
- первый экран: заголовок, пояснение, кнопка «Смотреть номера»;
- категории: стандарт, студия, люкс;
- подвал: адрес, режим работы, телефон, e-mail.

### Каталог

- карточки: фото, категория, цена, вместимость, площадь, удобства, кнопка «Забронировать»;
- фильтр: категория, цена от/до, количество гостей;
- кнопки «Применить» и «Сбросить фильтр»;
- состояния: обычная случайная подборка, результаты фильтра, «ничего не найдено».

### Бронирование

- карточка выбранного номера;
- календарь на месяц: свободно / занято / прошло;
- поля: имя, фамилия, телефон, e-mail, даты;
- состояния: пустая форма, ошибки валидации, успешная отправка.

### UI/UX-чек-лист

- сетка 12 колонок, отступы 8/16/24;
- контраст текста не ниже 4.5:1;
- кликабельные зоны не меньше 44×44 px;
- видимый фокус у полей и кнопок;
- подписи полей не только placeholder;
- сообщения об ошибках рядом с полем;
- мобильная версия 390 px;
- одинаковые компоненты на всех экранах.

Готовые страницы проекта можно использовать как кликабельный прототип: запустите сайт и снимите скриншоты. На экзамене сложите макеты в `docs/prototypes/` и закоммитьте — это доказательство для критерия «Разработка интерфейсов».

## 5. Задание 3 — сайт и серверная часть (60 минут)

### 5.1. База данных

Минимум четыре таблицы:

```sql
CREATE TABLE categories (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(32) NOT NULL UNIQUE,
  name VARCHAR(64) NOT NULL
);

CREATE TABLE rooms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id TINYINT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  capacity TINYINT UNSIGNED NOT NULL,
  area DECIMAL(5,1) NOT NULL,
  image VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id INT UNSIGNED NOT NULL,
  first_name VARCHAR(64) NOT NULL,
  last_name VARCHAR(64) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(190) NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  status ENUM('new','approved','rejected') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id),
  CHECK (check_out > check_in)
);

CREATE TABLE room_amenities (
  room_id INT UNSIGNED NOT NULL,
  amenity_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (room_id, amenity_id)
);
```

Полная схема с удобствами и админом — `db/schema.sql`.

### 5.2. Подключение и вывод каталога

```php
$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
```

Случайные 6 номеров:

```php
$stmt = $pdo->prepare(
    'SELECT r.*, c.name AS category_name
     FROM rooms r JOIN categories c ON c.id = r.category_id
     WHERE r.is_active = 1
     ORDER BY RAND() LIMIT 6'
);
$stmt->execute();
```

`LIMIT` подставляется числом из конфига, не из `$_GET`.

### 5.3. Фильтрация на сервере

Собирайте WHERE только из разрешённых полей, значения — через `?`:

```php
$where = ['r.is_active = 1'];
$params = [];

if ($categoryId) {
    $where[] = 'r.category_id = ?';
    $params[] = $categoryId;
}
if ($priceMin !== null) {
    $where[] = 'r.price >= ?';
    $params[] = $priceMin;
}
if ($priceMax !== null) {
    $where[] = 'r.price <= ?';
    $params[] = $priceMax;
}
if ($capacity) {
    $where[] = 'r.capacity >= ?';
    $params[] = $capacity;
}

$sql = 'SELECT ... FROM rooms r JOIN categories c ON ... WHERE ' . implode(' AND ', $where);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
```

Не склеивайте `$_GET` в SQL. Категорию и вместимость приводите к `int`, цену — к `float`, сортировку и лимит задавайте кодом.

### 5.4. Валидация формы

Все проверки — на сервере, даже если есть JS:

```php
if (!preg_match('/^[А-Яа-яЁё][А-Яа-яЁё\s.\-]*$/u', $firstName)) {
    $errors['first_name'] = 'Имя может содержать только кириллицу, пробел, точку и дефис.';
}

if (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
    $errors['phone'] = 'Телефон должен быть в формате +7(ХХХ)ХХХ-ХХ-ХХ.';
}

$today = new DateTimeImmutable('today');
$in = DateTimeImmutable::createFromFormat('!d.m.Y', $checkInRaw);
if (!$in || $in->format('d.m.Y') !== $checkInRaw) {
    $errors['check_in'] = 'Дата заезда должна быть в формате ДД.ММ.ГГГГ.';
} elseif ($in < $today) {
    $errors['check_in'] = 'Дата заезда не может быть раньше сегодняшней.';
}
```

Проверяйте обратное преобразование даты: `32.01.2027` иначе превратится в `01.02.2027`.

### 5.5. Сохранение без двойных броней

```php
$pdo->beginTransaction();

$stmt = $pdo->prepare('SELECT id FROM rooms WHERE id = ? AND is_active = 1 FOR UPDATE');
$stmt->execute([$roomId]);

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM bookings
     WHERE room_id = ? AND status = 'approved'
       AND check_in < ? AND check_out > ?"
);
$stmt->execute([$roomId, $checkOutIso, $checkInIso]);

if ((int)$stmt->fetchColumn() > 0) {
    throw new DomainException('Эти даты уже заняты.');
}

$stmt = $pdo->prepare(
    'INSERT INTO bookings (room_id, first_name, last_name, phone, email, check_in, check_out, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$roomId, $firstName, $lastName, $phone, $email, $checkInIso, $checkOutIso, 'new']);
$pdo->commit();
```

`FOR UPDATE` + транзакция защищают от гонки двух одновременных заявок.

### 5.6. Что ещё проверить в задании 3

- [ ] Все страницы открываются по ссылкам, без ручного ввода URL.
- [ ] Фильтр и сброс работают без JS (обычная GET-форма).
- [ ] После ошибки введённые значения сохраняются.
- [ ] После успеха — сообщение и запись в БД.
- [ ] Экранирование вывода: `htmlspecialchars`.
- [ ] CSRF-токен в каждой POST-форме.
- [ ] Пароль администратора — хеш, не открытый текст.
- [ ] Нет SQL-конкатенации пользовательских данных.

## 6. Задание 4 — календарь и админка (60 минут)

### 6.1. Занятые периоды

Берите только одобренные брони:

```sql
SELECT check_in, check_out
FROM bookings
WHERE room_id = ? AND status = 'approved' AND check_out >= CURDATE()
ORDER BY check_in
```

Семантика: `check_out` — день выезда, номер свободен с этого дня. Поэтому заняты дни `[check_in; check_out)`.

### 6.2. Календарь на PHP

```php
$first = $month->modify('first day of this month');
$daysInMonth = (int)$first->format('t');
$offset = (int)$first->format('N') - 1; // неделя с понедельника

for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = $first->setDate((int)$first->format('Y'), (int)$first->format('n'), $day);
    $blocked = false;

    foreach ($ranges as [$from, $to]) {
        if ($date >= $from && $date < $to) {
            $blocked = true;
            break;
        }
    }

    echo $blocked ? '<span class="is-blocked">' . $day . '</span>'
                  : '<button data-date="' . $date->format('Y-m-d') . '">' . $day . '</button>';
}
```

### 6.3. Поведение JS

- клик по свободному дню → дата заезда;
- второй клик по более позднему дню → дата выезда;
- клик по занятому дню игнорируется;
- при отправке JS проверяет формат, порядок и пересечение, но сервер проверяет всё повторно.

Ключевая проверка пересечения:

```js
function overlaps(start, end) {
    return blocked.some(([from, to]) => start < to && end > from);
}
```

### 6.4. Админ-панель

```php
// вход
$stmt = $pdo->prepare('SELECT * FROM admins WHERE login = ?');
$stmt->execute([$login]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
}
```

```php
// одобрение с повторной проверкой пересечения
if ($status === 'approved'
    && !is_room_free($roomId, $checkIn, $checkOut, $bookingId)) {
    return [false, 'Даты уже заняты другой подтверждённой бронью.'];
}
```

Требования задания:

- логин `hotel123`, пароль `change-me`;
- доступ к панели только после входа;
- список заявок;
- одобрение и удаление;
- после одобрения период становится неактивным в календаре.

## 7. Git и интеграция

```bash
git init
git add .
git commit -m "feat: schema, catalog and booking flow"
git add docs
git commit -m "docs: UML and prototypes"
git branch -M main
git remote add origin <url>
git push -u origin main
```

Для 7 баллов за интеграцию покажите:

1. Сайт открывается по URL, а не только локальным файлом.
2. БД импортирована, данные видны.
3. CSS/JS/картинки загружаются без 404.
4. Обработка несуществующего номера — 404.
5. Форма без CSRF — 419.
6. Понятные сообщения об ошибках.

## 8. Типичные ошибки

1. Валидация только на JS — снимается балл за серверный код.
2. `ORDER BY RAND()` без `LIMIT` или лимит из `$_GET`.
3. Даты сравниваются как строки `ДД.ММ.ГГГГ`, а не через `DateTime`.
4. Нет проверки `check_out > check_in`.
5. Календарь строится без смещения первого дня.
6. Одобрение не проверяет пересечение с другой бронью.
7. Пароль хранится открытым текстом.
8. SQL собирается конкатенацией.
9. Вывод без `htmlspecialchars`.
10. Забыт коммит прототипов/диаграмм — теряются баллы за VCS.

## 9. Что сказать эксперту

- «Валидация серверная, JS только для удобства».
- «Фильтр собирается из белого списка полей, значения идут через prepared statements».
- «Бронь создаётся в транзакции с `SELECT ... FOR UPDATE`, поэтому двойная бронь невозможна».
- «Дата выезда — день освобождения, поэтому пересечение проверяется как `existing.check_in < new.check_out AND existing.check_out > new.check_in`».
- «Пароль администратора хранится в виде bcrypt-хеша, вход защищён CSRF-токеном и ограничением попыток».
- «Диаграммы и прототипы лежат в репозитории, история коммитов показывает этапы».
