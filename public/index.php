<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
require_permission('dashboard.view');

$stats = [
    'members'    => db_value('SELECT COUNT(*) FROM users WHERE is_active = 1'),
    'open_tasks' => db_value("SELECT COUNT(*) FROM tasks WHERE status <> 'done'"),
    'my_tasks'   => db_value("SELECT COUNT(*) FROM tasks WHERE status <> 'done' AND assignee_id = ?", [$user['id']]),
    'overdue'    => db_value("SELECT COUNT(*) FROM tasks WHERE status <> 'done' AND due_date < CURDATE()"),
];

$myTasks = db_all(
    "SELECT * FROM tasks WHERE assignee_id = ? AND status <> 'done'
     ORDER BY FIELD(priority, 'high', 'normal', 'low'), due_date IS NULL, due_date LIMIT 10",
    [$user['id']]
);

$entries = can('activity.view')
    ? db_all('SELECT a.*, u.display_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 8')
    : [];

render('dashboard', ['title' => 'Dashboard', 'stats' => $stats, 'myTasks' => $myTasks, 'entries' => $entries]);
