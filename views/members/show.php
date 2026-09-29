<?php $me = current_user(); ?>
<div class="page-header">
    <h1><?= e($member['display_name']) ?> <?= role_badge($member['role']) ?>
        <?php if (!$member['is_active']): ?><span class="tag">Deaktiviert</span><?php endif; ?>
    </h1>
    <div class="actions">
        <?php if ($isSelf): ?>
            <a class="btn" href="<?= e(url('profile.php')) ?>">Profil bearbeiten</a>
        <?php elseif ($canManage): ?>
            <a class="btn" href="<?= e(url('member_edit.php?id=' . $member['id'])) ?>">Bearbeiten</a>
            <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_active">
                <button type="submit" class="btn"><?= $member['is_active'] ? 'Deaktivieren' : 'Aktivieren' ?></button>
            </form>
            <form method="post" class="inline" onsubmit="return confirm('Mitglied wirklich endgültig löschen? Notizen zu diesem Mitglied werden ebenfalls gelöscht.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn danger">Löschen</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="grid-2">
    <section class="card">
        <h2>Details</h2>
        <dl class="details">
            <dt>Benutzername</dt><dd><?= e($member['username']) ?></dd>
            <dt>E-Mail</dt><dd><a href="mailto:<?= e($member['email']) ?>"><?= e($member['email']) ?></a></dd>
            <dt>Rolle</dt><dd><?= e(role_label($member['role'])) ?></dd>
            <dt>Mitglied seit</dt><dd><?= e(format_date($member['created_at'])) ?></dd>
            <dt>Letzter Login</dt><dd><?= e(format_datetime($member['last_login_at'])) ?></dd>
        </dl>
    </section>

    <section class="card">
        <div class="page-header">
            <h2>Aufgaben</h2>
            <?php if (can('tasks.manage')): ?>
                <a class="btn small" href="<?= e(url('task_edit.php?assignee_id=' . $member['id'])) ?>">+ Aufgabe zuweisen</a>
            <?php endif; ?>
        </div>
        <?php if (!$tasks): ?>
            <p class="muted">Keine Aufgaben zugewiesen.</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($tasks as $task): ?>
                    <li class="<?= $task['status'] === 'done' ? 'done' : '' ?>">
                        <a href="<?= e(url('task_edit.php?id=' . $task['id'])) ?>"><?= e($task['title']) ?></a>
                        <span class="tag status-<?= e($task['status']) ?>"><?= e(TASK_STATUSES[$task['status']]) ?></span>
                        <?php if ($task['due_date']): ?><span class="muted small">fällig <?= e(format_date($task['due_date'])) ?></span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<?php if (can('notes.view')): ?>
    <section class="card">
        <h2>Interne Notizen</h2>
        <?php if (can('notes.create')): ?>
            <form method="post" class="note-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_note">
                <textarea name="body" rows="3" placeholder="Notiz zu <?= e($member['display_name']) ?> …" required></textarea>
                <button type="submit" class="btn primary">Notiz speichern</button>
            </form>
        <?php endif; ?>
        <?php if (!$notes): ?>
            <p class="muted">Noch keine Notizen.</p>
        <?php endif; ?>
        <?php foreach ($notes as $note): ?>
            <article class="note">
                <header>
                    <strong><?= e($note['author_name'] ?? 'Gelöschter Benutzer') ?></strong>
                    <span class="muted small"><?= e(format_datetime($note['created_at'])) ?></span>
                    <?php if ((int) $note['author_id'] === (int) $me['id'] || can('notes.delete')): ?>
                        <form method="post" class="inline" onsubmit="return confirm('Notiz löschen?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_note">
                            <input type="hidden" name="note_id" value="<?= (int) $note['id'] ?>">
                            <button type="submit" class="link danger">Löschen</button>
                        </form>
                    <?php endif; ?>
                </header>
                <p><?= nl2br(e($note['body'])) ?></p>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (can('activity.view')): ?>
    <section class="card">
        <h2>Aktivität</h2>
        <?php require __DIR__ . '/../partials/activity_table.php'; ?>
    </section>
<?php endif; ?>
