<h1>Mein Profil</h1>

<?php if ($errors): ?>
    <div class="flash flash-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="grid-2">
    <section class="card">
        <h2>Profildaten</h2>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="profile">
            <label>Benutzername
                <input type="text" value="<?= e($user['username']) ?>" disabled>
            </label>
            <label>Rolle
                <input type="text" value="<?= e(role_label($user['role'])) ?>" disabled>
            </label>
            <label>Anzeigename
                <input type="text" name="display_name" value="<?= e($form['display_name']) ?>" required maxlength="100">
            </label>
            <label>E-Mail
                <input type="email" name="email" value="<?= e($form['email']) ?>" required maxlength="190">
            </label>
            <button type="submit" class="btn primary">Speichern</button>
        </form>
    </section>

    <section class="card">
        <h2>Passwort ändern</h2>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">
            <label>Aktuelles Passwort
                <input type="password" name="current_password" required autocomplete="current-password">
            </label>
            <label>Neues Passwort (mind. 10 Zeichen)
                <input type="password" name="new_password" required minlength="10" autocomplete="new-password">
            </label>
            <label>Neues Passwort wiederholen
                <input type="password" name="new_password_confirm" required minlength="10" autocomplete="new-password">
            </label>
            <button type="submit" class="btn primary">Passwort ändern</button>
        </form>
    </section>
</div>
