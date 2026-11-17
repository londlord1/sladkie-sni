<?php /** @var string $viewPath */ ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Сладкие сны') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main">Перейти к содержанию</a>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('home')) ?>">
            <span class="brand-mark" aria-hidden="true">☾</span>
            <span class="brand-text">
                <strong>Сладкие сны</strong>
                <small>отдых без забот</small>
            </span>
        </a>

        <nav class="main-nav" aria-label="Основная навигация">
            <a href="<?= e(url('home')) ?>" class="<?= current_route() === 'home' ? 'is-active' : '' ?>">Главная</a>
            <a href="<?= e(url('catalog')) ?>" class="<?= current_route() === 'catalog' ? 'is-active' : '' ?>">Каталог</a>
            <?php if (admin_user() !== null): ?>
                <a href="<?= e(url('admin.bookings')) ?>" class="<?= strpos(current_route(), 'admin.') === 0 ? 'is-active' : '' ?>">Заявки</a>
                <form class="nav-form" method="post" action="<?= e(url('admin.logout')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="nav-link">Выйти</button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('admin.login')) ?>" class="<?= current_route() === 'admin.login' ? 'is-active' : '' ?>">Панель управления</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php $flashes = flash_get(); ?>
<?php if ($flashes !== []): ?>
    <div class="container flash-stack">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="status">
                <?= e($flash['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main" class="site-main">
    <?php require $viewPath; ?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-about">
            <a class="brand brand-footer" href="<?= e(url('home')) ?>">
                <span class="brand-mark" aria-hidden="true">☾</span>
                <span class="brand-text">
                    <strong>Сладкие сны</strong>
                    <small>отдых без забот</small>
                </span>
            </a>
            <p>Небольшая гостиница в центре города: светлые номера, тихий двор и завтрак до полудня.</p>
        </div>

        <div class="footer-contacts">
            <h3>Контакты</h3>
            <p>г. Новосибирск, ул. Сладкая, 12</p>
            <p>Режим работы: круглосуточно, без выходных</p>
            <p><a href="tel:+73832220000">+7 (383) 222-00-00</a></p>
            <p><a href="mailto:hello@sweetdreams.ru">hello@sweetdreams.ru</a></p>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> «Сладкие сны». Учебный проект для демонстрационного экзамена.</span>
        <a href="<?= e(url('admin.login')) ?>">Для персонала</a>
    </div>
</footer>

<script src="assets/js/booking.js" defer></script>
</body>
</html>
