<?php
// Rollen & Rechte. Höheres Level = mehr Rechte.
// Ein Mitglied kann nur Mitglieder mit niedrigerem Level verwalten.

const ROLES = [
    'owner'     => ['label' => 'Owner',     'level' => 100, 'color' => '#c0392b'],
    'admin'     => ['label' => 'Admin',     'level' => 80,  'color' => '#d35400'],
    'moderator' => ['label' => 'Moderator', 'level' => 50,  'color' => '#2980b9'],
    'supporter' => ['label' => 'Supporter', 'level' => 30,  'color' => '#27ae60'],
];

// Recht => minimal benötigte Rolle
const PERMISSIONS = [
    'dashboard.view'  => ['label' => 'Dashboard ansehen',                 'min_role' => 'supporter'],
    'members.view'    => ['label' => 'Teammitglieder ansehen',            'min_role' => 'supporter'],
    'members.manage'  => ['label' => 'Mitglieder anlegen/bearbeiten',     'min_role' => 'admin'],
    'notes.view'      => ['label' => 'Interne Notizen lesen',             'min_role' => 'moderator'],
    'notes.create'    => ['label' => 'Interne Notizen schreiben',         'min_role' => 'moderator'],
    'notes.delete'    => ['label' => 'Fremde Notizen löschen',            'min_role' => 'admin'],
    'tasks.view'      => ['label' => 'Aufgaben ansehen',                  'min_role' => 'supporter'],
    'tasks.manage'    => ['label' => 'Aufgaben erstellen/zuweisen',       'min_role' => 'moderator'],
    'activity.view'   => ['label' => 'Aktivitätsprotokoll ansehen',       'min_role' => 'admin'],
];

function role_level(string $role): int
{
    return ROLES[$role]['level'] ?? 0;
}

function role_label(string $role): string
{
    return ROLES[$role]['label'] ?? $role;
}

function role_exists(string $role): bool
{
    return isset(ROLES[$role]);
}

function role_has(string $role, string $permission): bool
{
    if (!isset(PERMISSIONS[$permission])) {
        return false;
    }
    return role_level($role) >= role_level(PERMISSIONS[$permission]['min_role']);
}

function can(string $permission): bool
{
    $user = current_user();
    return $user !== null && role_has($user['role'], $permission);
}

function require_permission(string $permission): void
{
    if (!can($permission)) {
        deny();
    }
}

function deny(string $message = 'Dir fehlt die Berechtigung für diese Seite.'): void
{
    http_response_code(403);
    render('error', ['title' => 'Kein Zugriff', 'message' => $message]);
    exit;
}

function not_found(string $message = 'Diese Seite existiert nicht.'): void
{
    http_response_code(404);
    render('error', ['title' => 'Nicht gefunden', 'message' => $message]);
    exit;
}

// Darf $actor das Mitglied $target verwalten?
function can_manage_member(array $actor, array $target): bool
{
    if (!role_has($actor['role'], 'members.manage')) {
        return false;
    }
    if ($actor['role'] === 'owner') {
        return true;
    }
    return role_level($actor['role']) > role_level($target['role']);
}

// Verhindert, dass das Panel ohne aktiven Owner zurückbleibt
function is_last_active_owner(array $member): bool
{
    if ($member['role'] !== 'owner' || !$member['is_active']) {
        return false;
    }
    return (int) db_value("SELECT COUNT(*) FROM users WHERE role = 'owner' AND is_active = 1") <= 1;
}

// Rollen, die $actor vergeben darf
function assignable_roles(array $actor): array
{
    $result = [];
    foreach (ROLES as $key => $role) {
        if ($actor['role'] === 'owner' || $role['level'] < role_level($actor['role'])) {
            $result[$key] = $role;
        }
    }
    return $result;
}
