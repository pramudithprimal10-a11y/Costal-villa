<?php
require_once __DIR__ . '/config.php';
$user = require_login();
if ($user['role'] === 'admin') redirect('admin/index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT) ?: 0;
    $cancel = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ? AND user_id = ? AND booking_status = 'pending'");
    $cancel->execute([$bookingId, $user['user_id']]);
    flash($cancel->rowCount() ? 'success' : 'error', $cancel->rowCount() ? 'Your pending booking was cancelled.' : 'That booking could not be cancelled.');
    redirect('my-bookings.php');
}
$statement = $pdo->prepare('SELECT b.*, r.room_name, r.room_type FROM bookings b JOIN rooms r ON r.room_id = b.room_id WHERE b.user_id = ? ORDER BY b.booking_date DESC');
$statement->execute([$user['user_id']]);
$bookings = $statement->fetchAll();
$pageTitle = 'My bookings | SeaGlass Villa'; require __DIR__ . '/includes/header.php';
?>
<section class="dashboard-page wrap"><div class="page-heading"><div><p class="eyebrow">Your time by the water</p><h1>My bookings</h1><p>Hi <?= e($user['name']) ?>. Here are the details of your stays.</p></div><a class="button button-dark" href="index.php#rooms">Find another room <span aria-hidden="true">→</span></a></div><?php if (!$bookings): ?><div class="empty-state booking-empty"><span class="empty-icon">✳</span><strong>Your next stay is still a blank page.</strong><p>Browse the rooms and find a little space by the sea.</p><a class="button button-dark" href="index.php#rooms">Explore rooms</a></div><?php else: ?><div class="booking-list"><?php foreach ($bookings as $booking): ?><article class="booking-row"><div class="booking-room"><img src="<?= e(room_image($booking['room_type'])) ?>" alt=""><div><small><?= e(ucfirst($booking['room_type'])) ?> room</small><strong><?= e($booking['room_name']) ?></strong><span>Booking #<?= (int) $booking['booking_id'] ?></span></div></div><div class="booking-dates"><small>YOUR DATES</small><strong><?= e(date('M j', strtotime($booking['check_in']))) ?> – <?= e(date('M j, Y', strtotime($booking['check_out']))) ?></strong><span><?= nights_between($booking['check_in'], $booking['check_out']) ?> nights · <?= (int) $booking['guest_count'] ?> guests</span></div><div class="booking-amount"><small>ESTIMATED TOTAL</small><strong><?= money($booking['total_amount']) ?></strong><span><?= e(ucfirst($booking['booking_status'])) ?></span></div><div class="booking-status"><span class="status status-<?= e($booking['booking_status']) ?>"><?= e(ucfirst($booking['booking_status'])) ?></span><?php if ($booking['booking_status'] === 'pending'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>"><button class="quiet-button" type="submit" data-confirm="Cancel this pending booking?">Cancel request</button></form><?php endif; ?></div></article><?php endforeach; ?></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
