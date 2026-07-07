<?php

/**
 * home/index.php — หน้าแรก
 * ตัวแปร: $banners, $latestNews, $latestAlbums, $stats
 */

use App\Core\Controller;
use App\Models\Setting;

$base = config('app.url');
?>

<?php if (!empty($banners)): ?>
    <!-- ============ BANNER SLIDER ============ -->
    <section class="banner-slider" id="bannerSlider">
        <div class="banner-track">
            <?php foreach ($banners as $b): ?>
                <?php $inner = '<img src="' . Controller::e($base . $b['image_path']) . '" alt="' . Controller::e((string) $b['title']) . '" loading="lazy">'; ?>
                <div class="banner-slide">
                    <?php if (!empty($b['link_url'])): ?>
                        <a href="<?= Controller::e($b['link_url']) ?>"><?= $inner ?></a>
                    <?php else: ?>
                        <?= $inner ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($banners) > 1): ?>
            <button class="banner-arrow prev" aria-label="ก่อนหน้า">‹</button>
            <button class="banner-arrow next" aria-label="ถัดไป">›</button>
            <div class="banner-dots"></div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="container hero-grid">
        <div class="reveal in">
            <span class="eyebrow">ยินดีต้อนรับ EMS โรงพยาบาลร้อยเอ็ด </span>
            <h1><?= Controller::e(Setting::get('site_name')) ?><br>
                <span class="accent">มุ่งสู่ความเป็นเลิศ</span>
            </h1>
            <p class="lead"><?= Controller::e(Setting::get('site_tagline', 'พัฒนาผู้เรียนอย่างรอบด้าน สู่พลเมืองคุณภาพของสังคม')) ?></p>
            <div class="hero-cta">
                <a href="<?= $base ?>/news" class="btn btn-primary">ข่าวประชาสัมพันธ์ →</a>
                <a href="#about" class="btn btn-ghost">รู้จักโรงเรียน</a>
            </div>
        </div>
        <div class="hero-visual reveal in">
            <div class="hero-emblem">🏫</div>
            <div class="glass">
                <strong><?= Controller::e(Setting::get('site_name')) ?></strong><br>
                <span style="opacity:.9;font-size:.9rem">แหล่งเรียนรู้คู่ชุมชน</span>
            </div>
        </div>
    </div>
</section>

<!-- ============ สถิติ (Animated Counter) ============ -->
<section class="stats">
    <div class="container stats-grid">
        <div class="stat">
            <div class="num" data-target="<?= (int) $stats['students'] ?>">0</div>
            <div class="label">นักเรียน</div>
        </div>
        <div class="stat">
            <div class="num" data-target="<?= (int) $stats['teachers'] ?>">0</div>
            <div class="label">ครูและบุคลากร</div>
        </div>
        <div class="stat">
            <div class="num" data-target="<?= (int) $stats['news'] ?>">0</div>
            <div class="label">ข่าวประชาสัมพันธ์</div>
        </div>
        <div class="stat">
            <div class="num" data-target="<?= (int) $stats['years'] ?>">0</div>
            <div class="label">ปีแห่งการก่อตั้ง</div>
        </div>
    </div>
</section>

<!-- ============ ข่าวล่าสุด ============ -->
<section class="section" id="news">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">อัปเดตล่าสุด</span>
            <h2>ข่าวประชาสัมพันธ์</h2>
            <p>ติดตามข่าวสาร กิจกรรม และประกาศต่าง ๆ ของโรงเรียน</p>
        </div>

        <?php if (empty($latestNews)): ?>
            <div class="empty-state">
                <div class="ico">📰</div>
                <p>ยังไม่มีข่าวในขณะนี้</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($latestNews as $item): ?>
                    <article class="news-card reveal">
                        <div class="thumb">
                            <?php if (!empty($item['cover_image'])): ?>
                                <img src="<?= Controller::e($base . $item['cover_image']) ?>"
                                    alt="<?= Controller::e($item['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="placeholder">📷</div>
                            <?php endif; ?>
                            <?php if (!empty($item['category_name'])): ?>
                                <span class="cat"><?= Controller::e($item['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="body">
                            <h3><a href="<?= $base ?>/news/<?= Controller::e($item['slug']) ?>"><?= Controller::e($item['title']) ?></a></h3>
                            <p class="excerpt"><?= Controller::e(mb_substr((string) $item['excerpt'], 0, 110)) ?></p>
                            <div class="meta">
                                <span>📅 <?= date('d/m/', strtotime($item['published_at'])) . (date('Y', strtotime($item['published_at'])) + 543) ?></span>
                                <span>👁 <?= number_format((int) $item['views']) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="center mt-3">
                <a href="<?= $base ?>/news" class="btn btn-gold">ดูข่าวทั้งหมด</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ แกลเลอรีล่าสุด ============ -->
<?php if (!empty($latestAlbums)): ?>
    <section class="section" style="background:var(--surface-2)" id="gallery">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">ภาพกิจกรรม</span>
                <h2>แกลเลอรีล่าสุด</h2>
            </div>
            <div class="card-grid">
                <?php foreach ($latestAlbums as $a): ?>
                    <a href="<?= $base ?>/gallery/<?= Controller::e($a['slug']) ?>" class="news-card reveal" style="color:inherit">
                        <div class="thumb">
                            <?php if (!empty($a['cover_image'])): ?>
                                <img src="<?= Controller::e($base . $a['cover_image']) ?>" alt="<?= Controller::e($a['title']) ?>" loading="lazy">
                            <?php else: ?><div class="placeholder">🖼️</div><?php endif; ?>
                            <?php if (isset($a['image_count'])): ?><span class="cat"><?= (int) $a['image_count'] ?> รูป</span><?php endif; ?>
                        </div>
                        <div class="body">
                            <h3><?= Controller::e($a['title']) ?></h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="center mt-3"><a href="<?= $base ?>/gallery" class="btn btn-gold">ดูแกลเลอรีทั้งหมด</a></div>
        </div>
    </section>
<?php endif; ?>

<!-- ============ Quick Links ============ -->
<section class="section" id="about">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">บริการออนไลน์</span>
            <h2>ลิงก์ด่วน</h2>
        </div>
        <div class="quicklinks">
            <a href="<?= $base ?>/news" class="quicklink reveal">
                <div class="ico">📢</div><span>ข่าวสาร</span>
            </a>
            <a href="<?= $base ?>/personnel" class="quicklink reveal">
                <div class="ico">👨‍🏫</div><span>บุคลากร</span>
            </a>
            <a href="<?= $base ?>/downloads" class="quicklink reveal">
                <div class="ico">📄</div><span>ดาวน์โหลดเอกสาร</span>
            </a>
            <a href="<?= $base ?>/gallery" class="quicklink reveal">
                <div class="ico">🖼️</div><span>แกลเลอรี</span>
            </a>
        </div>
    </div>
</section>