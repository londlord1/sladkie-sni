<?php
declare(strict_types=1);

function categories(): array
{
    return db()->query('SELECT id, code, name FROM categories ORDER BY id')->fetchAll();
}

function room_amenities_map(array $roomIds): array
{
    $roomIds = array_values(array_unique(array_map('intval', $roomIds)));

    if ($roomIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($roomIds), '?'));
    $stmt = db()->prepare(
        "SELECT ra.room_id, a.name
         FROM room_amenities ra
         JOIN amenities a ON a.id = ra.amenity_id
         WHERE ra.room_id IN ($placeholders)
         ORDER BY a.name"
    );
    $stmt->execute($roomIds);

    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $map[(int)$row['room_id']][] = $row['name'];
    }

    return $map;
}

function find_rooms(array $filters = [], int $limit = 0, bool $random = false): array
{
    $sql = "SELECT r.id, r.category_id, r.title, r.price, r.capacity, r.area,
                   r.description, r.image, c.code AS category_code, c.name AS category_name
            FROM rooms r
            JOIN categories c ON c.id = r.category_id
            WHERE r.is_active = 1";
    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= ' AND r.category_id = ?';
        $params[] = (int)$filters['category_id'];
    }

    if (isset($filters['price_min']) && $filters['price_min'] !== null) {
        $sql .= ' AND r.price >= ?';
        $params[] = (float)$filters['price_min'];
    }

    if (isset($filters['price_max']) && $filters['price_max'] !== null) {
        $sql .= ' AND r.price <= ?';
        $params[] = (float)$filters['price_max'];
    }

    if (!empty($filters['capacity'])) {
        $sql .= ' AND r.capacity >= ?';
        $params[] = (int)$filters['capacity'];
    }

    if (!empty($filters['q'])) {
        $sql .= ' AND (r.title LIKE ? OR r.description LIKE ?)';
        $like = '%' . $filters['q'] . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= $random ? ' ORDER BY RAND()' : ' ORDER BY r.price ASC, r.id ASC';

    if ($limit > 0) {
        $sql .= ' LIMIT ' . $limit;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();

    $amenities = room_amenities_map(array_column($rooms, 'id'));
    foreach ($rooms as &$room) {
        $room['amenities'] = $amenities[(int)$room['id']] ?? [];
    }
    unset($room);

    return $rooms;
}

function find_room(int $id): ?array
{
    $stmt = db()->prepare(
        "SELECT r.id, r.category_id, r.title, r.price, r.capacity, r.area,
                r.description, r.image, c.code AS category_code, c.name AS category_name
         FROM rooms r
         JOIN categories c ON c.id = r.category_id
         WHERE r.id = ? AND r.is_active = 1"
    );
    $stmt->execute([$id]);
    $room = $stmt->fetch();

    if (!$room) {
        return null;
    }

    $room['amenities'] = room_amenities_map([$id])[$id] ?? [];

    return $room;
}

function approved_intervals(int $roomId): array
{
    $stmt = db()->prepare(
        "SELECT check_in, check_out
         FROM bookings
         WHERE room_id = ?
           AND status = 'approved'
           AND check_out >= ?
         ORDER BY check_in"
    );
    $stmt->execute([$roomId, date('Y-m-d')]);

    return $stmt->fetchAll();
}

function is_room_free(int $roomId, string $checkIn, string $checkOut, ?int $exceptBookingId = null): bool
{
    $sql = "SELECT COUNT(*)
            FROM bookings
            WHERE room_id = ?
              AND status = 'approved'
              AND check_in < ?
              AND check_out > ?";
    $params = [$roomId, $checkOut, $checkIn];

    if ($exceptBookingId !== null) {
        $sql .= ' AND id <> ?';
        $params[] = $exceptBookingId;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn() === 0;
}

function create_booking(array $data): int
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('SELECT id FROM rooms WHERE id = ? AND is_active = 1 FOR UPDATE');
        $stmt->execute([(int)$data['room_id']]);

        if ($stmt->fetchColumn() === false) {
            throw new DomainException('Выбранный номер больше недоступен.');
        }

        if (!is_room_free((int)$data['room_id'], $data['check_in_iso'], $data['check_out_iso'])) {
            throw new DomainException('На выбранные даты номер уже забронирован. Выберите другой период.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO bookings (room_id, first_name, last_name, phone, email, check_in, check_out, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int)$data['room_id'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['email'],
            $data['check_in_iso'],
            $data['check_out_iso'],
            'new',
        ]);

        $id = (int)$pdo->lastInsertId();
        $pdo->commit();

        return $id;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function booking_find(int $id): ?array
{
    $stmt = db()->prepare(
        "SELECT b.*, r.title AS room_title, c.name AS category_name
         FROM bookings b
         JOIN rooms r ON r.id = b.room_id
         JOIN categories c ON c.id = r.category_id
         WHERE b.id = ?"
    );
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    return $booking ?: null;
}

function bookings_list(?string $status = null): array
{
    $sql = "SELECT b.*, r.title AS room_title, c.name AS category_name
            FROM bookings b
            JOIN rooms r ON r.id = b.room_id
            JOIN categories c ON c.id = r.category_id";
    $params = [];

    if ($status !== null) {
        $sql .= ' WHERE b.status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY b.created_at DESC, b.id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function booking_counts(): array
{
    $counts = ['all' => 0, 'new' => 0, 'approved' => 0, 'rejected' => 0];

    foreach (db()->query('SELECT status, COUNT(*) AS total FROM bookings GROUP BY status') as $row) {
        $counts[(string)$row['status']] = (int)$row['total'];
        $counts['all'] += (int)$row['total'];
    }

    return $counts;
}

function set_booking_status(int $bookingId, string $status): array
{
    if (!in_array($status, ['approved', 'rejected'], true)) {
        return [false, 'Некорректный статус заявки.'];
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ? FOR UPDATE');
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            $pdo->rollBack();
            return [false, 'Заявка не найдена.'];
        }

        if ($status === 'approved'
            && !is_room_free((int)$booking['room_id'], $booking['check_in'], $booking['check_out'], $bookingId)
        ) {
            $pdo->rollBack();
            return [false, 'Нельзя одобрить заявку: эти даты уже заняты другой подтверждённой бронью.'];
        }

        $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$status, $bookingId]);
        $pdo->commit();

        return [true, $status === 'approved' ? 'Заявка одобрена.' : 'Заявка отклонена.'];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('set_booking_status: ' . $exception->getMessage());

        return [false, 'Не удалось изменить статус заявки.'];
    }
}

function delete_booking(int $bookingId): bool
{
    $stmt = db()->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->execute([$bookingId]);

    return $stmt->rowCount() > 0;
}

function admin_user(): ?array
{
    static $loaded = false;
    static $admin = null;

    if ($loaded) {
        return $admin;
    }

    $loaded = true;
    $adminId = (int)($_SESSION['admin_id'] ?? 0);

    if ($adminId <= 0) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, login FROM admins WHERE id = ?');
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch() ?: null;

    if ($admin === null) {
        unset($_SESSION['admin_id']);
    }

    return $admin;
}

function ensure_admin(): void
{
    $count = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();

    if ($count > 0) {
        return;
    }

    $admin = config('admin');
    $stmt = db()->prepare('INSERT INTO admins (login, password_hash) VALUES (?, ?)');

    try {
        $stmt->execute([
            $admin['login'],
            password_hash($admin['password'], PASSWORD_DEFAULT),
        ]);
    } catch (PDOException $exception) {
        // Два первых запроса одновременно могли создать администратора.
        if ($exception->getCode() !== '23000') {
            throw $exception;
        }
    }
}

function admin_attempt(string $login, string $password): bool
{
    $stmt = db()->prepare('SELECT id, login, password_hash FROM admins WHERE login = ? LIMIT 1');
    $stmt->execute([$login]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, (string)$admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];

    return true;
}

function admin_logout(): void
{
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
}
