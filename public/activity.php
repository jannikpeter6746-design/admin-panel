<?php
require __DIR__ . '/../src/bootstrap.php';

require_login();
require_permission('activity.view');

$perPage = 50;
$page = max(1, input_int('page'));
$action = input('action');
$userId = input_int('user');

$where = [];
$params = [];
if ($action !== '' && isset(ACTIVITY_LABELS[$action])) {
    $where[] = 'a.action = ?';
    $params[] = $action;
}
if ($userId > 0) {
    $where[] = 'a.user_id = ?';
    $params[] = $userId;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$total = (int) db_value('SELECT COUNT(*) FROM activity_log a' . $whereSql, $params);
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$entries = db_all(
    'SELECT a.*, u.display_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id'
    . $whereSql . " ORDER BY a.id DESC LIMIT $perPage OFFSET $offset",
    $params
);

render('activity', [
    'title'   => 'Aktivitätsprotokoll',
    'entries' => $entries,
    'members' => db_all('SELECT id, display_name FROM users ORDER BY display_name'),
    'action'  => $action,
    'userId'  => $userId,
    'page'    => $page,
    'pages'   => $pages,
    'total'   => $total,
    'showIp'  => true,
]);
