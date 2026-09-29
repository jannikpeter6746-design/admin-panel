<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
require_permission('members.view');

$search = input('q');
$role = input('role');
$status = input('status', 'active');

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(username LIKE ? OR email LIKE ? OR display_name LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}
if ($role !== '' && role_exists($role)) {
    $where[] = 'role = ?';
    $params[] = $role;
}
if ($status === 'active') {
    $where[] = 'is_active = 1';
} elseif ($status === 'inactive') {
    $where[] = 'is_active = 0';
}

$sql = 'SELECT u.*, (SELECT COUNT(*) FROM tasks t WHERE t.assignee_id = u.id AND t.status <> \'done\') AS open_tasks FROM users u';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY FIELD(role, 'owner', 'admin', 'moderator', 'supporter'), display_name";

render('members/index', [
    'title'   => 'Team',
    'members' => db_all($sql, $params),
    'search'  => $search,
    'role'    => $role,
    'status'  => $status,
]);
