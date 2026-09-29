# Team Panel

Ein schlankes Team-Management-Panel für Hosting-Teams – PHP + MySQL, ohne Framework und ohne Composer-Abhängigkeiten. Läuft auf praktisch jedem Webspace (Plesk, cPanel, eigener VPS).

## Funktionen

- **Login & Teammitglieder** – Anmeldung, Mitglieder anlegen, bearbeiten, deaktivieren und löschen
- **Rollen & Rechte** – Owner, Admin, Moderator, Supporter mit abgestuften Rechten (Übersicht unter *Rollen & Rechte*)
- **Aktivitätsprotokoll** – wer hat wann was gemacht (inkl. IP und fehlgeschlagenen Logins), filterbar
- **Notizen & Aufgaben** – interne Notizen pro Mitglied, Aufgaben mit Zuweisung, Priorität, Fälligkeit und Status
- **Sicherheit** – `password_hash`, CSRF-Schutz, Prepared Statements, Session-Timeout, Brute-Force-Sperre, Schutz vor dem Entfernen des letzten Owners

## Voraussetzungen

- PHP 8.0+ mit `pdo_mysql`
- MySQL 5.7+ oder MariaDB 10.3+

## Installation

1. **Datenbank anlegen** und das Schema importieren:
   ```bash
   mysql -u root -p -e "CREATE DATABASE team_panel CHARACTER SET utf8mb4;"
   mysql -u root -p team_panel < database/schema.sql
   ```
   (Alternativ `database/schema.sql` über phpMyAdmin importieren.)

2. **Konfiguration** kopieren und Zugangsdaten eintragen:
   ```bash
   cp config/config.example.php config/config.php
   ```

3. **Webroot** der (Sub-)Domain auf den Ordner `public/` setzen.
   Falls das bei deinem Hosting nicht möglich ist, lade alles hoch und setze in der Config `base_url` auf den Pfad zu `public` (z. B. `'/panel/public'`). Die Ordner `config/`, `src/`, `views/`, `bin/` und `database/` sind per `.htaccess` gesperrt (Apache). Unter nginx diese Ordner selbst sperren.

4. **Ersten Owner anlegen** (per SSH):
   ```bash
   php bin/create-owner.php
   ```

5. Panel im Browser öffnen und anmelden. **HTTPS verwenden** – ohne HTTPS in der Config `secure_cookies` auf `false` setzen, sonst funktioniert der Login nicht.

Für lokales Testen: `php -S 127.0.0.1:8000 -t public`

## Rollen

| Recht | Supporter | Moderator | Admin | Owner |
|---|:-:|:-:|:-:|:-:|
| Dashboard, Team & Aufgaben ansehen | ✔ | ✔ | ✔ | ✔ |
| Status eigener Aufgaben ändern | ✔ | ✔ | ✔ | ✔ |
| Notizen lesen/schreiben | | ✔ | ✔ | ✔ |
| Aufgaben erstellen/zuweisen | | ✔ | ✔ | ✔ |
| Mitglieder verwalten, fremde Notizen löschen, Aktivitätslog | | | ✔ | ✔ |

Mitglieder können nur Teammitglieder mit **niedrigerer** Rolle verwalten und nur niedrigere Rollen vergeben. Owner dürfen alles. Rollen und Rechte lassen sich in `src/permissions.php` anpassen.

## Projektstruktur

```
public/      Webroot (Seiten + assets/)
src/         Logik: DB, Auth, Rechte, Aktivitätslog, Helfer
views/       HTML-Templates
config/      Konfiguration
database/    SQL-Schema
bin/         CLI-Skripte
```
