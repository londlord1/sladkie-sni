<?php
/** @var array $room */
/** @var array $form */
/** @var array $errors */
/** @var DateTimeImmutable $month */
/** @var array $cells */
/** @var array $ranges */

$currentMonth = new DateTimeImmutable('first day of this month 00:00');
$prevMonth = $month->modify('-1 month');
$nextMonth = $month->modify('+1 month');
$canPrev = $prevMonth >= $currentMonth;
$canNext = $nextMonth <= $currentMonth->modify('+12 months');
$blockedJson = json_encode($ranges, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
$weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
?>
<section class="page-head page-head-compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= e(url('catalog')) ?>">Каталог</a>
            <span aria-hidden="true">/</span>
            <span><?= e($room['title']) ?></span>
        </nav>
        <h1>Бронирование номера</h1>
    </div>
</section>

<section class="section section-tight">
    <div class="container booking-layout">
        <aside class="booking-summary">
            <img class="summary-image" src="<?= e(room_image($room)) ?>" alt="<?= e($room['title']) ?>" width="640" height="420">
            <p class="eyebrow"><?= e($room['category_name']) ?></p>
            <h2><?= e($room['title']) ?></h2>
            <p class="summary-price"><?= e(money($room['price'])) ?> <span>/ сутки</span></p>

            <ul class="summary-list">
                <li><span>Гостей</span><strong>до <?= (int)$room['capacity'] ?></strong></li>
                <li><span>Площадь</span><strong><?= e(area_value($room['area'])) ?> м²</strong></li>
                <li><span>Категория</span><strong><?= e($room['category_name']) ?></strong></li>
            </ul>

            <ul class="room-amenities room-amenities-wide">
                <?php foreach ($room['amenities'] ?? [] as $amenity): ?>
                    <li><?= e($amenity) ?></li>
                <?php endforeach; ?>
            </ul>

            <p class="summary-note">Оплата при заселении. Бесплатная отмена — не позднее чем за 24 часа до заезда.</p>
        </aside>

        <div class="booking-main">
            <?php if (!empty($errors['common'])): ?>
                <div class="alert alert-error" role="alert"><?= e($errors['common']) ?></div>
            <?php elseif ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    Проверьте отмеченные поля — заявка не отправлена.
                </div>
            <?php endif; ?>

            <section class="calendar-card" aria-labelledby="calendar-title">
                <div class="calendar-head">
                    <?php if ($canPrev): ?>
                        <a class="cal-nav" href="<?= e(url('booking', ['room_id' => (int)$room['id'], 'month' => $prevMonth->format('Y-m')])) ?>"
                           aria-label="Предыдущий месяц">←</a>
                    <?php else: ?>
                        <span class="cal-nav is-disabled" aria-hidden="true">←</span>
                    <?php endif; ?>

                    <h2 id="calendar-title"><?= e(month_name_ru($month)) ?></h2>

                    <?php if ($canNext): ?>
                        <a class="cal-nav" href="<?= e(url('booking', ['room_id' => (int)$room['id'], 'month' => $nextMonth->format('Y-m')])) ?>"
                           aria-label="Следующий месяц">→</a>
                    <?php else: ?>
                        <span class="cal-nav is-disabled" aria-hidden="true">→</span>
                    <?php endif; ?>
                </div>

                <div class="calendar-legend">
                    <span><i class="dot dot-free"></i>свободно</span>
                    <span><i class="dot dot-blocked"></i>занято</span>
                    <span><i class="dot dot-past"></i>прошло</span>
                </div>

                <div class="calendar-grid calendar-weekdays" aria-hidden="true">
                    <?php foreach ($weekdays as $weekday): ?>
                        <span><?= e($weekday) ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="calendar-grid" data-calendar>
                    <?php foreach ($cells as $cell): ?>
                        <?php if ($cell === null): ?>
                            <span class="cal-day is-empty"></span>
                        <?php elseif ($cell['blocked']): ?>
                            <span class="cal-day is-blocked" title="Даты уже забронированы"><?= (int)$cell['day'] ?></span>
                        <?php elseif ($cell['past']): ?>
                            <span class="cal-day is-past"><?= (int)$cell['day'] ?></span>
                        <?php else: ?>
                            <button type="button"
                                    class="cal-day is-free<?= $cell['today'] ? ' is-today' : '' ?>"
                                    data-date="<?= e($cell['iso']) ?>"><?= (int)$cell['day'] ?></button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <p class="calendar-hint">Нажмите на свободный день, чтобы подставить дату заезда, затем — дату выезда.</p>
            </section>

            <form class="booking-form"
                  method="post"
                  action="<?= e(url('booking.store')) ?>"
                  data-booking-form
                  data-blocked="<?= e($blockedJson) ?>"
                  data-price="<?= (float)$room['price'] ?>"
                  novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="room_id" value="<?= (int)$room['id'] ?>">
                <input type="hidden" name="month" value="<?= e($month->format('Y-m')) ?>">

                <div class="form-head">
                    <h2>Данные гостя</h2>
                    <p>Все поля обязательны. Проверка данных выполняется на сервере.</p>
                </div>

                <div class="field-row">
                    <div class="field<?= isset($errors['first_name']) ? ' has-error' : '' ?>">
                        <label for="first_name">Имя</label>
                        <input id="first_name" name="first_name" type="text"
                               value="<?= e($form['first_name'] ?? '') ?>"
                               autocomplete="given-name" required
                               aria-describedby="first_name-hint">
                        <p class="field-hint" id="first_name-hint">Кириллица, пробел, точка, дефис</p>
                        <?php if (isset($errors['first_name'])): ?>
                            <p class="field-error"><?= e($errors['first_name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field<?= isset($errors['last_name']) ? ' has-error' : '' ?>">
                        <label for="last_name">Фамилия</label>
                        <input id="last_name" name="last_name" type="text"
                               value="<?= e($form['last_name'] ?? '') ?>"
                               autocomplete="family-name" required
                               aria-describedby="last_name-hint">
                        <p class="field-hint" id="last_name-hint">Кириллица, пробел, точка, дефис</p>
                        <?php if (isset($errors['last_name'])): ?>
                            <p class="field-error"><?= e($errors['last_name']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>">
                        <label for="phone">Телефон</label>
                        <input id="phone" name="phone" type="tel"
                               value="<?= e($form['phone'] ?? '') ?>"
                               placeholder="+7(999)123-45-67"
                               autocomplete="tel" inputmode="tel" required>
                        <?php if (isset($errors['phone'])): ?>
                            <p class="field-error"><?= e($errors['phone']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
                        <label for="email">E-mail</label>
                        <input id="email" name="email" type="email"
                               value="<?= e($form['email'] ?? '') ?>"
                               placeholder="guest@example.ru"
                               autocomplete="email" inputmode="email" required>
                        <?php if (isset($errors['email'])): ?>
                            <p class="field-error"><?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field<?= isset($errors['check_in']) ? ' has-error' : '' ?>">
                        <label for="check_in">Дата заезда</label>
                        <input id="check_in" name="check_in" type="text"
                               value="<?= e($form['check_in'] ?? '') ?>"
                               placeholder="ДД.ММ.ГГГГ" inputmode="numeric"
                               autocomplete="off" data-date-input required>
                        <?php if (isset($errors['check_in'])): ?>
                            <p class="field-error"><?= e($errors['check_in']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field<?= isset($errors['check_out']) ? ' has-error' : '' ?>">
                        <label for="check_out">Дата выезда</label>
                        <input id="check_out" name="check_out" type="text"
                               value="<?= e($form['check_out'] ?? '') ?>"
                               placeholder="ДД.ММ.ГГГГ" inputmode="numeric"
                               autocomplete="off" data-date-input required>
                        <?php if (isset($errors['check_out'])): ?>
                            <p class="field-error"><?= e($errors['check_out']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="booking-total" data-total hidden>
                    <span data-nights-label>0 ночей</span>
                    <strong data-total-value>0 ₽</strong>
                </div>

                <p class="form-client-error" data-client-error hidden></p>

                <button class="btn btn-primary btn-large btn-block" type="submit">Отправить заявку</button>
                <p class="form-note">
                    Нажимая кнопку, вы соглашаетесь на обработку персональных данных.
                    Заявка не является оплатой: администратор свяжется с вами для подтверждения.
                </p>
            </form>
        </div>
    </div>
</section>
