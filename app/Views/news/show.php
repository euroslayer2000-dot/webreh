<?php
/**
 * news/show.php — หน้าอ่านข่าว
 * ตัวแปร: $news
 * หมายเหตุ: $news['content'] เป็น HTML จาก CKEditor จึงไม่ escape
 *           (ใน production ควรผ่าน HTMLPurifier ตอนบันทึกเพื่อกัน XSS)
 */
use App\Core\Controller;
$base = config('app.url');
$dateTh = date('d/m/', strtotime($news['published_at'])) . (date('Y', strtotime($news['published_at'])) + 543);
?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= $base ?>/">หน้าแรก</a> /
            <a href="<?= $base ?>/news">ข่าวประชาสัมพันธ์</a> /
            <?= Controller::e($news['category_name'] ?? 'ข่าว') ?>
        </div>
        <h1><?= Controller::e($news['title']) ?></h1>
        <p class="breadcrumb">📅 <?= $dateTh ?> · 👁 <?= number_format((int) $news['views']) ?> ครั้ง</p>
    </div>
</div>

<section class="section">
    <article class="container article">
        <?php if (!empty($news['cover_image'])): ?>
            <div class="cover">
                <img src="<?= Controller::e($base . $news['cover_image']) ?>" alt="<?= Controller::e($news['title']) ?>">
            </div>
        <?php endif; ?>

        <div class="article-body">
            <?= $news['content'] /* HTML content */ ?>
        </div>

        <div class="mt-3">
            <a href="<?= $base ?>/news" class="btn btn-ghost">← กลับไปหน้าข่าว</a>
        </div>
    </article>
</section>
