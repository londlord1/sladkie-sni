<?php
$categoryInfo = [
    'standard' => [
        'text' => 'Компактно и продуманно: всё, что нужно для деловой поездки или короткого отдыха.',
        'price' => 'от 4 200 ₽',
    ],
    'studio' => [
        'text' => 'Больше пространства: кухонная зона, диван и место для неспешного утра.',
        'price' => 'от 6 900 ₽',
    ],
    'lux' => [
        'text' => 'Гостиная, ванна и панорамные окна. Для особых поводов и долгих выходных.',
        'price' => 'от 12 500 ₽',
    ],
];
?>

<section class="hero">
    <div class="container hero-inner">
        <div class="hero-copy">
            <p class="eyebrow">Гостиница «Сладкие сны»</p>
            <h1>Проснитесь там, где вам хорошо</h1>
            <p class="hero-lead">
                Светлые номера, тишина и завтрак до полудня. Выберите даты — остальное мы возьмём на себя.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary btn-large" href="<?= e(url('catalog')) ?>">Смотреть номера</a>
                <a class="btn btn-ghost btn-large" href="#categories">Категории</a>
            </div>
            <ul class="hero-facts">
                <li><strong>3</strong><span>категории</span></li>
                <li><strong>от 4 200 ₽</strong><span>за сутки</span></li>
                <li><strong>24/7</strong><span>на связи</span></li>
            </ul>
        </div>

        <div class="hero-media">
            <img src="assets/img/hero.jpg" alt="Холл гостиницы «Сладкие сны»" width="960" height="720">
            <div class="hero-note">
                Завтрак включён
                <small>для всех категорий номеров</small>
            </div>
        </div>
    </div>
</section>

<section class="section" id="categories">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Категории</p>
                <h2>Три способа отдохнуть</h2>
            </div>
            <p class="section-lead">От компактного «Стандарта» до люкса с гостиной. Цены указаны за сутки.</p>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <?php $info = $categoryInfo[$category['code']] ?? ['text' => 'Уютные номера этой категории.', 'price' => '']; ?>
                <a class="category-card category-<?= e($category['code']) ?>"
                   href="<?= e(url('catalog', ['category_id' => (int)$category['id']])) ?>">
                    <span class="category-name"><?= e($category['name']) ?></span>
                    <span class="category-text"><?= e($info['text']) ?></span>
                    <span class="category-bottom">
                        <span class="category-price"><?= e($info['price']) ?></span>
                        <span class="category-link">Подобрать номер →</span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-muted">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Подборка</p>
                <h2>Номера, которые стоит увидеть</h2>
            </div>
            <a class="link-arrow" href="<?= e(url('catalog')) ?>">Весь каталог →</a>
        </div>

        <div class="room-grid">
            <?php foreach ($rooms as $room): ?>
                <?php require BASE_PATH . '/app/views/partials/room-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container benefits-grid">
        <article class="benefit">
            <span class="benefit-icon" aria-hidden="true">◷</span>
            <h3>Заезд в любое время</h3>
            <p>Стойка регистрации работает круглосуточно. Предупредите о позднем заезде — вас встретят.</p>
        </article>
        <article class="benefit">
            <span class="benefit-icon" aria-hidden="true">☕</span>
            <h3>Завтрак до полудня</h3>
            <p>Каши, сырники, свежая выпечка и кофе. Включён в стоимость любого номера.</p>
        </article>
        <article class="benefit">
            <span class="benefit-icon" aria-hidden="true">⌘</span>
            <h3>Работать удобно</h3>
            <p>Быстрый Wi-Fi, рабочий стол и тихий двор — можно совместить поездку и дела.</p>
        </article>
    </div>
</section>
