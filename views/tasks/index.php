<?php $me = current_user(); ?>
<div class="page-header">
    <h1>Aufgaben</h1>
    <?php if (can('tasks.manage')): ?>
        <a class="btn primary" href="<?= e(url('task_edit.php')) ?>">+ Neue Aufgabe</a>
    <?php endif; ?>
</div>

<form method="get" class="filters">
    <select name="status">
        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Nicht erledigt</option>
        <?php foreach (TASK_STATUSES as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Alle</option>
    </select>
    <select name="assignee">
        <option value="">Alle Mitglieder</option>
        <option value="me" <?= $assignee === 'me' ? 'selected' : '' ?>>Nur meine</option>
        <option value="none" <?= $assignee === 'none' ? 'selected' : '' ?>>Nicht zugewiesen</option>
        <?php foreach ($members as $m): ?>
            <option value="<?= (int) $m['id'] ?>" <?= $assignee === (string) $m['id'] ? 'selected' : '' ?>><?= e($m['display_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Filtern</button>
</form>

<div class="card">
    <?php if (!$tasks): ?>
        <p class="muted">Keine Aufgaben gefunden.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr><th>Aufgabe</th><th>Priorität</th><th>Zugewiesen an</th><th>Fällig</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $task):
                $overdue = $task['status'] !== 'done' && $task['due_date'] && $task['due_date'] < date('Y-m-d');
                $canChange = can('tasks.manage') || (int) $task['assignee_id'] === (int) $me['id'];
            ?>
                <tr class="<?= $task['status'] === 'done' ? 'done' : '' ?>">
                    <td><a href="<?= e(url('task_edit.php?id=' . $task['id'])) ?>"><?= e($task['title']) ?></a></td>
                    <td><span class="tag tag-<?= e($task['priority']) ?>"><?= e(TASK_PRIORITIES[$task['priority']]) ?></span></td>
                    <td><?= $task['assignee_name'] !== null ? e($task['assignee_name']) : '<span class="muted">–</span>' ?></td>
                    <td class="nowrap <?= $overdue ? 'overdue' : '' ?>"><?= e(format_date($task['due_date'])) ?></td>
                    <td>
                        <?php if ($canChange): ?>
                            <form method="post" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>">
                                <input type="hidden" name="f_status" value="<?= e($status) ?>">
                                <input type="hidden" name="f_assignee" value="<?= e($assignee) ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select status-<?= e($task['status']) ?>">
                                    <?php foreach (TASK_STATUSES as $key => $label): ?>
                                        <option value="<?= e($key) ?>" <?= $task['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <noscript><button type="submit" class="btn small">OK</button></noscript>
                            </form>
                        <?php else: ?>
                            <span class="tag status-<?= e($task['status']) ?>"><?= e(TASK_STATUSES[$task['status']]) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
