<?php
require_once __DIR__ . '/config.php';
$user = require_login();
$roomId = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT) ?: (int) ($_POST['room_id'] ?? 0);
$roomStatement = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ? AND room_status = 'active'");
$roomStatement->execute([$roomId]);
$room = $roomStatement->fetch();
if (!$room) { flash('error', 'That room is no longer available.'); redirect('index.php#rooms'); }
$checkIn = trim($_POST['check_in'] ?? $_GET['check_in'] ?? '');
$checkOut = trim($_POST['check_out'] ?? $_GET['check_out'] ?? '');
$guests = max(1, (int) ($_POST['guest_count'] ?? 1));
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!valid_stay($checkIn, $checkOut)) $errors[] = 'Choose a check-in date from today and a later check-out date.';
    if ($guests < 1 || $guests > (int) $room['max_guests']) $errors[] = 'Choose a guest count supported by this room.';
    if (!$errors && overlaps_confirmed($pdo, $roomId, $checkIn, $checkOut)) $errors[] = 'This room has a confirmed booking during those dates. Please choose another stay.';
    if (!$errors) {
        $nights = nights_between($checkIn, $checkOut);
        $amount = $nights * (float) $room['price_per_night'];
        $insert = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, guest_count, total_amount, booking_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $insert->execute([$user['user_id'], $roomId, $checkIn, $checkOut, $guests, $amount]);
        flash('success', 'Your request is in. We will confirm your stay soon.');
        redirect('my-bookings.php');
    }
}
$pageTitle = 'Request a stay | SeaGlass Villa'; require __DIR__ . '/includes/header.php';
?>
<section class="booking-page wrap"><div class="booking-intro"><p class="eyebrow">A few details and it is yours</p><h1>Request your<br><em>coastal stay.</em></h1><p>Your booking remains pending until the villa confirms availability. No payment is collected online.</p><a class="text-link" href="room.php?id=<?= (int) $room['room_id'] ?>">← Back to room details</a></div><div class="booking-form-wrap"><div class="booking-room-summary"><img src="<?= e(room_image($room['room_type'])) ?>" alt=""><div><small><?= e(ucfirst($room['room_type'])) ?> room</small><strong><?= e($room['room_name']) ?></strong><span><?= money($room['price_per_night']) ?> / night</span></div></div><?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post" class="stack-form" data-price-per-night="<?= e((string) $room['price_per_night']) ?>"><?= csrf_field() ?><input type="hidden" name="room_id" value="<?= (int) $room['room_id'] ?>"><div class="form-grid"><label>Check in<input type="date" name="check_in" min="<?= date('Y-m-d') ?>" value="<?= e($checkIn) ?>" required></label><label>Check out<input type="date" name="check_out" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e($checkOut) ?>" required></label></div><label>Guests<select name="guest_count"><?php for ($i = 1; $i <= (int) $room['max_guests']; $i++): ?><option value="<?= $i ?>" <?= $i === $guests ? 'selected' : '' ?>><?= $i ?> guest<?= $i === 1 ? '' : 's' ?></option><?php endfor; ?></select></label><div class="guest-details"><span>Booking as</span><strong><?= e($user['name']) ?></strong><small><?= e($user['email']) ?> · <?= e($user['contact_number'] ?? 'Contact details on file') ?></small></div><div class="estimate"><span data-total-nights>Choose dates</span><strong>LKR <span data-total-price>—</span></strong></div><button class="button button-dark button-wide" type="submit">Send booking request <span aria-hidden="true">→</span></button></form></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
