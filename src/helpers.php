<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim((string) config('app.base_url', ''), '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function input_int(string $key): int
{
    return (int) input($key, '0');
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ---- CSRF ----

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        render('error', ['title' => 'Sitzung abgelaufen', 'message' => 'Ungültiges Formular-Token. Bitte lade die Seite neu und versuche es erneut.']);
        exit;
    }
}

// ---- Flash-Nachrichten ----

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// ---- Views ----

function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = ROOT_DIR . '/views/' . $view . '.php';
    ob_start();
    require $viewFile;
    $content = ob_get_clean();
    require ROOT_DIR . '/views/layout.php';
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return '–';
    }
    return date('d.m.Y H:i', strtotime($value));
}

function format_date(?string $value): string
{
    if (!$value) {
        return '–';
    }
    return date('d.m.Y', strtotime($value));
}

function role_badge(string $role): string
{
    $color = ROLES[$role]['color'] ?? '#7f8c8d';
    return '<span class="badge" style="--badge:' . e($color) . '">' . e(role_label($role)) . '</span>';
}

const TASK_STATUSES = [
    'open'        => 'Offen',
    'in_progress' => 'In Arbeit',
    'done'        => 'Erledigt',
];

const TASK_PRIORITIES = [
    'low'    => 'Niedrig',
    'normal' => 'Normal',
    'high'   => 'Hoch',
];

function validate_password(string $password): ?string
{
    if (strlen($password) < 10) {
        return 'Das Passwort muss mindestens 10 Zeichen lang sein.';
    }
    return null;
}
