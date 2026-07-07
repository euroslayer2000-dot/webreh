<?php
/**
 * admin/contacts/index.php — กล่องข้อความติดต่อ
 * ตัวแปร: $contacts
 */
use App\Core\Controller;
$base = config('app.url');
?>
<div class="panel">
    <div class="panel-head">
        <h2>ข้อความติดต่อ (<?= count($contacts) ?>)</h2>
    </div>

    <?php if (empty($contacts)): ?>
        <div class="empty-state"><div class="ico">✉️</div><p>ยังไม่มีข้อความ</p></div>
    <?php else: ?>
    <table class="data">
        <thead><tr><th>ผู้ส่ง</th><th>เรื่อง</th><th>ช่องทางติดต่อ</th><th>วันที่</th><th>สถานะ</th></tr></thead>
        <tbody>
            <?php foreach ($contacts as $c): ?>
            <tr class="<?= (int) $c['is_read'] === 0 ? 'unread' : '' ?>"
                style="cursor:pointer" onclick="location.href='<?= $base ?>/admin/contacts/<?= (int) $c['id'] ?>'">
                <td><?= Controller::e($c['name']) ?></td>
                <td><?= Controller::e((string) $c['subject']) ?: '(ไม่มีหัวข้อ)' ?></td>
                <td style="color:var(--ink-soft)"><?= Controller::e((string) ($c['email'] ?: $c['phone'])) ?: '—' ?></td>
                <td><?= date('d/m/', strtotime($c['created_at'])) . (date('Y', strtotime($c['created_at'])) + 543) ?></td>
                <td>
                    <?php if ((int) $c['is_read'] === 0): ?>
                        <span class="badge badge-published">ใหม่</span>
                    <?php else: ?>
                        <span class="badge badge-draft">อ่านแล้ว</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
