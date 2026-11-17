<?php /** @var array $room */ ?>
<article class="room-card">
    <div class="room-media">
        <img src="<?= e(room_image($room)) ?>" alt="<?= e($room['title']) ?>" loading="lazy" width="640" height="420">
        <span class="room-badge"><?= e($room['category_name']) ?></span>
    </div>
    <div class="room-body">
        <div class="room-top">
            <h3><?= e($room['title']) ?></h3>
            <p class="room-price"><?= e(money($room['price'])) ?><span>/сутки</span></p>
        </div>
        <ul class="room-meta">
            <li>до <?= (int)$room['capacity'] ?> гостей</li>
            <li><?= e(area_value($room['area'])) ?> м²</li>
        </ul>
        <ul class="room-amenities">
            <?php foreach (array_slice($room['amenities'] ?? [], 0, 4) as $amenity): ?>
                <li><?= e($amenity) ?></li>
            <?php endforeach; ?>
        </ul>
        <a class="btn btn-primary btn-block" href="<?= e(url('booking', ['room_id' => (int)$room['id']])) ?>">Забронировать</a>
    </div>
</article>
