<?php
/**
 * admin/users/index.php — ตารางผู้ใช้งาน
 * ตัวแปร: $users
 */
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Auth;
$base = config('app.url');
$roleLabels = ['super_admin' => 'ผู้ดูแลระบบ', 'editor' => 'ผู้แก้ไขเนื้อหา', 'teacher' => 'ครู'];
?>
<div class="panel">
    <div class="panel-head">
        <h2>ผู้ใช้งานทั้งหมด (<?= count($users) ?>)</h2>
        <a href="<?= $base ?>/admin/users/create" class="btn btn-primary btn-sm">+ เพิ่มผู้ใช้งาน</a>
    </div>
    <?php if (empty($users)): ?>
        <div class="empty-state"><div class="ico">👤</div><p>ยังไม่มีข้อมูลผู้ใช้งาน</p></div>
    <?php else: ?>
    <table class="data">
        <thead>
            <tr><th>ชื่อ</th><th>อีเมล</th><th>บทบาท</th><th>สถานะ</th><th style="text-align:right">จัดการ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <?php $isSelf = (int) $u['id'] === Auth::id(); ?>
            <tr>
                <td style="font-weight:600"><?= Controller::e($u['name']) ?><?= $isSelf ? ' <span class="badge">คุณ</span>' : '' ?></td>
                <td><?= Controller::e($u['email']) ?></td>
                <td><?= Controller::e($roleLabels[$u['role']] ?? $u['role']) ?></td>
                <td><?= (int) $u['is_active'] === 1
                        ? '<span class="badge badge-published">เปิดใช้งาน</span>'
                        : '<span class="badge badge-draft">ปิดใช้งาน</span>' ?>
                    <?php if (!empty($u['must_change_password'])): ?>
                        <span class="badge">รอเปลี่ยนรหัสผ่าน</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions-cell" style="justify-content:flex-end">
                        <a href="<?= $base ?>/admin/users/edit/<?= (int) $u['id'] ?>" class="btn btn-ghost btn-sm">แก้ไข</a>
                        <?php if (!$isSelf): ?>
                        <form method="post" action="<?= $base ?>/admin/users/toggle/<?= (int) $u['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-ghost btn-sm"><?= (int) $u['is_active'] === 1 ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?></button>
                        </form>
                        <form method="post" action="<?= $base ?>/admin/users/delete/<?= (int) $u['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="button" class="btn btn-danger btn-sm" data-confirm="ลบผู้ใช้งาน &quot;<?= Controller::e($u['name']) ?>&quot;?">ลบ</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
