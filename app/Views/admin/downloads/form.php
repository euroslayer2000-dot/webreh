<?php
/**
 * admin/downloads/form.php — ฟอร์มเพิ่มเอกสารดาวน์โหลด
 * ตัวแปร: $categories
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$val = fn(string $k, $d = '') => $_SESSION['old'][$k] ?? $d;
?>
<form method="post" action="<?= $base ?>/admin/downloads/store" enctype="multipart/form-data" class="form-card">
    <?= Csrf::field() ?>

    <div class="form-group">
        <label for="title">ชื่อเอกสาร *</label>
        <input type="text" id="title" name="title" class="form-control" value="<?= Controller::e((string) $val('title')) ?>" required>
    </div>

    <div class="form-group">
        <label for="category_id">หมวดหมู่</label>
        <select id="category_id" name="category_id" class="form-control" style="max-width:320px">
            <option value="">— ไม่ระบุ —</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= (string) $val('category_id') === (string) $cat['id'] ? 'selected' : '' ?>>
                    <?= Controller::e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="document">ไฟล์เอกสาร *</label>
        <input type="file" id="document" name="document" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
        <div class="form-hint">รองรับ PDF, Word (DOC/DOCX), Excel (XLS/XLSX) สูงสุด 15 MB</div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">เพิ่มเอกสาร</button>
        <a href="<?= $base ?>/admin/downloads" class="btn btn-ghost">ยกเลิก</a>
    </div>
</form>
<?php Controller::clearOld(); ?>
