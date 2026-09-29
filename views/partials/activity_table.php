<?php /** @var array $entries */ ?>
<?php if (!$entries): ?>
    <p class="muted">Noch keine Einträge.</p>
<?php else: ?>
    <table class="table">
        <thead>
        <tr><th>Zeit</th><th>Benutzer</th><th>Aktion</th><th>Details</th><?php if (!empty($showIp)): ?><th>IP</th><?php endif; ?></tr>
        </thead>
        <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td class="nowrap"><?= e(format_datetime($entry['created_at'])) ?></td>
                <td><?= $entry['display_name'] !== null ? e($entry['display_name']) : '<span class="muted">System</span>' ?></td>
                <td><?= e(activity_label($entry['action'])) ?></td>
                <td><?= e($entry['details']) ?></td>
                <?php if (!empty($showIp)): ?><td class="muted small"><?= e($entry['ip_address']) ?></td><?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
