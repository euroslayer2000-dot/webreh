<?php
/**
 * downloads/index.php — เอกสารดาวน์โหลด จัดกลุ่มตามหมวด
 * ตัวแปร: $grouped (category => documents[])
 */
use App\Core\Controller;
$base = config('app.url');

// ฟังก์ชันแปลงขนาดไฟล์ให้อ่านง่าย
$fmtSize = function (?int $bytes): string {
    if (!$bytes) return '-';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $n = $bytes;
    while ($n >= 1024 && $i < 3) { $n /= 1024; $i++; }
    return round($n, 1) . ' ' . $units[$i];
};
$icon = fn(string $ext) => match (strtolower($ext)) {
    'pdf' => '📕', 'doc', 'docx' => '📘', 'xls', 'xlsx' => '📗', default => '📄'
};
?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="<?= $base ?>/">หน้าแรก</a> / ดาวน์โหลดเอกสาร</div>
        <h1>ดาวน์โหลดเอกสาร</h1>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if (empty($grouped)): ?>
            <div class="empty-state"><div class="ico">📄</div><p>ยังไม่มีเอกสารให้ดาวน์โหลด</p></div>
        <?php else: ?>
            <?php foreach ($grouped as $cat => $docs): ?>
                <div class="dept-block reveal">
                    <h2 class="dept-title"><?= Controller::e((string) $cat) ?></h2>
                    <div class="doc-list">
                        <?php foreach ($docs as $d): ?>
                            <a class="doc-item" href="<?= $base ?>/download/<?= (int) $d['id'] ?>">
                                <span class="doc-icon"><?= $icon((string) $d['file_ext']) ?></span>
                                <span class="doc-info">
                                    <span class="doc-name"><?= Controller::e($d['title']) ?></span>
                                    <span class="doc-meta">
                                        <?= strtoupper((string) $d['file_ext']) ?> · <?= $fmtSize($d['file_size']) ?>
                                        · ดาวน์โหลด <?= number_format((int) $d['download_count']) ?> ครั้ง
                                    </span>
                                </span>
                                <span class="doc-dl">⬇ ดาวน์โหลด</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
