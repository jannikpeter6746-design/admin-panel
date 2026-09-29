<?php
$user = current_user();
$appName = config('app.name', 'Team Panel');
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$nav = [
    ['index.php',    'Dashboard',      'dashboard.view', ['index.php']],
    ['members.php',  'Team',           'members.view',   ['members.php', 'member.php', 'member_edit.php']],
    ['tasks.php',    'Aufgaben',       'tasks.view',     ['tasks.php', 'task_edit.php']],
    ['roles.php',    'Rollen & Rechte', null,            ['roles.php']],
    ['activity.php', 'Aktivität',      'activity.view',  ['activity.php']],
];
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? '') !== '' ? $title . ' · ' . $appName : $appName) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head>
<body class="<?= $user ? 'app' : 'guest' ?>">
<?php if ($user): ?>
    <aside class="sidebar">
        <div class="brand"><?= e($appName) ?></div>
        <nav>
            <?php foreach ($nav as [$href, $label, $perm, $pages]): ?>
                <?php if ($perm === null || can($perm)): ?>
                    <a href="<?= e(url($href)) ?>" class="<?= in_array($currentPage, $pages, true) ? 'active' : '' ?>"><?= e($label) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-user">
            <div><strong><?= e($user['display_name']) ?></strong></div>
            <div><?= role_badge($user['role']) ?></div>
            <div class="sidebar-links">
                <a href="<?= e(url('profile.php')) ?>">Profil</a>
                <form method="post" action="<?= e(url('logout.php')) ?>" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="link">Abmelden</button>
                </form>
            </div>
        </div>
    </aside>
<?php endif; ?>
    <main class="content">
        <?php foreach (take_flashes() as $flash): ?>
            <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
</body>
</html>
