<?php
// Kopiere diese Datei nach config/config.php und passe die Werte an.

return [
    'app' => [
        'name'     => 'Team Panel',
        // Basis-URL-Pfad, falls das Panel in einem Unterordner liegt (z. B. '/panel'), sonst ''
        'base_url' => '',
        'timezone' => 'Europe/Berlin',
        // Auf true setzen, wenn das Panel nur über HTTPS erreichbar ist (empfohlen)
        'secure_cookies' => true,
    ],
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'team_panel',
        'user'     => 'team_panel',
        'password' => 'CHANGE_ME',
    ],
    'security' => [
        // Max. fehlgeschlagene Logins pro IP innerhalb des Zeitfensters
        'max_login_attempts' => 5,
        'login_window_minutes' => 15,
        // Session-Timeout bei Inaktivität (Minuten)
        'session_timeout_minutes' => 60,
    ],
];
