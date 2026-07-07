<?php
/**
 * admin/settings/index.php — ตั้งค่าเว็บไซต์ + SEO
 * ตัวแปร: $settings (array key=>value)
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$s = fn(string $k, string $d = '') => Controller::e($settings[$k] ?? $d);
?>
<form method="post" action="<?= $base ?>/admin/settings/update" enctype="multipart/form-data" class="form-card">
    <?= Csrf::field() ?>

    <h2 style="font-size:1.15rem;margin-bottom:1rem">ข้อมูลทั่วไป</h2>
    <div class="form-group">
        <label for="site_name">ชื่อโรงเรียน</label>
        <input type="text" id="site_name" name="site_name" class="form-control" value="<?= $s('site_name') ?>">
    </div>
    <div class="form-group">
        <label for="site_tagline">คำขวัญ / สโลแกน</label>
        <input type="text" id="site_tagline" name="site_tagline" class="form-control" value="<?= $s('site_tagline') ?>">
    </div>

    <h2 style="font-size:1.15rem;margin:1.6rem 0 1rem">ข้อมูลติดต่อ</h2>
    <div class="form-row">
        <div class="form-group">
            <label for="contact_phone">เบอร์โทร</label>
            <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?= $s('contact_phone') ?>">
        </div>
        <div class="form-group">
            <label for="contact_email">อีเมล</label>
            <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?= $s('contact_email') ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="contact_address">ที่อยู่</label>
        <input type="text" id="contact_address" name="contact_address" class="form-control" value="<?= $s('contact_address') ?>">
    </div>

    <h2 style="font-size:1.15rem;margin:1.6rem 0 1rem">โซเชียลมีเดีย</h2>
    <div class="form-row">
        <div class="form-group">
            <label for="facebook_url">Facebook URL</label>
            <input type="text" id="facebook_url" name="facebook_url" class="form-control" value="<?= $s('facebook_url') ?>">
        </div>
        <div class="form-group">
            <label for="line_url">LINE URL</label>
            <input type="text" id="line_url" name="line_url" class="form-control" value="<?= $s('line_url') ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="youtube_url">YouTube URL</label>
        <input type="text" id="youtube_url" name="youtube_url" class="form-control" value="<?= $s('youtube_url') ?>">
    </div>

    <h2 style="font-size:1.15rem;margin:1.6rem 0 1rem">SEO / การแชร์โซเชียล</h2>
    <div class="form-group">
        <label for="meta_description">คำอธิบายเว็บไซต์ (Meta Description)</label>
        <input type="text" id="meta_description" name="meta_description" class="form-control" maxlength="300" value="<?= $s('meta_description') ?>">
        <div class="form-hint">ข้อความสรุปที่ขึ้นใน Google และตอนแชร์ลิงก์</div>
    </div>
    <div class="form-group">
        <label for="meta_keywords">คำค้น (Keywords)</label>
        <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" value="<?= $s('meta_keywords') ?>">
        <div class="form-hint">คั่นด้วยเครื่องหมายจุลภาค เช่น โรงเรียน, รับสมัคร, กิจกรรม</div>
    </div>
    <div class="form-group">
        <label for="og_image">รูปแชร์เริ่มต้น (OG Image)</label>
        <input type="file" id="og_image" name="og_image" class="form-control" accept="image/*">
        <div class="form-hint">รูปที่แสดงเมื่อแชร์หน้าเว็บลง Facebook/LINE (แนะนำ 1200×630 px)</div>
        <?php if (!empty($settings['og_image'])): ?>
            <img src="<?= Controller::e($base . $settings['og_image']) ?>" alt="" style="width:100%;max-width:360px;border-radius:10px;margin-top:.6rem">
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">บันทึกการตั้งค่า</button>
        <a href="<?= $base ?>/admin/dashboard" class="btn btn-ghost">กลับ</a>
    </div>
</form>
