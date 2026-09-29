<div class="card login-card">
    <h1><?= e(config('app.name', 'Team Panel')) ?></h1>
    <p class="muted">Bitte melde dich an.</p>
    <?php if (!empty($error)): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('login.php')) ?>">
        <?= csrf_field() ?>
        <label>Benutzername oder E-Mail
            <input type="text" name="username" value="<?= e($username ?? '') ?>" required autofocus autocomplete="username">
        </label>
        <label>Passwort
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit" class="btn primary block">Anmelden</button>
    </form>
</div>
