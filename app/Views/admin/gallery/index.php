<?php
/**
 * admin/gallery/index.php — รายการอัลบั้ม
 * ตัวแปร: $albums
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
?>
<div class="panel">
    <div class="panel-head">
        <h2>อัลบั้มทั้งหมด (<?= count($albums) ?>)</h2>
        <a href="<?= $base ?>/admin/gallery/create" class="btn btn-primary btn-sm">+ สร้างอัลบั้ม</a>
    </div>
    <?php if (empty($albums)): ?>
        <div class="empty-state"><div class="ico">🖼️</div><p>ยังไม่มีอัลบั้ม</p></div>
    <?php else: ?>
    <table class="data">
        <thead><tr><th>ชื่ออัลบั้ม</th><th>จำนวนรูป</th><th>วันที่จัดกิจกรรม</th><th style="text-align:right">จัดการ</th></tr></thead>
        <tbody>
            <?php foreach ($albums as $a): ?>
            <tr>
                <td style="font-weight:600"><?= Controller::e($a['title']) ?></td>
                <td><?= (int) $a['image_count'] ?> รูป</td>
                <td><?= !empty($a['event_date']) ? date('d/m/', strtotime($a['event_date'])) . (date('Y', strtotime($a['event_date'])) + 543) : '—' ?></td>
                <td>
                    <div class="actions-cell" style="justify-content:flex-end">
                        <a href="<?= $base ?>/admin/gallery/edit/<?= (int) $a['id'] ?>" class="btn btn-ghost btn-sm">จัดการรูป</a>
                        <form method="post" action="<?= $base ?>/admin/gallery/delete/<?= (int) $a['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="button" class="btn btn-danger btn-sm" data-confirm="ลบอัลบั้ม &quot;<?= Controller::e($a['title']) ?>&quot; และรูปทั้งหมด?">ลบ</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
