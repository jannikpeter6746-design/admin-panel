<?php

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $user = null;
    if (!empty($_SESSION['user_id'])) {
        $row = db_one('SELECT * FROM users WHERE id = ? AND is_active = 1', [$_SESSION['user_id']]);
        if ($row) {
            $user = $row;
        } else {
            // Konto gelöscht oder deaktiviert -> Session beenden
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('login.php');
    }
    return $user;
}

function too_many_login_attempts(string $ip): bool
{
    $count = (int) db_value(
        'SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > (NOW() - INTERVAL ? MINUTE)',
        [$ip, (int) config('security.login_window_minutes', 15)]
    );
    return $count >= (int) config('security.max_login_attempts', 5);
}

function attempt_login(string $username, string $password): ?array
{
    $user = db_one('SELECT * FROM users WHERE username = ? OR email = ?', [$username, $username]);

    if (!$user || !password_verify($password, $user['password_hash']) || !$user['is_active']) {
        db_query('INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)', [client_ip(), substr($username, 0, 50)]);
        return null;
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        db_query('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['last_activity'] = time();

    db_query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
    db_query('DELETE FROM login_attempts WHERE ip_address = ?', [client_ip()]);

    return $user;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function enforce_session_timeout(): void
{
    if (empty($_SESSION['user_id'])) {
        return;
    }
    $timeout = (int) config('security.session_timeout_minutes', 60) * 60;
    $last = $_SESSION['last_activity'] ?? time();
    if (time() - $last > $timeout) {
        logout();
        session_start();
        flash('info', 'Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.');
        return;
    }
    $_SESSION['last_activity'] = time();
}
