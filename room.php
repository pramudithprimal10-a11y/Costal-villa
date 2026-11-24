<?php
require_once __DIR__ . '/config.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$statement = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ? AND room_status = 'active'");
$statement->execute([$id]);
$room = $statement->fetch();
if (!$room) { http_response_code(404); $pageTitle = 'Room not found'; require __DIR__ . '/includes/header.php'; echo '<section class="section wrap"><h1>Room not found</h1><p>This room may no longer be available.</p><a class="text-link" href="index.php">Back to all rooms →</a></section>'; require __DIR__ . '/includes/footer.php'; exit; }
$pageTitle = $room['room_name'] . ' | SeaGlass Villa';
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
require __DIR__ . '/includes/header.php';
?>
<section class="detail-hero wrap"><div class="detail-image"><img src="<?= e(room_image($room['room_type'])) ?>" alt="Illustration of <?= e($room['room_name']) ?>"></div><div class="detail-copy"><p class="eyebrow"><?= e(ucfirst($room['room_type'])) ?> room · Mirissa</p><h1><?= e($room['room_name']) ?></h1><p class="lead"><?= e($room['description']) ?></p><div class="detail-meta"><span>♧ <?= (int) $room['max_guests'] ?> guests</span><span>◷ <?= e($room['bed_summary']) ?></span><span>☼ Garden view</span></div><div class="detail-price"><span><strong><?= money($room['price_per_night']) ?></strong> / night</span><span>Breakfast included</span></div><a class="button button-dark" href="book.php?room_id=<?= (int) $room['room_id'] ?><?= $checkIn && $checkOut ? '&check_in=' . urlencode($checkIn) . '&check_out=' . urlencode($checkOut) : '' ?>">Request this room <span aria-hidden="true">→</span></a><small class="muted">No payment is taken online. Your request will be pending until confirmed.</small></div></section>
<section class="detail-features wrap"><p class="eyebrow">The little comforts</p><div class="feature-list"><span>Air conditioning</span><span>Private ensuite</span><span>Fresh linen</span><span>Wi-Fi</span><span>Daily breakfast</span><span>Garden access</span></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
