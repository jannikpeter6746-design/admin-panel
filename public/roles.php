<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();

$counts = [];
foreach (db_all('SELECT role, COUNT(*) AS c FROM users WHERE is_active = 1 GROUP BY role') as $row) {
    $counts[$row['role']] = (int) $row['c'];
}

render('roles', ['title' => 'Rollen & Rechte', 'counts' => $counts]);
