<?php
$pageTitle = $pageTitle ?? 'SeaGlass Villa | Slow days by the sea';
$user = current_user();
$isAdminArea = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/');
$basePath = $isAdminArea ? '../' : '';
$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f5ef">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/styles.css">
    <script src="<?= $basePath ?>assets/js/app.js" defer></script>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= $isAdminArea ? 'index.php' : 'index.php' ?>" aria-label="SeaGlass Villa home">
            <span class="brand-mark" aria-hidden="true">S</span>
            <span><strong>SeaGlass</strong><small>VILLA · MIRISSA</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav">Menu</button>
        <nav class="main-nav" id="main-nav" aria-label="Main navigation">
            <?php if ($isAdminArea): ?>
                <a href="index.php">Reservations</a>
                <a href="rooms.php">Rooms</a>
                <a href="../index.php">View villa</a>
                <form class="nav-form" action="../logout.php" method="post"><?= csrf_field() ?><button class="nav-link" type="submit">Sign out</button></form>
            <?php else: ?>
                <a href="index.php#stay">The stay</a>
                <a href="index.php#rooms">Rooms</a>
                <a href="index.php#story">Our place</a>
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <a href="admin/index.php">Admin</a>
                <?php elseif ($user): ?>
                    <a href="my-bookings.php">My bookings</a>
                <?php endif; ?>
                <?php if ($user): ?>
                    <form class="nav-form" action="logout.php" method="post"><?= csrf_field() ?><button class="nav-link" type="submit">Sign out</button></form>
                <?php else: ?>
                    <a href="login.php">Sign in</a>
                    <a class="button button-small" href="register.php">Book a stay</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if ($flashMessage): ?>
    <div class="flash flash-<?= e($flashMessage['type']) ?>" role="status"><?= e($flashMessage['message']) ?></div>
<?php endif; ?>
<main>
