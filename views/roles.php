<h1>Rollen & Rechte</h1>
<p class="muted">Jede Rolle enthält alle Rechte der darunterliegenden Rollen. Mitglieder können nur Teammitglieder mit einer niedrigeren Rolle verwalten (Owner verwalten alle).</p>

<div class="card">
    <table class="table matrix">
        <thead>
        <tr>
            <th>Recht</th>
            <?php foreach (ROLES as $key => $role): ?>
                <th><?= role_badge($key) ?><div class="muted small"><?= (int) ($counts[$key] ?? 0) ?> aktiv</div></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach (PERMISSIONS as $perm => $info): ?>
            <tr>
                <td><?= e($info['label']) ?> <code class="muted small"><?= e($perm) ?></code></td>
                <?php foreach (ROLES as $key => $role): ?>
                    <td class="center"><?= role_has($key, $perm) ? '<span class="yes">✔</span>' : '<span class="no">–</span>' ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="muted small">Rollen und Rechte werden in <code>src/permissions.php</code> definiert.</p>
