<?php
/**
 * admin/dashboard/index.php — แดชบอร์ด
 * ตัวแปร: $stats (total, published, draft)
 */
use App\Core\Controller;
$base = config('app.url');
?>
<div class="kpi-grid">
    <div class="kpi">
        <div class="k-label">ข่าวทั้งหมด</div>
        <div class="k-num"><span class="num" data-target="<?= (int) $stats['total'] ?>">0</span></div>
    </div>
    <div class="kpi">
        <div class="k-label">เผยแพร่แล้ว</div>
        <div class="k-num"><span class="num" data-target="<?= (int) $stats['published'] ?>">0</span></div>
    </div>
    <div class="kpi">
        <div class="k-label">ฉบับร่าง</div>
        <div class="k-num"><span class="num" data-target="<?= (int) $stats['draft'] ?>">0</span></div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>เริ่มต้นใช้งาน</h2>
    </div>
    <div style="padding:1.4rem">
        <p>ยินดีต้อนรับสู่ระบบจัดการเว็บไซต์หน่วยบริการการแพทย์ฉุกเฉิน คุณสามารถเริ่มต้นได้จาก:</p>
        <div style="display:flex;gap:.8rem;flex-wrap:wrap;margin-top:1rem">
            <a href="<?= $base ?>/admin/news/create" class="btn btn-primary">+ เพิ่มข่าวใหม่</a>
            <a href="<?= $base ?>/admin/news" class="btn btn-ghost">จัดการข่าวทั้งหมด</a>
            <a href="<?= $base ?>/" target="_blank" class="btn btn-gold">ดูหน้าเว็บจริง</a>
        </div>
    </div>
</div>
