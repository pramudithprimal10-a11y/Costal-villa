<?php
require_once __DIR__ . '/config.php';
if (current_user()) redirect(current_user()['role'] === 'admin' ? 'admin/index.php' : 'my-bookings.php');
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $statement = $pdo->prepare('SELECT user_id, name, email, contact_number, password, role FROM users WHERE email = ?');
    $statement->execute([$email]);
    $account = $statement->fetch();
    if ($account && password_verify($password, $account['password'])) {
        session_regenerate_id(true);
        unset($account['password']);
        $_SESSION['user'] = $account;
        flash('success', 'Welcome back, ' . $account['name'] . '.');
        redirect($account['role'] === 'admin' ? 'admin/index.php' : 'my-bookings.php');
    }
    $error = 'We could not match those details. Please try again.';
}
$pageTitle = 'Sign in | SeaGlass Villa'; require __DIR__ . '/includes/header.php';
?>
<section class="auth-section wrap"><div class="auth-aside"><p class="eyebrow eyebrow-light">A little closer to the coast</p><h1>Welcome<br><em>back.</em></h1><p>Your room, your dates, and the details of your next stay are waiting here.</p></div><div class="form-panel"><p class="eyebrow">Your SeaGlass account</p><h2>Sign in</h2><?php if ($error): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><?= csrf_field() ?><label>Email address<input type="email" name="email" autocomplete="email" required value="<?= e($email) ?>"></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="button button-dark button-wide" type="submit">Sign in <span aria-hidden="true">→</span></button></form><p class="form-foot">New to SeaGlass? <a class="text-link" href="register.php">Create an account</a></p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
