<?php
/**
 * contact/index.php — ฟอร์มติดต่อ + ข้อมูลติดต่อ + PDPA consent
 */
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Setting;
$base = config('app.url');
?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="<?= $base ?>/">หน้าแรก</a> / ติดต่อเรา</div>
        <h1>ติดต่อเรา</h1>
    </div>
</div>

<section class="section">
    <div class="container contact-layout">
        <!-- ข้อมูลติดต่อ -->
        <div class="contact-info">
            <h2>ช่องทางติดต่อ</h2>
            <ul class="contact-list">
                <li><span class="ci-ico">📍</span> <?= Controller::e(Setting::get('contact_address')) ?></li>
                <li><span class="ci-ico">📞</span> <?= Controller::e(Setting::get('contact_phone')) ?></li>
                <li><span class="ci-ico">✉️</span> <?= Controller::e(Setting::get('contact_email')) ?></li>
            </ul>
            <div class="map-embed">
                <iframe
                    src="https://www.google.com/maps?q=<?= urlencode(Setting::get('contact_address')) ?>&output=embed"
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    title="แผนที่หน่วยงาน"></iframe>
            </div>
        </div>

        <!-- ฟอร์ม -->
        <div class="contact-form-wrap">
            <h2>ส่งข้อความถึงเรา</h2>
            <form method="post" action="<?= $base ?>/contact" class="contact-form">
                <?= Csrf::field() ?>
                <!-- honeypot กันบอท (ซ่อนจากผู้ใช้จริง) -->
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label>อย่ากรอกช่องนี้ <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="form-group">
                    <label for="name">ชื่อ-นามสกุล *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= Controller::old('name') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">อีเมล</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= Controller::old('email') ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">เบอร์โทร</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?= Controller::old('phone') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="subject">เรื่อง</label>
                    <input type="text" id="subject" name="subject" class="form-control" value="<?= Controller::old('subject') ?>">
                </div>
                <div class="form-group">
                    <label for="message">ข้อความ *</label>
                    <textarea id="message" name="message" class="form-control" style="min-height:130px" required><?= Controller::old('message') ?></textarea>
                </div>

                <!-- PDPA consent -->
                <label class="consent-box">
                    <input type="checkbox" name="consent" value="1" required>
                    <span>ข้าพเจ้ายินยอมให้หน่วยงานจัดเก็บและใช้ข้อมูลส่วนบุคคลข้างต้น
                    เพื่อวัตถุประสงค์ในการติดต่อกลับ ตามนโยบายความเป็นส่วนตัว (PDPA)</span>
                </label>

                <button type="submit" class="btn btn-primary">ส่งข้อความ</button>
            </form>
        </div>
    </div>
</section>
<?php Controller::clearOld(); ?>
