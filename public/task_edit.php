<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
require_permission('tasks.view');

$id = input_int('id');
$task = null;
if ($id > 0) {
    $task = db_one(
        'SELECT t.*, c.display_name AS creator_name FROM tasks t LEFT JOIN users c ON c.id = t.created_by WHERE t.id = ?',
        [$id]
    );
    if (!$task) {
        not_found('Aufgabe nicht gefunden.');
    }
} elseif (!can('tasks.manage')) {
    deny('Du darfst keine Aufgaben erstellen.');
}

$canManage = can('tasks.manage');
$isAssignee = $task && (int) $task['assignee_id'] === (int) $user['id'];
// Aktive Mitglieder + aktuell zugewiesenes (evtl. deaktiviertes) Mitglied
$members = db_all(
    'SELECT id, display_name FROM users WHERE is_active = 1 OR id = ? ORDER BY display_name',
    [(int) ($task['assignee_id'] ?? 0)]
);
$memberIds = array_map('intval', array_column($members, 'id'));

$form = [
    'title'       => $task['title'] ?? '',
    'description' => $task['description'] ?? '',
    'assignee_id' => (string) ($task['assignee_id'] ?? input('assignee_id')),
    'priority'    => $task['priority'] ?? 'normal',
    'status'      => $task['status'] ?? 'open',
    'due_date'    => $task['due_date'] ?? '',
];
$errors = [];

if (is_post()) {
    verify_csrf();
    $action = input('action', 'save');

    if ($action === 'delete') {
        if (!$canManage || !$task) {
            deny();
        }
        db_query('DELETE FROM tasks WHERE id = ?', [$task['id']]);
        log_activity('task_deleted', 'task', (int) $task['id'], $task['title']);
        flash('success', 'Aufgabe gelöscht.');
        redirect('tasks.php');
    }

    if (!$canManage) {
        // Zugewiesene Mitglieder dürfen nur den Status ändern
        if (!$isAssignee) {
            deny('Du kannst diese Aufgabe nicht bearbeiten.');
        }
        $status = input('status');
        if (isset(TASK_STATUSES[$status]) && $status !== $task['status']) {
            db_query('UPDATE tasks SET status = ? WHERE id = ?', [$status, $task['id']]);
            log_activity('task_status', 'task', (int) $task['id'], sprintf('%s: %s → %s', $task['title'], TASK_STATUSES[$task['status']], TASK_STATUSES[$status]));
            flash('success', 'Status aktualisiert.');
        }
        redirect('task_edit.php?id=' . $task['id']);
    }

    foreach (array_keys($form) as $field) {
        $form[$field] = input($field);
    }

    if ($form['title'] === '' || mb_strlen($form['title']) > 200) {
        $errors[] = 'Titel ist erforderlich (max. 200 Zeichen).';
    }
    if ($form['assignee_id'] !== '' && !in_array((int) $form['assignee_id'], $memberIds, true)) {
        $errors[] = 'Ungültiges Teammitglied ausgewählt.';
    }
    if (!isset(TASK_PRIORITIES[$form['priority']])) {
        $errors[] = 'Ungültige Priorität.';
    }
    if (!isset(TASK_STATUSES[$form['status']])) {
        $errors[] = 'Ungültiger Status.';
    }
    if ($form['due_date'] !== '' && !DateTime::createFromFormat('Y-m-d', $form['due_date'])) {
        $errors[] = 'Ungültiges Fälligkeitsdatum.';
    }

    if (!$errors) {
        $values = [
            $form['title'],
            $form['description'] !== '' ? $form['description'] : null,
            $form['assignee_id'] !== '' ? (int) $form['assignee_id'] : null,
            $form['priority'],
            $form['status'],
            $form['due_date'] !== '' ? $form['due_date'] : null,
        ];
        if ($task) {
            db_query('UPDATE tasks SET title = ?, description = ?, assignee_id = ?, priority = ?, status = ?, due_date = ? WHERE id = ?', [...$values, $task['id']]);
            log_activity('task_updated', 'task', (int) $task['id'], $form['title']);
            flash('success', 'Aufgabe gespeichert.');
            redirect('task_edit.php?id=' . $task['id']);
        }
        db_query('INSERT INTO tasks (title, description, assignee_id, priority, status, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)', [...$values, $user['id']]);
        $newId = (int) db()->lastInsertId();
        log_activity('task_created', 'task', $newId, $form['title']);
        flash('success', 'Aufgabe erstellt.');
        redirect('tasks.php');
    }
}

render('tasks/form', [
    'title'      => $task ? $task['title'] : 'Neue Aufgabe',
    'task'       => $task,
    'form'       => $form,
    'members'    => $members,
    'errors'     => $errors,
    'canManage'  => $canManage,
    'isAssignee' => $isAssignee,
]);
