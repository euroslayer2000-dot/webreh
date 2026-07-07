<?php
/**
 * gallery/show.php — ดูรูปในอัลบั้ม พร้อม lightbox (เปิดดูรูปใหญ่)
 * ตัวแปร: $album, $images
 */
use App\Core\Controller;
$base = config('app.url');
?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= $base ?>/">หน้าแรก</a> /
            <a href="<?= $base ?>/gallery">แกลเลอรี</a> /
            <?= Controller::e($album['title']) ?>
        </div>
        <h1><?= Controller::e($album['title']) ?></h1>
        <?php if (!empty($album['description'])): ?>
            <p class="breadcrumb"><?= Controller::e($album['description']) ?></p>
        <?php endif; ?>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if (empty($images)): ?>
            <div class="empty-state"><div class="ico">📷</div><p>อัลบั้มนี้ยังไม่มีรูป</p></div>
        <?php else: ?>
            <div class="photo-grid">
                <?php foreach ($images as $img): ?>
                    <button class="photo-item reveal" data-full="<?= Controller::e($base . $img['image_path']) ?>"
                            data-caption="<?= Controller::e((string) $img['caption']) ?>">
                        <img src="<?= Controller::e($base . $img['image_path']) ?>" alt="<?= Controller::e((string) $img['caption']) ?>" loading="lazy">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="mt-3"><a href="<?= $base ?>/gallery" class="btn btn-ghost">← กลับไปหน้าแกลเลอรี</a></div>
    </div>
</section>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" aria-hidden="true">
    <button class="lightbox-close" aria-label="ปิด">✕</button>
    <button class="lightbox-nav prev" aria-label="ก่อนหน้า">‹</button>
    <figure class="lightbox-figure">
        <img id="lightbox-img" src="" alt="">
        <figcaption id="lightbox-caption"></figcaption>
    </figure>
    <button class="lightbox-nav next" aria-label="ถัดไป">›</button>
</div>

<script>
(function () {
    const items = Array.from(document.querySelectorAll('.photo-item'));
    const box = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    const cap = document.getElementById('lightbox-caption');
    let idx = 0;
    function open(i) {
        idx = i;
        const el = items[i];
        img.src = el.dataset.full;
        cap.textContent = el.dataset.caption || '';
        box.classList.add('open');
        box.setAttribute('aria-hidden', 'false');
    }
    function close() { box.classList.remove('open'); box.setAttribute('aria-hidden', 'true'); }
    function step(d) { open((idx + d + items.length) % items.length); }
    items.forEach((el, i) => el.addEventListener('click', () => open(i)));
    box.querySelector('.lightbox-close').addEventListener('click', close);
    box.querySelector('.prev').addEventListener('click', e => { e.stopPropagation(); step(-1); });
    box.querySelector('.next').addEventListener('click', e => { e.stopPropagation(); step(1); });
    box.addEventListener('click', e => { if (e.target === box) close(); });
    document.addEventListener('keydown', e => {
        if (!box.classList.contains('open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') step(-1);
        if (e.key === 'ArrowRight') step(1);
    });
})();
</script>
