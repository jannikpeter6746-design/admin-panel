<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
require_permission('tasks.view');

// Schnelle Statusänderung aus der Liste
if (is_post()) {
    verify_csrf();
    $task = db_one('SELECT * FROM tasks WHERE id = ?', [input_int('task_id')]);
    $status = input('status');
    if (!$task) {
        not_found('Aufgabe nicht gefunden.');
    }
    if (!can('tasks.manage') && (int) $task['assignee_id'] !== (int) $user['id']) {
        deny('Du kannst nur den Status deiner eigenen Aufgaben ändern.');
    }
    if (isset(TASK_STATUSES[$status]) && $status !== $task['status']) {
        db_query('UPDATE tasks SET status = ? WHERE id = ?', [$status, $task['id']]);
        log_activity('task_status', 'task', (int) $task['id'], sprintf('%s: %s → %s', $task['title'], TASK_STATUSES[$task['status']], TASK_STATUSES[$status]));
        flash('success', 'Status aktualisiert.');
    }
    redirect('tasks.php?' . http_build_query(array_filter([
        'status' => input('f_status'), 'assignee' => input('f_assignee'),
    ])));
}

$status = input('status', 'active');
$assignee = input('assignee');

$where = [];
$params = [];
if ($status === 'active') {
    $where[] = "t.status <> 'done'";
} elseif (isset(TASK_STATUSES[$status])) {
    $where[] = 't.status = ?';
    $params[] = $status;
}
if ($assignee === 'me') {
    $where[] = 't.assignee_id = ?';
    $params[] = $user['id'];
} elseif ($assignee === 'none') {
    $where[] = 't.assignee_id IS NULL';
} elseif (ctype_digit($assignee)) {
    $where[] = 't.assignee_id = ?';
    $params[] = (int) $assignee;
}

$sql = 'SELECT t.*, a.display_name AS assignee_name, c.display_name AS creator_name
        FROM tasks t
        LEFT JOIN users a ON a.id = t.assignee_id
        LEFT JOIN users c ON c.id = t.created_by';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY t.status = 'done', FIELD(t.priority, 'high', 'normal', 'low'), t.due_date IS NULL, t.due_date, t.id DESC";

render('tasks/index', [
    'title'    => 'Aufgaben',
    'tasks'    => db_all($sql, $params),
    'members'  => db_all('SELECT id, display_name FROM users WHERE is_active = 1 ORDER BY display_name'),
    'status'   => $status,
    'assignee' => $assignee,
]);
