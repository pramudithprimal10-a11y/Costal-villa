<?php
require_once dirname(__DIR__) . '/config.php';
require_admin();
$errors = [];
$form = ['room_id' => '', 'room_name' => '', 'room_type' => 'suite', 'short_description' => '', 'description' => '', 'price_per_night' => '', 'max_guests' => '2', 'bed_summary' => 'Queen bed', 'room_status' => 'active', 'featured' => '0'];
$allowedTypes = ['suite', 'family', 'garden'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'deactivate' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE rooms SET room_status = 'inactive' WHERE room_id = ?");
        $stmt->execute([$id]);
        flash('success', 'Room deactivated. Existing booking records were kept.'); redirect('rooms.php');
    }
    if ($action === 'save') {
        $form = [
            'room_id' => $id ?: '', 'room_name' => trim($_POST['room_name'] ?? ''),
            'room_type' => $_POST['room_type'] ?? '', 'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => trim($_POST['description'] ?? ''), 'price_per_night' => trim($_POST['price_per_night'] ?? ''),
            'max_guests' => trim($_POST['max_guests'] ?? ''), 'bed_summary' => trim($_POST['bed_summary'] ?? ''),
            'room_status' => $_POST['room_status'] ?? 'active', 'featured' => isset($_POST['featured']) ? '1' : '0',
        ];
        if ($form['room_name'] === '' || mb_strlen($form['room_name']) > 100) $errors[] = 'Enter a room name up to 100 characters.';
        if (!in_array($form['room_type'], $allowedTypes, true)) $errors[] = 'Choose a listed room type.';
        if ($form['short_description'] === '' || mb_strlen($form['short_description']) > 220) $errors[] = 'Enter a short description up to 220 characters.';
        if ($form['description'] === '') $errors[] = 'Enter a room description.';
        if (!is_numeric($form['price_per_night']) || (float) $form['price_per_night'] <= 0) $errors[] = 'Enter a price above zero.';
        if (!filter_var($form['max_guests'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]])) $errors[] = 'Guest capacity must be between 1 and 12.';
        if (!in_array($form['room_status'], ['active', 'inactive'], true)) $errors[] = 'Choose an available room status.';
        if (!$errors) {
            $fields = [$form['room_name'], $form['room_type'], $form['short_description'], $form['description'], (float) $form['price_per_night'], (int) $form['max_guests'], $form['bed_summary'], $form['room_status'], (int) $form['featured']];
            if ($id) {
                $stmt = $pdo->prepare('UPDATE rooms SET room_name=?, room_type=?, short_description=?, description=?, price_per_night=?, max_guests=?, bed_summary=?, room_status=?, featured=? WHERE room_id=?');
                $stmt->execute([...$fields, $id]);
                flash('success', 'Room details updated.'); redirect('rooms.php');
            } else {
                $stmt = $pdo->prepare('INSERT INTO rooms (room_name, room_type, short_description, description, price_per_night, max_guests, bed_summary, room_status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute($fields);
                flash('success', 'Room added to the collection.'); redirect('rooms.php');
            }
        }
    }
}
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE room_id = ?'); $stmt->execute([(int) $_GET['edit']]);
    $existing = $stmt->fetch();
    if ($existing) $form = array_merge($form, $existing);
}
$rooms = $pdo->query('SELECT r.*, (SELECT COUNT(*) FROM bookings b WHERE b.room_id=r.room_id) AS booking_count FROM rooms r ORDER BY r.room_status, r.room_name')->fetchAll();
$pageTitle = 'Manage rooms | SeaGlass Villa'; require dirname(__DIR__) . '/includes/header.php';
?>
<section class="dashboard-page wrap"><div class="page-heading"><div><p class="eyebrow">SeaGlass operations</p><h1>Room collection</h1><p>Add a new hideaway, update its details, or make it unavailable.</p></div><a class="button button-outline" href="index.php">← Reservations</a></div><div class="admin-content-grid"><div class="form-panel admin-form-panel"><p class="eyebrow"><?= $form['room_id'] ? 'Update a room' : 'Add to the collection' ?></p><h2><?= $form['room_id'] ? 'Room details' : 'New room' ?></h2><?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post" class="stack-form"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="room_id" value="<?= e((string) $form['room_id']) ?>"><label>Room name<input name="room_name" required maxlength="100" value="<?= e((string) $form['room_name']) ?>"></label><div class="form-grid"><label>Room type<select name="room_type"><?php foreach ($allowedTypes as $type): ?><option value="<?= $type ?>" <?= $type === $form['room_type'] ? 'selected' : '' ?>><?= ucfirst($type) ?></option><?php endforeach; ?></select></label><label>Max guests<input type="number" name="max_guests" min="1" max="12" required value="<?= e((string) $form['max_guests']) ?>"></label></div><label>Short description<input name="short_description" maxlength="220" required value="<?= e((string) $form['short_description']) ?>"></label><label>Full description<textarea name="description" rows="4" required><?= e((string) $form['description']) ?></textarea></label><div class="form-grid"><label>Price per night (LKR)<input type="number" name="price_per_night" min="1" step="100" required value="<?= e((string) $form['price_per_night']) ?>"></label><label>Bed summary<input name="bed_summary" maxlength="120" required value="<?= e((string) $form['bed_summary']) ?>"></label></div><label>Status<select name="room_status"><option value="active" <?= $form['room_status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $form['room_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></label><label class="check-label"><input type="checkbox" name="featured" value="1" <?= (int) $form['featured'] ? 'checked' : '' ?>> Feature this room on the home page</label><button class="button button-dark button-wide" type="submit"><?= $form['room_id'] ? 'Save room changes' : 'Add room' ?> <span aria-hidden="true">→</span></button><?php if ($form['room_id']): ?><a class="text-link" href="rooms.php">Cancel editing</a><?php endif; ?></form></div><div class="room-admin-list"><p class="eyebrow">Current rooms</p><h2>Room list <span class="muted">(<?= count($rooms) ?>)</span></h2><?php foreach ($rooms as $room): ?><article class="room-admin-card"><img src="../<?= e(room_image($room['room_type'])) ?>" alt=""><div class="room-admin-main"><strong><?= e($room['room_name']) ?></strong><small><?= e(ucfirst($room['room_type'])) ?> · <?= money($room['price_per_night']) ?> / night · <?= (int) $room['max_guests'] ?> guests</small><span class="status status-<?= e($room['room_status']) ?>"><?= e(ucfirst($room['room_status'])) ?></span></div><div class="room-admin-actions"><a class="mini-button" href="rooms.php?edit=<?= (int) $room['room_id'] ?>">Edit</a><?php if ($room['room_status'] === 'active'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="deactivate"><input type="hidden" name="room_id" value="<?= (int) $room['room_id'] ?>"><button class="mini-button mini-muted" type="submit" data-confirm="Deactivate this room? Existing booking history will stay intact.">Deactivate</button></form><?php endif; ?></div></article><?php endforeach; ?></div></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
