<?php
require __DIR__ . '/../src/bootstrap.php';

if (is_post()) {
    verify_csrf();
    if ($user = current_user()) {
        log_activity('logout', 'user', (int) $user['id']);
    }
    logout();
}

redirect('login.php');
