<?php
require_once __DIR__ . '/config.php';

$accounts = [
    ['Nila Perera', 'admin@seaglass.lk', '+94 77 555 0140', 'CoastAdmin!2026', 'admin'],
    ['Amaya Silva', 'guest@example.com', '+94 71 234 5678', 'GuestStay!2026', 'guest'],
];

foreach ($accounts as [$name, $email, $phone, $password, $role]) {
    $check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
    $check->execute([$email]);
    if (!$check->fetch()) {
        $insert = $pdo->prepare('INSERT INTO users (name, email, contact_number, password, role) VALUES (?, ?, ?, ?, ?)');
        $insert->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role]);
        echo "Created {$role}: {$email}" . PHP_EOL;
    } else {
        echo "Already exists: {$email}" . PHP_EOL;
    }
}

echo "Demo accounts are ready. Remove this script from any public deployment." . PHP_EOL;
