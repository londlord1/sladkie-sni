<?php
declare(strict_types=1);

function query_string(string $key): string
{
    $value = $_GET[$key] ?? '';

    return is_string($value) ? $value : '';
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? $value : '';
}

function parse_price(string $value): ?float
{
    if ($value === '' || !is_numeric($value)) {
        return null;
    }

    $price = (float)$value;

    return $price >= 0 ? $price : null;
}

function render_booking_page(array $room, array $form, array $errors, DateTimeImmutable $month): void
{
    $intervals = approved_intervals((int)$room['id']);

    render('booking', [
        'title' => 'Бронирование номера — Сладкие сны',
        'room' => $room,
        'form' => $form,
        'errors' => $errors,
        'month' => $month,
        'cells' => calendar_cells($month, $intervals),
        'ranges' => booking_ranges($intervals),
    ]);
}

function action_home(): void
{
    render('home', [
        'title' => 'Сладкие сны — гостиница и бронирование номеров',
        'categories' => categories(),
        'rooms' => find_rooms([], 3, true),
    ]);
}

function action_catalog(): void
{
    $categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
    $capacity = filter_input(INPUT_GET, 'capacity', FILTER_VALIDATE_INT);

    $priceMin = parse_price(query_string('price_min'));
    $priceMax = parse_price(query_string('price_max'));

    if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
        [$priceMin, $priceMax] = [$priceMax, $priceMin];
    }

    $filters = [
        'category_id' => $categoryId ?: null,
        'price_min' => $priceMin,
        'price_max' => $priceMax,
        'capacity' => $capacity ?: null,
    ];

    $hasFilters = $filters['category_id'] !== null
        || $priceMin !== null
        || $priceMax !== null
        || $filters['capacity'] !== null;

    $rooms = find_rooms(
        $filters,
        $hasFilters ? 0 : (int)config('catalog_random_count', 6),
        !$hasFilters
    );

    render('catalog', [
        'title' => 'Каталог номеров — Сладкие сны',
        'categories' => categories(),
        'rooms' => $rooms,
        'filters' => $filters,
        'hasFilters' => $hasFilters,
    ]);
}

function action_booking(): void
{
    $roomId = (int)query_string('room_id');
    $room = find_room($roomId);

    if ($room === null) {
        http_response_code(404);
        render('errors/404', ['title' => 'Номер не найден — Сладкие сны']);
        return;
    }

    $month = resolve_month(query_string('month'));

    render_booking_page($room, [
        'first_name' => '',
        'last_name' => '',
        'phone' => '',
        'email' => '',
        'check_in' => '',
        'check_out' => '',
    ], [], $month);
}

function action_booking_store(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(url('catalog'));
    }

    if (!csrf_verify($_POST['csrf'] ?? null)) {
        http_response_code(419);
        render('errors/419', ['title' => 'Сессия истекла — Сладкие сны']);
        return;
    }

    $roomId = (int)post_string('room_id');
    $room = find_room($roomId);

    if ($room === null) {
        http_response_code(404);
        render('errors/404', ['title' => 'Номер не найден — Сладкие сны']);
        return;
    }

    [$data, $errors] = validate_booking($_POST, $room);
    $month = resolve_month(post_string('month'));

    if ($errors === []) {
        try {
            $bookingId = create_booking($data);
            $_SESSION['last_booking_id'] = $bookingId;
            flash_set(
                'success',
                'Заявка №' . $bookingId . ' успешно отправлена. Администратор свяжется с вами для подтверждения.'
            );
            redirect(url('booking.success', ['id' => $bookingId]));
        } catch (DomainException $exception) {
            $errors['check_in'] = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log('booking store: ' . $exception->getMessage());
            $errors['common'] = 'Не удалось сохранить заявку. Попробуйте ещё раз.';
        }
    }

    http_response_code(422);
    render_booking_page($room, $data, $errors, $month);
}

function action_booking_success(): void
{
    $bookingId = (int)query_string('id');
    $booking = null;

    if ($bookingId > 0 && (int)($_SESSION['last_booking_id'] ?? 0) === $bookingId) {
        $booking = booking_find($bookingId);
    }

    render('success', [
        'title' => 'Заявка отправлена — Сладкие сны',
        'booking' => $booking,
        'bookingId' => $bookingId,
    ]);
}

function require_admin(): void
{
    if (admin_user() === null) {
        flash_set('error', 'Войдите в панель управления.');
        redirect(url('admin.login'));
    }
}

function action_admin_login(): void
{
    ensure_admin();

    if (admin_user() !== null) {
        redirect(url('admin.bookings'));
    }

    render('admin/login', [
        'title' => 'Вход в панель управления — Сладкие сны',
        'error' => null,
        'login' => '',
    ]);
}

function action_admin_login_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(url('admin.login'));
    }

    if (!csrf_verify($_POST['csrf'] ?? null)) {
        http_response_code(419);
        render('errors/419', ['title' => 'Сессия истекла — Сладкие сны']);
        return;
    }

    ensure_admin();

    if ((int)($_SESSION['login_locked_until'] ?? 0) > time()) {
        http_response_code(429);
        render('admin/login', [
            'title' => 'Вход в панель управления — Сладкие сны',
            'error' => 'Слишком много попыток. Попробуйте через минуту.',
            'login' => '',
        ]);
        return;
    }

    $login = trim(post_string('login'));
    $password = post_string('password');

    if (admin_attempt($login, $password)) {
        unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
        redirect(url('admin.bookings'));
    }

    $_SESSION['login_attempts'] = (int)($_SESSION['login_attempts'] ?? 0) + 1;

    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_locked_until'] = time() + 60;
    }

    http_response_code(422);
    render('admin/login', [
        'title' => 'Вход в панель управления — Сладкие сны',
        'error' => 'Неверный логин или пароль.',
        'login' => $login,
    ]);
}

function action_admin_logout(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf'] ?? null)) {
        redirect(url('home'));
    }

    admin_logout();
    flash_set('success', 'Вы вышли из панели управления.');
    redirect(url('home'));
}

function action_admin_bookings(): void
{
    require_admin();

    $status = query_string('status');
    $filter = in_array($status, ['new', 'approved', 'rejected'], true) ? $status : null;

    render('admin/bookings', [
        'title' => 'Заявки на бронирование — Сладкие сны',
        'bookings' => bookings_list($filter),
        'counts' => booking_counts(),
        'status' => $filter,
    ]);
}

function action_admin_booking_status(): void
{
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf'] ?? null)) {
        flash_set('error', 'Не удалось выполнить действие. Обновите страницу и попробуйте снова.');
        redirect(url('admin.bookings'));
    }

    $bookingId = (int)post_string('id');
    $status = post_string('status');
    $returnStatus = post_string('return_status');

    [$ok, $message] = set_booking_status($bookingId, $status);
    flash_set($ok ? 'success' : 'error', $message);

    redirect(url('admin.bookings', $returnStatus !== '' ? ['status' => $returnStatus] : []));
}

function action_admin_booking_delete(): void
{
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf'] ?? null)) {
        flash_set('error', 'Не удалось удалить заявку.');
        redirect(url('admin.bookings'));
    }

    $bookingId = (int)post_string('id');
    $returnStatus = post_string('return_status');

    if ($bookingId > 0 && delete_booking($bookingId)) {
        flash_set('success', 'Заявка №' . $bookingId . ' удалена.');
    } else {
        flash_set('error', 'Заявка не найдена.');
    }

    redirect(url('admin.bookings', $returnStatus !== '' ? ['status' => $returnStatus] : []));
}
