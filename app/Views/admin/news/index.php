<?php
/**
 * admin/news/index.php — ตารางจัดการข่าว
 * ตัวแปร: $result (items, total_pages, page)
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$items = $result['items'];
?>
<div class="panel">
    <div class="panel-head">
        <h2>รายการข่าวทั้งหมด (<?= number_format($result['total']) ?>)</h2>
        <a href="<?= $base ?>/admin/news/create" class="btn btn-primary btn-sm">+ เพิ่มข่าว</a>
    </div>

    <?php if (empty($items)): ?>
        <div class="empty-state"><div class="ico">📰</div><p>ยังไม่มีข่าว — เริ่มเพิ่มข่าวแรกของคุณ</p></div>
    <?php else: ?>
    <table class="data">
        <thead>
            <tr>
                <th>หัวข้อ</th>
                <th>หมวดหมู่</th>
                <th>สถานะ</th>
                <th>ยอดชม</th>
                <th>วันที่</th>
                <th style="text-align:right">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td style="font-weight:600;max-width:320px"><?= Controller::e($item['title']) ?></td>
                <td><?= Controller::e($item['category_name'] ?? '—') ?></td>
                <td>
                    <?php if ($item['status'] === 'published'): ?>
                        <span class="badge badge-published">เผยแพร่</span>
                    <?php else: ?>
                        <span class="badge badge-draft">ฉบับร่าง</span>
                    <?php endif; ?>
                </td>
                <td><?= number_format((int) $item['views']) ?></td>
                <td><?= date('d/m/', strtotime($item['created_at'])) . (date('Y', strtotime($item['created_at'])) + 543) ?></td>
                <td>
                    <div class="actions-cell" style="justify-content:flex-end">
                        <a href="<?= $base ?>/admin/news/edit/<?= (int) $item['id'] ?>" class="btn btn-ghost btn-sm">แก้ไข</a>
                        <form method="post" action="<?= $base ?>/admin/news/delete/<?= (int) $item['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="button" class="btn btn-danger btn-sm"
                                    data-confirm="ต้องการลบข่าว &quot;<?= Controller::e($item['title']) ?>&quot; ใช่หรือไม่?">ลบ</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php if ($result['total_pages'] > 1): ?>
<ul class="pagination">
    <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
        <li>
            <?php if ($i === $result['page']): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= $base ?>/admin/news?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        </li>
    <?php endfor; ?>
</ul>
<?php endif; ?>
