<?php
/**
 * admin/news/form.php — ฟอร์มเพิ่ม/แก้ไขข่าว (ใช้ร่วมกัน)
 * ตัวแปร: $news (null = เพิ่มใหม่), $categories
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$isEdit = $news !== null;
$action = $isEdit
    ? $base . '/admin/news/update/' . (int) $news['id']
    : $base . '/admin/news/store';

// ค่าเริ่มต้น (ใช้ old() ถ้า validation ไม่ผ่าน มิฉะนั้นใช้ค่าจาก DB)
$val = fn(string $k, $dbVal = '') => $_SESSION['old'][$k] ?? ($isEdit ? ($news[$k] ?? $dbVal) : $dbVal);
?>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="form-card">
    <?= Csrf::field() ?>

    <div class="form-group">
        <label for="title">หัวข้อข่าว *</label>
        <input type="text" id="title" name="title" class="form-control"
               value="<?= Controller::e((string) $val('title')) ?>" required>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="category_id">หมวดหมู่</label>
            <select id="category_id" name="category_id" class="form-control">
                <option value="">— ไม่ระบุ —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>"
                        <?= (string) $val('category_id') === (string) $cat['id'] ? 'selected' : '' ?>>
                        <?= Controller::e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">สถานะ</label>
            <select id="status" name="status" class="form-control">
                <option value="draft"     <?= $val('status') === 'draft' ? 'selected' : '' ?>>ฉบับร่าง</option>
                <option value="published" <?= $val('status') === 'published' ? 'selected' : '' ?>>เผยแพร่</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="excerpt">คำโปรย (สรุปสั้น)</label>
        <input type="text" id="excerpt" name="excerpt" class="form-control" maxlength="500"
               value="<?= Controller::e((string) $val('excerpt')) ?>">
        <div class="form-hint">แสดงในการ์ดข่าวและใช้เป็นคำอธิบายตอนแชร์โซเชียล</div>
    </div>

    <div class="form-group">
        <label for="content">เนื้อหาข่าว *</label>
        <textarea id="content" name="content" class="form-control"><?= Controller::e((string) $val('content')) ?></textarea>
    </div>

    <div class="form-group">
        <label for="cover_image">รูปปก (JPG/PNG/WEBP, สูงสุด 3 MB)</label>
        <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
        <?php if ($isEdit && !empty($news['cover_image'])): ?>
            <div class="form-hint">รูปปัจจุบัน:
                <a href="<?= Controller::e($base . $news['cover_image']) ?>" target="_blank">ดูรูป</a>
                (อัปโหลดใหม่เพื่อเปลี่ยน)
            </div>
        <?php endif; ?>
    </div>

    <details class="form-group">
        <summary style="cursor:pointer;font-family:var(--font-display);font-weight:500">ตั้งค่า SEO (ไม่บังคับ)</summary>
        <div style="margin-top:1rem">
            <div class="form-group">
                <label for="meta_title">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" class="form-control"
                       value="<?= Controller::e((string) $val('meta_title')) ?>">
                <div class="form-hint">ถ้าเว้นว่าง ระบบจะใช้หัวข้อข่าวอัตโนมัติ</div>
            </div>
            <div class="form-group">
                <label for="meta_desc">Meta Description</label>
                <input type="text" id="meta_desc" name="meta_desc" class="form-control" maxlength="300"
                       value="<?= Controller::e((string) $val('meta_desc')) ?>">
            </div>
            <div class="form-group">
                <label for="og_image">รูปสำหรับแชร์โซเชียล (OG Image)</label>
                <input type="file" id="og_image" name="og_image" class="form-control" accept="image/*">
                <div class="form-hint">ถ้าเว้นว่าง ระบบจะใช้รูปปกข่าวตอนแชร์ (แนะนำ 1200×630 px)</div>
                <?php if ($isEdit && !empty($news['og_image'])): ?>
                    <img src="<?= Controller::e($base . $news['og_image']) ?>" alt="" style="width:100%;max-width:300px;border-radius:8px;margin-top:.5rem">
                <?php endif; ?>
            </div>
        </div>
    </details>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มข่าว' ?></button>
        <a href="<?= $base ?>/admin/news" class="btn btn-ghost">ยกเลิก</a>
    </div>
</form>

<!-- CKEditor 5 (แปลง textarea เป็น rich text editor) -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#content'), {
        language: 'th'
    }).catch(err => console.error(err));
</script>
<?php Controller::clearOld(); ?>
