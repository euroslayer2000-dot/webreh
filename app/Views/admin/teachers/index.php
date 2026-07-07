<?php
/**
 * admin/teachers/index.php — ตารางบุคลากร
 * ตัวแปร: $teachers
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
?>
<div class="panel">
    <div class="panel-head">
        <h2>บุคลากรทั้งหมด (<?= count($teachers) ?>)</h2>
        <a href="<?= $base ?>/admin/teachers/create" class="btn btn-primary btn-sm">+ เพิ่มบุคลากร</a>
    </div>
    <?php if (empty($teachers)): ?>
        <div class="empty-state"><div class="ico">👥</div><p>ยังไม่มีข้อมูลบุคลากร</p></div>
    <?php else: ?>
    <table class="data">
        <thead>
            <tr><th>ลำดับ</th><th>ชื่อ</th><th>ตำแหน่ง</th><th>กลุ่มสาระ</th><th>สถานะ</th><th style="text-align:right">จัดการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($teachers as $t): ?>
            <tr>
                <td><?= (int) $t['sort_order'] ?></td>
                <td style="font-weight:600">
                    <div style="display:flex;align-items:center;gap:.6rem">
                        <?php if (!empty($t['photo'])): ?>
                            <img src="<?= Controller::e($base . $t['photo']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover">
                        <?php else: ?>
                            <span class="avatar" style="width:36px;height:36px"><?= mb_substr($t['name'],0,1) ?></span>
                        <?php endif; ?>
                        <?= Controller::e($t['name']) ?>
                    </div>
                </td>
                <td><?= Controller::e($t['position'] ?? '—') ?></td>
                <td><?= Controller::e($t['department'] ?? '—') ?></td>
                <td><?= (int) $t['is_active'] === 1
                        ? '<span class="badge badge-published">แสดง</span>'
                        : '<span class="badge badge-draft">ซ่อน</span>' ?></td>
                <td>
                    <div class="actions-cell" style="justify-content:flex-end">
                        <a href="<?= $base ?>/admin/teachers/edit/<?= (int) $t['id'] ?>" class="btn btn-ghost btn-sm">แก้ไข</a>
                        <form method="post" action="<?= $base ?>/admin/teachers/delete/<?= (int) $t['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="button" class="btn btn-danger btn-sm" data-confirm="ลบ &quot;<?= Controller::e($t['name']) ?>&quot;?">ลบ</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
