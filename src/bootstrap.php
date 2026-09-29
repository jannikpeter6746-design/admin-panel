<?php
declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__));

$configFile = ROOT_DIR . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Konfiguration fehlt: Bitte config/config.example.php nach config/config.php kopieren und anpassen.');
}
$GLOBALS['config'] = require $configFile;

date_default_timezone_set(config('app.timezone', 'Europe/Berlin'));

require ROOT_DIR . '/src/helpers.php';
require ROOT_DIR . '/src/db.php';
require ROOT_DIR . '/src/permissions.php';
require ROOT_DIR . '/src/auth.php';
require ROOT_DIR . '/src/activity.php';

function config(string $key, $default = null)
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

if (PHP_SAPI !== 'cli') {
    session_name('team_panel_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => config('app.base_url', '') ?: '/',
        'secure'   => (bool) config('app.secure_cookies', true),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');

    enforce_session_timeout();
}
