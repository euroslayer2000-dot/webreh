<?php
/**
 * admin/gallery/form.php — สร้าง/แก้ไขอัลบั้ม + จัดการรูปในอัลบั้ม
 * ตัวแปร: $album (null = สร้างใหม่), $images (เมื่อแก้ไข)
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$isEdit = $album !== null;
$action = $isEdit ? $base . '/admin/gallery/update/' . (int) $album['id'] : $base . '/admin/gallery/store';
$val = fn(string $k, $d = '') => $isEdit ? ($album[$k] ?? $d) : $d;
?>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="form-card">
    <?= Csrf::field() ?>
    <div class="form-group">
        <label for="title">ชื่ออัลบั้ม *</label>
        <input type="text" id="title" name="title" class="form-control" value="<?= Controller::e((string) $val('title')) ?>" required>
    </div>
    <div class="form-group">
        <label for="description">คำอธิบาย</label>
        <input type="text" id="description" name="description" class="form-control" maxlength="500" value="<?= Controller::e((string) $val('description')) ?>">
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="event_date">วันที่จัดกิจกรรม</label>
            <input type="date" id="event_date" name="event_date" class="form-control" value="<?= Controller::e((string) $val('event_date')) ?>">
        </div>
        <div class="form-group">
            <label for="cover_image">รูปปกอัลบั้ม</label>
            <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกอัลบั้ม' : 'สร้างอัลบั้ม' ?></button>
        <a href="<?= $base ?>/admin/gallery" class="btn btn-ghost">กลับ</a>
    </div>
</form>

<?php if ($isEdit): ?>
<!-- อัปโหลดรูปเข้าอัลบั้ม -->
<div class="form-card" style="margin-top:1.5rem">
    <h2 style="font-size:1.15rem">เพิ่มรูปเข้าอัลบั้ม</h2>
    <form method="post" action="<?= $base ?>/admin/gallery/images/<?= (int) $album['id'] ?>" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <div class="form-group">
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
            <div class="form-hint">เลือกได้หลายรูปพร้อมกัน (สูงสุด 3 MB ต่อรูป)</div>
        </div>
        <button type="submit" class="btn btn-gold">อัปโหลดรูป</button>
    </form>

    <?php if (!empty($images)): ?>
    <h3 style="margin-top:1.5rem;font-size:1rem">รูปในอัลบั้ม (<?= count($images) ?>)</h3>
    <div class="admin-photo-grid">
        <?php foreach ($images as $img): ?>
            <div class="admin-photo">
                <img src="<?= Controller::e($base . $img['image_path']) ?>" alt="" loading="lazy">
                <form method="post" action="<?= $base ?>/admin/gallery/image-delete/<?= (int) $img['id'] ?>">
                    <?= Csrf::field() ?>
                    <button type="button" class="photo-del" data-confirm="ลบรูปนี้?">✕</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p style="color:var(--ink-soft);margin-top:1rem">ยังไม่มีรูปในอัลบั้มนี้</p>
    <?php endif; ?>
</div>
<?php endif; ?>
