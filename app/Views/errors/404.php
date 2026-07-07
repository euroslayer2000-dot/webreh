<?php
use App\Models\Setting;
$base = config('app.url');
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ไม่พบหน้า (404)</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;600;700&family=Sarabun:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>
    <div style="min-height:100vh;display:grid;place-items:center;text-align:center;padding:2rem">
        <div>
            <div style="font-size:6rem;font-weight:700;font-family:var(--font-display);
                        background:linear-gradient(135deg,var(--brand-red),var(--brand-gold));
                        -webkit-background-clip:text;background-clip:text;color:transparent">404</div>
            <h1>ไม่พบหน้าที่คุณต้องการ</h1>
            <p style="color:var(--ink-soft)">หน้านี้อาจถูกย้ายหรือลบไปแล้ว</p>
            <a href="<?= $base ?>/" class="btn btn-primary" style="margin-top:1rem">กลับหน้าแรก</a>
        </div>
    </div>
</body>
</html>
