<?php
/** @var array $bookings */
/** @var array $counts */
/** @var string|null $status */

$tabs = [
    ['key' => null, 'label' => 'Все', 'count' => $counts['all']],
    ['key' => 'new', 'label' => 'Новые', 'count' => $counts['new']],
    ['key' => 'approved', 'label' => 'Одобренные', 'count' => $counts['approved']],
    ['key' => 'rejected', 'label' => 'Отклонённые', 'count' => $counts['rejected']],
];
?>
<section class="page-head page-head-compact">
    <div class="container">
        <div class="admin-head">
            <div>
                <p class="eyebrow">Администрирование</p>
                <h1>Заявки на бронирование</h1>
                <p class="page-lead">Одобряйте заявки после проверки данных и следите за занятостью номеров.</p>
            </div>
            <a class="btn btn-ghost" href="<?= e(url('catalog')) ?>">Открыть сайт</a>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <nav class="tabs" aria-label="Фильтр заявок">
            <?php foreach ($tabs as $tab): ?>
                <?php
                $isActive = $status === $tab['key'];
                $tabUrl = $tab['key'] === null ? url('admin.bookings') : url('admin.bookings', ['status' => $tab['key']]);
                ?>
                <a class="tab<?= $isActive ? ' is-active' : '' ?>" href="<?= e($tabUrl) ?>">
                    <?= e($tab['label']) ?>
                    <span class="tab-count"><?= (int)$tab['count'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($bookings === []): ?>
            <div class="empty-state">
                <h2>Заявок пока нет</h2>
                <p>Когда гость отправит форму бронирования, заявка появится здесь.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>Гость</th>
                        <th>Контакты</th>
                        <th>Номер</th>
                        <th>Даты</th>
                        <th>Статус</th>
                        <th>Создана</th>
                        <th class="actions-col">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td class="nowrap">№<?= (int)$booking['id'] ?></td>
                            <td>
                                <strong><?= e($booking['last_name'] . ' ' . $booking['first_name']) ?></strong>
                            </td>
                            <td class="contacts-cell">
                                <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $booking['phone'])) ?>"><?= e($booking['phone']) ?></a>
                                <a href="mailto:<?= e($booking['email']) ?>"><?= e($booking['email']) ?></a>
                            </td>
                            <td>
                                <?= e($booking['room_title']) ?>
                                <small class="muted"><?= e($booking['category_name']) ?></small>
                            </td>
                            <td class="nowrap">
                                <?= e(date_ru($booking['check_in'])) ?><br>
                                <small class="muted">по <?= e(date_ru($booking['check_out'])) ?></small>
                            </td>
                            <td>
                                <span class="status status-<?= e($booking['status']) ?>">
                                    <?= e(booking_status_label($booking['status'])) ?>
                                </span>
                            </td>
                            <td class="nowrap muted"><?= e(date('d.m.Y H:i', strtotime((string)$booking['created_at']))) ?></td>
                            <td>
                                <div class="row-actions">
                                    <?php if ($booking['status'] !== 'approved'): ?>
                                        <form method="post" action="<?= e(url('admin.booking.status')) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <input type="hidden" name="return_status" value="<?= e($status ?? '') ?>">
                                            <button class="btn btn-small btn-success" type="submit">Одобрить</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($booking['status'] !== 'rejected'): ?>
                                        <form method="post" action="<?= e(url('admin.booking.status')) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="hidden" name="return_status" value="<?= e($status ?? '') ?>">
                                            <button class="btn btn-small btn-ghost" type="submit">Отклонить</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="post" action="<?= e(url('admin.booking.delete')) ?>"
                                          onsubmit="return confirm('Удалить заявку №<?= (int)$booking['id'] ?>? Действие необратимо.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                                        <input type="hidden" name="return_status" value="<?= e($status ?? '') ?>">
                                        <button class="btn btn-small btn-danger-ghost" type="submit">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
