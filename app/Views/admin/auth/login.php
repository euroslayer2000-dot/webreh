<?php
/**
 * admin/auth/login.php — หน้าเข้าสู่ระบบหลังบ้าน
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
    <title>เข้าสู่ระบบ — <?= Controller::e(Setting::get('site_name')) ?></title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
            <div class="logo-lg"><?= mb_substr(Setting::get('site_name'), 0, 1) ?></div>
            <h1>เข้าสู่ระบบ</h1>
            <p class="sub">สำหรับเจ้าหน้าที่และผู้ดูแลระบบ</p>

            <form method="post" action="<?= $base ?>/admin/login">
                <?= Csrf::field() ?>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= Controller::old('email') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
            </form>

            <div style="text-align:center;margin-top:1rem">
                <a href="<?= $base ?>/admin/forgot" style="font-size:.9rem">ลืมรหัสผ่าน?</a>
            </div>

            <div class="login-hint">
                <strong>บัญชีทดสอบ:</strong><br>
                อีเมล: admin@school.ac.th<br>
                รหัสผ่าน: Admin@1234
            </div>
        </div>
    </div>
    <script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
<?php Controller::clearOld(); ?>
