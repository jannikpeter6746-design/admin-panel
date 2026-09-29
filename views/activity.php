<div class="page-header">
    <h1>Aktivitätsprotokoll</h1>
    <span class="muted"><?= (int) $total ?> Einträge</span>
</div>

<form method="get" class="filters">
    <select name="action">
        <option value="">Alle Aktionen</option>
        <?php foreach (ACTIVITY_LABELS as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $action === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="user">
        <option value="0">Alle Benutzer</option>
        <?php foreach ($members as $m): ?>
            <option value="<?= (int) $m['id'] ?>" <?= $userId === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['display_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Filtern</button>
</form>

<div class="card">
    <?php require __DIR__ . '/partials/activity_table.php'; ?>
</div>

<?php if ($pages > 1): ?>
    <nav class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php $query = http_build_query(array_filter(['action' => $action, 'user' => $userId ?: null, 'page' => $i])); ?>
            <a href="?<?= e($query) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
