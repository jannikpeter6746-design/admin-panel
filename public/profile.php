<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();

$form = [
    'display_name' => $user['display_name'],
    'email'        => $user['email'],
];
$errors = [];

if (is_post()) {
    verify_csrf();
    $action = input('action');

    if ($action === 'profile') {
        $form['display_name'] = input('display_name');
        $form['email'] = input('email');

        if ($form['display_name'] === '' || mb_strlen($form['display_name']) > 100) {
            $errors[] = 'Anzeigename ist erforderlich (max. 100 Zeichen).';
        }
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Bitte eine gültige E-Mail-Adresse angeben.';
        } elseif (db_one('SELECT id FROM users WHERE email = ? AND id <> ?', [$form['email'], $user['id']])) {
            $errors[] = 'Diese E-Mail-Adresse wird bereits verwendet.';
        }
        if (!$errors) {
            db_query('UPDATE users SET display_name = ?, email = ? WHERE id = ?', [$form['display_name'], $form['email'], $user['id']]);
            log_activity('member_updated', 'user', (int) $user['id'], 'Eigenes Profil');
            flash('success', 'Profil gespeichert.');
            redirect('profile.php');
        }
    } elseif ($action === 'password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['new_password_confirm'] ?? '');

        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Das aktuelle Passwort ist falsch.';
        } elseif ($msg = validate_password($new)) {
            $errors[] = $msg;
        } elseif ($new !== $confirm) {
            $errors[] = 'Die neuen Passwörter stimmen nicht überein.';
        }
        if (!$errors) {
            db_query('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            session_regenerate_id(true);
            log_activity('password_changed', 'user', (int) $user['id']);
            flash('success', 'Passwort geändert.');
            redirect('profile.php');
        }
    }
}

render('profile', ['title' => 'Mein Profil', 'user' => $user, 'form' => $form, 'errors' => $errors]);
