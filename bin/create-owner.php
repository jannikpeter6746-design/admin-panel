<?php
// Legt ein Owner-Konto an. Aufruf:  php bin/create-owner.php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Nur über die Kommandozeile ausführbar.');
}

require __DIR__ . '/../src/bootstrap.php';

function prompt(string $label, bool $hidden = false): string
{
    echo $label . ': ';
    if ($hidden && DIRECTORY_SEPARATOR === '/' && stream_isatty(STDIN)) {
        system('stty -echo');
        $value = trim((string) fgets(STDIN));
        system('stty echo');
        echo PHP_EOL;
        return $value;
    }
    return trim((string) fgets(STDIN));
}

$username = prompt('Benutzername');
$email = prompt('E-Mail');
$displayName = prompt('Anzeigename') ?: $username;
$password = prompt('Passwort (mind. 10 Zeichen)', true);

if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
    fwrite(STDERR, "Ungültiger Benutzername.\n");
    exit(1);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Ungültige E-Mail-Adresse.\n");
    exit(1);
}
if ($msg = validate_password($password)) {
    fwrite(STDERR, $msg . "\n");
    exit(1);
}
if (db_one('SELECT id FROM users WHERE username = ? OR email = ?', [$username, $email])) {
    fwrite(STDERR, "Benutzername oder E-Mail existiert bereits.\n");
    exit(1);
}

db_query(
    "INSERT INTO users (username, email, display_name, password_hash, role) VALUES (?, ?, ?, ?, 'owner')",
    [$username, $email, $displayName, password_hash($password, PASSWORD_DEFAULT)]
);
$id = (int) db()->lastInsertId();
log_activity('member_created', 'user', $id, $displayName . ' (Owner, per CLI)', null);

echo "Owner-Konto \"$username\" wurde angelegt.\n";
