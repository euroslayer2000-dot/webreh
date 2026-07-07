<?php
/**
 * gallery/index.php — รายการอัลบั้มภาพ
 * ตัวแปร: $albums (แต่ละรายการมี image_count)
 */
use App\Core\Controller;
$base = config('app.url');
?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="<?= $base ?>/">หน้าแรก</a> / แกลเลอรี</div>
        <h1>แกลเลอรีภาพกิจกรรม</h1>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if (empty($albums)): ?>
            <div class="empty-state"><div class="ico">🖼️</div><p>ยังไม่มีอัลบั้มภาพ</p></div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($albums as $a): ?>
                    <a href="<?= $base ?>/gallery/<?= Controller::e($a['slug']) ?>" class="news-card reveal" style="color:inherit">
                        <div class="thumb">
                            <?php if (!empty($a['cover_image'])): ?>
                                <img src="<?= Controller::e($base . $a['cover_image']) ?>" alt="<?= Controller::e($a['title']) ?>" loading="lazy">
                            <?php else: ?><div class="placeholder">🖼️</div><?php endif; ?>
                            <span class="cat"><?= (int) $a['image_count'] ?> รูป</span>
                        </div>
                        <div class="body">
                            <h3><?= Controller::e($a['title']) ?></h3>
                            <?php if (!empty($a['description'])): ?>
                                <p class="excerpt"><?= Controller::e(mb_substr($a['description'], 0, 90)) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($a['event_date'])): ?>
                                <div class="meta"><span>📅 <?= date('d/m/', strtotime($a['event_date'])) . (date('Y', strtotime($a['event_date'])) + 543) ?></span></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
