<?php
/**
 * admin/teachers/form.php — ฟอร์มเพิ่ม/แก้ไขเจ้าหน้าที่
 * ตัวแปร: $teacher (null = เพิ่มใหม่)
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$isEdit = $teacher !== null;
$action = $isEdit ? $base . '/admin/teachers/update/' . (int) $teacher['id'] : $base . '/admin/teachers/store';
$val = fn(string $k, $d = '') => $_SESSION['old'][$k] ?? ($isEdit ? ($teacher[$k] ?? $d) : $d);
$active = $isEdit ? (int) ($teacher['is_active'] ?? 1) === 1 : true;
?>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="form-card">
    <?= Csrf::field() ?>
    <div class="form-group">
        <label for="name">ชื่อ-นามสกุล *</label>
        <input type="text" id="name" name="name" class="form-control" value="<?= Controller::e((string) $val('name')) ?>" required>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="position">ตำแหน่ง</label>
            <input type="text" id="position" name="position" class="form-control" value="<?= Controller::e((string) $val('position')) ?>" placeholder="เช่น เจ้าหน้าที่กู้ชีพระดับสูง">
        </div>
        <div class="form-group">
            <label for="department">หน่วย / ฝ่าย</label>
            <input type="text" id="department" name="department" class="form-control" value="<?= Controller::e((string) $val('department')) ?>" placeholder="เช่น หน่วยปฏิบัติการฉุกเฉิน">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="email">อีเมล</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= Controller::e((string) $val('email')) ?>">
        </div>
        <div class="form-group">
            <label for="sort_order">ลำดับการแสดง</label>
            <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?= Controller::e((string) $val('sort_order', '0')) ?>">
            <div class="form-hint">เลขน้อยแสดงก่อน</div>
        </div>
    </div>
    <div class="form-group">
        <label for="photo">รูปภาพ (JPG/PNG/WEBP, สูงสุด 3 MB)</label>
        <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
        <?php if ($isEdit && !empty($teacher['photo'])): ?>
            <div class="form-hint"><img src="<?= Controller::e($base . $teacher['photo']) ?>" alt="" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-top:.5rem"></div>
        <?php endif; ?>
    </div>
    <label class="consent-box" style="background:var(--surface-2)">
        <input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>>
        <span>แสดงบนหน้าเว็บ</span>
    </label>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มเจ้าหน้าที่' ?></button>
        <a href="<?= $base ?>/admin/teachers" class="btn btn-ghost">ยกเลิก</a>
    </div>
</form>
<?php Controller::clearOld(); ?>
