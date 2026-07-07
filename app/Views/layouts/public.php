<?php
/**
 * layouts/public.php — โครงหน้าเว็บสาธารณะ
 * รับตัวแปร: $pageTitle, $content, และ (ทางเลือก) $metaDesc, $ogImage
 */
use App\Core\Controller;
use App\Models\Setting;

$siteName = Setting::get('site_name', 'โรงเรียนตัวอย่าง');
$title    = ($pageTitle ?? '') !== '' ? "{$pageTitle} — {$siteName}" : $siteName;
$desc     = $metaDesc ?? Setting::get('meta_description');
$keywords = Setting::get('meta_keywords');
$ogFinal  = !empty($ogImage) ? $ogImage : Setting::get('og_image');
$base     = config('app.url');
$flash    = Controller::getFlash();
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Controller::e($title) ?></title>
    <meta name="description" content="<?= Controller::e($desc) ?>">
    <?php if ($keywords !== ''): ?><meta name="keywords" content="<?= Controller::e($keywords) ?>"><?php endif; ?>

    <!-- Open Graph สำหรับแชร์ Facebook/Line -->
    <meta property="og:title" content="<?= Controller::e($title) ?>">
    <meta property="og:description" content="<?= Controller::e($desc) ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($ogFinal)): ?>
        <meta property="og:image" content="<?= Controller::e($base . $ogFinal) ?>">
    <?php endif; ?>
    <link rel="sitemap" type="application/xml" href="<?= $base ?>/sitemap.xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>
    <!-- Loading animation -->
    <div class="loader-overlay"><div class="loader-ring"></div></div>

    <!-- Toast -->
    <?php if ($flash): ?>
    <div class="toast-stack">
        <?php foreach ($flash as $f): ?>
            <div class="toast <?= Controller::e($f['type']) ?>">
                <?= $f['type'] === 'success' ? '✓' : '⚠' ?>
                <span><?= Controller::e($f['message']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Navbar -->
    <header class="site-header">
        <div class="container nav">
            <a href="<?= $base ?>/" class="brand">
                <span class="logo"><?= mb_substr($siteName, 0, 1) ?></span>
                <span class="name"><?= Controller::e($siteName) ?>
                    <small><?= Controller::e(Setting::get('site_tagline')) ?></small>
                </span>
            </a>
            <ul class="nav-links">
                <li><a href="<?= $base ?>/">หน้าแรก</a></li>
                <li><a href="<?= $base ?>/news">ข่าวสาร</a></li>
                <li><a href="<?= $base ?>/personnel">บุคลากร</a></li>
                <li><a href="<?= $base ?>/gallery">แกลเลอรี</a></li>
                <li><a href="<?= $base ?>/downloads">ดาวน์โหลด</a></li>
                <li><a href="<?= $base ?>/contact">ติดต่อ</a></li>
                <li><a href="<?= $base ?>/admin/login">เข้าสู่ระบบ</a></li>
            </ul>
            <div class="nav-actions">
                <button class="theme-toggle" aria-label="สลับโหมดกลางวัน/กลางคืน">🌙</button>
                <button class="nav-toggle" aria-label="เมนู">☰</button>
            </div>
        </div>
    </header>

    <main>
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer" id="contact">
        <div class="container footer-grid">
            <div>
                <h4><?= Controller::e($siteName) ?></h4>
                <p><?= Controller::e(Setting::get('contact_address')) ?></p>
                <div class="social-row">
                    <a href="<?= Controller::e(Setting::get('facebook_url', '#')) ?>" aria-label="Facebook">f</a>
                    <a href="#" aria-label="Line">L</a>
                    <a href="#" aria-label="YouTube">▶</a>
                </div>
            </div>
            <div>
                <h4>ลิงก์ด่วน</h4>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>/news">ข่าวประชาสัมพันธ์</a></li>
                    <li><a href="<?= $base ?>/#about">เกี่ยวกับโรงเรียน</a></li>
                    <li><a href="<?= $base ?>/#gallery">แกลเลอรี</a></li>
                </ul>
            </div>
            <div>
                <h4>บริการ</h4>
                <ul class="footer-links">
                    <li><a href="#">ดาวน์โหลดเอกสาร</a></li>
                    <li><a href="#">บุคลากร</a></li>
                    <li><a href="<?= $base ?>/admin/login">สำหรับเจ้าหน้าที่</a></li>
                </ul>
            </div>
            <div>
                <h4>ติดต่อเรา</h4>
                <ul class="footer-links">
                    <li>📞 <?= Controller::e(Setting::get('contact_phone')) ?></li>
                    <li>✉️ <?= Controller::e(Setting::get('contact_email')) ?></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom container">
            © <?= date('Y') + 543 ?> <?= Controller::e($siteName) ?> · สงวนลิขสิทธิ์
            · <a href="<?= $base ?>/privacy" style="color:inherit;text-decoration:underline">นโยบายความเป็นส่วนตัว</a>
        </div>
    </footer>

    <!-- Cookie consent (PDPA) -->
    <div class="cookie-banner" id="cookieBanner" hidden>
        <p>เว็บไซต์นี้ใช้คุกกี้ที่จำเป็นเพื่อให้ใช้งานได้อย่างเหมาะสม
            อ่านเพิ่มเติมที่ <a href="<?= $base ?>/privacy">นโยบายความเป็นส่วนตัว</a></p>
        <button class="btn btn-primary btn-sm" id="cookieAccept">ยอมรับ</button>
    </div>

    <script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
