<?php /** @var array $rooms */ ?>
<section class="page-head">
    <div class="container">
        <p class="eyebrow">Каталог</p>
        <h1>Номера и цены</h1>
        <p class="page-lead">Фильтры работают на стороне сервера: выберите категорию, бюджет и количество гостей.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container catalog-layout">
        <form class="filter-card" method="get" action="index.php" aria-label="Фильтр номеров">
            <input type="hidden" name="r" value="catalog">
            <h2>Фильтр</h2>

            <label class="filter-field">
                <span>Категория</span>
                <select name="category_id">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>"
                            <?= (int)($filters['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="filter-row">
                <label class="filter-field">
                    <span>Цена от, ₽</span>
                    <input type="number" name="price_min" min="0" step="100"
                           value="<?= e(query_string('price_min')) ?>" placeholder="2 500">
                </label>
                <label class="filter-field">
                    <span>до, ₽</span>
                    <input type="number" name="price_max" min="0" step="100"
                           value="<?= e(query_string('price_max')) ?>" placeholder="18 000">
                </label>
            </div>

            <label class="filter-field">
                <span>Количество гостей</span>
                <select name="capacity">
                    <option value="">Не важно</option>
                    <?php for ($guests = 1; $guests <= 4; $guests++): ?>
                        <option value="<?= $guests ?>" <?= (int)($filters['capacity'] ?? 0) === $guests ? 'selected' : '' ?>>
                            от <?= $guests ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </label>

            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">Применить</button>
                <a class="btn btn-ghost" href="<?= e(url('catalog')) ?>">Сбросить фильтр</a>
            </div>
        </form>

        <div class="catalog-results">
            <div class="results-head">
                <p>Найдено номеров: <strong><?= count($rooms) ?></strong></p>
                <?php if ($hasFilters): ?>
                    <span class="hint">Показаны все номера, подходящие под условия</span>
                <?php else: ?>
                    <span class="hint">Случайная подборка из <?= (int)config('catalog_random_count', 6) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($rooms === []): ?>
                <div class="empty-state">
                    <h2>Ничего не нашлось</h2>
                    <p>Попробуйте увеличить бюджет или выбрать другую категорию — в гостинице точно есть свободные номера.</p>
                    <a class="btn btn-primary" href="<?= e(url('catalog')) ?>">Сбросить фильтр</a>
                </div>
            <?php else: ?>
                <div class="room-grid">
                    <?php foreach ($rooms as $room): ?>
                        <?php require BASE_PATH . '/app/views/partials/room-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
