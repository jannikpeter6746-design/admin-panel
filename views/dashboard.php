<h1>Hallo, <?= e(current_user()['display_name']) ?> 👋</h1>

<div class="stats">
    <div class="stat"><span class="stat-value"><?= (int) $stats['members'] ?></span><span class="stat-label">Aktive Mitglieder</span></div>
    <div class="stat"><span class="stat-value"><?= (int) $stats['open_tasks'] ?></span><span class="stat-label">Offene Aufgaben</span></div>
    <div class="stat"><span class="stat-value"><?= (int) $stats['my_tasks'] ?></span><span class="stat-label">Meine offenen Aufgaben</span></div>
    <div class="stat"><span class="stat-value"><?= (int) $stats['overdue'] ?></span><span class="stat-label">Überfällig</span></div>
</div>

<div class="grid-2">
    <section class="card">
        <h2>Meine Aufgaben</h2>
        <?php if (!$myTasks): ?>
            <p class="muted">Keine offenen Aufgaben. 🎉</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($myTasks as $task): ?>
                    <li>
                        <a href="<?= e(url('task_edit.php?id=' . $task['id'])) ?>"><?= e($task['title']) ?></a>
                        <span class="tag tag-<?= e($task['priority']) ?>"><?= e(TASK_PRIORITIES[$task['priority']]) ?></span>
                        <span class="tag status-<?= e($task['status']) ?>"><?= e(TASK_STATUSES[$task['status']]) ?></span>
                        <?php if ($task['due_date']): ?><span class="muted small">fällig <?= e(format_date($task['due_date'])) ?></span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <?php if (can('activity.view')): ?>
        <section class="card">
            <h2>Letzte Aktivität</h2>
            <?php require __DIR__ . '/partials/activity_table.php'; ?>
            <p><a href="<?= e(url('activity.php')) ?>">Alle anzeigen →</a></p>
        </section>
    <?php endif; ?>
</div>
