<?php
/**
 * admin/contacts/show.php — อ่านข้อความติดต่อ
 * ตัวแปร: $contact
 */
use App\Core\Controller;
use App\Core\Csrf;
$base = config('app.url');
?>
<div class="msg-view">
    <div class="msg-row"><span class="msg-label">ผู้ส่ง</span><span><?= Controller::e($contact['name']) ?></span></div>
    <div class="msg-row"><span class="msg-label">อีเมล</span><span><?= Controller::e((string) $contact['email']) ?: '—' ?></span></div>
    <div class="msg-row"><span class="msg-label">เบอร์โทร</span><span><?= Controller::e((string) $contact['phone']) ?: '—' ?></span></div>
    <div class="msg-row"><span class="msg-label">เรื่อง</span><span><?= Controller::e((string) $contact['subject']) ?: '(ไม่มีหัวข้อ)' ?></span></div>
    <div class="msg-row"><span class="msg-label">ส่งเมื่อ</span>
        <span><?= date('d/m/', strtotime($contact['created_at'])) . (date('Y', strtotime($contact['created_at'])) + 543) . ' ' . date('H:i', strtotime($contact['created_at'])) ?> น.</span>
    </div>
    <div class="msg-row"><span class="msg-label">PDPA</span>
        <span>
            <?php if (!empty($contact['consent_at'])): ?>
                <span class="consent-pill">✓ ยินยอมให้เก็บข้อมูล</span>
                <small style="color:var(--ink-soft)"> (<?= Controller::e((string) $contact['ip_address']) ?>)</small>
            <?php else: ?>
                <span style="color:var(--ink-soft)">ไม่มีบันทึกการยินยอม</span>
            <?php endif; ?>
        </span>
    </div>

    <div class="msg-body"><?= Controller::e($contact['message']) ?></div>

    <div class="form-actions">
        <?php if (!empty($contact['email'])): ?>
            <a href="mailto:<?= Controller::e($contact['email']) ?>?subject=ตอบกลับ: <?= rawurlencode((string) $contact['subject']) ?>" class="btn btn-primary">✉️ ตอบกลับทางอีเมล</a>
        <?php endif; ?>
        <a href="<?= $base ?>/admin/contacts" class="btn btn-ghost">← กลับกล่องข้อความ</a>
        <form method="post" action="<?= $base ?>/admin/contacts/delete/<?= (int) $contact['id'] ?>" style="margin-left:auto">
            <?= Csrf::field() ?>
            <button type="button" class="btn btn-danger" data-confirm="ลบข้อความนี้?">ลบ</button>
        </form>
    </div>
</div>
