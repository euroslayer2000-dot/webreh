<?php
/**
 * personnel/index.php — บุคลากร จัดกลุ่มตามกลุ่มสาระ
 * ตัวแปร: $groups (dept => teachers[])
 */
use App\Core\Controller;
$base = config('app.url');
?>
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="<?= $base ?>/">หน้าแรก</a> / บุคลากร</div>
        <h1>บุคลากร</h1>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if (empty($groups)): ?>
            <div class="empty-state"><div class="ico">👥</div><p>ยังไม่มีข้อมูลบุคลากร</p></div>
        <?php else: ?>
            <?php foreach ($groups as $dept => $members): ?>
                <div class="dept-block reveal">
                    <h2 class="dept-title"><?= Controller::e((string) $dept) ?></h2>
                    <div class="people-grid">
                        <?php foreach ($members as $t): ?>
                            <div class="person-card">
                                <div class="person-photo">
                                    <?php if (!empty($t['photo'])): ?>
                                        <img src="<?= Controller::e($base . $t['photo']) ?>" alt="<?= Controller::e($t['name']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="person-initial"><?= mb_substr($t['name'], 0, 1) ?></div>
                                    <?php endif; ?>
                                </div>
                                <h3><?= Controller::e($t['name']) ?></h3>
                                <?php if (!empty($t['position'])): ?>
                                    <p class="person-role"><?= Controller::e($t['position']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($t['email'])): ?>
                                    <a class="person-mail" href="mailto:<?= Controller::e($t['email']) ?>">✉️ <?= Controller::e($t['email']) ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
