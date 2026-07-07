<?php
/**
 * admin/auth/reset.php — หน้าตั้งรหัสผ่านใหม่ (จากลิงก์ในอีเมล)
 * ตัวแปร: $token
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
    <title>ตั้งรหัสผ่านใหม่ — <?= Controller::e(Setting::get('site_name')) ?></title>
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
            <div class="logo-lg">🔒</div>
            <h1>ตั้งรหัสผ่านใหม่</h1>
            <p class="sub">กรอกรหัสผ่านใหม่ของคุณ (อย่างน้อย 8 ตัวอักษร)</p>

            <form method="post" action="<?= $base ?>/admin/reset/<?= Controller::e($token) ?>">
                <?= Csrf::field() ?>
                <div class="form-group">
                    <label for="password">รหัสผ่านใหม่</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="8" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password_confirm">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-primary">บันทึกรหัสผ่านใหม่</button>
            </form>
        </div>
    </div>
    <script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
