<div class="page-header">
    <h1><?= e($task ? 'Aufgabe' : 'Neue Aufgabe') ?></h1>
    <div class="actions">
        <a class="btn" href="<?= e(url('tasks.php')) ?>">Zurück</a>
        <?php if ($task && $canManage): ?>
            <form method="post" class="inline" onsubmit="return confirm('Aufgabe wirklich löschen?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn danger">Löschen</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card narrow">
    <?php if ($errors): ?>
        <div class="flash flash-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($canManage): ?>
        <form method="post">
            <?= csrf_field() ?>
            <label>Titel
                <input type="text" name="title" value="<?= e($form['title']) ?>" required maxlength="200">
            </label>
            <label>Beschreibung
                <textarea name="description" rows="5"><?= e($form['description']) ?></textarea>
            </label>
            <div class="row">
                <label>Zugewiesen an
                    <select name="assignee_id">
                        <option value="">– Niemand –</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= (int) $m['id'] ?>" <?= $form['assignee_id'] === (string) $m['id'] ? 'selected' : '' ?>><?= e($m['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Fällig am
                    <input type="date" name="due_date" value="<?= e($form['due_date']) ?>">
                </label>
            </div>
            <div class="row">
                <label>Priorität
                    <select name="priority">
                        <?php foreach (TASK_PRIORITIES as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $form['priority'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Status
                    <select name="status">
                        <?php foreach (TASK_STATUSES as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $form['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button type="submit" class="btn primary"><?= $task ? 'Speichern' : 'Erstellen' ?></button>
        </form>
    <?php else: ?>
        <h2><?= e($task['title']) ?></h2>
        <p>
            <span class="tag tag-<?= e($task['priority']) ?>"><?= e(TASK_PRIORITIES[$task['priority']]) ?></span>
            <?php if ($task['due_date']): ?><span class="muted">fällig <?= e(format_date($task['due_date'])) ?></span><?php endif; ?>
        </p>
        <?php if ($task['description']): ?>
            <p><?= nl2br(e($task['description'])) ?></p>
        <?php endif; ?>
        <?php if ($isAssignee): ?>
            <form method="post" class="row">
                <?= csrf_field() ?>
                <label>Status
                    <select name="status">
                        <?php foreach (TASK_STATUSES as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $task['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="btn primary">Status speichern</button>
            </form>
        <?php else: ?>
            <p>Status: <span class="tag status-<?= e($task['status']) ?>"><?= e(TASK_STATUSES[$task['status']]) ?></span></p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($task): ?>
        <p class="muted small">
            Erstellt von <?= e($task['creator_name'] ?? 'Unbekannt') ?> am <?= e(format_datetime($task['created_at'])) ?>
            · zuletzt geändert <?= e(format_datetime($task['updated_at'])) ?>
        </p>
    <?php endif; ?>
</div>
