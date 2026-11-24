<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'SeaGlass Villa | A slower kind of coast';
$checkIn = trim($_GET['check_in'] ?? '');
$checkOut = trim($_GET['check_out'] ?? '');
$guestCount = max(1, min(8, (int) ($_GET['guests'] ?? 2)));
$searching = $checkIn !== '' || $checkOut !== '';
$searchError = '';

$sql = 'SELECT r.* FROM rooms r WHERE r.room_status = "active" AND r.max_guests >= :guests';
$params = [':guests' => $guestCount];
if ($searching) {
    if (!valid_stay($checkIn, $checkOut)) {
        $searchError = 'Choose a check-in date from today and a later check-out date.';
        $searching = false;
    } else {
        $sql .= " AND NOT EXISTS (
            SELECT 1 FROM bookings b WHERE b.room_id = r.room_id
              AND b.booking_status = 'confirmed'
              AND b.check_in < :check_out AND b.check_out > :check_in
        )";
        $params[':check_in'] = $checkIn;
        $params[':check_out'] = $checkOut;
    }
}
$sql .= ' ORDER BY r.featured DESC, r.price_per_night ASC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$rooms = $statement->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-art" aria-hidden="true"></div>
    <div class="hero-content wrap">
        <p class="eyebrow eyebrow-light"><span class="eyebrow-line"></span> A little place by the Indian Ocean</p>
        <h1>Make room<br>for the <em>slow days.</em></h1>
        <p class="hero-copy">A tucked-away villa in Mirissa, where mornings start with salt air and afternoons have nowhere else to be.</p>
        <a class="button button-light" href="#availability">Find your room <span aria-hidden="true">↗</span></a>
        <div class="hero-note"><span>05° 56' N · 80° 28' E</span><span>Mirissa, Sri Lanka</span></div>
    </div>
    <div class="hero-stamp" aria-hidden="true"><span>Stay a little longer</span><strong>SG</strong><small>2026 · SOUTH COAST</small></div>
</section>

<section class="booking-band" id="availability">
    <div class="wrap booking-band-inner">
        <div><p class="eyebrow">Your coastal pause</p><h2>Find the room that feels like yours.</h2></div>
        <form class="search-form" method="get" action="index.php">
            <label>Check in<input type="date" name="check_in" min="<?= date('Y-m-d') ?>" value="<?= e($checkIn) ?>" required></label>
            <label>Check out<input type="date" name="check_out" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e($checkOut) ?>" required></label>
            <label>Guests<select name="guests"><?php for ($i = 1; $i <= 8; $i++): ?><option value="<?= $i ?>" <?= $i === $guestCount ? 'selected' : '' ?>><?= $i ?> guest<?= $i === 1 ? '' : 's' ?></option><?php endfor; ?></select></label>
            <button class="button button-dark" type="submit">Check dates <span aria-hidden="true">→</span></button>
        </form>
    </div>
</section>

<section class="section wrap" id="rooms">
    <div class="section-heading">
        <div><p class="eyebrow">A stay for every kind of slow</p><h2>Rooms with a little <em>more soul.</em></h2></div>
        <p>Natural textures, thoughtful details, and the gentle rhythm of the coast. Pick a hideaway that fits your kind of getaway.</p>
    </div>
    <?php if ($searchError): ?><div class="notice notice-error"><?= e($searchError) ?></div><?php endif; ?>
    <?php if ($searching): ?><div class="search-result-line"><span><?= count($rooms) ?> room<?= count($rooms) === 1 ? '' : 's' ?> for <?= e(date('M j', strtotime($checkIn))) ?> – <?= e(date('M j, Y', strtotime($checkOut))) ?></span><a href="index.php#rooms">Clear dates</a></div><?php endif; ?>
    <?php if (!$rooms): ?>
        <div class="empty-state"><strong>No rooms match those dates yet.</strong><p>Try different dates or browse all rooms below.</p><a class="text-link" href="index.php#rooms">Browse all rooms →</a></div>
    <?php else: ?>
        <div class="room-grid">
            <?php foreach ($rooms as $room): ?>
                <article class="room-card">
                    <a class="room-photo" href="room.php?id=<?= (int) $room['room_id'] ?>"><img src="<?= e(room_image($room['room_type'])) ?>" alt="Illustration of the <?= e($room['room_name']) ?>"></a>
                    <div class="room-card-body">
                        <div class="room-card-top"><span class="room-kind"><?= e(ucfirst($room['room_type'])) ?> room</span><span class="room-capacity">↗ <?= (int) $room['max_guests'] ?> guests</span></div>
                        <h3><a href="room.php?id=<?= (int) $room['room_id'] ?>"><?= e($room['room_name']) ?></a></h3>
                        <p><?= e($room['short_description']) ?></p>
                        <div class="room-card-bottom"><span><strong><?= money($room['price_per_night']) ?></strong><small> / night</small></span><a class="text-link" href="room.php?id=<?= (int) $room['room_id'] ?><?= $checkIn && $checkOut ? '&check_in=' . urlencode($checkIn) . '&check_out=' . urlencode($checkOut) : '' ?>">Explore room <span aria-hidden="true">→</span></a></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="story-section" id="story">
    <div class="story-art" role="img" aria-label="Stylized sea and palm leaves at the coast"></div>
    <div class="story-copy"><p class="eyebrow">A softer side of the south</p><h2>Close enough to everything. <em>Far from the rush.</em></h2><p>Set between the village and the water, SeaGlass is made for long breakfasts, barefoot walks, and finding your own rhythm. Come as you are. Leave a little lighter.</p><div class="story-detail"><span class="detail-icon">✳</span><span><strong>Made for slow living</strong><small>Garden breakfasts · Ocean air · Local warmth</small></span></div><a class="text-link" href="#stay">The little details <span aria-hidden="true">→</span></a></div>
</section>
<section class="amenities wrap" id="stay">
    <div class="amenity"><span>01</span><strong>Steps to the shore</strong><small>Find the water in just a few minutes.</small></div>
    <div class="amenity"><span>02</span><strong>Breakfast in the garden</strong><small>Fresh fruit, warm hoppers, slow starts.</small></div>
    <div class="amenity"><span>03</span><strong>Thoughtful comfort</strong><small>Cool rooms, soft linens, room to unwind.</small></div>
    <div class="amenity"><span>04</span><strong>Local, always</strong><small>Good people and the best nearby spots.</small></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
