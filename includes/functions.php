<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float|int|string $amount): string
{
    return 'LKR ' . number_format((float) $amount, 0);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        $_SESSION['flash'] = ['type' => 'info', 'message' => 'Please sign in to continue.'];
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_admin(): array
{
    $user = current_user();
    if (!$user) {
        flash('info', 'Please sign in to continue.');
        header('Location: ../login.php');
        exit;
    }
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Administrator access is required.');
    }
    return $user;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function valid_stay(string $checkIn, string $checkOut): bool
{
    $in = DateTimeImmutable::createFromFormat('!Y-m-d', $checkIn);
    $out = DateTimeImmutable::createFromFormat('!Y-m-d', $checkOut);
    $today = new DateTimeImmutable('today');
    return $in !== false && $out !== false && $in->format('Y-m-d') === $checkIn
        && $out->format('Y-m-d') === $checkOut && $in >= $today && $out > $in;
}

function nights_between(string $checkIn, string $checkOut): int
{
    return (int) (new DateTimeImmutable($checkIn))->diff(new DateTimeImmutable($checkOut))->days;
}

function overlaps_confirmed(PDO $pdo, int $roomId, string $checkIn, string $checkOut, ?int $excludeId = null): bool
{
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE room_id = :room_id AND booking_status = 'confirmed'
              AND check_in < :check_out AND check_out > :check_in";
    $params = [':room_id' => $roomId, ':check_in' => $checkIn, ':check_out' => $checkOut];
    if ($excludeId !== null) {
        $sql .= ' AND booking_id <> :exclude_id';
        $params[':exclude_id'] = $excludeId;
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return (int) $statement->fetchColumn() > 0;
}

function room_image(string $roomType): string
{
    $map = ['suite' => 'room1.jpg', 'family' => 'room2.webp', 'garden' => 'room3.webp'];
    return 'assets/images/' . ($map[strtolower($roomType)] ?? 'room-suite.svg');
}
