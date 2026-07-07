<?php
/**
 * admin/downloads/index.php — รายการเอกสาร
 * ตัวแปร: $documents
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
$fmtSize = function (?int $b): string {
    if (!$b) return '-';
    $u = ['B','KB','MB','GB']; $i = 0; $n = $b;
    while ($n >= 1024 && $i < 3) { $n /= 1024; $i++; }
    return round($n, 1) . ' ' . $u[$i];
};
?>
<div class="panel">
    <div class="panel-head">
        <h2>เอกสารทั้งหมด (<?= count($documents) ?>)</h2>
        <a href="<?= $base ?>/admin/downloads/create" class="btn btn-primary btn-sm">+ เพิ่มเอกสาร</a>
    </div>
    <?php if (empty($documents)): ?>
        <div class="empty-state"><div class="ico">📄</div><p>ยังไม่มีเอกสาร</p></div>
    <?php else: ?>
    <table class="data">
        <thead><tr><th>ชื่อเอกสาร</th><th>หมวดหมู่</th><th>ชนิด</th><th>ขนาด</th><th>ดาวน์โหลด</th><th style="text-align:right">จัดการ</th></tr></thead>
        <tbody>
            <?php foreach ($documents as $d): ?>
            <tr>
                <td style="font-weight:600"><?= Controller::e($d['title']) ?></td>
                <td><?= Controller::e($d['category_name'] ?? '—') ?></td>
                <td><?= strtoupper((string) $d['file_ext']) ?></td>
                <td><?= $fmtSize($d['file_size']) ?></td>
                <td><?= number_format((int) $d['download_count']) ?></td>
                <td>
                    <div class="actions-cell" style="justify-content:flex-end">
                        <a href="<?= $base ?>/download/<?= (int) $d['id'] ?>" class="btn btn-ghost btn-sm" target="_blank">ดู</a>
                        <form method="post" action="<?= $base ?>/admin/downloads/delete/<?= (int) $d['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="button" class="btn btn-danger btn-sm" data-confirm="ลบ &quot;<?= Controller::e($d['title']) ?>&quot;?">ลบ</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
