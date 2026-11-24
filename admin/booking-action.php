<?php
require_once dirname(__DIR__) . '/config.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Use the booking action form.'); }
verify_csrf();
$bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT) ?: 0;
$action = $_POST['action'] ?? '';
$statement = $pdo->prepare('SELECT b.*, r.room_status FROM bookings b JOIN rooms r ON r.room_id = b.room_id WHERE b.booking_id = ?');
$statement->execute([$bookingId]);
$booking = $statement->fetch();
if (!$booking) { flash('error', 'Booking not found.'); redirect('index.php'); }

if ($action === 'confirm' && $booking['booking_status'] === 'pending') {
    if ($booking['room_status'] !== 'active' || overlaps_confirmed($pdo, (int) $booking['room_id'], $booking['check_in'], $booking['check_out'], $bookingId)) {
        flash('error', 'This stay overlaps an existing confirmed booking or the room is inactive.');
    } else {
        $update = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ? AND booking_status = 'pending'");
        $update->execute([$bookingId]);
        flash('success', 'Booking confirmed.');
    }
} elseif ($action === 'cancel' && $booking['booking_status'] !== 'cancelled') {
    $update = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ? AND booking_status <> 'cancelled'");
    $update->execute([$bookingId]);
    flash('success', 'Booking cancelled.');
} else {
    flash('info', 'No booking status change was needed.');
}
redirect('index.php');
