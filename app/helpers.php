<?php
declare(strict_types=1);

function config(?string $key = null, $default = null)
{
    static $config = null;

    if ($config === null) {
        $config = require BASE_PATH . '/app/config.php';
    }

    if ($key === null) {
        return $config;
    }

    return array_key_exists($key, $config) ? $config[$key] : $default;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $route = 'home', array $params = []): string
{
    return 'index.php?' . http_build_query(array_merge(['r' => $route], $params));
}

function current_route(): string
{
    $route = $_GET['r'] ?? 'home';

    return is_string($route) ? $route : 'home';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify($token): bool
{
    return is_string($token)
        && $token !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function render(string $view, array $data = []): void
{
    $viewPath = BASE_PATH . '/app/views/' . $view . '.php';

    if (!is_file($viewPath)) {
        http_response_code(500);
        exit('Не найден шаблон: ' . e($view));
    }

    extract($data, EXTR_SKIP);
    require BASE_PATH . '/app/views/layout.php';
}

function str_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return (int)preg_match_all('/./us', $value);
}

function money($amount): string
{
    return number_format((float)$amount, 0, '.', ' ') . ' ₽';
}

function area_value($area): string
{
    $formatted = number_format((float)$area, 1, ',', '');
    return rtrim(rtrim($formatted, '0'), ',');
}

function parse_date(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!d.m.Y', $value);

    if ($date === false || $date->format('d.m.Y') !== $value) {
        return null;
    }

    return $date;
}

function date_ru(?string $iso): string
{
    if (!$iso) {
        return '—';
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', substr((string)$iso, 0, 10));

    return $date ? $date->format('d.m.Y') : '—';
}

function room_image(array $room): string
{
    $name = basename((string)($room['image'] ?? ''));

    if ($name !== '' && is_file(BASE_PATH . '/public/assets/img/' . $name)) {
        return 'assets/img/' . $name;
    }

    return 'assets/img/standard.jpg';
}

function booking_status_label(string $status): string
{
    $labels = [
        'new' => 'Новая',
        'approved' => 'Одобрена',
        'rejected' => 'Отклонена',
    ];

    return $labels[$status] ?? $status;
}

function month_name_ru(DateTimeImmutable $date): string
{
    $months = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь',
    ];

    return $months[(int)$date->format('n')] . ' ' . $date->format('Y');
}

function resolve_month(string $value): DateTimeImmutable
{
    $current = new DateTimeImmutable('first day of this month 00:00');

    if (preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
        $month = DateTimeImmutable::createFromFormat('!Y-m-d', $value . '-01');

        if ($month instanceof DateTimeImmutable) {
            $min = $current;
            $max = $current->modify('+12 months');

            if ($month < $min) {
                return $min;
            }
            if ($month > $max) {
                return $max;
            }

            return $month;
        }
    }

    return $current;
}

function calendar_cells(DateTimeImmutable $month, array $intervals): array
{
    $first = $month->modify('first day of this month');
    $daysInMonth = (int)$first->format('t');
    $offset = (int)$first->format('N') - 1;
    $today = new DateTimeImmutable('today');
    $todayIso = $today->format('Y-m-d');

    $ranges = [];
    foreach ($intervals as $interval) {
        $ranges[] = [
            new DateTimeImmutable(substr((string)$interval['check_in'], 0, 10)),
            new DateTimeImmutable(substr((string)$interval['check_out'], 0, 10)),
        ];
    }

    $cells = array_fill(0, $offset, null);

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = $first->setDate((int)$first->format('Y'), (int)$first->format('n'), $day);
        $blocked = false;

        foreach ($ranges as $range) {
            if ($date >= $range[0] && $date < $range[1]) {
                $blocked = true;
                break;
            }
        }

        $cells[] = [
            'day' => $day,
            'iso' => $date->format('Y-m-d'),
            'past' => $date < $today,
            'blocked' => $blocked,
            'today' => $date->format('Y-m-d') === $todayIso,
        ];
    }

    return $cells;
}

function booking_ranges(array $intervals): array
{
    $ranges = [];
    $today = date('Y-m-d');

    foreach ($intervals as $interval) {
        $from = substr((string)$interval['check_in'], 0, 10);
        $to = substr((string)$interval['check_out'], 0, 10);

        if ($to < $today) {
            continue;
        }

        $ranges[] = [$from, $to];
    }

    return $ranges;
}
