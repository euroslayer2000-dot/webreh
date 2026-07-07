<?php
/**
 * admin/auth/forgot.php — หน้าขอลิงก์ตั้งรหัสผ่านใหม่
 */
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Setting;
$base = config('app.url');
$flash = Controller::getFlash();
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ลืมรหัสผ่าน — <?= Controller::e(Setting::get('site_name')) ?></title>
    <meta name="robots" content="noindex">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
</head>
<body>
    <?php if ($flash): ?>
    <div class="toast-stack">
        <?php foreach ($flash as $f): ?>
            <div class="toast <?= Controller::e($f['type']) ?>"><?= $f['type']==='success'?'✓':'⚠' ?> <span><?= Controller::e($f['message']) ?></span></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="login-wrap">
        <div class="login-card">
            <div class="logo-lg">🔑</div>
            <h1>ลืมรหัสผ่าน</h1>
            <p class="sub">กรอกอีเมลของคุณ เราจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปให้</p>

            <form method="post" action="<?= $base ?>/admin/forgot">
                <?= Csrf::field() ?>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" id="email" name="email" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary">ส่งลิงก์ตั้งรหัสผ่านใหม่</button>
            </form>

            <div style="text-align:center;margin-top:1.2rem">
                <a href="<?= $base ?>/admin/login" style="font-size:.9rem">← กลับหน้าเข้าสู่ระบบ</a>
            </div>
        </div>
    </div>
    <script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
