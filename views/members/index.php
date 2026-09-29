<div class="page-header">
    <h1>Team</h1>
    <?php if (can('members.manage')): ?>
        <a class="btn primary" href="<?= e(url('member_edit.php')) ?>">+ Mitglied hinzufügen</a>
    <?php endif; ?>
</div>

<form method="get" class="filters">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Name, Benutzername oder E-Mail">
    <select name="role">
        <option value="">Alle Rollen</option>
        <?php foreach (ROLES as $key => $r): ?>
            <option value="<?= e($key) ?>" <?= $role === $key ? 'selected' : '' ?>><?= e($r['label']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status">
        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktiv</option>
        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Deaktiviert</option>
        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Alle</option>
    </select>
    <button type="submit" class="btn">Filtern</button>
</form>

<div class="card">
    <?php if (!$members): ?>
        <p class="muted">Keine Mitglieder gefunden.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr><th>Name</th><th>Benutzername</th><th>E-Mail</th><th>Rolle</th><th>Status</th><th>Offene Aufgaben</th><th>Letzter Login</th></tr>
            </thead>
            <tbody>
            <?php foreach ($members as $m): ?>
                <tr>
                    <td><a href="<?= e(url('member.php?id=' . $m['id'])) ?>"><?= e($m['display_name']) ?></a></td>
                    <td><?= e($m['username']) ?></td>
                    <td><?= e($m['email']) ?></td>
                    <td><?= role_badge($m['role']) ?></td>
                    <td><?= $m['is_active'] ? '<span class="tag status-done">Aktiv</span>' : '<span class="tag">Deaktiviert</span>' ?></td>
                    <td><?= (int) $m['open_tasks'] ?></td>
                    <td class="nowrap"><?= e(format_datetime($m['last_login_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
