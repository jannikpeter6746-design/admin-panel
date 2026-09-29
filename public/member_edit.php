<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
require_permission('members.manage');

$id = input_int('id');
$member = null;
if ($id > 0) {
    $member = db_one('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$member) {
        not_found('Dieses Mitglied existiert nicht.');
    }
    if ((int) $member['id'] === (int) $user['id']) {
        flash('info', 'Dein eigenes Konto bearbeitest du im Profil.');
        redirect('profile.php');
    }
    if (!can_manage_member($user, $member)) {
        deny('Du darfst dieses Mitglied nicht bearbeiten.');
    }
}

$roles = assignable_roles($user);
$form = [
    'username'     => $member['username'] ?? '',
    'email'        => $member['email'] ?? '',
    'display_name' => $member['display_name'] ?? '',
    'role'         => $member['role'] ?? 'supporter',
];
$errors = [];

if (is_post()) {
    verify_csrf();
    foreach (array_keys($form) as $field) {
        $form[$field] = input($field);
    }
    $password = (string) ($_POST['password'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $form['username'])) {
        $errors[] = 'Benutzername: 3–50 Zeichen, erlaubt sind Buchstaben, Zahlen, Punkt, Unter- und Bindestrich.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Bitte eine gültige E-Mail-Adresse angeben.';
    }
    if ($form['display_name'] === '' || mb_strlen($form['display_name']) > 100) {
        $errors[] = 'Anzeigename ist erforderlich (max. 100 Zeichen).';
    }
    if (!isset($roles[$form['role']])) {
        $errors[] = 'Diese Rolle darfst du nicht vergeben.';
    }
    if ($member && $form['role'] !== $member['role'] && is_last_active_owner($member)) {
        $errors[] = 'Der letzte aktive Owner kann nicht herabgestuft werden.';
    }
    if (!$member || $password !== '') {
        if ($msg = validate_password($password)) {
            $errors[] = $msg;
        }
    }
    $duplicate = db_one(
        'SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ?',
        [$form['username'], $form['email'], $member['id'] ?? 0]
    );
    if ($duplicate) {
        $errors[] = 'Benutzername oder E-Mail wird bereits verwendet.';
    }

    if (!$errors) {
        if ($member) {
            db_query(
                'UPDATE users SET username = ?, email = ?, display_name = ?, role = ? WHERE id = ?',
                [$form['username'], $form['email'], $form['display_name'], $form['role'], $member['id']]
            );
            log_activity('member_updated', 'user', (int) $member['id'], $form['display_name']);
            if ($form['role'] !== $member['role']) {
                log_activity('member_role', 'user', (int) $member['id'], sprintf('%s: %s → %s', $form['display_name'], role_label($member['role']), role_label($form['role'])));
            }
            if ($password !== '') {
                db_query('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $member['id']]);
                log_activity('password_reset', 'user', (int) $member['id'], $form['display_name']);
            }
            flash('success', 'Mitglied wurde aktualisiert.');
            redirect('member.php?id=' . $member['id']);
        }

        db_query(
            'INSERT INTO users (username, email, display_name, password_hash, role) VALUES (?, ?, ?, ?, ?)',
            [$form['username'], $form['email'], $form['display_name'], password_hash($password, PASSWORD_DEFAULT), $form['role']]
        );
        $newId = (int) db()->lastInsertId();
        log_activity('member_created', 'user', $newId, sprintf('%s (%s)', $form['display_name'], role_label($form['role'])));
        flash('success', 'Mitglied wurde angelegt.');
        redirect('member.php?id=' . $newId);
    }
}

render('members/form', [
    'title'  => $member ? 'Mitglied bearbeiten' : 'Mitglied hinzufügen',
    'member' => $member,
    'form'   => $form,
    'roles'  => $roles,
    'errors' => $errors,
]);
