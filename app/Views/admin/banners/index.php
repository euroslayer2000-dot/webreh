<?php
/**
 * admin/banners/index.php — รายการแบนเนอร์
 * ตัวแปร: $banners
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
?>
<div class="panel">
    <div class="panel-head">
        <h2>แบนเนอร์หน้าแรก (<?= count($banners) ?>)</h2>
        <a href="<?= $base ?>/admin/banners/create" class="btn btn-primary btn-sm">+ เพิ่มแบนเนอร์</a>
    </div>

    <?php if (empty($banners)): ?>
        <div class="empty-state"><div class="ico">🎞️</div><p>ยังไม่มีแบนเนอร์ — เพิ่มแบนเนอร์เพื่อแสดงสไลด์บนหน้าแรก</p></div>
    <?php else: ?>
    <table class="data">
        <thead><tr><th>รูป</th><th>หัวข้อ</th><th>ลิงก์</th><th>ลำดับ</th><th>สถานะ</th><th style="text-align:right">จัดการ</th></tr></thead>
        <tbody>
            <?php foreach ($banners as $b): ?>
            <tr>
                <td><img src="<?= Controller::e($base . $b['image_path']) ?>" alt="" style="width:120px;height:48px;object-fit:cover;border-radius:8px"></td>
                <td style="font-weight:600"><?= Controller::e((string) $b['title']) ?: '—' ?></td>
                <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--ink-soft)"><?= Controller::e((string) $b['link_url']) ?: '—' ?></td>
                <td><?= (int) $b['sort_order'] ?></td>
                <td>
                    <?php if ((int) $b['is_active'] === 1): ?>
                        <span class="badge badge-published">แสดง</span>
                    <?php else: ?>
                        <span class="badge badge-draft">ซ่อน</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions-cell" style="justify-content:flex-end">
                        <a href="<?= $base ?>/admin/banners/edit/<?= (int) $b['id'] ?>" class="btn btn-ghost btn-sm">แก้ไข</a>
                        <form method="post" action="<?= $base ?>/admin/banners/delete/<?= (int) $b['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="button" class="btn btn-danger btn-sm" data-confirm="ลบแบนเนอร์นี้?">ลบ</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
