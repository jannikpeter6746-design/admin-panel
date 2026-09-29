<div class="page-header">
    <h1><?= e($title) ?></h1>
    <a class="btn" href="<?= e(url($member ? 'member.php?id=' . $member['id'] : 'members.php')) ?>">Abbrechen</a>
</div>

<div class="card narrow">
    <?php if ($errors): ?>
        <div class="flash flash-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>
        <label>Anzeigename
            <input type="text" name="display_name" value="<?= e($form['display_name']) ?>" required maxlength="100">
        </label>
        <label>Benutzername
            <input type="text" name="username" value="<?= e($form['username']) ?>" required maxlength="50" pattern="[A-Za-z0-9_.\-]{3,50}">
        </label>
        <label>E-Mail
            <input type="email" name="email" value="<?= e($form['email']) ?>" required maxlength="190">
        </label>
        <label>Rolle
            <select name="role">
                <?php foreach ($roles as $key => $r): ?>
                    <option value="<?= e($key) ?>" <?= $form['role'] === $key ? 'selected' : '' ?>><?= e($r['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><?= $member ? 'Neues Passwort (leer lassen, um es nicht zu ändern)' : 'Passwort' ?>
            <input type="password" name="password" minlength="10" autocomplete="new-password" <?= $member ? '' : 'required' ?>>
        </label>
        <button type="submit" class="btn primary"><?= $member ? 'Speichern' : 'Anlegen' ?></button>
    </form>
</div>
