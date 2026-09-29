<?php

const ACTIVITY_LABELS = [
    'login'            => 'Angemeldet',
    'logout'           => 'Abgemeldet',
    'login_failed'     => 'Fehlgeschlagener Login',
    'member_created'   => 'Mitglied angelegt',
    'member_updated'   => 'Mitglied bearbeitet',
    'member_role'      => 'Rolle geändert',
    'member_disabled'  => 'Mitglied deaktiviert',
    'member_enabled'   => 'Mitglied aktiviert',
    'member_deleted'   => 'Mitglied gelöscht',
    'password_changed' => 'Passwort geändert',
    'password_reset'   => 'Passwort zurückgesetzt',
    'note_created'     => 'Notiz erstellt',
    'note_deleted'     => 'Notiz gelöscht',
    'task_created'     => 'Aufgabe erstellt',
    'task_updated'     => 'Aufgabe bearbeitet',
    'task_status'      => 'Aufgabenstatus geändert',
    'task_deleted'     => 'Aufgabe gelöscht',
];

function log_activity(string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null, ?int $userId = null): void
{
    $userId = $userId ?? (current_user()['id'] ?? null);
    db_query(
        'INSERT INTO activity_log (user_id, action, target_type, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)',
        [$userId, $action, $targetType, $targetId, $details !== null ? mb_substr($details, 0, 500) : null, PHP_SAPI === 'cli' ? 'cli' : client_ip()]
    );
}

function activity_label(string $action): string
{
    return ACTIVITY_LABELS[$action] ?? $action;
}
