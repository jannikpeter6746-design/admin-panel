<?php
require __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$error = null;
$username = '';

if (is_post()) {
    verify_csrf();
    $username = input('username');
    $password = (string) ($_POST['password'] ?? '');

    if (too_many_login_attempts(client_ip())) {
        $error = 'Zu viele fehlgeschlagene Anmeldeversuche. Bitte warte einige Minuten.';
    } elseif ($username === '' || $password === '') {
        $error = 'Bitte Benutzername und Passwort eingeben.';
    } elseif ($user = attempt_login($username, $password)) {
        log_activity('login', 'user', (int) $user['id'], null, (int) $user['id']);
        redirect('index.php');
    } else {
        log_activity('login_failed', null, null, 'Benutzername: ' . $username);
        $error = 'Anmeldung fehlgeschlagen. Bitte prüfe deine Zugangsdaten.';
    }
}

render('login', ['title' => 'Anmelden', 'error' => $error, 'username' => $username]);
