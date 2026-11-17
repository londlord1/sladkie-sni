<?php
declare(strict_types=1);

function validate_booking(array $input, array $room): array
{
    $input = array_map(static function ($value) {
        return is_string($value) ? $value : '';
    }, $input);

    $data = [
        'room_id' => (int)$room['id'],
        'first_name' => trim($input['first_name'] ?? ''),
        'last_name' => trim($input['last_name'] ?? ''),
        'phone' => trim($input['phone'] ?? ''),
        'email' => trim($input['email'] ?? ''),
        'check_in' => trim($input['check_in'] ?? ''),
        'check_out' => trim($input['check_out'] ?? ''),
        'check_in_iso' => '',
        'check_out_iso' => '',
    ];
    $errors = [];

    foreach (['first_name' => 'Имя', 'last_name' => 'Фамилия'] as $field => $label) {
        $data[$field] = (string)preg_replace('/\s+/u', ' ', $data[$field]);

        if ($data[$field] === '') {
            $errors[$field] = $label . ' — обязательное поле.';
            continue;
        }

        if (str_length($data[$field]) > 64) {
            $errors[$field] = $label . ': не более 64 символов.';
            continue;
        }

        if (!preg_match('/^[А-Яа-яЁё][А-Яа-яЁё\s.\-]*$/u', $data[$field])) {
            $errors[$field] = $label . ' может содержать только буквы кириллицы, пробел, точку и дефис.';
        }
    }

    if ($data['phone'] === '') {
        $errors['phone'] = 'Телефон — обязательное поле.';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $data['phone'])) {
        $errors['phone'] = 'Телефон должен быть в формате +7(ХХХ)ХХХ-ХХ-ХХ.';
    }

    if ($data['email'] === '') {
        $errors['email'] = 'E-mail — обязательное поле.';
    } elseif (str_length($data['email']) > 190 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Укажите корректный адрес электронной почты.';
    }

    $today = new DateTimeImmutable('today');
    $checkIn = parse_date($data['check_in']);
    $checkOut = parse_date($data['check_out']);

    if ($data['check_in'] === '') {
        $errors['check_in'] = 'Укажите дату заезда.';
    } elseif ($checkIn === null) {
        $errors['check_in'] = 'Дата заезда должна быть в формате ДД.ММ.ГГГГ.';
    } elseif ($checkIn < $today) {
        $errors['check_in'] = 'Дата заезда не может быть раньше сегодняшней.';
    }

    if ($data['check_out'] === '') {
        $errors['check_out'] = 'Укажите дату выезда.';
    } elseif ($checkOut === null) {
        $errors['check_out'] = 'Дата выезда должна быть в формате ДД.ММ.ГГГГ.';
    } elseif ($checkIn !== null && $checkOut <= $checkIn) {
        $errors['check_out'] = 'Дата выезда должна быть позже даты заезда.';
    }

    if ($checkIn !== null) {
        $data['check_in_iso'] = $checkIn->format('Y-m-d');
    }

    if ($checkOut !== null) {
        $data['check_out_iso'] = $checkOut->format('Y-m-d');
    }

    return [$data, $errors];
}
