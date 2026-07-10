<?php
/**
 * admin/auth/change-password.php — บังคับเปลี่ยนรหัสผ่านครั้งแรก
 * (แสดงเมื่อ super_admin เป็นคนตั้งรหัสผ่านเริ่มต้นให้)
 */
use App\Core\Csrf;
$base = config('app.url');
?>
<div class="panel" style="max-width:480px">
    <div class="panel-head">
        <h2>ตั้งรหัสผ่านใหม่</h2>
    </div>
    <p>เพื่อความปลอดภัย กรุณาตั้งรหัสผ่านใหม่ของคุณเองก่อนใช้งานระบบต่อ</p>
    <form method="post" action="<?= $base ?>/admin/change-password" class="form-card">
        <?= Csrf::field() ?>
        <div class="form-group">
            <label for="password">รหัสผ่านใหม่</label>
            <input type="password" id="password" name="password" class="form-control" minlength="8" required autofocus>
        </div>
        <div class="form-group">
            <label for="password_confirm">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" minlength="8" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">บันทึกรหัสผ่านใหม่</button>
        </div>
    </form>
</div>
