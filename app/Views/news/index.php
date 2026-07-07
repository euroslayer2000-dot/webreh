<?php
/**
 * news/index.php — รายการข่าวสาธารณะ พร้อมค้นหาและแบ่งหน้า
 * ตัวแปร: $result (items,total_pages,page), $search
 */
use App\Core\Controller;
$base = config('app.url');
$items = $result['items'];
$page = $result['page'];
$totalPages = $result['total_pages'];
?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="<?= $base ?>/">หน้าแรก</a> / ข่าวประชาสัมพันธ์</div>
        <h1>ข่าวประชาสัมพันธ์</h1>
        <form class="search-bar" method="get" action="<?= $base ?>/news">
            <input type="text" name="q" placeholder="ค้นหาข่าว..." value="<?= Controller::e($search) ?>">
            <button class="btn btn-primary" type="submit">ค้นหา</button>
        </form>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="ico">🔍</div>
                <p><?= $search !== '' ? 'ไม่พบข่าวที่ตรงกับ "' . Controller::e($search) . '"' : 'ยังไม่มีข่าว' ?></p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($items as $item): ?>
                    <article class="news-card reveal">
                        <div class="thumb">
                            <?php if (!empty($item['cover_image'])): ?>
                                <img src="<?= Controller::e($base . $item['cover_image']) ?>" alt="<?= Controller::e($item['title']) ?>" loading="lazy">
                            <?php else: ?><div class="placeholder">📷</div><?php endif; ?>
                            <?php if (!empty($item['category_name'])): ?>
                                <span class="cat"><?= Controller::e($item['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="body">
                            <h3><a href="<?= $base ?>/news/<?= Controller::e($item['slug']) ?>"><?= Controller::e($item['title']) ?></a></h3>
                            <p class="excerpt"><?= Controller::e(mb_substr((string) $item['excerpt'], 0, 120)) ?></p>
                            <div class="meta">
                                <span>📅 <?= date('d/m/', strtotime($item['published_at'])) . (date('Y', strtotime($item['published_at'])) + 543) ?></span>
                                <span>👁 <?= number_format((int) $item['views']) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <?php
                $q = $search !== '' ? '&q=' . urlencode($search) : '';
                for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li>
                        <?php if ($i === $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= $base ?>/news?page=<?= $i . $q ?>"><?= $i ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
