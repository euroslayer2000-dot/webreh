<?php
/**
 * layouts/admin.php — โครงหลังบ้าน (sidebar + topbar)
 * ตัวแปร: $pageTitle, $content
 */
use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\Setting;
$base = config('app.url');
$user = Auth::user();
$flash = Controller::getFlash();
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isActive = fn(string $p) => str_contains((string) $current, $p) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Controller::e($pageTitle ?? 'ผู้ดูแลระบบ') ?> — <?= Controller::e(Setting::get('site_name')) ?></title>
    <meta name="robots" content="noindex">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
    <!-- SweetAlert2 สำหรับยืนยันการลบ -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if ($flash): ?>
    <div class="toast-stack">
        <?php foreach ($flash as $f): ?>
            <div class="toast <?= Controller::e($f['type']) ?>"><?= $f['type']==='success'?'✓':'⚠' ?> <span><?= Controller::e($f['message']) ?></span></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="admin-body">
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="<?= $base ?>/admin/dashboard" class="brand">
                <span class="logo"><?= mb_substr(Setting::get('site_name'),0,1) ?></span>
                <span class="name" style="color:#fff">ระบบจัดการ</span>
            </a>
            <?php $unread = \App\Models\Contact::unreadCount(); ?>
            <ul class="sidebar-nav">
                <li><a class="<?= $isActive('/admin/dashboard') ?>" href="<?= $base ?>/admin/dashboard">📊 แดชบอร์ด</a></li>
                <li><a class="<?= $isActive('/admin/news') ?>" href="<?= $base ?>/admin/news">📰 จัดการข่าว</a></li>
                <li><a class="<?= $isActive('/admin/teachers') ?>" href="<?= $base ?>/admin/teachers">👨‍🏫 บุคลากร</a></li>
                <li><a class="<?= $isActive('/admin/gallery') ?>" href="<?= $base ?>/admin/gallery">🖼️ แกลเลอรี</a></li>
                <li><a class="<?= $isActive('/admin/downloads') ?>" href="<?= $base ?>/admin/downloads">📄 เอกสาร</a></li>
                <li><a class="<?= $isActive('/admin/banners') ?>" href="<?= $base ?>/admin/banners">🎞️ แบนเนอร์</a></li>
                <li><a class="<?= $isActive('/admin/contacts') ?>" href="<?= $base ?>/admin/contacts">✉️ กล่องข้อความ
                    <?php if ($unread > 0): ?><span class="nav-badge"><?= $unread ?></span><?php endif; ?></a></li>
                <li><a class="<?= $isActive('/admin/settings') ?>" href="<?= $base ?>/admin/settings">⚙️ ตั้งค่าเว็บไซต์</a></li>
                <li><a href="<?= $base ?>/" target="_blank">🌐 ดูเว็บไซต์</a></li>
            </ul>
            <div class="sidebar-foot">
                <form method="post" action="<?= $base ?>/admin/logout">
                    <?= Csrf::field() ?>
                    <button class="btn btn-ghost btn-sm" style="width:100%;color:#f7e9c9;border-color:rgba(255,255,255,.3)">ออกจากระบบ</button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <div class="admin-main">
            <div class="admin-topbar">
                <h1><?= Controller::e($pageTitle ?? '') ?></h1>
                <div class="admin-user">
                    <button class="theme-toggle" aria-label="สลับโหมด">🌙</button>
                    <div class="avatar"><?= mb_substr($user['name'] ?? 'A', 0, 1) ?></div>
                    <div>
                        <div style="font-weight:600"><?= Controller::e($user['name'] ?? '') ?></div>
                        <div style="font-size:.8rem;color:var(--ink-soft)"><?= Controller::e($user['role'] ?? '') ?></div>
                    </div>
                </div>
            </div>

            <?= $content ?>
        </div>
    </div>
    <script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
