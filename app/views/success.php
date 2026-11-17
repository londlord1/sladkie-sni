<?php /** @var array|null $booking */ ?>
<section class="section">
    <div class="container narrow">
        <div class="success-card">
            <span class="success-icon" aria-hidden="true">✓</span>
            <p class="eyebrow">Готово</p>
            <h1>Заявка отправлена</h1>
            <p class="success-lead">
                Спасибо! Мы получили вашу заявку и передали её администратору.
                Он свяжется с вами по телефону, чтобы подтвердить бронирование.
            </p>

            <?php if ($booking !== null): ?>
                <dl class="success-details">
                    <div>
                        <dt>Номер заявки</dt>
                        <dd>№<?= (int)$booking['id'] ?></dd>
                    </div>
                    <div>
                        <dt>Номер</dt>
                        <dd><?= e($booking['room_title']) ?></dd>
                    </div>
                    <div>
                        <dt>Даты</dt>
                        <dd><?= e(date_ru($booking['check_in'])) ?> — <?= e(date_ru($booking['check_out'])) ?></dd>
                    </div>
                    <div>
                        <dt>Статус</dt>
                        <dd><span class="status status-new"><?= e(booking_status_label($booking['status'])) ?></span></dd>
                    </div>
                </dl>
            <?php elseif ($bookingId > 0): ?>
                <p class="success-number">Номер заявки: №<?= (int)$bookingId ?></p>
            <?php endif; ?>

            <div class="success-actions">
                <a class="btn btn-primary" href="<?= e(url('catalog')) ?>">Вернуться в каталог</a>
                <a class="btn btn-ghost" href="<?= e(url('home')) ?>">На главную</a>
            </div>
        </div>
    </div>
</section>
