<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
require_permission('members.view');

$member = db_one('SELECT * FROM users WHERE id = ?', [input_int('id')]);
if (!$member) {
    not_found('Dieses Mitglied existiert nicht.');
}
$memberId = (int) $member['id'];
$isSelf = $memberId === (int) $user['id'];
$canManage = !$isSelf && can_manage_member($user, $member);

if (is_post()) {
    verify_csrf();
    $action = input('action');

    switch ($action) {
        case 'add_note':
            require_permission('notes.create');
            $body = input('body');
            if ($body === '') {
                flash('error', 'Die Notiz darf nicht leer sein.');
                break;
            }
            db_query('INSERT INTO notes (member_id, author_id, body) VALUES (?, ?, ?)', [$memberId, $user['id'], $body]);
            log_activity('note_created', 'user', $memberId, 'Notiz zu ' . $member['display_name']);
            flash('success', 'Notiz gespeichert.');
            break;

        case 'delete_note':
            $note = db_one('SELECT * FROM notes WHERE id = ? AND member_id = ?', [input_int('note_id'), $memberId]);
            if (!$note) {
                not_found('Notiz nicht gefunden.');
            }
            if ((int) $note['author_id'] !== (int) $user['id'] && !can('notes.delete')) {
                deny('Du darfst nur eigene Notizen löschen.');
            }
            db_query('DELETE FROM notes WHERE id = ?', [$note['id']]);
            log_activity('note_deleted', 'user', $memberId, 'Notiz zu ' . $member['display_name']);
            flash('success', 'Notiz gelöscht.');
            break;

        case 'toggle_active':
            if (!$canManage) {
                deny();
            }
            if ($member['is_active'] && is_last_active_owner($member)) {
                flash('error', 'Der letzte aktive Owner kann nicht deaktiviert werden.');
                break;
            }
            $newState = $member['is_active'] ? 0 : 1;
            db_query('UPDATE users SET is_active = ? WHERE id = ?', [$newState, $memberId]);
            log_activity($newState ? 'member_enabled' : 'member_disabled', 'user', $memberId, $member['display_name']);
            flash('success', $newState ? 'Mitglied wurde aktiviert.' : 'Mitglied wurde deaktiviert.');
            break;

        case 'delete':
            if (!$canManage) {
                deny();
            }
            if (is_last_active_owner($member)) {
                flash('error', 'Der letzte aktive Owner kann nicht gelöscht werden.');
                break;
            }
            db_query('DELETE FROM users WHERE id = ?', [$memberId]);
            log_activity('member_deleted', 'user', $memberId, sprintf('%s (%s)', $member['display_name'], $member['username']));
            flash('success', 'Mitglied wurde gelöscht.');
            redirect('members.php');

        default:
            flash('error', 'Unbekannte Aktion.');
    }
    redirect('member.php?id=' . $memberId);
}

$notes = can('notes.view')
    ? db_all('SELECT n.*, u.display_name AS author_name FROM notes n LEFT JOIN users u ON u.id = n.author_id WHERE n.member_id = ? ORDER BY n.created_at DESC', [$memberId])
    : [];

$tasks = db_all(
    "SELECT * FROM tasks WHERE assignee_id = ? ORDER BY status = 'done', FIELD(priority, 'high', 'normal', 'low'), due_date IS NULL, due_date",
    [$memberId]
);

$entries = can('activity.view')
    ? db_all('SELECT a.*, u.display_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id WHERE a.user_id = ? OR (a.target_type = \'user\' AND a.target_id = ?) ORDER BY a.id DESC LIMIT 15', [$memberId, $memberId])
    : [];

render('members/show', [
    'title'     => $member['display_name'],
    'member'    => $member,
    'isSelf'    => $isSelf,
    'canManage' => $canManage,
    'notes'     => $notes,
    'tasks'     => $tasks,
    'entries'   => $entries,
]);
