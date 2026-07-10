<?php
/**
 * admin/users/form.php — ฟอร์มเพิ่ม/แก้ไขผู้ใช้งาน
 * ตัวแปร: $user (null = เพิ่มใหม่)
 */
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Auth;
$base = config('app.url');
$isEdit = $user !== null;
$isSelf = $isEdit && (int) $user['id'] === Auth::id();
$action = $isEdit ? $base . '/admin/users/update/' . (int) $user['id'] : $base . '/admin/users/store';
$val = fn(string $k, $d = '') => $_SESSION['old'][$k] ?? ($isEdit ? ($user[$k] ?? $d) : $d);
$active = $isEdit ? (int) ($user['is_active'] ?? 1) === 1 : true;
$roleLabels = ['super_admin' => 'ผู้ดูแลระบบ', 'editor' => 'ผู้แก้ไขเนื้อหา', 'teacher' => 'ครู'];
$role = (string) $val('role', 'editor');
?>
<form method="post" action="<?= $action ?>" class="form-card">
    <?= Csrf::field() ?>
    <div class="form-group">
        <label for="name">ชื่อ-นามสกุล *</label>
        <input type="text" id="name" name="name" class="form-control" value="<?= Controller::e((string) $val('name')) ?>" required>
    </div>
    <div class="form-group">
        <label for="email">อีเมล *</label>
        <input type="email" id="email" name="email" class="form-control" value="<?= Controller::e((string) $val('email')) ?>" required>
    </div>
    <div class="form-group">
        <label for="role">บทบาท *</label>
        <select id="role" name="role" class="form-control" <?= $isSelf ? 'disabled' : '' ?> required>
            <?php foreach ($roleLabels as $value => $label): ?>
                <option value="<?= $value ?>" <?= $role === $value ? 'selected' : '' ?>><?= Controller::e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($isSelf): ?>
            <input type="hidden" name="role" value="<?= Controller::e($role) ?>">
        <?php endif; ?>
    </div>
    <label class="consent-box" style="background:var(--surface-2)">
        <input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>>
        <span>เปิดใช้งานบัญชี</span>
    </label>
    <?php if ($isSelf): ?>
        <input type="hidden" name="is_active" value="<?= $active ? '1' : '0' ?>">
        <div class="form-hint">ไม่สามารถเปลี่ยนบทบาทหรือปิดใช้งานบัญชีของตนเองได้</div>
    <?php endif; ?>
    <?php if (!$isEdit): ?>
        <div class="form-hint">ระบบจะส่งอีเมลลิงก์ให้ผู้ใช้งานตั้งรหัสผ่านด้วยตนเอง</div>
    <?php endif; ?>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มผู้ใช้งาน' ?></button>
        <a href="<?= $base ?>/admin/users" class="btn btn-ghost">ยกเลิก</a>
    </div>
</form>
<?php Controller::clearOld(); ?>
