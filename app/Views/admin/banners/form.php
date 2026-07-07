<?php
/**
 * admin/banners/form.php — ฟอร์มเพิ่ม/แก้ไขแบนเนอร์
 * ตัวแปร: $banner (null = เพิ่มใหม่)
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$isEdit = $banner !== null;
$action = $isEdit ? $base . '/admin/banners/update/' . (int) $banner['id'] : $base . '/admin/banners/store';
$val = fn(string $k, $d = '') => $isEdit ? ($banner[$k] ?? $d) : $d;
$active = $isEdit ? (int) ($banner['is_active'] ?? 1) === 1 : true;
?>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="form-card">
    <?= Csrf::field() ?>

    <div class="form-group">
        <label for="title">หัวข้อ (ไม่บังคับ)</label>
        <input type="text" id="title" name="title" class="form-control" value="<?= Controller::e((string) $val('title')) ?>">
    </div>

    <div class="form-group">
        <label for="link_url">ลิงก์เมื่อคลิก (ไม่บังคับ)</label>
        <input type="text" id="link_url" name="link_url" class="form-control" value="<?= Controller::e((string) $val('link_url')) ?>" placeholder="https://...">
    </div>

    <div class="form-group">
        <label for="image">รูปแบนเนอร์ <?= $isEdit ? '(อัปโหลดใหม่เพื่อเปลี่ยน)' : '*' ?></label>
        <input type="file" id="image" name="image" class="form-control" accept="image/*" <?= $isEdit ? '' : 'required' ?>>
        <div class="form-hint">แนะนำสัดส่วนกว้าง เช่น 1600×600 px (JPG/PNG/WEBP, สูงสุด 3 MB)</div>
        <?php if ($isEdit && !empty($banner['image_path'])): ?>
            <img src="<?= Controller::e($base . $banner['image_path']) ?>" alt="" style="width:100%;max-width:420px;border-radius:10px;margin-top:.6rem">
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="sort_order">ลำดับการแสดง</label>
        <input type="number" id="sort_order" name="sort_order" class="form-control" style="max-width:160px"
               value="<?= Controller::e((string) $val('sort_order', '0')) ?>">
        <div class="form-hint">เลขน้อยแสดงก่อน</div>
    </div>

    <label class="consent-box">
        <input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>>
        <span>แสดงแบนเนอร์นี้บนหน้าแรก</span>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มแบนเนอร์' ?></button>
        <a href="<?= $base ?>/admin/banners" class="btn btn-ghost">ยกเลิก</a>
    </div>
</form>
