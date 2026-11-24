<?php
require_once __DIR__ . '/config.php';
if (current_user()) redirect(current_user()['role'] === 'admin' ? 'admin/index.php' : 'my-bookings.php');
$errors = [];
$values = ['name' => '', 'email' => '', 'contact_number' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => strtolower(trim($_POST['email'] ?? '')),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    if (mb_strlen($values['name']) < 2 || mb_strlen($values['name']) > 100) $errors[] = 'Enter a name between 2 and 100 characters.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (mb_strlen($values['contact_number']) < 7 || mb_strlen($values['contact_number']) > 25) $errors[] = 'Enter a valid contact number.';
    if (strlen($password) < 8) $errors[] = 'Use a password with at least 8 characters.';
    if (!$errors) {
        $check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $check->execute([$values['email']]);
        if ($check->fetch()) $errors[] = 'An account with that email already exists.';
        else {
            $insert = $pdo->prepare("INSERT INTO users (name, email, contact_number, password, role) VALUES (?, ?, ?, ?, 'guest')");
            $insert->execute([$values['name'], $values['email'], $values['contact_number'], password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['user_id' => (int) $pdo->lastInsertId(), 'name' => $values['name'], 'email' => $values['email'], 'contact_number' => $values['contact_number'], 'role' => 'guest'];
            flash('success', 'Welcome to SeaGlass. Your account is ready.');
            redirect('my-bookings.php');
        }
    }
}
$pageTitle = 'Create an account | SeaGlass Villa'; require __DIR__ . '/includes/header.php';
?>
<section class="auth-section wrap"><div class="auth-aside"><p class="eyebrow eyebrow-light">Your next slow morning</p><h1>Good stays<br>start <em>here.</em></h1><p>Create an account to request a room and keep all your booking details together.</p></div><div class="form-panel"><p class="eyebrow">Join us by the sea</p><h2>Create your account</h2><?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post" class="stack-form"><?= csrf_field() ?><label>Full name<input name="name" autocomplete="name" required maxlength="100" value="<?= e($values['name']) ?>"></label><label>Email address<input type="email" name="email" autocomplete="email" required value="<?= e($values['email']) ?>"></label><label>Contact number<input name="contact_number" autocomplete="tel" required maxlength="25" value="<?= e($values['contact_number']) ?>"></label><label>Password<input type="password" name="password" autocomplete="new-password" required minlength="8"></label><button class="button button-dark button-wide" type="submit">Create account <span aria-hidden="true">→</span></button></form><p class="form-foot">Already have an account? <a class="text-link" href="login.php">Sign in</a></p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
